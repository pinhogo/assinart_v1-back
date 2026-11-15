<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Artist;
use App\Models\ButtonClick;
use App\Models\PageView;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

trait AggregatesMetrics
{
    protected function classifyReferrer(?string $referrer): string
    {
        if (empty($referrer)) {
            return 'direct';
        }
        $ref = strtolower($referrer);
        if (str_contains($ref, 'instagram.com')) return 'instagram';
        if (str_contains($ref, 'tiktok.com')) return 'tiktok';
        if (str_contains($ref, 'whatsapp') || str_contains($ref, 'wa.me')) return 'whatsapp';
        if (str_contains($ref, 'google.')) return 'google';
        if (str_contains($ref, 'youtube.com') || str_contains($ref, 'youtu.be')) return 'youtube';
        if (str_contains($ref, 'facebook.com') || str_contains($ref, 'fb.me')) return 'facebook';
        if (str_contains($ref, 'twitter.com') || str_contains($ref, 'x.com') || str_contains($ref, 't.co')) return 'x';
        return 'other';
    }

    protected function classifyDevice(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'desktop';
        }
        $ua = strtolower($userAgent);
        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) return 'tablet';
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) return 'mobile';
        return 'desktop';
    }

    protected function buildMetricsSummary(Artist $artist, string $period): array
    {
        $days = match ($period) { '7d' => 7, '90d' => 90, 'all' => 180, default => 30 };
        $from = now()->subDays($days - 1)->startOfDay();
        $to = now()->endOfDay();

        $pageViews = PageView::where('artist_id', $artist->id)->whereBetween('viewed_at', [$from, $to])->get();
        $buttonIds = $artist->buttons()->pluck('id');
        $clicks = ButtonClick::whereIn('button_id', $buttonIds)->whereBetween('clicked_at', [$from, $to])->get();

        $viewsByDate = $pageViews->groupBy(fn ($pv) => Carbon::parse($pv->viewed_at)->format('Y-m-d'));
        $visitorsByDate = $pageViews->groupBy(fn ($pv) => Carbon::parse($pv->viewed_at)->format('Y-m-d'));
        $clicksByDate = $clicks->groupBy(fn ($c) => Carbon::parse($c->clicked_at)->format('Y-m-d'));

        $series = [];
        $dateRange = CarbonPeriod::create($from, '1 day', $to);
        foreach ($dateRange as $date) {
            $formatted = $date->format('Y-m-d');
            $dayViews = $viewsByDate->get($formatted, collect());
            $dayClicks = $clicksByDate->get($formatted, collect());
            $series[] = [
                'date' => $formatted,
                'views' => $dayViews->count(),
                'unique_visitors' => $dayViews->pluck('visitor_id')->unique()->count(),
                'clicks' => $dayClicks->count(),
            ];
        }

        $links = $artist->buttons()->get()->map(function ($btn) use ($clicks) {
            return [
                'link_id' => (int) $btn->id,
                'title' => $btn->title,
                'platform' => $btn->platform,
                'display_mode' => $btn->display_mode,
                'clicks' => $clicks->where('button_id', $btn->id)->count(),
            ];
        })->values()->all();

        $referrers = $pageViews->groupBy('referrer_source')->map(fn ($group, $key) => [
            'source' => $key,
            'views' => $group->count(),
        ])->values()->all();

        $devices = $pageViews->groupBy('device_type')->map(fn ($group, $key) => [
            'device' => $key,
            'views' => $group->count(),
        ])->values()->all();

        $byHour = array_fill(0, 24, 0);
        foreach ($pageViews as $pv) {
            $h = (int) Carbon::parse($pv->viewed_at)->format('H');
            $byHour[$h]++;
        }

        $byWeekday = array_fill(0, 7, 0);
        foreach ($pageViews as $pv) {
            $w = (int) Carbon::parse($pv->viewed_at)->format('w');
            $byWeekday[$w]++;
        }

        return [
            'period' => $period,
            'range' => ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')],
            'totals' => [
                'views' => $pageViews->count(),
                'unique_visitors' => $pageViews->pluck('visitor_id')->unique()->count(),
                'clicks' => $clicks->count(),
            ],
            'series' => $series,
            'links' => $links,
            'referrers' => $referrers,
            'devices' => $devices,
            'peak' => ['by_hour' => $byHour, 'by_weekday' => $byWeekday],
            'timezone' => 'America/Sao_Paulo',
        ];
    }
}
