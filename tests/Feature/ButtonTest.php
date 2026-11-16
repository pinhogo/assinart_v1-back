<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
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
        $this->postJson('/api/v1/buttons/reorder', [
            'items' => [
                ['id' => 1, 'display_mode' => 'button'],
                ['id' => 2, 'display_mode' => 'button'],
            ],
        ])->assertStatus(401);
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

        $b1 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 0, 'display_mode' => 'button']);
        $b2 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 1, 'display_mode' => 'button']);
        $b3 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 2, 'display_mode' => 'button']);

        // Inverte a ordem: [b3, b1, b2]
        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', [
                'items' => [
                    ['id' => $b3->id, 'display_mode' => 'button'],
                    ['id' => $b1->id, 'display_mode' => 'button'],
                    ['id' => $b2->id, 'display_mode' => 'button'],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Ordem dos botões atualizada com sucesso.',
                'data' => [
                    'updated_count' => 3,
                ],
            ]);

        $returnedIds = collect($response->json('data.buttons'))->pluck('id')->all();
        $this->assertSame([$b3->id, $b1->id, $b2->id], $returnedIds);

        $b3->refresh();
        $b1->refresh();
        $b2->refresh();

        $this->assertEquals(0, $b3->order_index);
        $this->assertEquals(1, $b1->order_index);
        $this->assertEquals(2, $b2->order_index);
    }

    /**
     * Tier 1 (API-10): Reordenação suporta mover item para topo, meio e fim preservando índices contíguos.
     */
    public function test_reorder_moves_button_to_top_middle_and_end(): void
    {
        $artist = Artist::factory()->create();

        $b1 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 0, 'display_mode' => 'button']);
        $b2 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 1, 'display_mode' => 'button']);
        $b3 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 2, 'display_mode' => 'button']);
        $b4 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 3, 'display_mode' => 'button']);

        // Move b4 para o topo, b1 para o meio e b2 para o fim: [b4, b3, b1, b2]
        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', [
                'items' => [
                    ['id' => $b4->id, 'display_mode' => 'button'],
                    ['id' => $b3->id, 'display_mode' => 'button'],
                    ['id' => $b1->id, 'display_mode' => 'button'],
                    ['id' => $b2->id, 'display_mode' => 'button'],
                ],
            ]);

        $response->assertStatus(200);

        $returnedIds = collect($response->json('data.buttons'))->pluck('id')->all();
        $this->assertSame([$b4->id, $b3->id, $b1->id, $b2->id], $returnedIds);

        $this->assertSame(0, $b4->fresh()->order_index);
        $this->assertSame(1, $b3->fresh()->order_index);
        $this->assertSame(2, $b1->fresh()->order_index);
        $this->assertSame(3, $b2->fresh()->order_index);
    }

    /**
     * Tier 1 (API-10): Troca de seção button <-> circular normaliza circular em 0..K-1 e button em K..M-1.
     */
    public function test_reorder_switches_sections_and_normalizes_circular_then_button_indexes(): void
    {
        $artist = Artist::factory()->create();

        $c1 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 0, 'display_mode' => 'circular', 'show_icon_background' => true]);
        $b1 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 1, 'display_mode' => 'button']);
        $b2 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 2, 'display_mode' => 'button']);

        // Envia b2 promovido a circular, c1 rebaixado a button e b1 mantido em button (mesmo intercalados no payload)
        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', [
                'items' => [
                    ['id' => $c1->id, 'display_mode' => 'button'],
                    ['id' => $b2->id, 'display_mode' => 'circular'],
                    ['id' => $b1->id, 'display_mode' => 'button'],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'updated_count' => 3,
                ],
            ]);

        $returnedIds = collect($response->json('data.buttons'))->pluck('id')->all();
        // circular primeiro (0..K-1), seguido de button (K..M-1)
        $this->assertSame([$b2->id, $c1->id, $b1->id], $returnedIds);

        $b2->refresh();
        $c1->refresh();
        $b1->refresh();

        $this->assertSame('circular', $b2->display_mode);
        $this->assertSame(0, $b2->order_index);
        $this->assertSame('button', $c1->display_mode);
        $this->assertSame(1, $c1->order_index);
        $this->assertSame('button', $b1->display_mode);
        $this->assertSame(2, $b1->order_index);
    }

    /**
     * Tier 1 (API-10): Promoção para circular força show_icon_background=true e converte icon_variant 'none' para 'default'.
     */
    public function test_reorder_promoting_to_circular_forces_background_and_converts_none_icon_to_default(): void
    {
        $artist = Artist::factory()->create();

        $noneButton = Button::factory()->create([
            'artist_id' => $artist->id,
            'order_index' => 0,
            'display_mode' => 'button',
            'icon_variant' => 'none',
            'show_icon_background' => false,
        ]);

        $whiteButton = Button::factory()->create([
            'artist_id' => $artist->id,
            'order_index' => 1,
            'display_mode' => 'button',
            'icon_variant' => 'white',
            'show_icon_background' => false,
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', [
                'items' => [
                    ['id' => $noneButton->id, 'display_mode' => 'circular'],
                    ['id' => $whiteButton->id, 'display_mode' => 'circular'],
                ],
            ]);

        $response->assertStatus(200);

        $noneButton->refresh();
        $whiteButton->refresh();

        $this->assertSame('circular', $noneButton->display_mode);
        $this->assertSame('default', $noneButton->icon_variant);
        $this->assertTrue($noneButton->show_icon_background);
        $this->assertSame(0, $noneButton->order_index);

        $this->assertSame('circular', $whiteButton->display_mode);
        $this->assertSame('white', $whiteButton->icon_variant);
        $this->assertTrue($whiteButton->show_icon_background);
        $this->assertSame(1, $whiteButton->order_index);
    }

    /**
     * Tier 1 (API-10): Reordenação corrige colisões pré-existentes de order_index entre seções.
     */
    public function test_reorder_fixes_preexisting_order_index_collisions(): void
    {
        $artist = Artist::factory()->create();

        $c1 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 0, 'display_mode' => 'circular', 'show_icon_background' => true]);
        $c2 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 0, 'display_mode' => 'circular', 'show_icon_background' => true]);
        $b1 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 2, 'display_mode' => 'button']);
        $b2 = Button::factory()->create(['artist_id' => $artist->id, 'order_index' => 2, 'display_mode' => 'button']);

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', [
                'items' => [
                    ['id' => $c2->id, 'display_mode' => 'circular'],
                    ['id' => $c1->id, 'display_mode' => 'circular'],
                    ['id' => $b2->id, 'display_mode' => 'button'],
                    ['id' => $b1->id, 'display_mode' => 'button'],
                ],
            ]);

        $response->assertStatus(200);

        $this->assertSame(0, $c2->fresh()->order_index);
        $this->assertSame(1, $c1->fresh()->order_index);
        $this->assertSame(2, $b2->fresh()->order_index);
        $this->assertSame(3, $b1->fresh()->order_index);
    }

    /**
     * Tier 2 (API-10): Reordenação bloqueia mais de 5 botões circulares com HTTP 422 e preserva estado.
     */
    public function test_reorder_rejects_more_than_5_circular_buttons_with_422(): void
    {
        $artist = Artist::factory()->create();

        $buttons = collect();
        for ($i = 0; $i < 6; $i++) {
            $buttons->push(Button::factory()->create([
                'artist_id' => $artist->id,
                'order_index' => $i,
                'display_mode' => $i < 5 ? 'circular' : 'button',
                'show_icon_background' => $i < 5,
            ]));
        }

        $items = $buttons->map(fn (Button $btn) => [
            'id' => $btn->id,
            'display_mode' => 'circular',
        ])->all();

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', ['items' => $items]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Você pode ter no máximo 5 botões circulares.',
            ]);

        // O 6º botão não foi convertido para circular
        $this->assertSame('button', $buttons->last()->fresh()->display_mode);
        $this->assertSame(5, $buttons->last()->fresh()->order_index);
    }

    /**
     * Tier 2: Reordenação com array vazio, IDs duplicados ou display_mode inválido é rejeitada (422).
     */
    public function test_reorder_validation_fails_on_empty_duplicate_ids_or_invalid_mode(): void
    {
        $artist = Artist::factory()->create();

        // Vazio
        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', ['items' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items']);

        // Duplicados
        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', [
                'items' => [
                    ['id' => 1, 'display_mode' => 'button'],
                    ['id' => 1, 'display_mode' => 'circular'],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.id']);

        // display_mode inválido
        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', [
                'items' => [
                    ['id' => 1, 'display_mode' => 'pill'],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.display_mode']);
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

        $btnA1 = Button::factory()->create(['artist_id' => $artistA->id, 'order_index' => 0, 'display_mode' => 'button']);
        $btnA2 = Button::factory()->create(['artist_id' => $artistA->id, 'order_index' => 1, 'display_mode' => 'button']);
        $btnB = Button::factory()->create(['artist_id' => $artistB->id, 'order_index' => 0, 'display_mode' => 'button']);

        // Artista A tenta reordenar incluindo btnB
        $response = $this->actingAs($artistA, 'sanctum')
            ->postJson('/api/v1/buttons/reorder', [
                'items' => [
                    ['id' => $btnA2->id, 'display_mode' => 'circular'],
                    ['id' => $btnB->id, 'display_mode' => 'button'],
                    ['id' => $btnA1->id, 'display_mode' => 'button'],
                ],
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Um ou mais botões informados não pertencem ao seu perfil ou não existem.',
            ]);

        // Verifica integridade: nenhuma ordem ou display_mode foi alterado
        $btnA1->refresh();
        $btnA2->refresh();
        $btnB->refresh();

        $this->assertEquals(0, $btnA1->order_index);
        $this->assertEquals(1, $btnA2->order_index);
        $this->assertEquals('button', $btnA2->display_mode);
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
     * Tier 2 (API-11): Criação de botão permite até 30 links e bloqueia o 31º com HTTP 422.
     */
    public function test_store_button_enforces_maximum_of_30_buttons(): void
    {
        $artist = Artist::factory()->create();

        for ($i = 1; $i <= 30; $i++) {
            $this->actingAs($artist, 'sanctum')
                ->postJson('/api/v1/buttons', [
                    'title' => "Botão {$i}",
                    'url' => "https://example.com/link-{$i}",
                ])
                ->assertStatus(201);
        }

        $this->assertEquals(30, $artist->buttons()->count());

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => '31º Botão Proibido',
                'url' => 'https://example.com/extra',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Limite de 30 links atingido.',
                'errors' => [
                    'links' => ['Você pode cadastrar no máximo 30 links combinados entre redes sociais e personalizados.'],
                ],
            ]);

        $this->assertEquals(30, $artist->buttons()->count());
    }

    /**
     * Tier 1 (API-11): Novo botão circular é inserido ao final da seção circular mantendo sequência 0..M-1.
     */
    public function test_store_button_normalizes_section_order_when_adding_circular(): void
    {
        $artist = Artist::factory()->create();

        $c1 = Button::factory()->create([
            'artist_id' => $artist->id,
            'order_index' => 0,
            'display_mode' => 'circular',
            'show_icon_background' => true,
        ]);
        $b1 = Button::factory()->create([
            'artist_id' => $artist->id,
            'order_index' => 1,
            'display_mode' => 'button',
        ]);
        $b2 = Button::factory()->create([
            'artist_id' => $artist->id,
            'order_index' => 2,
            'display_mode' => 'button',
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => 'Spotify Circular',
                'url' => 'https://open.spotify.com/artist/pinho',
                'platform' => 'spotify',
                'display_mode' => 'circular',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'display_mode' => 'circular',
                    'order_index' => 1,
                    'show_icon_background' => true,
                ],
            ]);

        $this->assertSame(0, $c1->fresh()->order_index);
        $this->assertSame(1, $response->json('data.order_index'));
        $this->assertSame(2, $b1->fresh()->order_index);
        $this->assertSame(3, $b2->fresh()->order_index);
    }

    /**
     * Tier 2 (API-11): Criação via POST /buttons bloqueia o 6º botão circular com HTTP 422.
     */
    public function test_store_button_enforces_maximum_of_5_circular_buttons(): void
    {
        $artist = Artist::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            Button::factory()->create([
                'artist_id' => $artist->id,
                'order_index' => $i,
                'display_mode' => 'circular',
                'show_icon_background' => true,
            ]);
        }

        $circularResponse = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => '6º Circular Proibido',
                'url' => 'https://instagram.com/pinho',
                'platform' => 'instagram',
                'display_mode' => 'circular',
            ]);

        $circularResponse->assertStatus(422)
            ->assertJson([
                'message' => 'Você pode ter no máximo 5 botões circulares.',
            ]);

        $this->assertEquals(5, $artist->buttons()->where('display_mode', 'circular')->count());

        // Criar um botão padrão continua permitido (até 30 no total)
        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => 'Botão Padrão Permitido',
                'url' => 'https://example.com/padrao',
                'display_mode' => 'button',
            ])
            ->assertStatus(201);
    }

    /**
     * Tier 2 (API-11): Promoção para circular via PATCH /buttons/{id} bloqueia o 6º circular com HTTP 422.
     */
    public function test_update_button_enforces_maximum_of_5_circular_buttons(): void
    {
        $artist = Artist::factory()->create();

        $firstCircular = null;
        for ($i = 0; $i < 5; $i++) {
            $created = Button::factory()->create([
                'artist_id' => $artist->id,
                'order_index' => $i,
                'display_mode' => 'circular',
                'show_icon_background' => true,
            ]);
            $firstCircular ??= $created;
        }

        $regularButton = Button::factory()->create([
            'artist_id' => $artist->id,
            'order_index' => 5,
            'display_mode' => 'button',
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->patchJson("/api/v1/buttons/{$regularButton->id}", [
                'display_mode' => 'circular',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Você pode ter no máximo 5 botões circulares.',
            ]);

        $this->assertSame('button', $regularButton->fresh()->display_mode);

        // Atualizar metadados de um circular existente continua permitido mesmo com 5 circulares
        $this->actingAs($artist, 'sanctum')
            ->patchJson("/api/v1/buttons/{$firstCircular->id}", [
                'title' => 'Circular Atualizado',
                'display_mode' => 'circular',
            ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Circular Atualizado',
                    'display_mode' => 'circular',
                ],
            ]);
    }

    /**
     * Tier 1 & 2 (API-11): PATCH /buttons/{id} ignora order_index enviado e renormaliza sequência ao trocar display_mode.
     */
    public function test_update_button_ignores_order_index_and_renormalizes_on_display_mode_change(): void
    {
        $artist = Artist::factory()->create();

        $c1 = Button::factory()->create([
            'artist_id' => $artist->id,
            'order_index' => 0,
            'display_mode' => 'circular',
            'show_icon_background' => true,
        ]);
        $b1 = Button::factory()->create([
            'artist_id' => $artist->id,
            'order_index' => 1,
            'display_mode' => 'button',
            'icon_variant' => 'default',
            'show_icon_background' => false,
        ]);
        $b2 = Button::factory()->create([
            'artist_id' => $artist->id,
            'order_index' => 2,
            'display_mode' => 'button',
            'icon_variant' => 'none',
            'show_icon_background' => false,
        ]);

        // 1. Envia order_index bruto no PATCH — deve ser ignorado sem mutar a ordem
        $patchIgnoreOrder = $this->actingAs($artist, 'sanctum')
            ->patchJson("/api/v1/buttons/{$b2->id}", [
                'title' => 'Título Sem Mover Ordem',
                'order_index' => 99,
            ]);

        $patchIgnoreOrder->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => 'Título Sem Mover Ordem',
                    'order_index' => 2,
                ],
            ]);
        $this->assertSame(2, $b2->fresh()->order_index);

        // 2. Promove b2 (button -> circular): deve ir para circular (0..K-1) e b1 para button (K..M-1)
        $promoteResponse = $this->actingAs($artist, 'sanctum')
            ->patchJson("/api/v1/buttons/{$b2->id}", [
                'display_mode' => 'circular',
                'order_index' => 99,
            ]);

        $promoteResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'display_mode' => 'circular',
                    'icon_variant' => 'default',
                    'show_icon_background' => true,
                    'order_index' => 1,
                ],
            ]);

        $this->assertSame(0, $c1->fresh()->order_index);
        $this->assertSame(1, $b2->fresh()->order_index);
        $this->assertSame(2, $b1->fresh()->order_index);

        // 3. Rebaixa c1 (circular -> button): b2 assume 0, c1 e b1 ficam em button (1, 2)
        $demoteResponse = $this->actingAs($artist, 'sanctum')
            ->patchJson("/api/v1/buttons/{$c1->id}", [
                'display_mode' => 'button',
            ]);

        $demoteResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'display_mode' => 'button',
                    'order_index' => 1,
                ],
            ]);

        $this->assertSame(0, $b2->fresh()->order_index);
        $this->assertSame(1, $c1->fresh()->order_index);
        $this->assertSame(2, $b1->fresh()->order_index);
    }

    /**
     * Tier 1 (Contratos de API): Limpeza em massa de todos os botões do artista autenticado.
     */
    public function test_can_clear_all_buttons(): void
    {
        $artist = Artist::factory()->create();
        Button::factory()->count(3)->create(['artist_id' => $artist->id]);

        $this->assertEquals(3, $artist->buttons()->count());

        $response = $this->actingAs($artist, 'sanctum')->deleteJson('/api/v1/buttons');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Todos os botões foram excluídos com sucesso.',
            ]);

        $this->assertEquals(0, $artist->buttons()->count());
    }

    /**
     * Tier 1 (Contratos de API): Suporte a tipo featured e tipografia individual via PATCH.
     */
    public function test_can_update_typography_and_featured_type_via_patch(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create(['artist_id' => $artist->id]);

        $response = $this->actingAs($artist, 'sanctum')->patchJson("/api/v1/buttons/{$button->id}", [
            'type' => 'featured',
            'font_weight' => 'bold',
            'font_style' => 'italic',
            'text_border' => 'black',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $button->id,
                    'type' => 'featured',
                    'font_weight' => 'bold',
                    'font_style' => 'italic',
                    'text_border' => 'black',
                ],
            ]);

        $button->refresh();
        $this->assertEquals('featured', $button->type);
        $this->assertEquals('bold', $button->font_weight);
    }

    /**
     * FIX-API-05: Botões padrão nascem com show_icon_background = false e suportam text_align.
     */
    public function test_store_button_defaults_to_show_icon_background_false_and_persists_text_align(): void
    {
        $artist = Artist::factory()->create();

        $response = $this->actingAs($artist, 'sanctum')->postJson('/api/v1/buttons', [
            'title' => 'Portfólio Alinhado',
            'url' => 'https://assin.art/portfolio',
            'platform' => 'website',
            'display_mode' => 'button',
            'text_align' => 'right',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'display_mode' => 'button',
                    'show_icon_background' => false,
                    'text_align' => 'right',
                ],
            ]);

        $this->assertDatabaseHas('buttons', [
            'artist_id' => $artist->id,
            'title' => 'Portfólio Alinhado',
            'show_icon_background' => false,
            'text_align' => 'right',
        ]);
    }

    public function test_store_and_update_reject_non_http_schemes_and_invalid_hex_colors(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create(['artist_id' => $artist->id]);

        foreach (['javascript:alert(1)', 'ftp://files.example.com/a', 'data:text/html,<script>1</script>'] as $badUrl) {
            $this->actingAs($artist, 'sanctum')
                ->postJson('/api/v1/buttons', ['title' => 'Bad', 'url' => $badUrl])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['url']);

            $this->actingAs($artist, 'sanctum')
                ->putJson("/api/v1/buttons/{$button->id}", ['url' => $badUrl])
                ->assertStatus(422)
                ->assertJsonValidationErrors(['url']);
        }

        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => 'Bad Colors',
                'url' => 'https://example.com',
                'button_color' => 'not-hex',
                'text_color' => '#12',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['button_color', 'text_color']);

        $this->actingAs($artist, 'sanctum')
            ->patchJson("/api/v1/buttons/{$button->id}", [
                'button_color' => 'rgb(1,2,3)',
                'text_color' => '#GGGGGG',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['button_color', 'text_color']);
    }

    public function test_button_protects_artist_id_and_clicks_count_from_mass_assignment(): void
    {
        $artist = Artist::factory()->create();
        $otherArtist = Artist::factory()->create();

        $fillable = (new Button())->getFillable();
        $this->assertNotContains('artist_id', $fillable);
        $this->assertNotContains('clicks_count', $fillable);

        $created = $artist->buttons()->create([
            'artist_id' => $otherArtist->id,
            'title' => 'Protected Button',
            'url' => 'https://example.com',
            'clicks_count' => 999,
        ]);

        $fresh = $created->fresh();
        $this->assertSame($artist->id, $fresh->artist_id);
        $this->assertSame(0, $fresh->clicks_count);
    }

    public function test_button_image_upload_enforces_throttle_20_per_minute(): void
    {
        Storage::fake('public');
        $artist = Artist::factory()->create();
        $button = Button::factory()->create(['artist_id' => $artist->id]);

        try {
            for ($i = 0; $i < 20; $i++) {
                $this->actingAs($artist, 'sanctum')
                    ->postJson("/api/v1/buttons/{$button->id}/image", [
                        'image' => UploadedFile::fake()->create("btn{$i}.png", 50, 'image/png'),
                    ])
                    ->assertStatus(200);
            }

            $this->actingAs($artist, 'sanctum')
                ->postJson("/api/v1/buttons/{$button->id}/image", [
                    'image' => UploadedFile::fake()->create('btn21.png', 50, 'image/png'),
                ])
                ->assertStatus(429);
        } finally {
            RateLimiter::clear((string) $artist->id);
            Cache::flush();
        }
    }
}
