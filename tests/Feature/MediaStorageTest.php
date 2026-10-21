<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
}
