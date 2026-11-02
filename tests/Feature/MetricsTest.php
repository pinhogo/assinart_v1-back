<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use App\Models\ButtonClick;
use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_track_pageview_telemetry(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'mariasilva',
        ]);

        $response = $this->postJson('/api/v1/track', [
            'type' => 'pageview',
            'username' => 'mariasilva',
            'visitor_id' => 'anon-uuid-1234',
            'referrer' => 'https://www.instagram.com/stories',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)',
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('page_views', [
            'artist_id' => $artist->id,
            'visitor_id' => 'anon-uuid-1234',
            'referrer_source' => 'instagram',
            'device_type' => 'mobile',
        ]);
    }

    public function test_can_fetch_metrics_summary_with_integer_link_ids(): void
    {
        $artist = Artist::factory()->create();

        $button = Button::factory()->create([
            'artist_id' => $artist->id,
            'title' => 'Instagram',
            'clicks_count' => 5,
        ]);

        PageView::create([
            'artist_id' => $artist->id,
            'visitor_id' => 'visitor-1',
            'referrer_source' => 'instagram',
            'device_type' => 'mobile',
            'viewed_at' => now(),
        ]);

        ButtonClick::create([
            'button_id' => $button->id,
            'visitor_id' => 'visitor-1',
            'clicked_at' => now(),
        ]);

        Sanctum::actingAs($artist);

        $response = $this->getJson('/api/v1/metrics/summary?period=30d');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'period' => '30d',
                    'totals' => [
                        'views' => 1,
                        'unique_visitors' => 1,
                        'clicks' => 1,
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertIsArray($data['links']);
        $this->assertCount(1, $data['links']);
        $this->assertIsInt($data['links'][0]['link_id']);
        $this->assertEquals($button->id, $data['links'][0]['link_id']);
    }
}
