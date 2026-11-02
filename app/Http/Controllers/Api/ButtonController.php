<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ManagesButtonReorderAndImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreButtonRequest;
use App\Http\Requests\UpdateButtonRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function store(StoreButtonRequest $request): JsonResponse
    {
        $artist = $request->user();

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
        $validated['is_active'] = $validated['is_active'] ?? true;

        $displayMode = $validated['display_mode'] ?? 'button';
        $iconVariant = $validated['icon_variant'] ?? 'default';
        $showBackground = $validated['show_icon_background'] ?? true;

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
}
