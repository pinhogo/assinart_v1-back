<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Http\Requests\ReorderButtonsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

trait ManagesButtonReorderAndImage
{
    /**
     * Reordena os botões do artista autenticado em transação atômica.
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

            if (count($userButtonIds) !== count($orderedIds)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Um ou mais botões informados não pertencem ao seu perfil ou não existem.',
                ], 403);
            }

            foreach ($orderedIds as $index => $id) {
                $artist->buttons()->where('id', $id)->update(['order_index' => $index]);
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
