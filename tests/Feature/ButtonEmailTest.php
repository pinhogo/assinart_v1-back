<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ButtonEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_email_button_with_plain_email_normalizes_to_mailto(): void
    {
        $artist = Artist::factory()->create();

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => 'Contato',
                'url' => ' contato@assin.art ',
                'platform' => 'email',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.platform', 'email')
            ->assertJsonPath('data.url', 'mailto:contato@assin.art');

        $this->assertDatabaseHas('buttons', [
            'artist_id' => $artist->id,
            'platform' => 'email',
            'url' => 'mailto:contato@assin.art',
        ]);
    }

    public function test_post_email_button_with_mailto_prefix_returns_201(): void
    {
        $artist = Artist::factory()->create();

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => 'E-mail Comercial',
                'url' => 'MAILTO:contato@assin.art',
                'platform' => 'email',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.platform', 'email')
            ->assertJsonPath('data.url', 'mailto:contato@assin.art');

        $this->assertDatabaseHas('buttons', [
            'artist_id' => $artist->id,
            'platform' => 'email',
            'url' => 'mailto:contato@assin.art',
        ]);
    }

    public function test_post_email_button_with_invalid_email_returns_422(): void
    {
        $artist = Artist::factory()->create();

        foreach (['email-invalido', 'mailto:invalido', 'mailto:', 'https://assin.art'] as $invalid) {
            $response = $this->actingAs($artist, 'sanctum')
                ->postJson('/api/v1/buttons', [
                    'title' => 'Contato',
                    'url' => $invalid,
                    'platform' => 'email',
                ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['url'])
                ->assertJsonPath('errors.url.0', 'Informe um endereço de e-mail válido.');
        }
    }

    public function test_patch_url_without_platform_on_existing_email_button_normalizes_and_returns_200(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Contato',
            'platform' => 'email',
            'url' => 'mailto:antigo@assin.art',
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->patchJson("/api/v1/buttons/{$button->id}", [
                'url' => 'novo@assin.art',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.platform', 'email')
            ->assertJsonPath('data.url', 'mailto:novo@assin.art');

        $this->assertDatabaseHas('buttons', [
            'id' => $button->id,
            'url' => 'mailto:novo@assin.art',
        ]);
    }

    public function test_patch_changing_platform_to_email_normalizes_url_to_mailto(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Link',
            'platform' => 'custom',
            'url' => 'https://assin.art',
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->patchJson("/api/v1/buttons/{$button->id}", [
                'platform' => 'email',
                'url' => 'contato@assin.art',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.platform', 'email')
            ->assertJsonPath('data.url', 'mailto:contato@assin.art');
    }

    public function test_patch_email_button_with_invalid_email_returns_422(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Contato',
            'platform' => 'email',
            'url' => 'mailto:antigo@assin.art',
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->patchJson("/api/v1/buttons/{$button->id}", [
                'url' => 'nao-eh-email',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url'])
            ->assertJsonPath('errors.url.0', 'Informe um endereço de e-mail válido.');
    }

    public function test_post_non_email_platform_with_mailto_still_returns_422(): void
    {
        $artist = Artist::factory()->create();

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/buttons', [
                'title' => 'Instagram',
                'url' => 'mailto:contato@assin.art',
                'platform' => 'instagram',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url'])
            ->assertJsonPath('errors.url.0', 'A URL de destino deve ser um link válido.');
    }
}
