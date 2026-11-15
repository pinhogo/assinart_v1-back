<?php

namespace Tests\Feature;

use App\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackgroundMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_requests_return_401(): void
    {
        $this->postJson('/api/v1/profile/background')->assertStatus(401);
        $this->deleteJson('/api/v1/profile/background')->assertStatus(401);
        $this->postJson('/api/v1/profile/wallpaper')->assertStatus(401);
        $this->deleteJson('/api/v1/profile/wallpaper')->assertStatus(401);
    }

    public function test_upload_background_stores_file_and_updates_artist(): void
    {
        Storage::fake('public');
        $artist = Artist::factory()->create([
            'wallpaper_url' => null,
            'background_id' => 'preset_old',
        ]);

        $file = UploadedFile::fake()->image('wallpaper.jpg', 1920, 1080);

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/background', [
                'background' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'background_url',
                'wallpaper_url',
                'media' => ['type', 'value', 'url'],
            ])
            ->assertJson([
                'status' => 'success',
                'media' => [
                    'type' => 'upload',
                ],
            ]);

        $artist->refresh();
        $this->assertNotNull($artist->wallpaper_url);
        $this->assertNull($artist->background_id);

        $storedPath = 'wallpapers/' . $file->hashName();
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_upload_background_replaces_and_deletes_previous_file(): void
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old_wallpaper.png');
        $oldPath = $oldFile->store('wallpapers', 'public');

        $artist = Artist::factory()->create([
            'wallpaper_url' => Storage::url($oldPath),
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $newFile = UploadedFile::fake()->image('new_wallpaper.png');

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/wallpaper', [
                'wallpaper' => $newFile,
            ]);

        $response->assertStatus(200);

        Storage::disk('public')->assertMissing($oldPath);

        $newStoredPath = 'wallpapers/' . $newFile->hashName();
        Storage::disk('public')->assertExists($newStoredPath);
    }

    public function test_delete_background_removes_file_and_resets_attributes(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('wallpaper.png');
        $path = $file->store('wallpapers', 'public');

        $artist = Artist::factory()->create([
            'wallpaper_url' => Storage::url($path),
            'background_id' => 'pattern_dots',
            'background_color' => '#123456',
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->deleteJson('/api/v1/profile/background');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Plano de fundo removido com sucesso.',
            ]);

        Storage::disk('public')->assertMissing($path);

        $artist->refresh();
        $this->assertNull($artist->wallpaper_url);
        $this->assertNull($artist->background_id);
        $this->assertEquals('#FFFFFF', $artist->background_color);
    }

    public function test_patch_profile_handles_polymorphic_media_inputs(): void
    {
        $artist = Artist::factory()->create([
            'background_color' => '#FFFFFF',
            'background_id' => null,
            'cover_color' => null,
            'cover_url' => 'https://cdn.example.com/cover.png',
            'avatar_url' => 'https://cdn.example.com/avatar.png',
        ]);

        // 1. Atualizar com cor de background e preset de avatar
        $response1 = $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'background' => 'color:#FF5733',
                'avatar' => 'preset:avatar_02',
            ]);

        $response1->assertStatus(200);
        $artist->refresh();
        $this->assertEquals('#FF5733', $artist->background_color);
        $this->assertNull($artist->background_id);
        $this->assertNull($artist->avatar_url);

        // 2. Atualizar com preset de background e cor de capa
        $response2 = $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'background' => 'preset:grid_dark',
                'cover' => 'color:#000000',
            ]);

        $response2->assertStatus(200);
        $artist->refresh();
        $this->assertEquals('grid_dark', $artist->background_id);
        $this->assertEquals('#000000', $artist->cover_color);
        $this->assertNull($artist->cover_url);
    }

    public function test_patch_profile_accepts_safe_data_image_svg_presets_and_rejects_xss(): void
    {
        $artist = Artist::factory()->create([
            'cover_color' => '#334455',
            'cover_url' => null,
            'avatar_url' => null,
        ]);

        $svgAvatar = 'data:image/svg+xml;utf8,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3C/svg%3E';
        $svgCover = 'data:image/svg+xml;utf8,%3Csvg%20viewBox%3D%220%200%2010%2010%22%3E%3C/svg%3E';

        $okResponse = $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'avatar_url' => $svgAvatar,
                'cover_url' => $svgCover,
            ]);

        $okResponse->assertStatus(200);
        $artist->refresh();
        $this->assertEquals($svgAvatar, $artist->avatar_url);
        $this->assertEquals($svgCover, $artist->cover_url);
        $this->assertNull($artist->cover_color);

        $solidCoverResponse = $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'cover_color' => '#6B2D3C',
            ]);

        $solidCoverResponse->assertStatus(200);
        $artist->refresh();
        $this->assertEquals('#6B2D3C', $artist->cover_color);
        $this->assertNull($artist->cover_url);

        $xssResponse = $this->actingAs($artist, 'sanctum')
            ->patchJson('/api/v1/profile', [
                'avatar_url' => 'data:image/svg+xml;utf8,%3Csvg%3E%3Cscript%3Ealert(1)%3C/script%3E%3C/svg%3E',
            ]);

        $xssResponse->assertStatus(422)
            ->assertJsonValidationErrors(['avatar_url']);
    }
}
