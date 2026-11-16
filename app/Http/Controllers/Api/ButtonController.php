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

class ButtonController extends Controller
{
    use ManagesButtonReorderAndImage;

    public function index(Request $request): JsonResponse
    {
        $buttons = $request->user()->buttons()->orderBy('order_index', 'asc')->get();

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
            $artistButtons = $artist->buttons()->orderBy('order_index', 'asc')->orderBy('id', 'asc')->lockForUpdate()->get();

            if ($artistButtons->count() >= ButtonOrderService::MAX_BUTTONS_PER_ARTIST) {
                return $this->limitErrorResponse(
                    'Limite de 30 links atingido.',
                    'links',
                    'Você pode cadastrar no máximo 30 links combinados entre redes sociais e personalizados.',
                );
            }

            $displayMode = $validated['display_mode'] ?? 'button';
            if ($displayMode === 'circular' && $artistButtons->where('display_mode', 'circular')->count() >= ButtonOrderService::MAX_CIRCULAR_BUTTONS) {
                return $this->limitErrorResponse(
                    'Você pode ter no máximo 5 botões circulares.',
                    'display_mode',
                    'Você pode ter no máximo 5 botões circulares.',
                );
            }

            $payload = $this->prepareStorePayload($validated, $artistButtons->count(), $orderService);
            $button = $artist->buttons()->create($payload);

            $artistButtons->push($button);
            $orderService->applyNormalizedPlan($orderService->normalizeArtistButtons($artistButtons));

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
            $artistButtons = $artist->buttons()->orderBy('order_index', 'asc')->orderBy('id', 'asc')->lockForUpdate()->get();

            /** @var Button $button */
            $button = $artistButtons->firstWhere('id', $id) ?? $artist->buttons()->findOrFail($id);

            if (array_key_exists('display_mode', $validated) && $validated['display_mode'] === null) {
                unset($validated['display_mode']);
            }

            $previousDisplayMode = $button->display_mode ?? 'button';
            $targetDisplayMode = $validated['display_mode'] ?? $previousDisplayMode;
            $displayModeChanged = $targetDisplayMode !== $previousDisplayMode;

            if ($displayModeChanged && $targetDisplayMode === 'circular') {
                $otherCircularCount = $artistButtons->where('id', '!=', $button->id)->where('display_mode', 'circular')->count();
                if ($otherCircularCount >= ButtonOrderService::MAX_CIRCULAR_BUTTONS) {
                    return $this->limitErrorResponse(
                        'Você pode ter no máximo 5 botões circulares.',
                        'display_mode',
                        'Você pode ter no máximo 5 botões circulares.',
                    );
                }
            }

            $button->update($this->prepareUpdatePayload($button, $validated, $targetDisplayMode, $orderService));

            if ($displayModeChanged) {
                $orderService->applyNormalizedPlan($orderService->normalizeArtistButtons($artistButtons));
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
        $this->deleteFileFromStorage($button->image_url, $artist->id);
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
            $this->deleteFileFromStorage($button->image_url, $artist->id);
        }
        $artist->buttons()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Todos os botões foram excluídos com sucesso.',
        ], 200);
    }
}
