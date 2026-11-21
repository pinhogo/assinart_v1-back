<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\Button;
use App\Models\ButtonClick;
use App\Models\FeatureLead;
use App\Models\FunnelEvent;
use App\Models\PageView;
use App\Services\ValidationKpiReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ValidationKpiReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_database_returns_zero_percentages_without_division_by_zero(): void
    {
        $report = app(ValidationKpiReportService::class)->generate(30);

        $this->assertSame(0.0, $report['kpis']['artist_activation_rate']['value']);
        $this->assertSame(0.0, $report['kpis']['public_links_ctr']['value']);
        $this->assertSame(0.0, $report['kpis']['editor_vitrine_intent_rate']['value']);
        $this->assertSame(0.0, $report['kpis']['landing_conversion_rate']['value']);
        $this->assertSame(0.0, $report['krs']['kr1_active_artists_weekly']['progress_pct']);
        $this->assertSame(0.0, $report['krs']['kr2_vitrine_leads_total']['progress_pct']);
        $this->assertSame(0, $report['leads_breakdown']['total']);

        $exitCode = Artisan::call('metrics:validation-report', ['--json' => true]);
        $this->assertSame(0, $exitCode);

        $decoded = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals(0.0, $decoded['kpis']['artist_activation_rate']['value']);
        $this->assertEquals(0.0, $decoded['kpis']['public_links_ctr']['value']);
        $this->assertEquals(0.0, $decoded['kpis']['editor_vitrine_intent_rate']['value']);
        $this->assertEquals(0.0, $decoded['kpis']['landing_conversion_rate']['value']);
        $this->assertEquals(0.0, $decoded['krs']['kr1_active_artists_weekly']['progress_pct']);
        $this->assertEquals(0.0, $decoded['krs']['kr2_vitrine_leads_total']['progress_pct']);
    }

    public function test_populated_database_computes_exact_kpis_krs_and_leads_breakdown_in_service_and_command(): void
    {
        $now = now();

        // 1) Activated artist (>= 20 views in last 7 days + active button -> counts for KPI 1 and KR1)
        $artist1 = Artist::factory()->create(['created_at' => $now->copy()->subDays(2)]);
        $button1 = Button::factory()->create(['artist_id' => $artist1->id, 'is_active' => true]);
        for ($i = 0; $i < 20; $i++) {
            PageView::create([
                'artist_id' => $artist1->id,
                'visitor_id' => 'a1-vis-' . $i,
                'referrer_source' => 'instagram',
                'device_type' => 'mobile',
                'viewed_at' => $now->copy()->subDays(1),
            ]);
        }

        // 2) Activated artist (5 views in 30d + active button -> counts for KPI 1, NOT KR1)
        $artist2 = Artist::factory()->create(['created_at' => $now->copy()->subDays(5)]);
        Button::factory()->create(['artist_id' => $artist2->id, 'is_active' => true]);
        for ($i = 0; $i < 5; $i++) {
            PageView::create([
                'artist_id' => $artist2->id,
                'visitor_id' => 'a2-vis-' . $i,
                'referrer_source' => 'direct',
                'device_type' => 'desktop',
                'viewed_at' => $now->copy()->subDays(10),
            ]);
        }

        // 3) Inactive artist (only inactive button, 10 views -> NOT activated)
        $artist3 = Artist::factory()->create(['created_at' => $now->copy()->subDays(1)]);
        Button::factory()->create(['artist_id' => $artist3->id, 'is_active' => false]);
        for ($i = 0; $i < 10; $i++) {
            PageView::create([
                'artist_id' => $artist3->id,
                'visitor_id' => 'a3-vis-' . $i,
                'referrer_source' => 'google',
                'device_type' => 'mobile',
                'viewed_at' => $now->copy()->subDays(3),
            ]);
        }

        // Total PageViews = 35; ButtonClicks = 7 -> CTR = 7 / 35 = 20.0%
        for ($i = 0; $i < 7; $i++) {
            ButtonClick::create([
                'button_id' => $button1->id,
                'visitor_id' => 'click-vis-' . $i,
                'clicked_at' => $now->copy()->subDays(1),
            ]);
        }

        // Funnel events: 4 unique editor_session actors (with 1 duplicate), 1 unique editor_vitrine_cta_clicked -> 25.0%
        FunnelEvent::create(['event_name' => 'editor_session', 'visitor_id' => 'ed-1', 'artist_id' => $artist1->id, 'occurred_at' => $now->copy()->subDays(1)]);
        FunnelEvent::create(['event_name' => 'editor_session', 'visitor_id' => 'ed-1-dup', 'artist_id' => $artist1->id, 'occurred_at' => $now->copy()->subDays(1)]);
        FunnelEvent::create(['event_name' => 'editor_session', 'visitor_id' => 'ed-2', 'artist_id' => $artist2->id, 'occurred_at' => $now->copy()->subDays(1)]);
        FunnelEvent::create(['event_name' => 'editor_session', 'visitor_id' => 'ed-3', 'artist_id' => $artist3->id, 'occurred_at' => $now->copy()->subDays(1)]);
        FunnelEvent::create(['event_name' => 'editor_session', 'visitor_id' => 'ed-anon', 'artist_id' => null, 'occurred_at' => $now->copy()->subDays(1)]);

        FunnelEvent::create(['event_name' => 'editor_vitrine_cta_clicked', 'visitor_id' => 'cta-1', 'artist_id' => $artist1->id, 'occurred_at' => $now->copy()->subDays(1)]);
        FunnelEvent::create(['event_name' => 'editor_vitrine_cta_clicked', 'visitor_id' => 'cta-1-dup', 'artist_id' => $artist1->id, 'occurred_at' => $now->copy()->subDays(1)]);

        // Home pageviews: 10 unique visitors
        for ($i = 0; $i < 10; $i++) {
            FunnelEvent::create([
                'event_name' => 'home_pageview',
                'visitor_id' => 'home-vis-' . $i,
                'artist_id' => null,
                'occurred_at' => $now->copy()->subDays(2),
            ]);
        }

        // Feature leads: 3 home_* leads + 1 editor_banner lead (total = 4; vitrine = 3, assistente = 1)
        FeatureLead::create([
            'artist_id' => null,
            'feature' => 'vitrine',
            'source' => 'home_hero',
            'contact_value' => 'lead1@exemplo.com',
            'metadata' => ['selling_type' => 'prints'],
        ]);
        FeatureLead::create([
            'artist_id' => null,
            'feature' => 'vitrine',
            'source' => 'home_footer',
            'contact_value' => '@lead2',
            'metadata' => ['selling_types' => ['prints', 'originais']],
        ]);
        FeatureLead::create([
            'artist_id' => null,
            'feature' => 'assistente',
            'source' => 'home_waitlist',
            'contact_value' => 'lead3@exemplo.com',
            'metadata' => ['selling_type' => 'comissoes', 'selling_types' => ['comissoes', 'originais']],
        ]);
        FeatureLead::create([
            'artist_id' => $artist1->id,
            'feature' => 'vitrine',
            'source' => 'editor_banner',
            'contact_value' => $artist1->email,
            'metadata' => null,
        ]);

        // Assert service calculations
        $report = app(ValidationKpiReportService::class)->generate(30);

        // KPI 1: 2 activated / 3 total = 66.67%
        $this->assertSame(66.67, $report['kpis']['artist_activation_rate']['value']);
        $this->assertSame(2, $report['kpis']['artist_activation_rate']['activated_artists']);
        $this->assertSame(3, $report['kpis']['artist_activation_rate']['total_artists']);

        // KPI 2: 7 clicks / 35 views = 20.0%
        $this->assertSame(20.0, $report['kpis']['public_links_ctr']['value']);
        $this->assertSame(7, $report['kpis']['public_links_ctr']['clicks']);
        $this->assertSame(35, $report['kpis']['public_links_ctr']['page_views']);

        // KPI 3: 1 cta / 4 editor sessions = 25.0%
        $this->assertSame(25.0, $report['kpis']['editor_vitrine_intent_rate']['value']);
        $this->assertSame(1, $report['kpis']['editor_vitrine_intent_rate']['vitrine_cta_clicks']);
        $this->assertSame(4, $report['kpis']['editor_vitrine_intent_rate']['editor_sessions']);

        // KPI 4: (3 new artists + 3 home leads) / 10 home visitors = 60.0%
        $this->assertSame(60.0, $report['kpis']['landing_conversion_rate']['value']);
        $this->assertSame(3, $report['kpis']['landing_conversion_rate']['new_artists']);
        $this->assertSame(3, $report['kpis']['landing_conversion_rate']['home_leads']);
        $this->assertSame(10, $report['kpis']['landing_conversion_rate']['home_unique_visitors']);

        // KRs: KR1 = 1/15 (6.67%), KR2 = 3/40 (7.5%)
        $this->assertSame(1, $report['krs']['kr1_active_artists_weekly']['current']);
        $this->assertSame(6.67, $report['krs']['kr1_active_artists_weekly']['progress_pct']);
        $this->assertSame(3, $report['krs']['kr2_vitrine_leads_total']['current']);
        $this->assertSame(7.5, $report['krs']['kr2_vitrine_leads_total']['progress_pct']);

        // Leads breakdown
        $this->assertSame(4, $report['leads_breakdown']['total']);
        $this->assertSame(['vitrine' => 3, 'assistente' => 1], $report['leads_breakdown']['by_feature']);
        $this->assertSame([
            'home_hero' => 1,
            'home_footer' => 1,
            'home_waitlist' => 1,
            'editor_banner' => 1,
        ], $report['leads_breakdown']['by_source']);
        $this->assertSame([
            'prints' => 2,
            'originais' => 2,
            'comissoes' => 1,
        ], $report['leads_breakdown']['selling_preferences']);

        // Assert Artisan command in table mode
        $this->artisan('metrics:validation-report', ['--days' => 30])
            ->expectsOutputToContain('KPI 1: artist_activation_rate')
            ->expectsOutputToContain('66.67%')
            ->expectsOutputToContain('KPI 2: public_links_ctr')
            ->expectsOutputToContain('20%')
            ->expectsOutputToContain('KPI 3: editor_vitrine_intent_rate')
            ->expectsOutputToContain('25%')
            ->expectsOutputToContain('KPI 4: landing_conversion_rate')
            ->expectsOutputToContain('60%')
            ->expectsOutputToContain('Feature Leads (Total: 4)')
            ->assertExitCode(0);

        // Assert Artisan command in JSON mode
        $exitCode = Artisan::call('metrics:validation-report', ['--days' => 30, '--json' => true]);
        $this->assertSame(0, $exitCode);

        $json = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(66.67, $json['kpis']['artist_activation_rate']['value']);
        $this->assertEquals(20.0, $json['kpis']['public_links_ctr']['value']);
        $this->assertEquals(25.0, $json['kpis']['editor_vitrine_intent_rate']['value']);
        $this->assertEquals(60.0, $json['kpis']['landing_conversion_rate']['value']);
        $this->assertSame(6.67, $json['krs']['kr1_active_artists_weekly']['progress_pct']);
        $this->assertSame(7.5, $json['krs']['kr2_vitrine_leads_total']['progress_pct']);
        $this->assertSame(4, $json['leads_breakdown']['total']);
    }
}
