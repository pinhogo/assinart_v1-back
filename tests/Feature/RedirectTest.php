<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tier 1: Rastreador de cliques incrementa atomicamente clicks_count e redireciona (302 Found).
     */
    public function test_click_tracker_increments_clicks_and_redirects(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'url' => 'https://assin.art/sobre',
            'clicks_count' => 0,
            'is_active' => true,
        ]);

        $response = $this->get("/r/{$button->id}");

        $response->assertStatus(302)
            ->assertRedirect('https://assin.art/sobre');

        $button->refresh();
        $this->assertEquals(1, $button->clicks_count);
    }

    /**
     * Tier 2: Múltiplos cliques consecutivos acumulam corretamente a contagem de cliques.
     */
    public function test_multiple_clicks_accumulate_clicks_count(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'url' => 'https://spotify.com/track/123',
            'clicks_count' => 10,
            'is_active' => true,
        ]);

        $this->get("/r/{$button->id}")->assertStatus(302);
        $this->get("/r/{$button->id}")->assertStatus(302);
        $this->get("/r/{$button->id}")->assertStatus(302);

        $button->refresh();
        $this->assertEquals(13, $button->clicks_count);
    }

    /**
     * Tier 2: Botão inativo (is_active = false) retorna HTTP 404 e não incrementa cliques.
     */
    public function test_inactive_button_redirect_returns_404_and_does_not_increment(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'url' => 'https://inativo.com',
            'clicks_count' => 5,
            'is_active' => false,
        ]);

        $response = $this->get("/r/{$button->id}");

        $response->assertStatus(404);

        $button->refresh();
        $this->assertEquals(5, $button->clicks_count);
    }

    /**
     * Tier 2: ID de botão inexistente retorna HTTP 404.
     */
    public function test_nonexistent_button_id_returns_404(): void
    {
        $response = $this->get('/r/999999');

        $response->assertStatus(404);
    }

    /**
     * Tier 2: ID com formato inválido (não numérico) é bloqueado pelo roteador (404).
     */
    public function test_non_numeric_button_id_returns_404(): void
    {
        $response = $this->get('/r/not-a-number');

        $response->assertStatus(404);
    }

    public function test_redirect_returns_404_for_draft_artist(): void
    {
        $artist = Artist::factory()->create(['is_published' => false]);
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'url' => 'https://assin.art/draft',
            'clicks_count' => 2,
            'is_active' => true,
        ]);

        $this->get("/r/{$button->id}")->assertStatus(404);
        $this->assertEquals(2, $button->fresh()->clicks_count);
    }

    public function test_redirect_returns_404_for_unsafe_url_scheme(): void
    {
        $artist = Artist::factory()->create(['is_published' => true]);
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'url' => 'javascript:alert(1)',
            'clicks_count' => 0,
            'is_active' => true,
        ]);

        $this->get("/r/{$button->id}")->assertStatus(404);
        $this->assertEquals(0, $button->fresh()->clicks_count);
    }

    public function test_redirect_truncates_visitor_id_to_64_characters(): void
    {
        $artist = Artist::factory()->create(['is_published' => true]);
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'url' => 'https://assin.art/ok',
            'is_active' => true,
        ]);

        $longVisitor = str_repeat('x', 100);
        $this->withHeader('X-Visitor-Id', $longVisitor)
            ->get("/r/{$button->id}")
            ->assertStatus(302);

        $this->assertDatabaseHas('button_clicks', [
            'button_id' => $button->id,
            'visitor_id' => substr($longVisitor, 0, 64),
        ]);
    }
}
