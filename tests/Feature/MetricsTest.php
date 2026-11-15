<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use App\Models\ButtonClick;
use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_pageview_records_telemetry_anonymously(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'testart',
        ]);

        $payload = [
            'type' => 'pageview',
            'username' => '@testart',
            'visitor_id' => 'anon-uuid-1234',
            'referrer' => 'https://instagram.com/p/xyz',
            'utm_source' => 'bio',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
        ];

        $response = $this->postJson('/api/v1/track', $payload);

        $response->assertStatus(204);

        $this->assertDatabaseHas('page_views', [
            'artist_id' => $artist->id,
            'visitor_id' => 'anon-uuid-1234',
            'referrer_source' => 'instagram',
            'device_type' => 'mobile',
            'utm_source' => 'bio',
        ]);
    }

    public function test_track_click_records_click_and_increments_count(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'clicks_count' => 10,
        ]);

        $payload = [
            'type' => 'click',
            'username' => $artist->username,
            'visitor_id' => 'anon-uuid-5678',
            'link_id' => $button->id,
        ];

        $response = $this->postJson('/api/v1/track', $payload);

        $response->assertStatus(204);

        $button->refresh();
        $this->assertEquals(11, $button->clicks_count);

        $this->assertDatabaseHas('button_clicks', [
            'button_id' => $button->id,
            'visitor_id' => 'anon-uuid-5678',
        ]);
    }

    public function test_track_handles_invalid_data_gracefully_with_204(): void
    {
        $response = $this->postJson('/api/v1/track', [
            'type' => 'pageview',
            'username' => 'nonexistent_artist',
        ]);

        $response->assertStatus(204);

        $responseClick = $this->postJson('/api/v1/track', [
            'type' => 'click',
            'link_id' => 999999,
        ]);

        $responseClick->assertStatus(204);
    }

    public function test_redirector_increments_clicks_count_and_creates_button_click(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'url' => 'https://external-portfolio.com',
            'clicks_count' => 5,
            'is_active' => true,
        ]);

        $response = $this->withHeader('X-Visitor-Id', 'visitor-xyz')
            ->get("/r/{$button->id}");

        $response->assertStatus(302);
        $response->assertRedirect('https://external-portfolio.com');

        $button->refresh();
        $this->assertEquals(6, $button->clicks_count);

        $this->assertDatabaseHas('button_clicks', [
            'button_id' => $button->id,
            'visitor_id' => 'visitor-xyz',
        ]);
    }

    public function test_metrics_summary_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/metrics/summary');
        $response->assertStatus(401);
    }

    public function test_metrics_summary_returns_aggregated_analytics(): void
    {
        $artist = Artist::factory()->create();
        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Instagram',
            'platform' => 'instagram',
            'display_mode' => 'button',
        ]);

        PageView::create([
            'artist_id' => $artist->id,
            'visitor_id' => 'vis-1',
            'referrer_source' => 'instagram',
            'device_type' => 'mobile',
            'viewed_at' => now(),
        ]);

        PageView::create([
            'artist_id' => $artist->id,
            'visitor_id' => 'vis-2',
            'referrer_source' => 'direct',
            'device_type' => 'desktop',
            'viewed_at' => now(),
        ]);

        ButtonClick::create([
            'button_id' => $button->id,
            'visitor_id' => 'vis-1',
            'clicked_at' => now(),
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->getJson('/api/v1/metrics/summary?period=7d');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'period',
                    'range' => ['from', 'to'],
                    'totals' => ['views', 'unique_visitors', 'clicks'],
                    'series',
                    'links',
                    'referrers',
                    'devices',
                    'peak' => ['by_hour', 'by_weekday'],
                    'timezone',
                ],
            ])
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'period' => '7d',
                    'totals' => [
                        'views' => 2,
                        'unique_visitors' => 2,
                        'clicks' => 1,
                    ],
                ],
            ]);
    }
}
