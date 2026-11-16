<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\InteractsWithMediaStorage;
use App\Http\Controllers\Controller;
use App\Http\Requests\OnboardingBackgroundRequest;
use App\Http\Requests\OnboardingButtonsRequest;
use App\Http\Requests\OnboardingLinksRequest;
use App\Services\ButtonOrderService;
use App\Support\OnboardingPlatformUrlFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    use InteractsWithMediaStorage;

    public const PALETTE_PRESETS = OnboardingPlatformUrlFormatter::PALETTE_PRESETS;

    public const PLATFORM_TITLES = OnboardingPlatformUrlFormatter::PLATFORM_TITLES;

    public function saveLinks(OnboardingLinksRequest $request, ButtonOrderService $orderService): JsonResponse
    {
        $artist = $request->user();
        $rawLinks = $request->input('links');
        $rawLinks = is_array($rawLinks) ? $rawLinks : [];

        [$parsedButtons, $circularCount] = OnboardingPlatformUrlFormatter::parseLinks($rawLinks, $orderService);

        if ($circularCount > ButtonOrderService::MAX_CIRCULAR_BUTTONS) {
            return response()->json([
                'message' => 'Você pode ter no máximo 5 botões circulares.',
                'errors' => ['links' => ['Você pode ter no máximo 5 botões circulares.']],
            ], 422);
        }

        $parsedButtons = $orderService->sortAndIndexParsedButtons($parsedButtons);

        $createdButtons = DB::transaction(function () use ($artist, $request, $parsedButtons) {
            if ($request->has('bio')) {
                $artist->bio = $request->input('bio');
                $artist->save();
            }

            foreach ($artist->buttons()->get(['id', 'image_url']) as $btn) {
                $this->deleteFileFromStorage($btn->image_url, $artist->id);
            }
            $artist->buttons()->delete();

            $results = [];
            foreach ($parsedButtons as $btnData) {
                $results[] = $artist->buttons()->create($btnData);
            }

            return $results;
        });

        $totalLinks = count($createdButtons);

        return response()->json([
            'status' => 'success',
            'message' => 'Links e bio salvos com sucesso.',
            'data' => [
                'bio' => $artist->fresh()->bio,
                'total_links' => $totalLinks,
                'remaining_slots' => max(0, ButtonOrderService::MAX_BUTTONS_PER_ARTIST - $totalLinks),
                'links' => $createdButtons,
                'buttons' => $createdButtons,
            ],
        ], 200);
    }

    public function saveBackground(OnboardingBackgroundRequest $request): JsonResponse
    {
        $artist = $request->user();
        $backgroundId = $request->input('background_id');
        $backgroundColor = $request->input('background_color');

        if (empty($backgroundColor) && !empty($backgroundId) && isset(self::PALETTE_PRESETS[$backgroundId])) {
            $backgroundColor = self::PALETTE_PRESETS[$backgroundId];
        }

        $updateData = array_filter([
            'background_color' => !empty($backgroundColor) ? $backgroundColor : null,
            'background_id' => $backgroundId,
        ], fn ($v) => $v !== null);

        if (!empty($updateData)) {
            $artist->update($updateData);
        }

        $fresh = $artist->fresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Fundo atualizado com sucesso.',
            'data' => [
                'background_color' => $fresh->background_color,
                'background_id' => $fresh->background_id,
            ],
        ], 200);
    }

    public function saveButtons(OnboardingButtonsRequest $request): JsonResponse
    {
        $artist = $request->user();
        $fillableUpdates = [];

        foreach (['button_shape', 'button_shadow', 'circular_icon_background', 'button_color', 'button_text_color', 'border_style', 'border_color'] as $field) {
            if ($request->has($field)) {
                $fillableUpdates[$field] = $request->input($field);
            }
        }
        if ($request->has('button_shadow_color') && $request->input('button_shadow_color') !== null) {
            $fillableUpdates['button_shadow_color'] = $request->input('button_shadow_color');
        }
        if ($request->has('circular_shadow_enabled') && $request->input('circular_shadow_enabled') !== null) {
            $fillableUpdates['circular_shadow_enabled'] = $request->boolean('circular_shadow_enabled');
        }

        if (!empty($fillableUpdates)) {
            $artist->update($fillableUpdates);
        }

        if ($request->boolean('reset_individual_colors')) {
            $artist->buttons()->update(['button_color' => null]);
        }

        $fresh = $artist->fresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Onboarding concluído com sucesso.',
            'redirect_url' => '/editor',
            'data' => [
                'button_shape' => $fresh->button_shape,
                'button_shadow' => $fresh->button_shadow,
                'button_shadow_color' => $fresh->button_shadow_color ?? '#000000',
                'circular_shadow_enabled' => (bool) ($fresh->circular_shadow_enabled ?? true),
                'circular_icon_background' => $fresh->circular_icon_background,
                'button_color' => $fresh->button_color,
                'button_text_color' => $fresh->button_text_color,
                'border_style' => $fresh->border_style,
                'border_color' => $fresh->border_color,
            ],
        ], 200);
    }

    protected function formatPlatformUrl(string $platform, string $input): string
    {
        return OnboardingPlatformUrlFormatter::formatPlatformUrl($platform, $input);
    }
}
