<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use App\Models\ButtonClick;
use App\Models\FeatureLead;
use App\Models\FunnelEvent;
use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): Artist
    {
        config(['app.admin_emails' => ['admin@assin.art']]);

        $admin = Artist::factory()->create([
            'name' => 'Admin AssinArt',
            'username' => 'admin_assinart',
            'email' => 'admin@assin.art',
            'email_verified_at' => now(),
            'created_at' => now()->subDays(40),
            'updated_at' => now()->subDays(40),
        ]);

        Sanctum::actingAs($admin);

        return $admin;
    }

    public function test_empty_database_with_only_admin_returns_zeroed_metrics_without_division_by_zero(): void
    {
        $this->actingAsAdmin();

        $response = $this->getJson('/api/v1/admin/validation-dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.period.days', 30)
            ->assertJsonPath('data.kpis.artist_activation_rate.value', 0)
            ->assertJsonPath('data.kpis.public_links_ctr.value', 0)
            ->assertJsonPath('data.kpis.editor_vitrine_intent_rate.value', 0)
            ->assertJsonPath('data.kpis.landing_conversion_rate.value', 0)
            ->assertJsonPath('data.krs.kr1_active_artists_weekly.current', 0)
            ->assertJsonPath('data.krs.kr1_active_artists_weekly.progress_pct', 0)
            ->assertJsonPath('data.krs.kr2_vitrine_leads_total.current', 0)
            ->assertJsonPath('data.krs.kr2_vitrine_leads_total.progress_pct', 0)
            ->assertJsonPath('data.krs.kr3_primary_retention_15d.returning_artists', 0)
            ->assertJsonPath('data.krs.kr3_primary_retention_15d.active_artists_base', 0)
            ->assertJsonPath('data.krs.kr3_primary_retention_15d.current_pct', 0)
            ->assertJsonPath('data.krs.kr3_primary_retention_15d.target_pct', 40)
            ->assertJsonPath('data.krs.kr3_primary_retention_15d.progress_pct', 0)
            ->assertJsonPath('data.leads_breakdown.total', 0)
            ->assertJsonPath('data.recent_feature_leads', []);

        $comparison = $response->json('data.feature_comparison');
        $this->assertCount(2, $comparison);
        $this->assertSame('vitrine', $comparison[0]['feature']);
        $this->assertSame(0, $comparison[0]['leads_total']);
        $this->assertSame('assistente', $comparison[1]['feature']);
        $this->assertSame(0, $comparison[1]['leads_total']);
    }

    public function test_populated_database_returns_consolidated_kpis_krs_comparison_top_artists_and_leads(): void
    {
        $now = now();
        $this->actingAsAdmin();

        // Artist 1: activated (>= 20 views in 7d + active button), returning via editor_session
        $artist1 = Artist::factory()->create([
            'name' => 'Clara Pintura',
            'username' => 'clara_pintura',
            'created_at' => $now->copy()->subDays(3),
            'updated_at' => $now->copy()->subDays(3),
        ]);
        $button1 = Button::factory()->create([
            'artist_id' => $artist1->id,
            'is_active' => true,
            'created_at' => $now->copy()->subDays(3),
            'updated_at' => $now->copy()->subDays(3),
        ]);
        for ($i = 0; $i < 20; $i++) {
            PageView::create([
                'artist_id' => $artist1->id,
                'visitor_id' => 'a1-v-' . $i,
                'referrer_source' => 'instagram',
                'device_type' => 'mobile',
                'viewed_at' => $now->copy()->subDays(1),
            ]);
        }
        for ($i = 0; $i < 5; $i++) {
            ButtonClick::create([
                'button_id' => $button1->id,
                'visitor_id' => 'a1-c-' . $i,
                'clicked_at' => $now->copy()->subDays(1),
            ]);
        }

        // Artist 2: activated (5 views in 30d + active button), NOT returning in 15d
        $artist2 = Artist::factory()->create([
            'name' => 'Bruno Ceramica',
            'username' => 'bruno_ceramica',
            'created_at' => $now->copy()->subDays(20),
            'updated_at' => $now->copy()->subDays(20),
        ]);
        Button::factory()->create([
            'artist_id' => $artist2->id,
            'is_active' => true,
            'created_at' => $now->copy()->subDays(20),
            'updated_at' => $now->copy()->subDays(20),
        ]);
        for ($i = 0; $i < 5; $i++) {
            PageView::create([
                'artist_id' => $artist2->id,
                'visitor_id' => 'a2-v-' . $i,
                'referrer_source' => 'direct',
                'device_type' => 'desktop',
                'viewed_at' => $now->copy()->subDays(10),
            ]);
        }

        // Artist 3: not activated (inactive button, 10 views)
        $artist3 = Artist::factory()->create([
            'name' => 'Duda Gravura',
            'username' => 'duda_gravura',
            'created_at' => $now->copy()->subDays(2),
            'updated_at' => $now->copy()->subDays(2),
        ]);
        Button::factory()->create([
            'artist_id' => $artist3->id,
            'is_active' => false,
            'created_at' => $now->copy()->subDays(2),
            'updated_at' => $now->copy()->subDays(2),
        ]);
        for ($i = 0; $i < 10; $i++) {
            PageView::create([
                'artist_id' => $artist3->id,
                'visitor_id' => 'a3-v-' . $i,
                'referrer_source' => 'google',
                'device_type' => 'mobile',
                'viewed_at' => $now->copy()->subDays(2),
            ]);
        }

        // Funnel events
        FunnelEvent::create([
            'event_name' => 'editor_session',
            'visitor_id' => 'ed-1',
            'artist_id' => $artist1->id,
            'occurred_at' => $now->copy()->subDays(1),
        ]);
        FunnelEvent::create([
            'event_name' => 'editor_session',
            'visitor_id' => 'ed-3',
            'artist_id' => $artist3->id,
            'occurred_at' => $now->copy()->subDays(1),
        ]);
        FunnelEvent::create([
            'event_name' => 'editor_vitrine_cta_clicked',
            'visitor_id' => 'cta-1',
            'artist_id' => $artist1->id,
            'occurred_at' => $now->copy()->subDays(1),
        ]);
        for ($i = 0; $i < 10; $i++) {
            FunnelEvent::create([
                'event_name' => 'home_pageview',
                'visitor_id' => 'home-v-' . $i,
                'artist_id' => null,
                'occurred_at' => $now->copy()->subDays(1),
            ]);
        }

        // Feature leads
        FeatureLead::create([
            'artist_id' => null,
            'feature' => 'vitrine',
            'source' => 'home_hero',
            'contact_value' => 'anon@exemplo.com',
            'metadata' => ['selling_type' => 'prints'],
            'created_at' => $now->copy()->subMinutes(10),
        ]);
        FeatureLead::create([
            'artist_id' => $artist1->id,
            'feature' => 'vitrine',
            'source' => 'editor_banner',
            'contact_value' => $artist1->email,
            'metadata' => ['selling_types' => ['originais', 'comissoes']],
            'created_at' => $now->copy()->subMinutes(5),
        ]);
        FeatureLead::create([
            'artist_id' => $artist2->id,
            'feature' => 'assistente',
            'source' => 'home_waitlist',
            'contact_value' => $artist2->email,
            'metadata' => ['selling_type' => 'prints'],
            'created_at' => $now->copy()->subMinutes(1),
        ]);

        // Default days = 30
        $response = $this->getJson('/api/v1/admin/validation-dashboard');
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.period.days', 30)
            // KPI 1: 2 activated / 4 total artists (admin + 3 artists) = 50%
            ->assertJsonPath('data.kpis.artist_activation_rate.activated_artists', 2)
            ->assertJsonPath('data.kpis.artist_activation_rate.total_artists', 4)
            ->assertJsonPath('data.kpis.artist_activation_rate.value', 50)
            // KPI 2: 5 clicks / 35 views = 14.29%
            ->assertJsonPath('data.kpis.public_links_ctr.clicks', 5)
            ->assertJsonPath('data.kpis.public_links_ctr.page_views', 35)
            ->assertJsonPath('data.kpis.public_links_ctr.value', 14.29)
            // KPI 3: 1 cta / 2 editor_sessions = 50%
            ->assertJsonPath('data.kpis.editor_vitrine_intent_rate.vitrine_cta_clicks', 1)
            ->assertJsonPath('data.kpis.editor_vitrine_intent_rate.editor_sessions', 2)
            ->assertJsonPath('data.kpis.editor_vitrine_intent_rate.value', 50)
            // KPI 4: (3 new artists in 30d + 2 home_leads) / 10 home visitors = 50%
            ->assertJsonPath('data.kpis.landing_conversion_rate.new_artists', 3)
            ->assertJsonPath('data.kpis.landing_conversion_rate.home_leads', 2)
            ->assertJsonPath('data.kpis.landing_conversion_rate.home_unique_visitors', 10)
            ->assertJsonPath('data.kpis.landing_conversion_rate.value', 50)
            // KRs
            ->assertJsonPath('data.krs.kr1_active_artists_weekly.current', 1)
            ->assertJsonPath('data.krs.kr2_vitrine_leads_total.current', 2)
            ->assertJsonPath('data.krs.kr3_primary_retention_15d.returning_artists', 1)
            ->assertJsonPath('data.krs.kr3_primary_retention_15d.active_artists_base', 2)
            ->assertJsonPath('data.krs.kr3_primary_retention_15d.current_pct', 50)
            ->assertJsonPath('data.krs.kr3_primary_retention_15d.target_pct', 40)
            ->assertJsonPath('data.krs.kr3_primary_retention_15d.progress_pct', 100)
            // Breakdown
            ->assertJsonPath('data.leads_breakdown.total', 3)
            ->assertJsonPath('data.leads_breakdown.by_feature.vitrine', 2)
            ->assertJsonPath('data.leads_breakdown.by_feature.assistente', 1)
            ->assertJsonPath('data.leads_breakdown.by_source.home_hero', 1)
            ->assertJsonPath('data.leads_breakdown.by_source.editor_banner', 1)
            ->assertJsonPath('data.leads_breakdown.by_source.home_waitlist', 1)
            ->assertJsonPath('data.leads_breakdown.selling_preferences.prints', 2)
            ->assertJsonPath('data.leads_breakdown.selling_preferences.originais', 1)
            ->assertJsonPath('data.leads_breakdown.selling_preferences.comissoes', 1);

        // Feature comparison: vitrine first
        $comparison = $response->json('data.feature_comparison');
        $this->assertSame('vitrine', $comparison[0]['feature']);
        $this->assertSame(2, $comparison[0]['leads_total']);
        $this->assertSame(1, $comparison[0]['unique_artists']);
        $this->assertSame(1, $comparison[0]['home_leads']);
        $this->assertSame(1, $comparison[0]['editor_leads']);
        $this->assertSame(1, $comparison[0]['editor_cta_clicks']);

        // Top active artists ordered by page_views desc
        $topArtists = $response->json('data.top_active_artists');
        $this->assertSame($artist1->id, $topArtists[0]['id']);
        $this->assertEquals(25.0, $topArtists[0]['ctr']);
        $this->assertTrue($topArtists[0]['has_vitrine_interest']);
        $this->assertEqualsCanonicalizing(['originais', 'comissoes'], $topArtists[0]['vitrine_selling_types']);
        $this->assertSame($artist3->id, $topArtists[1]['id']);
        $this->assertSame($artist2->id, $topArtists[2]['id']);

        // Recent feature leads ordered newest first
        $recentLeads = $response->json('data.recent_feature_leads');
        $this->assertCount(3, $recentLeads);
        $this->assertSame('assistente', $recentLeads[0]['feature']);
        $this->assertSame('Bruno Ceramica', $recentLeads[0]['artist_name']);
        $this->assertSame('bruno_ceramica', $recentLeads[0]['artist_username']);
        $this->assertNull($recentLeads[2]['artist_name']);
        $this->assertNull($recentLeads[2]['artist_username']);

        // Explicit days filters: 7, 90, 365
        $this->getJson('/api/v1/admin/validation-dashboard?days=7')
            ->assertStatus(200)
            ->assertJsonPath('data.period.days', 7)
            ->assertJsonPath('data.kpis.public_links_ctr.page_views', 30);

        $this->getJson('/api/v1/admin/validation-dashboard?days=90')
            ->assertStatus(200)
            ->assertJsonPath('data.period.days', 90);

        $this->getJson('/api/v1/admin/validation-dashboard?days=365')
            ->assertStatus(200)
            ->assertJsonPath('data.period.days', 365);
    }

    public function test_dashboard_caps_top_active_artists_at_20_and_recent_leads_at_50(): void
    {
        $now = now();
        $this->actingAsAdmin();

        // Create 22 artists (plus admin = 23 total artists) with distinct page view counts
        for ($i = 1; $i <= 22; $i++) {
            $artist = Artist::factory()->create();
            for ($v = 0; $v < $i; $v++) {
                PageView::create([
                    'artist_id' => $artist->id,
                    'visitor_id' => "bulk-{$i}-{$v}",
                    'referrer_source' => 'direct',
                    'device_type' => 'mobile',
                    'viewed_at' => $now->copy()->subHours(2),
                ]);
            }
        }

        // Create 52 feature leads
        for ($i = 1; $i <= 52; $i++) {
            FeatureLead::create([
                'artist_id' => null,
                'feature' => 'vitrine',
                'source' => 'home_hero',
                'contact_value' => "lead{$i}@exemplo.com",
                'created_at' => $now->copy()->subMinutes(60 - $i),
            ]);
        }

        $response = $this->getJson('/api/v1/admin/validation-dashboard');
        $response->assertStatus(200);

        $topArtists = $response->json('data.top_active_artists');
        $this->assertCount(20, $topArtists);
        $this->assertSame(22, $topArtists[0]['page_views']);
        $this->assertSame(3, $topArtists[19]['page_views']);

        $recentLeads = $response->json('data.recent_feature_leads');
        $this->assertCount(50, $recentLeads);
        $this->assertSame('lead52@exemplo.com', $recentLeads[0]['contact_value']);
        $this->assertSame('lead3@exemplo.com', $recentLeads[49]['contact_value']);
    }

    public function test_invalid_days_query_parameter_returns_422(): void
    {
        $this->actingAsAdmin();

        $this->getJson('/api/v1/admin/validation-dashboard?days=15')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['days']);

        $this->getJson('/api/v1/admin/validation-dashboard?days=abc')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['days']);
    }
}
