<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ButtonTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tier 2: Requisições não autenticadas para rotas de botões retornam 401.
     */
    public function test_unauthenticated_button_requests_return_401(): void
    {
        $this->getJson('/api/v1/buttons')->assertStatus(401);
        $this->postJson('/api/v1/buttons', ['title' => 'Link', 'url' => 'https://test.com'])->assertStatus(401);
        $this->putJson('/api/v1/buttons/1', ['title' => 'Link'])->assertStatus(401);
        $this->deleteJson('/api/v1/buttons/1')->assertStatus(401);
        $this->postJson('/api/v1/buttons/reorder', ['ordered_ids' => [1, 2]])->assertStatus(401);
    }

    /**
     * Tier 1: Listagem de botões retorna apenas os botões do artista logado ordenados por order_index ASC.
     */
    public function test_can_list_buttons_ordered_by_order_index_asc(): void
    {
        $artistA = Artist::factory()->create();
        $artistB = Artist::factory()->create();

        // Botões do Artista A em ordem não sequencial de criação
        $btn3 = Button::factory()->create(['artist_id' => $artistA->id, 'order_index' => 2, 'title' => 'Terceiro']);
        $btn1 = Button::factory()->create(['artist_id' => $artistA->id, 'order_index' => 0, 'title' => 'Primeiro']);
        $btn2 = Button::factory()->create(['artist_id' => $artistA->id, 'order_index' => 1, 'title' => 'Segundo']);

        // Botão de outro artista
        Button::factory()->create(['artist_id' => $artistB->id, 'title' => 'Botão do Artista B']);

        $response = $this->actingAs($artistA, 'sanctum')
            ->getJson('/api/v1/buttons');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data'])
            ->assertJson([
                'status' => 'success',
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
        $this->assertEquals('Primeiro', $data[0]['title']);
        $this->assertEquals('Segundo', $data[1]['title']);
        $this->assertEquals('Terceiro', $data[2]['title']);
    }

    /**
     * Tier 1: Criação de botão padrão com auto-incremento de order_index.
     */
    public function test_can_create_standard_button_and_increments_order_index(): void
    {
        $artist = Artist::factory()->create();

        Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 0]);
        Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 1]);

        $payload = [
            'title' => 'Ouça no Spotify',
            'url' => 'https://open.spotify.com/artist/12345',
            'platform' => 'spotify',
            'type' => 'standard',
        ];

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Botão criado com sucesso.',
                'data' => [
                    'title' => 'Ouça no Spotify',
                    'order_index' => 2,
                    'is_active' => true,
                ],
            ]);

        $this->assertDatabaseHas('buttons', [
            'artist_id' => $artist->id,
            'title' => 'Ouça no Spotify',
            'order_index' => 2,
        ]);
    }

    /**
     * Tier 1: Suporte aos formatos icon_pill e card_featured.
     */
    public function test_can_create_icon_pill_and_card_featured_buttons(): void
    {
        $artist = Artist::factory()->create();

        // 1. Cria icon_pill
        $pillResponse = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => 'Instagram',
                'url' => 'https://instagram.com/pinho',
                'platform' => 'instagram',
                'type' => 'icon_pill',
            ]);
        $pillResponse->assertStatus(201)
            ->assertJson(['data' => ['type' => 'icon_pill']]);

        // 2. Cria card_featured com cores personalizadas
        $cardResponse = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => 'Novo Álbum em Destaque',
                'url' => 'https://album.art',
                'type' => 'card_featured',
                'button_color' => '#FF0055',
                'text_color' => '#FFFFFF',
            ]);
        $cardResponse->assertStatus(201)
            ->assertJson([
                'data' => [
                    'type' => 'card_featured',
                    'button_color' => '#FF0055',
                    'text_color' => '#FFFFFF',
                ],
            ]);
    }

    /**
     * Tier 2: Criação de botão falha quando campos obrigatórios (title ou url) estão ausentes.
     */
    public function test_create_button_validation_fails_when_fields_missing(): void
    {
        $artist = Artist::factory()->create();

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => '',
                'url' => 'not-a-valid-url',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'url']);
    }

    /**
     * Tier 1: Atualização de botão existente e alteração de estado is_active.
     */
    public function test_can_update_button_fields_and_active_state(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Título Antigo',
            'is_active' => true,
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->putJson("/api/v1/buttons/{$button->id}", [
                'title' => 'Título Atualizado',
                'is_active' => false,
                'button_color' => '#123456',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Botão atualizado com sucesso.',
                'data' => [
                    'title' => 'Título Atualizado',
                    'is_active' => false,
                    'button_color' => '#123456',
                ],
            ]);

        $button->refresh();
        $this->assertEquals('Título Atualizado', $button->title);
        $this->assertFalse($button->is_active);
    }

    /**
     * Tier 1: Exclusão de botão (DELETE /api/v1/buttons/{id}).
     */
    public function test_can_delete_button(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->deleteJson("/api/v1/buttons/{$button->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Botão removido com sucesso.',
            ]);

        $this->assertDatabaseMissing('buttons', [
            'id' => $button->id,
        ]);
    }

    /**
     * Tier 1: Reordenação atômica de botões via Drag & Drop (POST /api/v1/buttons/reorder).
     */
    public function test_can_reorder_buttons_atomically(): void
    {
        $artist = Artist::factory()->create();

        $b1 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 0]);
        $b2 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 1]);
        $b3 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 2]);

        // Inverte a ordem: [b3, b1, b2]
        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', [
                'ordered_ids' => [$b3->id, $b1->id, $b2->id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Ordem dos botões atualizada com sucesso.',
            ]);

        $b3->refresh();
        $b1->refresh();
        $b2->refresh();

        $this->assertEquals(0, $b3->order_index);
        $this->assertEquals(1, $b1->order_index);
        $this->assertEquals(2, $b2->order_index);
    }

    /**
     * Tier 2: Reordenação com array vazio ou IDs duplicados é rejeitada (422).
     */
    public function test_reorder_validation_fails_on_empty_or_duplicate_ids(): void
    {
        $artist = Artist::factory()->create();

        // Vazio
        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', ['ordered_ids' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ordered_ids']);

        // Duplicados
        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', ['ordered_ids' => [1, 1, 2]])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ordered_ids.0']);
    }

    /**
     * Tier 2 (Segurança): Artista A não pode atualizar botão pertencente ao Artista B (retorna 404).
     */
    public function test_cross_account_cannot_update_another_artists_button(): void
    {
        $artistA = Artist::factory()->create();
        $artistB = Artist::factory()->create();

        $buttonB = Button::factory()->create([
            'artist_id' => $artistB->id,
            'title' => 'Botão do Artista B Original',
        ]);

        $response = $this->actingAs($artistA, 'sanctum')
            ->putJson("/api/v1/buttons/{$buttonB->id}", [
                'title' => 'Tentativa de Hack',
            ]);

        $response->assertStatus(404);

        $buttonB->refresh();
        $this->assertEquals('Botão do Artista B Original', $buttonB->title);
    }

    /**
     * Tier 2 (Segurança): Artista A não pode deletar botão pertencente ao Artista B (retorna 404).
     */
    public function test_cross_account_cannot_delete_another_artists_button(): void
    {
        $artistA = Artist::factory()->create();
        $artistB = Artist::factory()->create();

        $buttonB = Button::factory()->create([
            'artist_id' => $artistB->id,
        ]);

        $response = $this->actingAs($artistA, 'sanctum')
            ->deleteJson("/api/v1/buttons/{$buttonB->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('buttons', ['id' => $buttonB->id]);
    }

    /**
     * Tier 2 (Segurança / Transação): Reordenação com injeção de ID estrangeiro é abortada (403) e nada é alterado.
     */
    public function test_cross_account_reorder_with_foreign_id_is_rejected_and_aborted(): void
    {
        $artistA = Artist::factory()->create();
        $artistB = Artist::factory()->create();

        $btnA1 = Button::factory()->create(['artist_id' => $artistA->id, 'order_index' => 0]);
        $btnA2 = Button::factory()->create(['artist_id' => $artistA->id, 'order_index' => 1]);
        $btnB = Button::factory()->create(['artist_id' => $artistB->id, 'order_index' => 0]);

        // Artista A tenta reordenar incluindo btnB
        $response = $this->actingAs($artistA, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', [
                'ordered_ids' => [$btnA2->id, $btnB->id, $btnA1->id],
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Um ou mais botões informados não pertencem ao seu perfil ou não existem.',
            ]);

        // Verifica integridade: nenhuma ordem foi alterada
        $btnA1->refresh();
        $btnA2->refresh();
        $btnB->refresh();

        $this->assertEquals(0, $btnA1->order_index);
        $this->assertEquals(1, $btnA2->order_index);
        $this->assertEquals(0, $btnB->order_index);
    }

    /**
     * Tier 1 (Especificação backend.md): Modo circular força exibição de ícone e container de fundo.
     */
    public function test_circular_mode_forces_icon_and_background(): void
    {
        $artist = Artist::factory()->create();

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => 'Instagram Circular',
                'url' => 'https://instagram.com/joaoartista',
                'platform' => 'instagram',
                'display_mode' => 'circular',
                'icon_variant' => 'none',
                'show_icon_background' => false,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'display_mode' => 'circular',
                    'icon_variant' => 'default',
                    'show_icon_background' => true,
                ],
            ]);

        $this->assertDatabaseHas('buttons', [
            'artist_id' => $artist->id,
            'title' => 'Instagram Circular',
            'display_mode' => 'circular',
            'icon_variant' => 'default',
            'show_icon_background' => true,
        ]);
    }

    /**
     * Tier 2 (Especificação backend.md): Criação de botão bloqueia ao atingir teto de 10 botões (422).
     */
    public function test_store_button_enforces_maximum_of_10_buttons(): void
    {
        $artist = Artist::factory()->create();

        // Cria 10 botões
        Button::factory()->count(10)->create([
            'artist_id' => $artist->id,
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => '11º Botão Proibido',
                'url' => 'https://example.com/extra',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Limite de 10 links atingido.',
            ]);
    }
}
