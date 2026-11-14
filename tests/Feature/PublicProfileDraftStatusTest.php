<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProfileDraftStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Draft profiles must return 404 draft without artist fields to visitors and other artists.
     */
    public function test_visitor_and_other_artist_on_draft_receive_404_draft_without_artist_data(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'artista_rascunho',
            'name' => 'Nome Secreto',
            'bio' => 'Bio Secreta',
            'avatar_url' => 'https://cdn.assin.art/avatars/secret.webp',
            'cover_url' => 'https://cdn.assin.art/covers/secret.webp',
            'is_published' => false,
        ]);

        Button::factory()->create([
            'artist_id' => $artist->id,
            'is_active' => true,
        ]);

        $other = Artist::factory()->create(['username' => 'outro_usuario']);

        foreach ([null, $other] as $viewer) {
            $this->app['auth']->forgetGuards();
            $request = $viewer ? $this->actingAs($viewer, 'sanctum') : $this;

            $response = $request->getJson('/artista_rascunho');

            $response->assertStatus(404)
                ->assertExactJson([
                    'status' => 'draft',
                    'message' => 'Perfil ainda não publicado.',
                ])
                ->assertDontSee('Nome Secreto')
                ->assertDontSee('Bio Secreta')
                ->assertDontSee('secret.webp');
        }
    }

    /**
     * Nonexistent usernames and reserved routes must still return 404 not_found.
     */
    public function test_nonexistent_and_reserved_usernames_return_404_not_found(): void
    {
        foreach (['/usuario_inexistente_xyz', '/@admin'] as $path) {
            $this->getJson($path)
                ->assertStatus(404)
                ->assertExactJson(['status' => 'not_found', 'message' => 'Artista não encontrado.']);
        }
    }

    /**
     * The authenticated owner can preview their own draft profile with HTTP 200.
     */
    public function test_owner_on_draft_profile_receives_200_with_is_published_false(): void
    {
        $owner = Artist::factory()->create([
            'username' => 'dono_rascunho',
            'is_published' => false,
        ]);

        Button::factory()->create([
            'artist_id' => $owner->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner, 'sanctum')
            ->getJson('/dono_rascunho');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'found',
                'artist' => [
                    'username' => 'dono_rascunho',
                    'is_published' => false,
                    'publication_status' => 'draft',
                ],
                'data' => [
                    'username' => 'dono_rascunho',
                    'is_published' => false,
                    'publication_status' => 'draft',
                ],
            ]);
    }

    /**
     * Published profiles return HTTP 200 to public visitors.
     */
    public function test_published_profile_returns_200(): void
    {
        Artist::factory()->create([
            'username' => 'artista_publicado',
            'is_published' => true,
        ]);

        $this->getJson('/artista_publicado')
            ->assertStatus(200)
            ->assertJson([
                'status' => 'no_links',
                'artist' => [
                    'username' => 'artista_publicado',
                    'is_published' => true,
                    'publication_status' => 'published',
                ],
            ]);
    }

    /**
     * Usernames with a dot resolve to 404 draft when unpublished and 200 when published.
     */
    public function test_username_with_dot_handles_draft_and_published_states(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'joao.silva',
            'is_published' => false,
        ]);

        $this->getJson('/joao.silva')
            ->assertStatus(404)
            ->assertExactJson(['status' => 'draft', 'message' => 'Perfil ainda não publicado.']);

        $artist->update(['is_published' => true]);

        $this->getJson('/joao.silva')
            ->assertStatus(200)
            ->assertJson([
                'artist' => [
                    'username' => 'joao.silva',
                    'is_published' => true,
                ],
            ]);
    }
}
