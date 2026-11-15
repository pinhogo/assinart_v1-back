<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Support\ReservedUsernames;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReservedUsernamesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Slugs required by FIX-API-08, plus the legacy words kept from the old lists.
     *
     * @return array<string, array{0: string}>
     */
    public static function reservedSlugs(): array
    {
        $slugs = [
            'ajuda', 'termos', 'privacidade', 'playground', 'cadastro', 'entrar', 'editor',
            'esqueci-senha', 'confirmacao-email', 'configuracoes', 'perfil', 'suporte',
            'contato', 'sobre', 'assinart', 'assin.art', 'api', 'storage', 'r', 'admin',
            'login', 'register', 'dashboard', 'up', 'health', 'metrics', 'analytics',
            'oauth', 'auth', 'assets', 'public', 'static', 'favicon.ico', 'robots.txt',
            'sitemap.xml', 'app', 'email', 'home', 'logout', 'null', 'root', 'sac',
            'settings', 'signin', 'signup', 'status', 'undefined',
        ];

        return array_combine($slugs, array_map(fn (string $slug): array => [$slug], $slugs));
    }

    public function test_single_source_contains_every_required_and_legacy_slug(): void
    {
        foreach (array_keys(self::reservedSlugs()) as $slug) {
            $this->assertContains($slug, ReservedUsernames::SLUGS, "Slug ausente: {$slug}");
        }
    }

    #[DataProvider('reservedSlugs')]
    public function test_check_returns_reserved_for_reserved_slug(string $slug): void
    {
        // Slugs with fewer than 2 characters fail the format rule before the reserved list is read.
        $expectedReason = strlen($slug) < 2 ? 'invalid_format' : 'reserved';

        $this->getJson('/api/v1/usernames/check?username=' . urlencode($slug))
            ->assertStatus(200)
            ->assertJson([
                'username' => $slug,
                'available' => false,
                'reason' => $expectedReason,
            ]);
    }

    public function test_check_rejects_reserved_slug_with_at_sign_and_uppercase(): void
    {
        $this->getJson('/api/v1/usernames/check?username=' . urlencode('@AJUDA'))
            ->assertStatus(200)
            ->assertJson(['username' => 'ajuda', 'available' => false, 'reason' => 'reserved']);
    }

    #[DataProvider('reservedSlugs')]
    public function test_register_rejects_reserved_slug_without_creating_artist(string $slug): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Teste Reservado',
            'username' => $slug,
            'email' => md5($slug) . '@assin.art',
            'password' => 'secret123',
            'agree_terms' => true,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['username']);

        $this->assertSame(0, Artist::count());
    }

    #[DataProvider('reservedSlugs')]
    public function test_profile_patch_rejects_reserved_slug_and_keeps_username(string $slug): void
    {
        $artist = Artist::factory()->create(['username' => 'dono_original']);

        $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', ['username' => $slug])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['username']);

        $this->assertSame('dono_original', $artist->fresh()->username);
    }

    public function test_profile_patch_rejects_reserved_slug_with_uppercase_and_at_sign(): void
    {
        $artist = Artist::factory()->create(['username' => 'dono_original']);

        $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', ['username' => '@Ajuda'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['username']);

        $this->assertSame('dono_original', $artist->fresh()->username);
    }

    #[DataProvider('reservedSlugs')]
    public function test_public_route_does_not_serve_reserved_slug(string $slug): void
    {
        // /up is the framework health route, registered before the public profile route.
        if ($slug === 'up') {
            $this->markTestSkipped('/up pertence à rota de health do framework.');
        }

        $this->getJson('/' . $slug)->assertStatus(404);
    }

    #[DataProvider('reservedSlugs')]
    public function test_public_controller_returns_not_found_for_reserved_slug_with_at_sign(string $slug): void
    {
        $this->getJson('/@' . $slug)
            ->assertStatus(404)
            ->assertExactJson(['status' => 'not_found', 'message' => 'Artista não encontrado.']);
    }

    public function test_public_route_does_not_serve_legacy_row_with_reserved_username(): void
    {
        Artist::factory()->create(['username' => 'ajuda', 'is_published' => true]);

        $this->getJson('/ajuda')->assertStatus(404);
        $this->getJson('/@ajuda')
            ->assertStatus(404)
            ->assertExactJson(['status' => 'not_found', 'message' => 'Artista não encontrado.']);
    }

    public function test_public_route_still_serves_similar_non_reserved_username(): void
    {
        Artist::factory()->create(['username' => 'ajudante', 'is_published' => true]);

        $this->getJson('/ajudante')
            ->assertStatus(200)
            ->assertJsonPath('status', 'no_links')
            ->assertJsonPath('artist.username', 'ajudante');
    }

    public function test_check_accepts_similar_non_reserved_username(): void
    {
        $this->getJson('/api/v1/usernames/check?username=ajudante')
            ->assertStatus(200)
            ->assertJson(['available' => true, 'reason' => null]);
    }
}
