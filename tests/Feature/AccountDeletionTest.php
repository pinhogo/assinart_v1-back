<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_delete_account_with_correct_password(): void
    {
        Storage::fake('public');

        $artist = Artist::factory()->create([
            'password' => Hash::make('secret123'),
        ]);

        $avatar = UploadedFile::fake()->image('avatar.jpg');
        $avatarPath = $avatar->store('avatars', 'public');
        $artist->update(['avatar_url' => Storage::url($avatarPath)]);

        $button = Button::factory()->create([
            'artist_id' => $artist->id,
        ]);

        Sanctum::actingAs($artist);

        $response = $this->postJson('/api/v1/auth/account/delete', [
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Conta excluída permanentemente.',
            ]);

        $this->assertDatabaseMissing('artists', ['id' => $artist->id]);
        $this->assertDatabaseMissing('buttons', ['id' => $button->id]);
        Storage::disk('public')->assertMissing($avatarPath);
    }

    public function test_delete_account_with_wrong_password_fails_with_422(): void
    {
        $artist = Artist::factory()->create([
            'password' => Hash::make('secret123'),
        ]);

        Sanctum::actingAs($artist);

        $response = $this->postJson('/api/v1/auth/account/delete', [
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseHas('artists', ['id' => $artist->id]);
    }

    public function test_delete_account_unauthenticated_returns_401(): void
    {
        $response = $this->postJson('/api/v1/auth/account/delete', [
            'password' => 'secret123',
        ]);

        $response->assertStatus(401);
    }
}
