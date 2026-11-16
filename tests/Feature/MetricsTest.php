<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use App\Models\ButtonClick;
use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
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

    public function test_track_validates_fields_and_returns_422(): void
    {
        $this->postJson('/api/v1/track', [
            'type' => 'invalid_event',
        ])->assertStatus(422)->assertJsonValidationErrors(['type']);

        $this->postJson('/api/v1/track', [
            'type' => 'pageview',
            'visitor_id' => str_repeat('a', 65),
            'link_id' => 0,
        ])->assertStatus(422)->assertJsonValidationErrors(['visitor_id', 'link_id']);
    }

    public function test_track_ignores_draft_artist_and_inactive_or_draft_buttons(): void
    {
        $draftArtist = Artist::factory()->create([
            'username' => 'draft_artist',
            'is_published' => false,
        ]);
        $draftButton = Button::factory()->create([
            'artist_id' => $draftArtist->id,
            'is_active' => true,
            'clicks_count' => 0,
        ]);

        $publishedArtist = Artist::factory()->create(['is_published' => true]);
        $inactiveButton = Button::factory()->create([
            'artist_id' => $publishedArtist->id,
            'is_active' => false,
            'clicks_count' => 0,
        ]);

        $this->postJson('/api/v1/track', [
            'type' => 'pageview',
            'username' => 'draft_artist',
            'visitor_id' => 'v1',
        ])->assertStatus(204);

        $this->postJson('/api/v1/track', [
            'type' => 'click',
            'link_id' => $draftButton->id,
            'visitor_id' => 'v1',
        ])->assertStatus(204);

        $this->postJson('/api/v1/track', [
            'type' => 'click',
            'link_id' => $inactiveButton->id,
            'visitor_id' => 'v1',
        ])->assertStatus(204);

        $this->assertDatabaseCount('page_views', 0);
        $this->assertDatabaseCount('button_clicks', 0);
        $this->assertSame(0, $draftButton->fresh()->clicks_count);
        $this->assertSame(0, $inactiveButton->fresh()->clicks_count);
    }

    public function test_track_enforces_throttle_120_per_minute(): void
    {
        try {
            for ($i = 0; $i < 120; $i++) {
                $this->postJson('/api/v1/track', [
                    'type' => 'pageview',
                    'username' => 'missing',
                ])->assertStatus(204);
            }

            $this->postJson('/api/v1/track', [
                'type' => 'pageview',
                'username' => 'missing',
            ])->assertStatus(429);
        } finally {
            RateLimiter::clear('127.0.0.1');
            Cache::flush();
        }
    }
}
