<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaStorageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Tier 2: Requisições não autenticadas para rotas de mídia retornam 401.
     */
    public function test_unauthenticated_media_requests_return_401(): void
    {
        $this->postJson('/api/v1/profile/avatar')->assertStatus(401);
        $this->deleteJson('/api/v1/profile/avatar')->assertStatus(401);
        $this->postJson('/api/v1/profile/cover')->assertStatus(401);
        $this->deleteJson('/api/v1/profile/cover')->assertStatus(401);
        $this->postJson('/api/v1/buttons/1/image')->assertStatus(401);
    }

    /**
     * Tier 1: Upload de foto de avatar JPG/PNG/WebP (HTTP 200, atualiza avatar_url e salva arquivo).
     */
    public function test_can_upload_avatar_image(): void
    {
        $artist = Artist::factory()->create();
        $file = UploadedFile::fake()->create('meu_avatar.png', 200, 'image/png');

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/avatar', [
                'avatar' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'avatar_url',
            ])
            ->assertJson([
                'status' => 'success',
            ]);

        $artist->refresh();
        $this->assertNotNull($artist->avatar_url);

        // Verifica que o arquivo foi fisicamente gravado no disco public
        $storedPath = str_replace('/storage/', '', parse_url($artist->avatar_url, PHP_URL_PATH));
        Storage::disk('public')->assertExists($storedPath);
    }

    /**
     * Tier 2: Upload de avatar > 5MB (5120 KB) é rejeitado com HTTP 422.
     */
    public function test_avatar_upload_over_5mb_fails(): void
    {
        $artist = Artist::factory()->create();
        $largeFile = UploadedFile::fake()->create('huge_avatar.jpg', 6000, 'image/jpeg');

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/avatar', [
                'avatar' => $largeFile,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /**
     * Tier 2: Arquivo não-imagem enviado como avatar é rejeitado (422).
     */
    public function test_non_image_avatar_upload_fails(): void
    {
        $artist = Artist::factory()->create();
        $pdfFile = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/avatar', [
                'avatar' => $pdfFile,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /**
     * Tier 1: Substituição de avatar remove o arquivo antigo do disco (zero órfãos).
     */
    public function test_replace_avatar_deletes_old_file_from_disk(): void
    {
        $artist = Artist::factory()->create();

        // 1. Primeiro upload
        $firstFile = UploadedFile::fake()->create('avatar1.jpg', 150, 'image/jpeg');
        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/avatar', ['avatar' => $firstFile]);

        $artist->refresh();
        $firstPath = str_replace('/storage/', '', parse_url($artist->avatar_url, PHP_URL_PATH));
        Storage::disk('public')->assertExists($firstPath);

        // 2. Segundo upload (substituição)
        $secondFile = UploadedFile::fake()->create('avatar2.png', 200, 'image/png');
        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/avatar', ['avatar' => $secondFile]);

        $artist->refresh();
        $secondPath = str_replace('/storage/', '', parse_url($artist->avatar_url, PHP_URL_PATH));

        // 3. O primeiro arquivo deve ter sido excluído e o segundo deve existir
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
    }

    /**
     * Tier 1: Exclusão de avatar (DELETE /api/v1/profile/avatar) limpa avatar_url e apaga arquivo.
     */
    public function test_delete_avatar_removes_file_and_nulls_url(): void
    {
        $artist = Artist::factory()->create();
        $file = UploadedFile::fake()->create('avatar_to_delete.jpg', 150, 'image/jpeg');

        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/avatar', ['avatar' => $file]);

        $artist->refresh();
        $filePath = str_replace('/storage/', '', parse_url($artist->avatar_url, PHP_URL_PATH));
        Storage::disk('public')->assertExists($filePath);

        $response = $this->actingAs($artist, 'sanctum')
            ->deleteJson('/api/v1/profile/avatar');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Avatar removido com sucesso.',
            ]);

        $artist->refresh();
        $this->assertNull($artist->avatar_url);
        Storage::disk('public')->assertMissing($filePath);
    }

    /**
     * Tier 1: Upload de capa panorâmica (POST /api/v1/profile/cover) atualiza cover_url.
     */
    public function test_can_upload_cover_image(): void
    {
        $artist = Artist::factory()->create();
        $coverFile = UploadedFile::fake()->create('banner_topo.webp', 500, 'image/webp');

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/cover', [
                'cover' => $coverFile,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'cover_url',
            ]);

        $artist->refresh();
        $this->assertNotNull($artist->cover_url);

        $storedPath = str_replace('/storage/', '', parse_url($artist->cover_url, PHP_URL_PATH));
        Storage::disk('public')->assertExists($storedPath);
    }

    /**
     * Tier 2: Upload de capa > 10MB (10240 KB) é rejeitado com 422.
     */
    public function test_cover_upload_over_10mb_fails(): void
    {
        $artist = Artist::factory()->create();
        $hugeCover = UploadedFile::fake()->create('huge_banner.jpg', 12000, 'image/jpeg');

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/cover', [
                'cover' => $hugeCover,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cover']);
    }

    /**
     * Tier 1: Substituição de capa remove a capa antiga do disco.
     */
    public function test_replace_cover_deletes_old_file_from_disk(): void
    {
        $artist = Artist::factory()->create();

        $cover1 = UploadedFile::fake()->create('cover1.jpg', 400, 'image/jpeg');
        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/cover', ['cover' => $cover1]);

        $artist->refresh();
        $path1 = str_replace('/storage/', '', parse_url($artist->cover_url, PHP_URL_PATH));
        Storage::disk('public')->assertExists($path1);

        $cover2 = UploadedFile::fake()->create('cover2.jpg', 400, 'image/jpeg');
        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/cover', ['cover' => $cover2]);

        $artist->refresh();
        $path2 = str_replace('/storage/', '', parse_url($artist->cover_url, PHP_URL_PATH));

        Storage::disk('public')->assertMissing($path1);
        Storage::disk('public')->assertExists($path2);
    }

    /**
     * Tier 1: Exclusão de capa (DELETE /api/v1/profile/cover) limpa cover_url e apaga arquivo.
     */
    public function test_delete_cover_removes_file_and_nulls_url(): void
    {
        $artist = Artist::factory()->create();
        $coverFile = UploadedFile::fake()->create('cover_to_delete.jpg', 300, 'image/jpeg');

        $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/cover', ['cover' => $coverFile]);

        $artist->refresh();
        $filePath = str_replace('/storage/', '', parse_url($artist->cover_url, PHP_URL_PATH));
        Storage::disk('public')->assertExists($filePath);

        $response = $this->actingAs($artist, 'sanctum')
            ->deleteJson('/api/v1/profile/cover');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Capa removida com sucesso.',
            ]);

        $artist->refresh();
        $this->assertNull($artist->cover_url);
        Storage::disk('public')->assertMissing($filePath);
    }

    /**
     * Tier 1: Upload de imagem para botão de card em destaque (POST /api/v1/buttons/{id}/image).
     */
    public function test_can_upload_card_button_thumbnail(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'type' => 'card_featured',
        ]);

        $cardImage = UploadedFile::fake()->create('card_thumb.png', 250, 'image/png');

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson("/api/v1/buttons/{$button->id}/image", [
                'image' => $cardImage,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'image_url',
            ]);

        $button->refresh();
        $this->assertNotNull($button->image_url);

        $storedPath = str_replace('/storage/', '', parse_url($button->image_url, PHP_URL_PATH));
        Storage::disk('public')->assertExists($storedPath);
    }

    /**
     * Tier 2: Tentativa de upload de imagem para botão de outro artista retorna 404.
     */
    public function test_cannot_upload_image_to_another_artists_button(): void
    {
        $artistA = Artist::factory()->create();
        $artistB = Artist::factory()->create();

        $buttonB = Button::factory()->create([
            'artist_id' => $artistB->id,
            'type' => 'card_featured',
        ]);

        $cardImage = UploadedFile::fake()->create('card_hacked.png', 100, 'image/png');

        $response = $this->actingAs($artistA, 'sanctum')
            ->postJson("/api/v1/buttons/{$buttonB->id}/image", [
                'image' => $cardImage,
            ]);

        $response->assertStatus(404);
    }

    private const AZURE_BASE = 'https://assinart.blob.core.windows.net/media/';

    private function useAzureDisk(): void
    {
        config(['filesystems.media_disk' => 'azure']);
        Storage::fake('azure');
    }

    public function test_azure_disk_uploads_and_replaces_media_without_orphans(): void
    {
        $this->useAzureDisk();
        $artist = Artist::factory()->create();

        $first = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/profile/avatar', ['avatar' => UploadedFile::fake()->create('a.png', 100, 'image/png')])
            ->assertStatus(200);
        $firstPath = 'avatars/' . basename($first->json('avatar_url'));
        Storage::disk('azure')->assertExists($firstPath);
        Storage::disk('public')->assertMissing($firstPath);

        $second = $this->actingAs($artist->fresh(), 'sanctum')
            ->postJson('/api/v1/profile/avatar', ['avatar' => UploadedFile::fake()->create('b.png', 100, 'image/png')])
            ->assertStatus(200);
        $secondPath = 'avatars/' . basename($second->json('avatar_url'));

        Storage::disk('azure')->assertMissing($firstPath);
        Storage::disk('azure')->assertExists($secondPath);
        $this->assertCount(1, Storage::disk('azure')->allFiles('avatars'));
    }

    public function test_azure_blob_urls_are_extracted_and_deleted_for_profile_media(): void
    {
        $this->useAzureDisk();
        $disk = Storage::disk('azure');
        $disk->put('avatars/a.png', 'x');
        $disk->put('covers/c.png', 'x');
        $disk->put('wallpapers/w.png', 'x');
        $artist = Artist::factory()->create([
            'avatar_url' => self::AZURE_BASE . 'avatars/a.png',
            'cover_url' => self::AZURE_BASE . 'covers/c.png',
            'wallpaper_url' => self::AZURE_BASE . 'wallpapers/w.png',
        ]);

        $this->actingAs($artist, 'sanctum')->deleteJson('/api/v1/profile/avatar')->assertStatus(200);
        $this->actingAs($artist, 'sanctum')->deleteJson('/api/v1/profile/cover')->assertStatus(200);
        $this->actingAs($artist, 'sanctum')->deleteJson('/api/v1/profile/background')->assertStatus(200);

        $disk->assertMissing('avatars/a.png');
        $disk->assertMissing('covers/c.png');
        $disk->assertMissing('wallpapers/w.png');
    }

    public function test_azure_blob_urls_are_deleted_for_buttons_and_legacy_local_urls_fall_back_to_public(): void
    {
        $this->useAzureDisk();
        Storage::disk('azure')->put('cards/one.png', 'x');
        Storage::disk('azure')->put('cards/two.png', 'x');
        Storage::disk('public')->put('cards/legacy.png', 'x');
        $artist = Artist::factory()->create();
        $one = Button::factory()->create(['artist_id' => $artist->id, 'image_url' => self::AZURE_BASE . 'cards/one.png']);
        Button::factory()->create(['artist_id' => $artist->id, 'image_url' => self::AZURE_BASE . 'cards/two.png']);
        Button::factory()->create(['artist_id' => $artist->id, 'image_url' => 'http://localhost/storage/cards/legacy.png']);

        $this->actingAs($artist, 'sanctum')->deleteJson("/api/v1/buttons/{$one->id}")->assertStatus(200);
        Storage::disk('azure')->assertMissing('cards/one.png');

        $this->actingAs($artist, 'sanctum')->deleteJson('/api/v1/buttons')->assertStatus(200);
        Storage::disk('azure')->assertMissing('cards/two.png');
        Storage::disk('public')->assertMissing('cards/legacy.png');
    }

    public function test_azure_media_urls_are_accepted_in_profile_update(): void
    {
        $artist = Artist::factory()->create();

        $this->actingAs($artist, 'sanctum')
            ->putJson('/api/v1/profile', ['wallpaper_url' => self::AZURE_BASE . 'wallpapers/w.png'])
            ->assertStatus(200);
    }

    public function test_storage_idor_protection_blocks_referencing_or_deleting_other_artists_media(): void
    {
        Storage::disk('public')->put('avatars/victim_avatar.png', 'victim');
        Storage::disk('public')->put('cards/victim_card.png', 'victim');

        $victim = Artist::factory()->create([
            'avatar_url' => '/storage/avatars/victim_avatar.png',
            'wallpaper_url' => self::AZURE_BASE . 'wallpapers/victim_wall.png',
        ]);
        Button::factory()->create([
            'artist_id' => $victim->id,
            'image_url' => '/storage/cards/victim_card.png',
        ]);

        $attacker = Artist::factory()->create([
            'avatar_url' => '/storage/avatars/victim_avatar.png',
        ]);
        $attackerButton = Button::factory()->create([
            'artist_id' => $attacker->id,
            'image_url' => '/storage/cards/victim_card.png',
        ]);

        // Requests reject referencing another artist's storage path (422)
        $this->actingAs($attacker, 'sanctum')
            ->putJson('/api/v1/profile', ['avatar_url' => '/storage/avatars/victim_avatar.png'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['avatar_url']);

        $this->actingAs($attacker, 'sanctum')
            ->putJson('/api/v1/profile', ['wallpaper_url' => self::AZURE_BASE . 'wallpapers/victim_wall.png'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['wallpaper_url']);

        $this->actingAs($attacker, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => 'IDOR Card',
                'url' => 'https://example.com',
                'image_url' => '/storage/cards/victim_card.png',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image_url']);

        $this->actingAs($attacker, 'sanctum')
            ->putJson("/api/v1/buttons/{$attackerButton->id}", [
                'image_url' => '/storage/cards/victim_card.png',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image_url']);

        // Deleting attacker's avatar or button must NOT delete victim's files from disk
        $this->actingAs($attacker, 'sanctum')->deleteJson('/api/v1/profile/avatar')->assertStatus(200);
        $this->actingAs($attacker, 'sanctum')->deleteJson("/api/v1/buttons/{$attackerButton->id}")->assertStatus(200);

        Storage::disk('public')->assertExists('avatars/victim_avatar.png');
        Storage::disk('public')->assertExists('cards/victim_card.png');
    }

    public function test_profile_upload_endpoints_enforce_throttle_20_per_minute(): void
    {
        $artist = Artist::factory()->create();

        try {
            for ($i = 0; $i < 20; $i++) {
                $this->actingAs($artist, 'sanctum')
                    ->postJson('/api/v1/profile/avatar', [
                        'avatar' => UploadedFile::fake()->create("a{$i}.png", 50, 'image/png'),
                    ])
                    ->assertStatus(200);
            }

            $this->actingAs($artist, 'sanctum')
                ->postJson('/api/v1/profile/avatar', [
                    'avatar' => UploadedFile::fake()->create('a21.png', 50, 'image/png'),
                ])
                ->assertStatus(429);
        } finally {
            RateLimiter::clear((string) $artist->id);
            Cache::flush();
        }
    }
}
