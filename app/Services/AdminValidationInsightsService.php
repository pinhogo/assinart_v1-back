<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\Button;
use App\Models\FeatureLead;
use App\Models\FunnelEvent;
use Carbon\Carbon;

class AdminValidationInsightsService
{
    public function __construct(
        private readonly ValidationKpiReportService $kpiReportService,
        private readonly AdminArtistLeadInsightsService $artistLeadInsightsService,
    ) {}

    /**
     * Generates consolidated validation insights for the admin dashboard.
     *
     * @return array<string, mixed>
     */
    public function generate(int $days = 30): array
    {
        $base = $this->kpiReportService->generate($days);
        $from = Carbon::parse($base['period']['from']);
        $to = Carbon::parse($base['period']['to']);
        $krs = $base['krs'];
        $krs['kr3_primary_retention_15d'] = $this->buildKr3Retention15d();

        return [
            'period' => $base['period'],
            'kpis' => $base['kpis'],
            'krs' => $krs,
            'leads_breakdown' => $base['leads_breakdown'],
            'feature_comparison' => $this->buildFeatureComparison($from, $to),
            'top_active_artists' => $this->artistLeadInsightsService->topActiveArtists($from, $to),
            'recent_feature_leads' => $this->artistLeadInsightsService->recentFeatureLeads(),
        ];
    }

    /** @return array<string, int|float> */
    private function buildKr3Retention15d(): array
    {
        $from15d = now()->subDays(15);
        $baseArtists = Artist::whereHas('activeButtons')->get(['id', 'created_at', 'updated_at']);
        $baseCount = $baseArtists->count();

        if ($baseCount === 0) {
            return ['current_pct' => 0.0, 'returning_artists' => 0, 'active_artists_base' => 0, 'target_pct' => 40.0, 'progress_pct' => 0.0];
        }

        $artistIds = $baseArtists->pluck('id')->all();
        $editorSessionSet = array_flip(
            FunnelEvent::where('event_name', 'editor_session')->where('occurred_at', '>=', $from15d)
                ->whereIn('artist_id', $artistIds)->distinct()->pluck('artist_id')->all()
        );
        $lastButtonUpdates = Button::whereIn('artist_id', $artistIds)->where('updated_at', '>=', $from15d)
            ->selectRaw('artist_id, MAX(updated_at) as last_updated_at')->groupBy('artist_id')
            ->pluck('last_updated_at', 'artist_id')->all();

        $returning = 0;
        foreach ($baseArtists as $artist) {
            if ($this->isReturningArtist($artist, $from15d, $editorSessionSet, $lastButtonUpdates)) {
                $returning++;
            }
        }

        $currentPct = round(($returning / $baseCount) * 100, 2);
        $progressPct = $currentPct > 0 ? min(100.0, round(($currentPct / 40.0) * 100, 2)) : 0.0;

        return [
            'current_pct' => $currentPct,
            'returning_artists' => $returning,
            'active_artists_base' => $baseCount,
            'target_pct' => 40.0,
            'progress_pct' => $progressPct,
        ];
    }

    /**
     * @param  array<int|string, int>  $editorSessionSet
     * @param  array<int|string, mixed>  $lastButtonUpdates
     */
    private function isReturningArtist(Artist $artist, Carbon $from15d, array $editorSessionSet, array $lastButtonUpdates): bool
    {
        if (isset($editorSessionSet[$artist->id])) {
            return true;
        }
        if ($artist->updated_at !== null && $artist->created_at !== null
            && $artist->updated_at->gte($from15d) && $artist->updated_at->gt($artist->created_at)) {
            return true;
        }
        $lastBtnUpdate = $lastButtonUpdates[$artist->id] ?? null;

        return $lastBtnUpdate !== null && $artist->created_at !== null && Carbon::parse($lastBtnUpdate)->gt($artist->created_at);
    }

    /** @return array<int, array<string, mixed>> */
    private function buildFeatureComparison(Carbon $from, Carbon $to): array
    {
        $stats = [
            'vitrine' => ['leads_total' => 0, 'home_leads' => 0, 'editor_leads' => 0],
            'assistente' => ['leads_total' => 0, 'home_leads' => 0, 'editor_leads' => 0],
        ];
        $uniqueArtists = ['vitrine' => [], 'assistente' => []];

        foreach (FeatureLead::query()->select(['feature', 'source', 'artist_id'])->cursor() as $lead) {
            $feature = $lead->feature;
            if (! is_string($feature) || $feature === '') {
                continue;
            }
            $stats[$feature] ??= ['leads_total' => 0, 'home_leads' => 0, 'editor_leads' => 0];
            $uniqueArtists[$feature] ??= [];
            $stats[$feature]['leads_total']++;
            if ($lead->artist_id !== null) {
                $uniqueArtists[$feature][(int) $lead->artist_id] = true;
            }
            if (is_string($lead->source) && str_starts_with($lead->source, 'home_')) {
                $stats[$feature]['home_leads']++;
            } elseif (is_string($lead->source) && str_starts_with($lead->source, 'editor_')) {
                $stats[$feature]['editor_leads']++;
            }
        }

        $eventNames = array_map(static fn (string $f): string => "editor_{$f}_cta_clicked", array_keys($stats));
        $ctaCounts = FunnelEvent::whereIn('event_name', $eventNames)->whereBetween('occurred_at', [$from, $to])
            ->selectRaw('event_name, COUNT(*) as total')->groupBy('event_name')->pluck('total', 'event_name')->all();

        $rows = [];
        foreach ($stats as $feature => $counts) {
            $rows[] = [
                'feature' => $feature,
                'leads_total' => $counts['leads_total'],
                'unique_artists' => count($uniqueArtists[$feature] ?? []),
                'home_leads' => $counts['home_leads'],
                'editor_leads' => $counts['editor_leads'],
                'editor_cta_clicks' => (int) ($ctaCounts["editor_{$feature}_cta_clicked"] ?? 0),
            ];
        }

        usort($rows, static function (array $a, array $b): int {
            if ($a['feature'] === 'vitrine' || $b['feature'] === 'vitrine') {
                return $a['feature'] === 'vitrine' ? ($b['feature'] === 'vitrine' ? 0 : -1) : 1;
            }

            return ($b['leads_total'] <=> $a['leads_total']) ?: strcmp($a['feature'], $b['feature']);
        });

        return $rows;
    }
}
