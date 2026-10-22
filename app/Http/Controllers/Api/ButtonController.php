<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderButtonsRequest;
use App\Http\Requests\StoreButtonRequest;
use App\Http\Requests\UpdateButtonRequest;
use App\Models\Button;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ButtonController extends Controller
{
    /**
     * Lista todos os botões do artista autenticado ordenados por order_index ASC.
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
     * Cria um novo botão para o artista autenticado.
     */
    public function store(StoreButtonRequest $request): JsonResponse
    {
        $artist = $request->user();

        // Limite global de 10 botões / links
        if ($artist->buttons()->count() >= 10) {
            return response()->json([
                'message' => 'Limite de 10 links atingido.',
                'errors' => [
                    'links' => ['Você pode cadastrar no máximo 10 links combinados entre redes sociais e personalizados.'],
                ],
            ], 422);
        }

        $validated = $request->validated();

        $validated['platform'] = $validated['platform'] ?? 'custom';
        $validated['type'] = $validated['type'] ?? 'standard';
        $validated['is_active'] = true;

        $displayMode = $validated['display_mode'] ?? 'button';
        $iconVariant = $validated['icon_variant'] ?? 'default';
        $showBackground = $validated['show_icon_background'] ?? true;

        // Regra de auto-switch para modo circular
        if ($displayMode === 'circular') {
            $showBackground = true;
            if ($iconVariant === 'none') {
                $iconVariant = 'default';
            }
        }

        $validated['display_mode'] = $displayMode;
        $validated['icon_variant'] = $iconVariant;
        $validated['show_icon_background'] = $showBackground;

        $maxOrder = $artist->buttons()->max('order_index');
        $validated['order_index'] = is_null($maxOrder) ? 0 : $maxOrder + 1;

        $button = $artist->buttons()->create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Botão criado com sucesso.',
            'data' => $button,
        ], 201);
    }

    /**
     * Atualiza um botão existente do artista autenticado.
     */
    public function update(UpdateButtonRequest $request, int $id): JsonResponse
    {
        $artist = $request->user();
        $button = $artist->buttons()->findOrFail($id);

        $validated = $request->validated();

        $targetDisplayMode = $validated['display_mode'] ?? $button->display_mode;
        if ($targetDisplayMode === 'circular') {
            $validated['show_icon_background'] = true;
            $currentIcon = $validated['icon_variant'] ?? $button->icon_variant;
            if ($currentIcon === 'none') {
                $validated['icon_variant'] = 'default';
            }
        }

        $button->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Botão atualizado com sucesso.',
            'data' => $button->fresh(),
        ]);
    }

    /**
     * Remove um botão pertencente ao artista autenticado.
     */
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

    /**
     * Reordena os botões do artista autenticado em transação atômica.
     * Recebe um array com os IDs na nova ordem: { "ordered_ids": [5, 2, 8, 1] }
     */
    public function reorder(ReorderButtonsRequest $request): JsonResponse
    {
        $artist = $request->user();
        $orderedIds = $request->validated('ordered_ids');

        return DB::transaction(function () use ($artist, $orderedIds) {
            $userButtonIds = $artist->buttons()
                ->whereIn('id', $orderedIds)
                ->pluck('id')
                ->all();

            // Garante que TODOS os IDs informados pertencem ao artista logado
            if (count($userButtonIds) !== count($orderedIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Um ou mais botões informados não pertencem ao seu perfil ou não existem.',
                ], 403);
            }

            foreach ($orderedIds as $index => $id) {
                $artist->buttons()
                    ->where('id', $id)
                    ->update(['order_index' => $index]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ordem dos botões atualizada com sucesso.',
                'data' => [
                    'updated_count' => count($orderedIds),
                ],
            ]);
        });
    }

    /**
     * Upload e substituição da imagem/thumbnail para botões do tipo card_featured.
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
