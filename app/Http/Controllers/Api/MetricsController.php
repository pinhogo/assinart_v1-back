<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AggregatesMetrics;
use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Button;
use App\Models\ButtonClick;
use App\Models\PageView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MetricsController extends Controller
{
    use AggregatesMetrics;

    public function track(Request $request): Response|JsonResponse
    {
        $type = $request->input('type');
        $username = ltrim(strtolower(trim($request->input('username', ''))), '@');
        $visitorId = $request->input('visitor_id') ?? $request->ip() ?? 'anonymous';

        if ($type === 'pageview') {
            $artist = Artist::where('username', $username)->first();
            if ($artist) {
                PageView::create([
                    'artist_id' => $artist->id,
                    'visitor_id' => $visitorId,
                    'referrer_source' => $this->classifyReferrer($request->input('referrer')),
                    'device_type' => $this->classifyDevice($request->input('user_agent') ?? $request->userAgent()),
                    'utm_source' => $request->input('utm_source'),
                    'referrer' => $request->input('referrer'),
                    'user_agent' => $request->input('user_agent') ?? $request->userAgent(),
                    'viewed_at' => now(),
                ]);
            }
            return response()->noContent();
        }

        if ($type === 'click') {
            $linkId = $request->input('link_id');
            if ($linkId) {
                $button = Button::find($linkId);
                if ($button) {
                    $button->increment('clicks_count');
                    ButtonClick::create([
                        'button_id' => $button->id,
                        'visitor_id' => $visitorId,
                        'clicked_at' => now(),
                    ]);
                }
            }
            return response()->noContent();
        }

        return response()->noContent();
    }

    public function summary(Request $request): JsonResponse
    {
        $artist = $request->user();
        $period = $request->query('period', '30d');
        if (!in_array($period, ['7d', '30d', '90d', 'all'], true)) {
            $period = '30d';
        }

        $summary = $this->buildMetricsSummary($artist, $period);

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ], 200);
    }
}
