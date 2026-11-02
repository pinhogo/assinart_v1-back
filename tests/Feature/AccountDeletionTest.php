<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_cannot_delete_account(): void
    {
        $response = $this->postJson('/api/v1/auth/account/delete', ['password' => 'secret123']);
        $response->assertStatus(401);

        $responseDelete = $this->deleteJson('/api/v1/auth/account', ['password' => 'secret123']);
        $responseDelete->assertStatus(401);
    }

    public function test_requires_password_to_delete_account(): void
    {
        $artist = Artist::factory()->create([
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/auth/account/delete', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_fails_with_incorrect_password(): void
    {
        $artist = Artist::factory()->create([
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/auth/account/delete', ['password' => 'wrong_password']);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Senha incorreta.',
            ]);

        $this->assertDatabaseHas('artists', ['id' => $artist->id]);
    }

    public function test_successfully_deletes_account_cascades_buttons_and_removes_files(): void
    {
        Storage::fake('public');

        $avatarFile = UploadedFile::fake()->image('avatar.png');
        $avatarPath = $avatarFile->store('avatars', 'public');

        $coverFile = UploadedFile::fake()->image('cover.png');
        $coverPath = $coverFile->store('covers', 'public');

        $wallpaperFile = UploadedFile::fake()->image('wallpaper.png');
        $wallpaperPath = $wallpaperFile->store('wallpapers', 'public');

        $cardFile = UploadedFile::fake()->image('card.png');
        $cardPath = $cardFile->store('cards', 'public');

        $artist = Artist::factory()->create([
            'password' => Hash::make('secret123'),
            'avatar_url' => Storage::url($avatarPath),
            'cover_url' => Storage::url($coverPath),
            'wallpaper_url' => Storage::url($wallpaperPath),
        ]);

        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'image_url' => Storage::url($cardPath),
        ]);

        $artist->createToken('test_token');

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/auth/account/delete', ['password' => 'secret123']);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Conta excluída permanentemente.',
            ]);

        $this->assertDatabaseMissing('artists', ['id' => $artist->id]);
        $this->assertDatabaseMissing('buttons', ['id' => $button->id]);

        Storage::disk('public')->assertMissing($avatarPath);
        Storage::disk('public')->assertMissing($coverPath);
        Storage::disk('public')->assertMissing($wallpaperPath);
        Storage::disk('public')->assertMissing($cardPath);
    }
}
