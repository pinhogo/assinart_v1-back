<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Button;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ButtonController extends Controller
{
    /**
     * Lista todos os botões do artista logado.
     */
    public function index(Request $request): JsonResponse
    {
        $artist = $request->user();
        $buttons = $artist->buttons()->orderBy('order_index', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $buttons,
        ]);
    }

    /**
     * Cria um novo botão/link.
     */
    public function store(Request $request): JsonResponse
    {
        $artist = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'url' => 'required|url|max:2048',
            'platform' => 'nullable|string|max:50',
            'type' => 'nullable|in:standard,icon_pill,card_featured',
            'button_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'font_family' => 'nullable|string|max:50',
            'image_url' => 'nullable|url|max:2048',
        ]);

        $maxOrder = $artist->buttons()->max('order_index') ?? -1;
        $validated['order_index'] = $maxOrder + 1;

        $button = $artist->buttons()->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Botão criado com sucesso.',
            'data' => $button,
        ], 201);
    }

    /**
     * Atualiza um botão existente.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $artist = $request->user();
        $button = $artist->buttons()->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:100',
            'url' => 'sometimes|required|url|max:2048',
            'platform' => 'nullable|string|max:50',
            'type' => 'nullable|in:standard,icon_pill,card_featured',
            'button_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'font_family' => 'nullable|string|max:50',
            'image_url' => 'nullable|url|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        $button->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Botão atualizado com sucesso.',
            'data' => $button,
        ]);
    }

    /**
     * Exclui um botão.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $artist = $request->user();
        $button = $artist->buttons()->findOrFail($id);
        $button->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Botão removido com sucesso.',
        ]);
    }

    /**
     * Reordena os botões via Drag & Drop.
     * Recebe um array com os IDs na nova ordem: { "ordered_ids": [5, 2, 8, 1] }
     */
    public function reorder(Request $request): JsonResponse
    {
        $artist = $request->user();

        $validated = $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'integer|exists:buttons,id',
        ]);

        DB::transaction(function () use ($artist, $validated) {
            foreach ($validated['ordered_ids'] as $index => $id) {
                $artist->buttons()
                    ->where('id', $id)
                    ->update(['order_index' => $index]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Ordem dos botões atualizada com sucesso.',
        ]);
    }
}
