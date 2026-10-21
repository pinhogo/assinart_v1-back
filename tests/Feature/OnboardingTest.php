<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tier 2: Requisições não autenticadas para as etapas de onboarding retornam 401.
     */
    public function test_unauthenticated_onboarding_requests_return_401(): void
    {
        $this->putJson('/api/v1/onboarding/links', ['bio' => 'Olá'])->assertStatus(401);
        $this->putJson('/api/v1/onboarding/background', ['background_color' => '#FFFFFF'])->assertStatus(401);
        $this->putJson('/api/v1/onboarding/buttons', ['button_shape' => 'square'])->assertStatus(401);
    }

    /**
     * Tier 1: Etapa 1 salva bio (<= 160) e até 5 links de plataformas válidas.
     */
    public function test_step1_saves_bio_and_up_to_5_links(): void
    {
        $artist = Artist::factory()->create();

        $payload = [
            'bio' => 'Pintor contemporâneo e ilustrador digital.',
            'links' => [
                ['platform' => 'instagram', 'url' => '@pinho_arte', 'title' => 'Instagram'],
                ['platform' => 'youtube', 'url' => 'pinho_oficial', 'title' => 'Canal do YouTube'],
                ['platform' => 'whatsapp', 'url' => '11999998888', 'title' => 'WhatsApp Comercial'],
                ['platform' => 'spotify', 'url' => 'artist123', 'title' => 'Spotify Artist'],
                ['platform' => 'behance', 'url' => 'pinho', 'title' => 'Portfólio Behance'],
            ],
        ];

        $response = $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/onboarding/links', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Links e bio salvos com sucesso.',
                'data' => [
                    'bio' => 'Pintor contemporâneo e ilustrador digital.',
                ],
            ]);

        $this->assertDatabaseHas('artists', [
            'id' => $artist->id,
            'bio' => 'Pintor contemporâneo e ilustrador digital.',
        ]);

        $this->assertEquals(5, $artist->buttons()->count());

        $instagramButton = $artist->buttons()->where('platform', 'instagram')->first();
        $this->assertNotNull($instagramButton);
        $this->assertEquals('https://instagram.com/pinho_arte', $instagramButton->url);
        $this->assertEquals(0, $instagramButton->order_index);
    }

    /**
     * Tier 2: Etapa 1 rejeita biografia com mais de 160 caracteres (422).
     */
    public function test_step1_rejects_bio_over_160_chars(): void
    {
        $artist = Artist::factory()->create();

        $longBio = str_repeat('A', 161);

        $response = $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/onboarding/links', [
                'bio' => $longBio,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bio']);
    }

    /**
     * Tier 2: Etapa 1 rejeita mais de 5 links (422).
     */
    public function test_step1_rejects_more_than_5_links(): void
    {
        $artist = Artist::factory()->create();

        $payload = [
            'bio' => 'Bio válida',
            'links' => [
                ['platform' => 'instagram', 'url' => 'user1'],
                ['platform' => 'youtube', 'url' => 'user2'],
                ['platform' => 'x', 'url' => 'user3'],
                ['platform' => 'spotify', 'url' => 'user4'],
                ['platform' => 'behance', 'url' => 'user5'],
                ['platform' => 'tiktok', 'url' => 'user6'],
            ],
        ];

        $response = $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/onboarding/links', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['links']);
    }

    /**
     * Tier 2: Etapa 1 aceita o formato draft do frontend (selectedPlatforms e linkValues).
     */
    public function test_step1_supports_frontend_selected_platforms_draft_format(): void
    {
        $artist = Artist::factory()->create();

        $payload = [
            'bio' => 'Bio via draft frontend',
            'selectedPlatforms' => ['instagram', 'tiktok', 'whatsapp'],
            'linkValues' => [
                'instagram' => 'pinho_draft',
                'tiktok' => 'pinho_tik',
                'whatsapp' => '5511988887777',
            ],
        ];

        $response = $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/onboarding/links', $payload);

        $response->assertStatus(200);

        $this->assertEquals(3, $artist->buttons()->count());
        $this->assertDatabaseHas('buttons', [
            'artist_id' => $artist->id,
            'platform' => 'instagram',
            'url' => 'https://instagram.com/pinho_draft',
        ]);
    }

    /**
     * Tier 1: Etapa 2 salva cor de fundo hexadecimal e identificador do padrão de textura.
     */
    public function test_step2_saves_hex_color_and_background_id(): void
    {
        $artist = Artist::factory()->create();

        $response = $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/onboarding/background', [
                'background_color' => '#E15B3A',
                'background_id' => 'terracotta',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'background_color' => '#E15B3A',
                    'background_id' => 'terracotta',
                ],
            ]);

        $this->assertDatabaseHas('artists', [
            'id' => $artist->id,
            'background_color' => '#E15B3A',
            'background_id' => 'terracotta',
        ]);
    }

    /**
     * Tier 2: Etapa 2 infere a cor a partir de um preset da paleta quando background_color não é enviado.
     */
    public function test_step2_infers_palette_preset_color(): void
    {
        $artist = Artist::factory()->create();

        $response = $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/onboarding/background', [
                'background_id' => 'butter',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'background_color' => '#F3E7B8',
                    'background_id' => 'butter',
                ],
            ]);

        $this->assertDatabaseHas('artists', [
            'id' => $artist->id,
            'background_color' => '#F3E7B8',
        ]);
    }

    /**
     * Tier 2: Etapa 2 rejeita código hexadecimal inválido (422).
     */
    public function test_step2_rejects_invalid_hex_color(): void
    {
        $artist = Artist::factory()->create();

        $response = $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/onboarding/background', [
                'background_color' => 'not-a-color',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['background_color']);
    }

    /**
     * Tier 1: Etapa 3 salva formato dos botões (square, rounded, pill) e conclui onboarding.
     */
    public function test_step3_saves_button_shape_and_finishes_onboarding(): void
    {
        $artist = Artist::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/onboarding/buttons', [
                'button_shape' => 'pill',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Onboarding concluído com sucesso.',
                'redirect_url' => '/editor',
                'data' => [
                    'button_shape' => 'pill',
                ],
            ]);

        $artist->refresh();
        $this->assertEquals('pill', $artist->button_shape);
        $this->assertNotNull($artist->email_verified_at);
    }

    /**
     * Tier 2: Etapa 3 rejeita formato de botão fora do enum permitido (422).
     */
    public function test_step3_rejects_invalid_button_shape(): void
    {
        $artist = Artist::factory()->create();

        $response = $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/onboarding/buttons', [
                'button_shape' => 'triangle',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['button_shape']);
    }

    /**
     * Tier 4: Cenário E2E - Jornada completa de onboarding (Etapas 1, 2 e 3 em sequência).
     */
    public function test_complete_onboarding_journey_end_to_end(): void
    {
        // 1. Artista recém registrado
        $artist = Artist::factory()->create([
            'username' => 'jornada_e2e',
            'email_verified_at' => null,
        ]);

        // 2. Executa Etapa 1
        $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/onboarding/links', [
                'bio' => 'Artista em jornada completa',
                'links' => [
                    ['platform' => 'instagram', 'url' => 'jornada_insta'],
                    ['platform' => 'website', 'url' => 'https://jornada.art'],
                ],
            ])
            ->assertStatus(200);

        // 3. Executa Etapa 2
        $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/onboarding/background', [
                'background_id' => 'lavender',
            ])
            ->assertStatus(200);

        // 4. Executa Etapa 3
        $step3 = $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/onboarding/buttons', [
                'button_shape' => 'rounded',
            ]);

        $step3->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'redirect_url' => '/editor',
            ]);

        // 5. Validação de estado consolidado no banco
        $artist->refresh();
        $this->assertEquals('Artista em jornada completa', $artist->bio);
        $this->assertEquals('#DAD3EC', $artist->background_color);
        $this->assertEquals('lavender', $artist->background_id);
        $this->assertEquals('rounded', $artist->button_shape);
        $this->assertNotNull($artist->email_verified_at);
        $this->assertEquals(2, $artist->buttons()->count());
    }
}
