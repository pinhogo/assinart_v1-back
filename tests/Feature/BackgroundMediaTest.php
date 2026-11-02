<?php

namespace Tests\Feature;

use App\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BackgroundMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_and_delete_background(): void
    {
        Storage::fake('public');

        $artist = Artist::factory()->create([
            'background_color' => '#FFFFFF',
            'wallpaper_url' => null,
        ]);

        Sanctum::actingAs($artist);

        $file = UploadedFile::fake()->image('wallpaper.png', 1920, 1080);

        $uploadResponse = $this->postJson('/api/v1/profile/background', [
            'background' => $file,
        ]);

        $uploadResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Plano de fundo atualizado com sucesso.',
                'media' => [
                    'type' => 'upload',
                ],
            ]);

        $artist->refresh();
        $this->assertNotNull($artist->wallpaper_url);

        $deleteResponse = $this->deleteJson('/api/v1/profile/background');

        $deleteResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Plano de fundo removido com sucesso.',
            ]);

        $artist->refresh();
        $this->assertNull($artist->wallpaper_url);
        $this->assertEquals('#FFFFFF', $artist->background_color);
    }

    public function test_profile_update_supports_patch_and_theme_typography(): void
    {
        $artist = Artist::factory()->create();
        Sanctum::actingAs($artist);

        $response = $this->patchJson('/api/v1/profile', [
            'profile_font_family' => 'Cabinet Grotesk',
            'profile_font_size' => 'lg',
            'profile_font_weight' => 'bold',
            'button_font_weight' => 'bold',
            'border_color' => '#FFE600',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'profile_font_family' => 'Cabinet Grotesk',
                    'profile_font_size' => 'lg',
                    'profile_font_weight' => 'bold',
                    'button_font_weight' => 'bold',
                    'border_color' => '#FFE600',
                ],
            ]);

        $artist->refresh();
        $this->assertEquals('Cabinet Grotesk', $artist->profile_font_family);
        $this->assertEquals('bold', $artist->profile_font_weight);
    }
}
