<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ManagesButtonReorderAndImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreButtonRequest;
use App\Http\Requests\UpdateButtonRequest;
use App\Models\Button;
use App\Services\ButtonOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ButtonController extends Controller
{
    use ManagesButtonReorderAndImage;

    public function index(Request $request): JsonResponse
    {
        $artist = $request->user();
        $buttons = $artist->buttons()->orderBy('order_index', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $buttons,
        ]);
    }

    public function store(StoreButtonRequest $request, ButtonOrderService $orderService): JsonResponse
    {
        $artist = $request->user();
        $validated = $request->validated();

        return DB::transaction(function () use ($artist, $validated, $orderService) {
            $artistButtons = $artist->buttons()
                ->orderBy('order_index', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            if ($artistButtons->count() >= ButtonOrderService::MAX_BUTTONS_PER_ARTIST) {
                return response()->json([
                    'message' => 'Limite de 30 links atingido.',
                    'errors' => [
                        'links' => ['Você pode cadastrar no máximo 30 links combinados entre redes sociais e personalizados.'],
                    ],
                ], 422);
            }

            $displayMode = $validated['display_mode'] ?? 'button';

            if (
                $displayMode === 'circular'
                && $artistButtons->where('display_mode', 'circular')->count() >= ButtonOrderService::MAX_CIRCULAR_BUTTONS
            ) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Você pode ter no máximo 5 botões circulares.',
                    'errors' => [
                        'display_mode' => ['Você pode ter no máximo 5 botões circulares.'],
                    ],
                ], 422);
            }

            $validated['platform'] = $validated['platform'] ?? 'custom';
            if ($validated['platform'] === 'email' && array_key_exists('url', $validated)) {
                $validated['url'] = $this->normalizeEmailUrl((string) $validated['url']);
            }
            $validated['type'] = $validated['type'] ?? 'standard';
            $validated['is_active'] = $validated['is_active'] ?? true;
            $validated['text_align'] = $validated['text_align'] ?? 'left';

            $iconVariant = $validated['icon_variant'] ?? 'default';
            $showBackground = array_key_exists('show_icon_background', $validated) && $validated['show_icon_background'] !== null
                ? (bool) $validated['show_icon_background']
                : ($displayMode === 'circular');

            $displayAttributes = $orderService->normalizeDisplayAttributes(
                $displayMode,
                $iconVariant,
                $showBackground,
            );

            $validated = array_merge($validated, $displayAttributes);
            $validated['order_index'] = $artistButtons->count();

            $button = $artist->buttons()->create($validated);

            $artistButtons->push($button);
            $plan = $orderService->normalizeArtistButtons($artistButtons);
            $orderService->applyNormalizedPlan($plan);

            return response()->json([
                'status' => 'success',
                'message' => 'Botão criado com sucesso.',
                'data' => $button->fresh(),
            ], 201);
        });
    }

    public function update(UpdateButtonRequest $request, int $id, ButtonOrderService $orderService): JsonResponse
    {
        $artist = $request->user();
        $validated = $request->validated();
        unset($validated['order_index']);

        return DB::transaction(function () use ($artist, $id, $validated, $orderService) {
            $artistButtons = $artist->buttons()
                ->orderBy('order_index', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            /** @var Button $button */
            $button = $artistButtons->firstWhere('id', $id) ?? $artist->buttons()->findOrFail($id);

            $targetPlatform = $validated['platform'] ?? $button->platform;
            if ($targetPlatform === 'email' && array_key_exists('url', $validated)) {
                $validated['url'] = $this->normalizeEmailUrl((string) $validated['url']);
            }

            if (array_key_exists('display_mode', $validated) && $validated['display_mode'] === null) {
                unset($validated['display_mode']);
            }

            $previousDisplayMode = $button->display_mode ?? 'button';
            $targetDisplayMode = $validated['display_mode'] ?? $previousDisplayMode;
            $displayModeChanged = $targetDisplayMode !== $previousDisplayMode;

            if ($displayModeChanged && $targetDisplayMode === 'circular') {
                $otherCircularCount = $artistButtons
                    ->where('id', '!=', $button->id)
                    ->where('display_mode', 'circular')
                    ->count();

                if ($otherCircularCount >= ButtonOrderService::MAX_CIRCULAR_BUTTONS) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Você pode ter no máximo 5 botões circulares.',
                        'errors' => [
                            'display_mode' => ['Você pode ter no máximo 5 botões circulares.'],
                        ],
                    ], 422);
                }
            }

            if ($targetDisplayMode === 'circular') {
                $currentIcon = $validated['icon_variant'] ?? $button->icon_variant;
                $normalizedDisplay = $orderService->normalizeDisplayAttributes('circular', $currentIcon, true);
                $validated = array_merge($validated, $normalizedDisplay);
            }

            $button->update($validated);

            if ($displayModeChanged) {
                $plan = $orderService->normalizeArtistButtons($artistButtons);
                $orderService->applyNormalizedPlan($plan);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Botão atualizado com sucesso.',
                'data' => $button->fresh(),
            ]);
        });
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $artist = $request->user();
        $button = $artist->buttons()->findOrFail($id);

        if ($button->image_url) {
            $parsedPath = parse_url($button->image_url, PHP_URL_PATH) ?? $button->image_url;
            $relativePath = ltrim(str_replace('/storage/', '', $parsedPath), '/');
            if (str_starts_with($relativePath, 'storage/')) {
                $relativePath = substr($relativePath, 8);
            }
            if ($relativePath && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        $button->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Botão removido com sucesso.',
        ]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $artist = $request->user();

        foreach ($artist->buttons as $button) {
            if ($button->image_url) {
                $parsedPath = parse_url($button->image_url, PHP_URL_PATH) ?? $button->image_url;
                $relativePath = ltrim(str_replace('/storage/', '', $parsedPath), '/');
                if (str_starts_with($relativePath, 'storage/')) {
                    $relativePath = substr($relativePath, 8);
                }
                if ($relativePath && Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            }
        }

        $artist->buttons()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Todos os botões foram excluídos com sucesso.',
        ], 200);
    }

    private function normalizeEmailUrl(string $url): string
    {
        $cleanEmail = trim((string) preg_replace('/^mailto:/i', '', trim($url)));

        return 'mailto:' . $cleanEmail;
    }
}
