<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AggregatesMetrics;
use App\Http\Controllers\Controller;
use App\Http\Requests\TrackEventRequest;
use App\Models\Artist;
use App\Models\Button;
use App\Models\ButtonClick;
use App\Models\FunnelEvent;
use App\Models\PageView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MetricsController extends Controller
{
    use AggregatesMetrics;

    public function track(TrackEventRequest $request): Response|JsonResponse
    {
        $validated = $request->validated();
        $type = $validated['type'];
        $username = ltrim(strtolower(trim((string) ($validated['username'] ?? ''))), '@');
        $providedVisitorId = trim((string) ($validated['visitor_id'] ?? ''));
        $rawVisitorId = $providedVisitorId !== '' ? $providedVisitorId : ($request->ip() ?? 'anonymous');
        $visitorId = mb_substr((string) $rawVisitorId, 0, 64);

        if ($type === 'pageview') {
            $artist = $username !== ''
                ? Artist::where('username', $username)->where('is_published', true)->first()
                : null;
            if ($artist) {
                $userAgent = $validated['user_agent'] ?? $request->userAgent();
                PageView::create([
                    'artist_id' => $artist->id,
                    'visitor_id' => $visitorId,
                    'referrer_source' => $this->classifyReferrer($validated['referrer'] ?? null),
                    'device_type' => $this->classifyDevice($userAgent),
                    'utm_source' => $validated['utm_source'] ?? null,
                    'referrer' => $validated['referrer'] ?? null,
                    'user_agent' => $userAgent,
                    'viewed_at' => now(),
                ]);
            }

            return response()->noContent();
        }

        if ($type === 'click') {
            $linkId = $validated['link_id'] ?? null;
            if ($linkId) {
                $button = Button::where('id', $linkId)
                    ->where('is_active', true)
                    ->whereHas('artist', fn ($q) => $q->where('is_published', true))
                    ->first();
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

        if ($type === 'funnel') {
            $artist = $request->user('sanctum');
            FunnelEvent::create([
                'event_name' => $validated['event_name'],
                'visitor_id' => $visitorId,
                'artist_id' => $artist?->id,
                'source' => $validated['source'] ?? null,
                'metadata' => $validated['metadata'] ?? null,
                'occurred_at' => now(),
            ]);

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

        return response()->json([
            'status' => 'success',
            'data' => $this->buildMetricsSummary($artist, $period),
        ], 200);
    }
}
