<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePublicationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * FIX-API-07: Cadastro sempre inicia em rascunho (is_published=false) mesmo se o cliente enviar true.
     */
    public function test_register_ignores_client_supplied_is_published_and_starts_as_draft(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Artista Novo',
            'username' => 'artista_novo_draft',
            'email' => 'novo.draft@assin.art',
            'password' => 'secret123',
            'agree_terms' => true,
            'is_published' => true,
            'publication_status' => 'published',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'artist' => [
                    'username' => 'artista_novo_draft',
                    'is_published' => false,
                    'publication_status' => 'draft',
                ],
                'data' => [
                    'user' => [
                        'username' => 'artista_novo_draft',
                        'is_published' => false,
                        'publication_status' => 'draft',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('artists', [
            'username' => 'artista_novo_draft',
            'is_published' => 0,
        ]);
    }

    /**
     * FIX-API-07: GET /api/v1/profile e /api/v1/auth/me expõem is_published e publication_status.
     */
    public function test_profile_and_me_expose_publication_fields(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'consulta_status',
            'is_published' => false,
        ]);

        $this->actingAs($artist, 'sanctum')
            ->getJson('/api/v1/profile')
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'username' => 'consulta_status',
                    'is_published' => false,
                    'publication_status' => 'draft',
                ],
            ]);

        $this->actingAs($artist, 'sanctum')
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'username' => 'consulta_status',
                    'is_published' => false,
                    'publication_status' => 'draft',
                ],
            ]);
    }

    /**
     * FIX-API-07: PATCH /api/v1/profile alterna publicação via is_published (boolean) e publication_status (string).
     */
    public function test_patch_profile_publishes_and_unpublishes_using_boolean_or_status_string(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'fluxo_publicacao',
            'is_published' => false,
        ]);

        Button::factory()->create([
            'artist_id' => $artist->id,
            'is_active' => true,
        ]);

        $this->app['auth']->forgetGuards();
        $this->getJson('/fluxo_publicacao')->assertStatus(404);

        // 1. Publica via is_published boolean
        $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', ['is_published' => true])
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'is_published' => true,
                    'publication_status' => 'published',
                ],
            ]);

        $this->app['auth']->forgetGuards();
        $this->getJson('/fluxo_publicacao')->assertStatus(200);

        // 2. Despublica via publication_status = 'draft'
        $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', ['publication_status' => 'draft'])
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'is_published' => false,
                    'publication_status' => 'draft',
                ],
            ]);

        $this->app['auth']->forgetGuards();
        $this->getJson('/fluxo_publicacao')->assertStatus(404);

        // 3. Republica via publication_status = 'published'
        $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', ['publication_status' => 'published'])
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'is_published' => true,
                    'publication_status' => 'published',
                ],
            ]);

        $this->app['auth']->forgetGuards();
        $this->getJson('/fluxo_publicacao')->assertStatus(200);
    }

    /**
     * FIX-API-07: Valores inválidos de is_published ou publication_status retornam 422 Unprocessable Entity.
     */
    public function test_patch_profile_rejects_invalid_publication_values_with_422(): void
    {
        $artist = Artist::factory()->create();

        $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', ['publication_status' => 'archived'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['publication_status']);

        $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', ['is_published' => 'invalido'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['is_published']);
    }

    /**
     * FIX-API-07: Token Bearer real no cabeçalho Authorization libera preview 200 apenas ao dono.
     */
    public function test_unpublished_profile_allows_owner_preview_via_bearer_token(): void
    {
        $owner = Artist::factory()->create([
            'username' => 'dono_bearer',
            'is_published' => false,
        ]);
        $other = Artist::factory()->create([
            'username' => 'terceiro_bearer',
        ]);

        $ownerToken = $owner->createToken('auth_token')->plainTextToken;
        $otherToken = $other->createToken('auth_token')->plainTextToken;

        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $otherToken)
            ->getJson('/dono_bearer')
            ->assertStatus(404);

        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $ownerToken)
            ->getJson('/dono_bearer')
            ->assertStatus(200)
            ->assertJson([
                'artist' => [
                    'username' => 'dono_bearer',
                    'is_published' => false,
                    'publication_status' => 'draft',
                ],
            ]);
    }
}
