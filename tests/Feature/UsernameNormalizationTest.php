<?php

namespace Tests\Feature;

use App\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class UsernameNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_and_patch_profile_normalize_uppercase_and_leading_at(): void
    {
        $registerResponse = $this->postJson('/api/v1/auth/register', [
            'name' => 'Artista Um',
            'username' => ' @Maiuscula ',
            'email' => 'um@assin.art',
            'password' => 'secret123',
            'agree_terms' => true,
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonPath('data.user.username', 'maiuscula');
        $this->assertDatabaseHas('artists', ['username' => 'maiuscula']);

        $artist = Artist::where('username', 'maiuscula')->firstOrFail();

        $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', ['username' => '@TstUsr03'])
            ->assertStatus(200)
            ->assertJsonPath('data.username', 'tstusr03');

        $this->assertDatabaseHas('artists', ['id' => $artist->id, 'username' => 'tstusr03']);
    }

    public function test_dotted_username_joao_silva_works_across_all_endpoints(): void
    {
        $this->getJson('/api/v1/usernames/check?username=joao.silva')
            ->assertStatus(200)
            ->assertJson(['username' => 'joao.silva', 'available' => true]);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Joao Silva',
            'username' => 'joao.silva',
            'email' => 'joao@assin.art',
            'password' => 'secret123',
            'agree_terms' => true,
        ])->assertStatus(201)->assertJsonPath('data.user.username', 'joao.silva');

        $joao = Artist::where('username', 'joao.silva')->firstOrFail();
        $this->actingAs($joao, 'sanctum')
            ->patchJson('/api/v1/profile', ['username' => 'joao.silva', 'is_published' => true])
            ->assertStatus(200)
            ->assertJsonPath('data.username', 'joao.silva');

        $this->app['auth']->forgetGuards();
        $this->getJson('/joao.silva')
            ->assertStatus(200)
            ->assertJsonPath('artist.username', 'joao.silva');
    }

    public function test_invalid_formats_reserved_and_dot_boundaries_return_422_and_check_rejects(): void
    {
        $artist = Artist::factory()->create(['username' => 'valido_base']);
        $invalids = ['.abc', 'abc.', 'a..b', 'a', str_repeat('a', 31)];

        foreach ($invalids as $bad) {
            $this->postJson('/api/v1/auth/register', [
                'name' => 'Teste',
                'username' => $bad,
                'email' => md5($bad) . '@assin.art',
                'password' => 'secret123',
                'agree_terms' => true,
            ])->assertStatus(422)->assertJsonValidationErrors(['username']);

            $this->actingAs($artist, 'sanctum')
                ->patchJson('/api/v1/profile', ['username' => $bad])
                ->assertStatus(422)->assertJsonValidationErrors(['username']);

            $this->getJson('/api/v1/usernames/check?username=' . urlencode($bad))
                ->assertStatus(200)
                ->assertJson(['available' => false, 'reason' => 'invalid_format']);
        }

        $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', ['username' => '@ADMIN'])
            ->assertStatus(422)->assertJsonValidationErrors(['username']);
    }

    public function test_taken_username_case_insensitive_returns_422_and_owner_keeps_own(): void
    {
        Artist::factory()->create(['username' => 'MariaArt']);
        $owner = Artist::factory()->create(['username' => 'DonoAtual']);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Duplicado',
            'username' => '@MARIAART',
            'email' => 'dup@assin.art',
            'password' => 'secret123',
            'agree_terms' => true,
        ])->assertStatus(422)->assertJsonValidationErrors(['username']);

        $this->actingAs($owner, 'sanctum')
            ->patchJson('/api/v1/profile', ['username' => 'mariaart'])
            ->assertStatus(422)->assertJsonValidationErrors(['username']);

        $this->actingAs($owner, 'sanctum')
            ->patchJson('/api/v1/profile', ['username' => '@DonoAtual'])
            ->assertStatus(200)
            ->assertJsonPath('data.username', 'donoatual');
    }

    public function test_migration_normalizes_existing_usernames_and_skips_collisions_with_log(): void
    {
        Log::spy();
        $clean = Artist::factory()->create(['username' => '@LegacyUser']);
        $existing = Artist::factory()->create(['username' => 'conflito']);
        $colliding = Artist::factory()->create(['username' => '@Conflito']);

        $migration = require database_path('migrations/2026_11_14_000002_normalize_artist_usernames.php');
        $migration->up();

        $this->assertSame('legacyuser', DB::table('artists')->where('id', $clean->id)->value('username'));
        $this->assertSame('conflito', DB::table('artists')->where('id', $existing->id)->value('username'));
        $this->assertSame('@Conflito', DB::table('artists')->where('id', $colliding->id)->value('username'));
        Log::shouldHaveReceived('warning')->once();
    }
}
