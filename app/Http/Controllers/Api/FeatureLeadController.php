<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeatureLeadRequest;
use App\Models\Artist;
use App\Models\FeatureLead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeatureLeadController extends Controller
{
    public function store(StoreFeatureLeadRequest $request): JsonResponse
    {
        $artist = $request->user('sanctum');
        $validated = $request->validated();
        $feature = $validated['feature'] ?? 'vitrine';
        $source = $validated['source'];
        $hasMetadata = array_key_exists('metadata', $validated) && $validated['metadata'] !== null;
        $metadata = $validated['metadata'] ?? null;
        $contactValue = $validated['contact_value'] ?? null;

        if ($artist instanceof Artist) {
            return $this->storeForArtist($artist, $feature, $source, $contactValue, $metadata, $hasMetadata);
        }

        return $this->storeForGuest($feature, $source, (string) $contactValue, $metadata);
    }

    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'feature' => ['sometimes', 'string', 'in:vitrine,assistente'],
        ]);

        $feature = $validated['feature'] ?? 'vitrine';
        $lead = FeatureLead::where('artist_id', $request->user()->id)
            ->where('feature', $feature)
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'registered' => $lead !== null,
                'lead' => $lead?->toDto(),
            ],
        ], 200);
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    private function storeForArtist(
        Artist $artist,
        string $feature,
        string $source,
        ?string $contactValue,
        ?array $metadata,
        bool $hasMetadata
    ): JsonResponse {
        $lead = FeatureLead::where('artist_id', $artist->id)
            ->where('feature', $feature)
            ->first();

        if ($lead) {
            if ($hasMetadata) {
                $lead->metadata = $metadata;
            }
            if ($lead->source !== $source) {
                $lead->source = $source;
            }
            if ($lead->isDirty()) {
                $lead->save();
            }

            return $this->leadResponse($lead, true, 200);
        }

        $lead = FeatureLead::create([
            'artist_id' => $artist->id,
            'feature' => $feature,
            'source' => $source,
            'contact_value' => $contactValue ?: $artist->email,
            'metadata' => $metadata,
        ]);

        return $this->leadResponse($lead, false, 201);
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    private function storeForGuest(
        string $feature,
        string $source,
        string $contactValue,
        ?array $metadata
    ): JsonResponse {
        $normalizedContact = mb_strtolower(trim($contactValue));

        $lead = FeatureLead::whereNull('artist_id')
            ->where('feature', $feature)
            ->where('contact_value', $normalizedContact)
            ->first();

        if ($lead) {
            return $this->leadResponse($lead, true, 200);
        }

        $lead = FeatureLead::create([
            'artist_id' => null,
            'feature' => $feature,
            'source' => $source,
            'contact_value' => $normalizedContact,
            'metadata' => $metadata,
        ]);

        return $this->leadResponse($lead, false, 201);
    }

    private function leadResponse(FeatureLead $lead, bool $alreadyRegistered, int $status): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => array_merge($lead->toDto(), [
                'already_registered' => $alreadyRegistered,
            ]),
        ], $status);
    }
}
