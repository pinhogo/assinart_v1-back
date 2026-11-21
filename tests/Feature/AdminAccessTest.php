<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_anonymous_request_to_admin_dashboard_returns_401(): void
    {
        $response = $this->getJson('/api/v1/admin/validation-dashboard');

        $response->assertStatus(401);
    }

    public function test_regular_verified_artist_is_not_admin_and_receives_403(): void
    {
        config(['app.admin_emails' => ['admin@assin.art']]);

        $artist = Artist::factory()->create([
            'email' => 'artista@assin.art',
            'password' => Hash::make('secret123'),
            'email_verified_at' => now(),
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'artista@assin.art',
            'password' => 'secret123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonPath('data.user.is_admin', false)
            ->assertJsonPath('artist.is_admin', false);

        Sanctum::actingAs($artist);

        $this->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('data.is_admin', false);

        $this->getJson('/api/v1/admin/validation-dashboard')
            ->assertStatus(403)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_artist_with_admin_email_but_unverified_email_is_blocked_with_403(): void
    {
        config(['app.admin_emails' => ['admin@assin.art']]);

        $artist = Artist::factory()->create([
            'email' => 'admin@assin.art',
            'email_verified_at' => null,
        ]);

        Sanctum::actingAs($artist);

        $this->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('data.is_admin', false);

        $this->getJson('/api/v1/admin/validation-dashboard')
            ->assertStatus(403)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_verified_admin_email_case_insensitive_with_whitespace_has_full_admin_access(): void
    {
        config(['app.admin_emails' => ['  Admin@Assin.Art  ']]);

        $admin = Artist::factory()->create([
            'email' => 'admin@assin.art',
            'password' => Hash::make('secret123'),
            'email_verified_at' => now(),
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@assin.art',
            'password' => 'secret123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonPath('data.user.is_admin', true)
            ->assertJsonPath('artist.is_admin', true);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('data.is_admin', true);

        $this->getJson('/api/v1/admin/validation-dashboard')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_mass_assignment_of_is_admin_is_ignored_on_register_and_profile_update(): void
    {
        config(['app.admin_emails' => ['admin@assin.art']]);

        $registerResponse = $this->postJson('/api/v1/auth/register', [
            'name' => 'Tentativa Admin',
            'username' => 'tentativa_admin',
            'email' => 'comum@assin.art',
            'password' => 'secret123',
            'agree_terms' => true,
            'is_admin' => true,
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonPath('data.user.is_admin', false)
            ->assertJsonPath('artist.is_admin', false);

        $artist = Artist::where('email', 'comum@assin.art')->firstOrFail();
        $artist->forceFill(['email_verified_at' => now()])->save();

        Sanctum::actingAs($artist);

        $this->putJson('/api/v1/profile', [
            'name' => 'Tentativa Admin Atualizada',
            'is_admin' => true,
        ])->assertStatus(200)
            ->assertJsonPath('data.is_admin', false);

        $this->getJson('/api/v1/admin/validation-dashboard')
            ->assertStatus(403)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_empty_admin_emails_config_blocks_all_artists(): void
    {
        config(['app.admin_emails' => []]);

        $artist = Artist::factory()->create([
            'email' => 'admin@assin.art',
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($artist);

        $this->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('data.is_admin', false);

        $this->getJson('/api/v1/admin/validation-dashboard')
            ->assertStatus(403)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    public function test_public_profile_does_not_leak_is_admin_or_email_for_admin_artist(): void
    {
        config(['app.admin_emails' => ['admin@assin.art']]);

        $admin = Artist::factory()->create([
            'username' => 'admin_publico',
            'email' => 'admin@assin.art',
            'email_verified_at' => now(),
            'is_published' => true,
        ]);

        Button::factory()->create([
            'artist_id' => $admin->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/' . $admin->username);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'found')
            ->assertJsonMissingPath('artist.is_admin')
            ->assertJsonMissingPath('artist.email')
            ->assertJsonMissingPath('data.is_admin')
            ->assertJsonMissingPath('data.email');
    }
}
