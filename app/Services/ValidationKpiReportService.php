<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\ButtonClick;
use App\Models\FeatureLead;
use App\Models\FunnelEvent;
use App\Models\PageView;
use Carbon\Carbon;

class ValidationKpiReportService
{
    /**
     * Generate validation KPIs, KR progress, and feature_leads breakdown.
     * Note: artist_activation_rate checks current active buttons + page_views in $days window.
     *
     * @return array<string, mixed>
     */
    public function generate(int $days = 30): array
    {
        $days = max(1, $days);
        $from = now()->subDays($days);
        $to = now();

        return [
            'period' => ['days' => $days, 'from' => $from->toIso8601String(), 'to' => $to->toIso8601String()],
            'kpis' => $this->buildKpis($days, $from, $to),
            'krs' => $this->buildKrs($to),
            'leads_breakdown' => $this->buildLeadsBreakdown(),
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private function buildKpis(int $days, Carbon $from, Carbon $to): array
    {
        $totalArtists = Artist::count();
        $activatedArtists = Artist::whereHas('activeButtons')
            ->whereHas('pageViews', fn ($q) => $q->whereBetween('viewed_at', [$from, $to]), '>=', 5)
            ->count();

        $totalPageViews = PageView::whereBetween('viewed_at', [$from, $to])->count();
        $totalClicks = ButtonClick::whereBetween('clicked_at', [$from, $to])->count();
        $editorSessions = $this->countUniqueActors('editor_session', $from, $to);
        $vitrineCtaClicks = $this->countUniqueActors('editor_vitrine_cta_clicked', $from, $to);

        $newArtists = Artist::whereBetween('created_at', [$from, $to])->count();
        $homeLeads = FeatureLead::where('source', 'like', 'home_%')->whereBetween('created_at', [$from, $to])->count();
        $homeVisitors = $this->countUniqueActors('home_pageview', $from, $to);

        return [
            'artist_activation_rate' => [
                'value' => $this->percentage($activatedArtists, $totalArtists),
                'target' => '> 25%',
                'activated_artists' => $activatedArtists,
                'total_artists' => $totalArtists,
                'window_days' => $days,
            ],
            'public_links_ctr' => [
                'value' => $this->percentage($totalClicks, $totalPageViews),
                'target' => '15% - 30%',
                'clicks' => $totalClicks,
                'page_views' => $totalPageViews,
            ],
            'editor_vitrine_intent_rate' => [
                'value' => $this->percentage($vitrineCtaClicks, $editorSessions),
                'target' => '> 20%',
                'vitrine_cta_clicks' => $vitrineCtaClicks,
                'editor_sessions' => $editorSessions,
            ],
            'landing_conversion_rate' => [
                'value' => $this->percentage($newArtists + $homeLeads, $homeVisitors),
                'target' => '> 5%',
                'new_artists' => $newArtists,
                'home_leads' => $homeLeads,
                'home_unique_visitors' => $homeVisitors,
            ],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private function buildKrs(Carbon $to): array
    {
        $weeklyFrom = now()->subDays(7);
        $kr1 = Artist::whereHas('activeButtons')
            ->whereHas('pageViews', fn ($q) => $q->whereBetween('viewed_at', [$weeklyFrom, $to]), '>=', 20)
            ->count();
        $kr2 = FeatureLead::where('feature', 'vitrine')->count();

        return [
            'kr1_active_artists_weekly' => ['current' => $kr1, 'target' => 15, 'progress_pct' => $this->percentage($kr1, 15)],
            'kr2_vitrine_leads_total' => ['current' => $kr2, 'target' => 40, 'progress_pct' => $this->percentage($kr2, 40)],
        ];
    }

    /** @return array<string, mixed> */
    private function buildLeadsBreakdown(): array
    {
        $leads = FeatureLead::all(['feature', 'source', 'metadata']);
        $byFeature = ['vitrine' => 0, 'assistente' => 0];
        $bySource = ['home_hero' => 0, 'home_footer' => 0, 'home_waitlist' => 0, 'editor_banner' => 0];
        $sellingPrefs = ['prints' => 0, 'originais' => 0, 'comissoes' => 0];

        foreach ($leads as $lead) {
            if (is_string($lead->feature) && $lead->feature !== '') {
                $byFeature[$lead->feature] = ($byFeature[$lead->feature] ?? 0) + 1;
            }
            if (is_string($lead->source) && $lead->source !== '') {
                $bySource[$lead->source] = ($bySource[$lead->source] ?? 0) + 1;
            }
            foreach ($this->extractSellingTypes($lead->metadata) as $type) {
                $sellingPrefs[$type] = ($sellingPrefs[$type] ?? 0) + 1;
            }
        }

        return [
            'total' => $leads->count(),
            'by_feature' => $byFeature,
            'by_source' => $bySource,
            'selling_preferences' => $sellingPrefs,
        ];
    }

    /** @return array<int, string> */
    private function extractSellingTypes(mixed $metadata): array
    {
        if (!is_array($metadata)) {
            return [];
        }
        $types = [];
        if (!empty($metadata['selling_type']) && is_string($metadata['selling_type'])) {
            $types[] = $metadata['selling_type'];
        }
        foreach (is_array($metadata['selling_types'] ?? null) ? $metadata['selling_types'] : [] as $type) {
            if (is_string($type) && $type !== '') {
                $types[] = $type;
            }
        }
        return array_values(array_unique($types));
    }

    private function countUniqueActors(string $eventName, Carbon $from, Carbon $to): int
    {
        return FunnelEvent::where('event_name', $eventName)
            ->whereBetween('occurred_at', [$from, $to])
            ->select(['artist_id', 'visitor_id'])
            ->distinct()
            ->get()
            ->unique(fn (FunnelEvent $e) => $e->artist_id ? 'a:' . $e->artist_id : 'v:' . ($e->visitor_id ?? ''))
            ->count();
    }

    private function percentage(int|float $numerator, int|float $denominator): float
    {
        return $denominator > 0 ? round(($numerator / $denominator) * 100, 2) : 0.0;
    }
}
