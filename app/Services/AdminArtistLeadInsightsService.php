<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\ButtonClick;
use App\Models\FeatureLead;
use App\Models\PageView;
use Carbon\Carbon;

class AdminArtistLeadInsightsService
{
    public function __construct(
        private readonly ValidationKpiReportService $kpiReportService,
    ) {}

    /**
     * Returns the top 20 active artists ranked by views, clicks, active buttons, and ID.
     *
     * @return array<int, array<string, mixed>>
     */
    public function topActiveArtists(Carbon $from, Carbon $to): array
    {
        $viewsByArtist = PageView::query()
            ->whereBetween('viewed_at', [$from, $to])
            ->selectRaw('artist_id, COUNT(*) as total')
            ->groupBy('artist_id')
            ->pluck('total', 'artist_id')
            ->all();

        $clicksByArtist = ButtonClick::query()
            ->join('buttons', 'button_clicks.button_id', '=', 'buttons.id')
            ->whereBetween('button_clicks.clicked_at', [$from, $to])
            ->selectRaw('buttons.artist_id as artist_id, COUNT(*) as total')
            ->groupBy('buttons.artist_id')
            ->pluck('total', 'artist_id')
            ->all();

        $vitrineByArtist = $this->loadVitrineInterestByArtist();

        $rows = Artist::query()
            ->withCount('activeButtons')
            ->get(['id', 'name', 'username', 'email', 'is_published', 'created_at'])
            ->map(function (Artist $artist) use ($viewsByArtist, $clicksByArtist, $vitrineByArtist): array {
                $artistId = (int) $artist->id;
                $views = (int) ($viewsByArtist[$artistId] ?? 0);
                $clicks = (int) ($clicksByArtist[$artistId] ?? 0);

                return [
                    'id' => $artistId,
                    'name' => $artist->name,
                    'username' => $artist->username,
                    'email' => $artist->email,
                    'is_published' => (bool) ($artist->is_published ?? true),
                    'active_buttons_count' => (int) ($artist->active_buttons_count ?? 0),
                    'page_views' => $views,
                    'clicks' => $clicks,
                    'ctr' => $views > 0 ? round(($clicks / $views) * 100, 2) : 0.0,
                    'has_vitrine_interest' => array_key_exists($artistId, $vitrineByArtist),
                    'vitrine_selling_types' => $vitrineByArtist[$artistId] ?? [],
                    'created_at' => $artist->created_at?->toIso8601String(),
                ];
            })
            ->all();

        usort($rows, static fn (array $a, array $b): int =>
            ($b['page_views'] <=> $a['page_views'])
            ?: ($b['clicks'] <=> $a['clicks'])
            ?: ($b['active_buttons_count'] <=> $a['active_buttons_count'])
            ?: ($a['id'] <=> $b['id'])
        );

        return array_slice($rows, 0, 20);
    }

    /**
     * Returns up to 50 most recent feature leads for qualitative follow-up.
     *
     * @return array<int, array<string, mixed>>
     */
    public function recentFeatureLeads(): array
    {
        return FeatureLead::query()
            ->with('artist:id,name,username')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn (FeatureLead $lead): array => [
                'id' => (int) $lead->id,
                'feature' => $lead->feature,
                'source' => $lead->source,
                'artist_id' => $lead->artist_id !== null ? (int) $lead->artist_id : null,
                'artist_name' => $lead->artist?->name,
                'artist_username' => $lead->artist?->username,
                'contact_value' => $lead->contact_value,
                'selling_types' => $this->kpiReportService->extractSellingTypes($lead->metadata),
                'created_at' => $lead->created_at?->toIso8601String(),
            ])
            ->all();
    }

    /** @return array<int, array<int, string>> */
    private function loadVitrineInterestByArtist(): array
    {
        $vitrineByArtist = [];

        $leads = FeatureLead::query()
            ->where('feature', 'vitrine')
            ->whereNotNull('artist_id')
            ->get(['artist_id', 'metadata']);

        foreach ($leads as $lead) {
            $artistId = (int) $lead->artist_id;
            $existing = $vitrineByArtist[$artistId] ?? [];
            $extracted = $this->kpiReportService->extractSellingTypes($lead->metadata);
            $vitrineByArtist[$artistId] = array_values(array_unique(array_merge($existing, $extracted)));
        }

        return $vitrineByArtist;
    }
}
