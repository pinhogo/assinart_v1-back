<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tier 1: Perfil público com botões ativos retorna 200 OK, status 'found' e estilos resolvidos por cascade.
     */
    public function test_public_profile_with_active_buttons_returns_found_and_cascade_styles(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'mariana_art',
            'name' => 'Mariana Artista',
            'font_family' => 'Syne',
            'font_color' => '#112233',
            'background_color' => '#FFE600',
        ]);

        // Botão sem estilos próprios (deve herdar do artista e padrões do sistema)
        $inheritedButton = Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Link Padrão',
            'url' => 'https://mariana.art',
            'button_color' => null,
            'text_color' => null,
            'font_family' => null,
            'order_index' => 0,
            'is_active' => true,
        ]);

        // Botão com estilos próprios sobrescrevendo o tema
        $overrideButton = Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Link Personalizado',
            'url' => 'https://mariana.art/loja',
            'button_color' => '#00FF00',
            'text_color' => '#FFFFFF',
            'font_family' => 'Inter',
            'order_index' => 1,
            'is_active' => true,
        ]);

        $response = $this->getJson('/mariana_art');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'found',
                'data' => [
                    'username' => 'mariana_art',
                    'name' => 'Mariana Artista',
                    'theme' => [
                        'font_family' => 'Syne',
                        'font_color' => '#112233',
                        'background_color' => '#FFE600',
                    ],
                ],
            ]);

        $buttons = $response->json('data.buttons');
        $this->assertCount(2, $buttons);

        // Validação da herança inteligente (cascade)
        $this->assertEquals('#FFE600', $buttons[0]['styles']['button_color']); // Default #FFE600
        $this->assertEquals('#112233', $buttons[0]['styles']['text_color']);   // Herdado do artista
        $this->assertEquals('Syne', $buttons[0]['styles']['font_family']);      // Herdado do artista
        $this->assertEquals(url('/r/' . $inheritedButton->id), $buttons[0]['redirect_url']);

        // Validação da sobreposição explícita (override)
        $this->assertEquals('#00FF00', $buttons[1]['styles']['button_color']);
        $this->assertEquals('#FFFFFF', $buttons[1]['styles']['text_color']);
        $this->assertEquals('Inter', $buttons[1]['styles']['font_family']);
    }

    /**
     * Tier 1: Perfil público de artista sem botões ativos retorna 200 OK com status 'no_links'.
     */
    public function test_public_profile_with_no_active_buttons_returns_no_links(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'sem_botoes',
            'bio' => 'Ainda sem links',
        ]);

        // Cria botão inativo
        Button::factory()->create([
            'artist_id' => $artist->id,
            'is_active' => false,
        ]);

        $response = $this->getJson('/sem_botoes');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'no_links',
                'data' => [
                    'username' => 'sem_botoes',
                    'bio' => 'Ainda sem links',
                    'buttons' => [],
                ],
            ]);
    }

    /**
     * Tier 2: Botões inativos (is_active = false) não são exibidos na página pública.
     */
    public function test_inactive_buttons_are_excluded_from_public_profile(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'filtra_inativos',
        ]);

        Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Botão Visível',
            'is_active' => true,
        ]);

        Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Botão Oculto',
            'is_active' => false,
        ]);

        $response = $this->getJson('/filtra_inativos');

        $response->assertStatus(200);
        $buttons = $response->json('data.buttons');
        $this->assertCount(1, $buttons);
        $this->assertEquals('Botão Visível', $buttons[0]['title']);
    }

    /**
     * Tier 2: Nome de usuário inexistente retorna HTTP 404 com status 'not_found'.
     */
    public function test_nonexistent_username_returns_404(): void
    {
        $response = $this->getJson('/usuario_fantasma_xyz');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'not_found',
                'message' => 'Artista não encontrado.',
            ]);
    }

    /**
     * Tier 2: Rotas de sistema reservadas chamadas diretamente na raiz retornam 404 not_found.
     */
    public function test_reserved_routes_on_root_return_404(): void
    {
        $reservedSlugs = ['admin', 'api', 'editor', 'r', 'login', 'register', 'dashboard'];

        foreach ($reservedSlugs as $slug) {
            $response = $this->getJson('/' . $slug);
            $response->assertStatus(404);
        }
    }

    /**
     * Tier 2: Sanitização de username com '@' inicial na URL pública.
     */
    public function test_public_profile_handles_leading_at_symbol(): void
    {
        Artist::factory()->create([
            'username' => 'pinho_oficial',
        ]);

        $response = $this->getJson('/@pinho_oficial');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'pinho_oficial',
                ],
            ]);
    }

    /**
     * Tier 1 (Especificação backend.md): Página pública particiona circular_links e button_links.
     */
    public function test_public_profile_partitions_circular_and_button_links(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'particionado_art',
            'button_shadow' => 'hard_large',
            'circular_icon_background' => 'auto',
        ]);

        // 2 botões circulares
        Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Instagram Circular',
            'platform' => 'instagram',
            'display_mode' => 'circular',
            'icon_variant' => 'black',
            'button_color' => null,
            'is_active' => true,
            'order_index' => 0,
        ]);
        Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Spotify Circular',
            'platform' => 'spotify',
            'display_mode' => 'circular',
            'icon_variant' => 'white',
            'button_color' => null,
            'is_active' => true,
            'order_index' => 1,
        ]);

        // 3 botões em pilha
        Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Botão Pilha 1',
            'display_mode' => 'button',
            'is_active' => true,
            'order_index' => 2,
        ]);
        Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Botão Pilha 2',
            'display_mode' => 'button',
            'is_active' => true,
            'order_index' => 3,
        ]);
        Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Botão Pilha 3',
            'display_mode' => 'button',
            'is_active' => true,
            'order_index' => 4,
        ]);

        $response = $this->getJson('/particionado_art');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'found',
                'artist' => [
                    'username' => 'particionado_art',
                    'button_shadow' => 'hard_large',
                ],
            ]);

        $circular = $response->json('circular_links');
        $buttons = $response->json('button_links');

        $this->assertCount(2, $circular);
        $this->assertCount(3, $buttons);

        // Validação da resolução de fundo contrastante 'auto' para circulares
        // icon_variant 'black' -> fundo auto '#FFFFFF'
        $this->assertEquals('#FFFFFF', $circular[0]['resolved_circular_bg']);
        // icon_variant 'white' -> fundo auto '#000000'
        $this->assertEquals('#000000', $circular[1]['resolved_circular_bg']);
    }

    /**
     * FIX-API-05: Endpoint público GET /{username} expõe text_align e show_icon_background reais dos botões.
     */
    public function test_public_profile_exposes_text_align_and_show_icon_background(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'alinhamento_art',
        ]);

        Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Botão Centralizado',
            'display_mode' => 'button',
            'show_icon_background' => false,
            'text_align' => 'center',
            'is_active' => true,
            'order_index' => 0,
        ]);

        $response = $this->getJson('/alinhamento_art');

        $response->assertStatus(200);
        $buttons = $response->json('button_links');
        $this->assertCount(1, $buttons);
        $this->assertEquals('center', $buttons[0]['text_align']);
        $this->assertEquals('center', $buttons[0]['styles']['text_align']);
        $this->assertFalse($buttons[0]['show_icon_background']);
    }

    /**
     * FIX-API-07: Perfil não publicado retorna 404 not_found para visitante anônimo.
     */
    public function test_unpublished_profile_returns_404_for_anonymous_visitor(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'rascunho_anonimo',
            'is_published' => false,
        ]);

        Button::factory()->create([
            'artist_id' => $artist->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/rascunho_anonimo');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'not_found',
                'message' => 'Artista não encontrado.',
            ]);
    }

    /**
     * FIX-API-07: Perfil não publicado retorna 404 not_found para outro artista autenticado.
     */
    public function test_unpublished_profile_returns_404_for_other_authenticated_artist(): void
    {
        Artist::factory()->create([
            'username' => 'rascunho_privado',
            'is_published' => false,
        ]);

        $otherArtist = Artist::factory()->create([
            'username' => 'outro_artista',
        ]);

        $response = $this->actingAs($otherArtist, 'sanctum')
            ->getJson('/rascunho_privado');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'not_found',
                'message' => 'Artista não encontrado.',
            ]);
    }

    /**
     * FIX-API-07: Proprietário autenticado pode pré-visualizar o próprio perfil em rascunho (HTTP 200).
     */
    public function test_unpublished_profile_allows_200_preview_for_owner(): void
    {
        $owner = Artist::factory()->create([
            'username' => 'rascunho_dono',
            'is_published' => false,
        ]);

        Button::factory()->create([
            'artist_id' => $owner->id,
            'title' => 'Preview Link',
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner, 'sanctum')
            ->getJson('/rascunho_dono');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'found',
                'artist' => [
                    'username' => 'rascunho_dono',
                    'is_published' => false,
                    'publication_status' => 'draft',
                ],
                'data' => [
                    'username' => 'rascunho_dono',
                    'is_published' => false,
                    'publication_status' => 'draft',
                ],
            ]);
    }

    /**
     * FIX-API-06: Botão circular com button_color hexadecimal válido prevalece sobre circular_icon_background 'auto'.
     */
    public function test_circular_link_with_individual_button_color_overrides_auto_background(): void
    {
        // icon_variant => fundo esperado para o botão irmão com button_color null (fallback 'auto')
        $variantFallbacks = [
            'black' => '#FFFFFF',
            'white' => '#000000',
            'default' => '#FFFFFF',
        ];

        foreach ($variantFallbacks as $iconVariant => $autoFallback) {
            $artist = Artist::factory()->create([
                'username' => 'circ_cor_' . $iconVariant,
                'circular_icon_background' => 'auto',
                'button_color' => '#FFE600',
            ]);

            $withColor = Button::factory()->create([
                'artist_id' => $artist->id,
                'title' => 'WhatsApp',
                'platform' => 'whatsapp',
                'display_mode' => 'circular',
                'icon_variant' => $iconVariant,
                'button_color' => '#ACE1AF',
                'is_active' => true,
                'order_index' => 0,
            ]);

            $withoutColor = Button::factory()->create([
                'artist_id' => $artist->id,
                'title' => 'Instagram',
                'platform' => 'instagram',
                'display_mode' => 'circular',
                'icon_variant' => $iconVariant,
                'button_color' => null,
                'is_active' => true,
                'order_index' => 1,
            ]);

            $response = $this->getJson('/circ_cor_' . $iconVariant);

            $response->assertStatus(200);
            $circular = collect($response->json('circular_links'));

            $colored = $circular->firstWhere('id', $withColor->id);
            $this->assertEquals('#ACE1AF', $colored['button_color']);
            $this->assertEquals('#ACE1AF', $colored['resolved_button_color']);
            $this->assertEquals('#ACE1AF', $colored['resolved_circular_bg']);

            $fallback = $circular->firstWhere('id', $withoutColor->id);
            $this->assertNull($fallback['button_color']);
            $this->assertEquals('#FFE600', $fallback['resolved_button_color']);
            $this->assertEquals($autoFallback, $fallback['resolved_circular_bg']);
        }
    }

    /**
     * FIX-API-06: Botão circular com button_color vazio ou inválido usa o fallback 'auto' por icon_variant.
     */
    public function test_circular_link_with_empty_or_invalid_button_color_uses_icon_variant_fallback(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'circ_cor_invalida',
            'circular_icon_background' => 'auto',
            'button_color' => '#FFE600',
        ]);

        $emptyColor = Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Spotify',
            'platform' => 'spotify',
            'display_mode' => 'circular',
            'icon_variant' => 'white',
            'button_color' => '',
            'is_active' => true,
            'order_index' => 0,
        ]);

        $invalidColor = Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'YouTube',
            'platform' => 'youtube',
            'display_mode' => 'circular',
            'icon_variant' => 'black',
            'button_color' => 'azul-neon',
            'is_active' => true,
            'order_index' => 1,
        ]);

        $response = $this->getJson('/circ_cor_invalida');

        $response->assertStatus(200);
        $circular = collect($response->json('circular_links'));

        $this->assertEquals('#000000', $circular->firstWhere('id', $emptyColor->id)['resolved_circular_bg']);
        $this->assertEquals('#FFFFFF', $circular->firstWhere('id', $invalidColor->id)['resolved_circular_bg']);
    }

    /**
     * FIX-API-06: Botão circular sem button_color usa circular_icon_background hexadecimal do artista.
     */
    public function test_circular_link_without_button_color_uses_artist_hex_background(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'circ_hex_artista',
            'circular_icon_background' => '#123456',
            'button_color' => '#FFE600',
        ]);

        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Behance',
            'platform' => 'behance',
            'display_mode' => 'circular',
            'icon_variant' => 'white',
            'button_color' => null,
            'is_active' => true,
            'order_index' => 0,
        ]);

        $response = $this->getJson('/circ_hex_artista');

        $response->assertStatus(200);
        $circular = collect($response->json('circular_links'))->firstWhere('id', $button->id);

        $this->assertEquals('#123456', $circular['resolved_circular_bg']);
    }
}
