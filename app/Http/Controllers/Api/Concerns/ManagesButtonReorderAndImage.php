<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Http\Requests\ReorderButtonsRequest;
use App\Models\Button;
use App\Services\ButtonOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait ManagesButtonReorderAndImage
{
    use InteractsWithMediaStorage;

    public function reorder(ReorderButtonsRequest $request, ButtonOrderService $orderService): JsonResponse
    {
        $artist = $request->user();
        $items = $request->validated('items');

        return DB::transaction(function () use ($artist, $items, $orderService) {
            $artistButtons = $artist->buttons()->orderBy('id', 'asc')->lockForUpdate()
                ->get(['id', 'display_mode', 'icon_variant', 'show_icon_background', 'order_index']);

            if (!$orderService->allItemsBelongToArtist($artistButtons, $items)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Um ou mais botões informados não pertencem ao seu perfil ou não existem.',
                ], 403);
            }

            $plan = $orderService->buildNormalizedUpdates($artistButtons, $items);
            if ($orderService->countCircularInPlan($plan) > ButtonOrderService::MAX_CIRCULAR_BUTTONS) {
                return $this->limitErrorResponse('Você pode ter no máximo 5 botões circulares.', 'items', 'Você pode ter no máximo 5 botões circulares.');
            }

            foreach ($plan as $entry) {
                $button = $entry['button'];
                $button->fill($entry['attributes']);
                if ($button->isDirty()) {
                    $button->save();
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ordem dos botões atualizada com sucesso.',
                'data' => [
                    'updated_count' => count($plan),
                    'buttons' => $artist->buttons()->orderBy('order_index', 'asc')->orderBy('id', 'asc')->get(),
                ],
            ]);
        });
    }

    public function uploadImage(Request $request, int $id): JsonResponse
    {
        $artist = $request->user();
        $button = $artist->buttons()->findOrFail($id);

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'image.required' => 'O envio de uma imagem é obrigatório.',
            'image.image' => 'O arquivo enviado deve ser uma imagem válida.',
            'image.mimes' => 'A imagem deve ser do formato JPEG, PNG, JPG ou WebP.',
            'image.max' => 'A imagem não pode exceder 5MB.',
        ]);

        $this->deleteFileFromStorage($button->image_url, $artist->id);
        $imageUrl = $this->storeMediaFile($request->file('image'), 'cards');
        $button->update(['image_url' => $imageUrl]);

        return response()->json([
            'status' => 'success',
            'message' => 'Imagem do card atualizada com sucesso.',
            'image_url' => $button->image_url,
        ]);
    }

    protected function limitErrorResponse(string $message, string $field, string $detail): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => [$field => [$detail]],
        ], 422);
    }

    protected function normalizeEmailUrl(string $url): string
    {
        return 'mailto:' . trim((string) preg_replace('/^mailto:/i', '', trim($url)));
    }

    protected function prepareStorePayload(array $validated, int $orderIndex, ButtonOrderService $orderService): array
    {
        $displayMode = $validated['display_mode'] ?? 'button';
        $validated['platform'] = $validated['platform'] ?? 'custom';
        if ($validated['platform'] === 'email' && array_key_exists('url', $validated)) {
            $validated['url'] = $this->normalizeEmailUrl((string) $validated['url']);
        }
        $validated['type'] = $validated['type'] ?? 'standard';
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['text_align'] = $validated['text_align'] ?? 'left';

        $showBackground = array_key_exists('show_icon_background', $validated) && $validated['show_icon_background'] !== null
            ? (bool) $validated['show_icon_background']
            : ($displayMode === 'circular');

        $displayAttributes = $orderService->normalizeDisplayAttributes(
            $displayMode,
            $validated['icon_variant'] ?? 'default',
            $showBackground,
        );

        return array_merge($validated, $displayAttributes, ['order_index' => $orderIndex]);
    }

    protected function prepareUpdatePayload(Button $button, array $validated, string $targetDisplayMode, ButtonOrderService $orderService): array
    {
        $targetPlatform = $validated['platform'] ?? $button->platform;
        if ($targetPlatform === 'email' && array_key_exists('url', $validated)) {
            $validated['url'] = $this->normalizeEmailUrl((string) $validated['url']);
        }

        if ($targetDisplayMode === 'circular') {
            $currentIcon = $validated['icon_variant'] ?? $button->icon_variant;
            $validated = array_merge($validated, $orderService->normalizeDisplayAttributes('circular', $currentIcon, true));
        }

        return $validated;
    }
}
