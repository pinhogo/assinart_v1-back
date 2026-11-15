<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Http\Requests\ReorderButtonsRequest;
use App\Models\Button;
use App\Services\ButtonOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait ManagesButtonReorderAndImage
{
    /**
     * Reordena os botões do artista autenticado em transação atômica com normalização por seção.
     */
    public function reorder(ReorderButtonsRequest $request, ButtonOrderService $orderService): JsonResponse
    {
        $artist = $request->user();
        $items = $request->validated('items');

        return DB::transaction(function () use ($artist, $items, $orderService) {
            $artistButtons = $artist->buttons()
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get(['id', 'display_mode', 'icon_variant', 'show_icon_background', 'order_index']);

            if (!$orderService->allItemsBelongToArtist($artistButtons, $items)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Um ou mais botões informados não pertencem ao seu perfil ou não existem.',
                ], 403);
            }

            $plan = $orderService->buildNormalizedUpdates($artistButtons, $items);

            if ($orderService->countCircularInPlan($plan) > ButtonOrderService::MAX_CIRCULAR_BUTTONS) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Você pode ter no máximo 5 botões circulares.',
                    'errors' => [
                        'items' => ['Você pode ter no máximo 5 botões circulares.'],
                    ],
                ], 422);
            }

            foreach ($plan as $entry) {
                /** @var Button $button */
                $button = $entry['button'];
                $button->fill($entry['attributes']);

                if ($button->isDirty()) {
                    $button->save();
                }
            }

            $buttons = $artist->buttons()
                ->orderBy('id', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Ordem dos botões atualizada com sucesso.',
                'data' => [
                    'updated_count' => count($plan),
                    'buttons' => $buttons,
                ],
            ]);
        });
    }

    /**
     * Upload e substituição da imagem/thumbnail para botões.
     */
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

        if ($button->image_url) {
            $parsedPath = parse_url($button->image_url, PHP_URL_PATH) ?? $button->image_url;
            $oldPath = ltrim(str_replace('/storage/', '', $parsedPath), '/');
            if (str_starts_with($oldPath, 'storage/')) {
                $oldPath = substr($oldPath, 8);
            }
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $path = $request->file('image')->store('cards', 'public');

        $button->update([
            'image_url' => Storage::url($path),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Imagem do card atualizada com sucesso.',
            'image_url' => $button->image_url,
        ]);
    }
}
