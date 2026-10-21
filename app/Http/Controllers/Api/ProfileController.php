<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Retorna os dados do perfil do artista autenticado.
     * GET /api/v1/profile
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user(),
        ], 200);
    }

    /**
     * Atualiza dados de perfil e tema visual Neo-Brutalista.
     * PUT /api/v1/profile
     */
    public function update(Request $request): JsonResponse
    {
        $artist = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'username' => 'sometimes|required|string|max:50|unique:artists,username,' . $artist->id,
            'bio' => 'nullable|string|max:500',
            'background_color' => 'nullable|string|max:20',
            'background_id' => 'nullable|string|max:50',
            'button_shape' => 'nullable|in:square,rounded,pill',
            'avatar_shape' => 'nullable|in:circle,square,rounded',
            'font_family' => 'nullable|string|max:50',
            'font_color' => 'nullable|string|max:20',
            'border_style' => 'nullable|string|max:30',
            'icon_variant' => 'nullable|in:default,black_outline,white_outline,none',
        ]);

        $artist->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Perfil atualizado com sucesso.',
            'data' => $artist,
        ], 200);
    }

    /**
     * Upload de foto de avatar com prevenção de arquivos órfãos.
     * POST /api/v1/profile/avatar
     * Max 5MB (5120 KB), mimes: jpeg, png, jpg, webp.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $artist = $request->user();

        // REGRA CRÍTICA: Se avatar_url já estiver preenchida, exclui o arquivo antigo do storage
        if ($artist->avatar_url) {
            $oldPath = $this->extractRelativeStoragePath($artist->avatar_url);
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $avatarUrl = Storage::url($path);

        $artist->update([
            'avatar_url' => $avatarUrl,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Avatar atualizado com sucesso.',
            'avatar_url' => $artist->avatar_url,
        ], 200);
    }

    /**
     * Remove o avatar do artista e exclui fisicamente o arquivo do storage.
     * DELETE /api/v1/profile/avatar
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $artist = $request->user();

        if ($artist->avatar_url) {
            $oldPath = $this->extractRelativeStoragePath($artist->avatar_url);
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $artist->update([
            'avatar_url' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Avatar removido com sucesso.',
        ], 200);
    }

    /**
     * Upload de imagem de capa / banner de topo com prevenção de arquivos órfãos.
     * POST /api/v1/profile/cover
     * Max 10MB (10240 KB), mimes: jpeg, png, jpg, webp.
     */
    public function uploadCover(Request $request): JsonResponse
    {
        $request->validate([
            'cover' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $artist = $request->user();

        // REGRA CRÍTICA: Se cover_url já estiver preenchida, exclui o arquivo antigo do storage
        if ($artist->cover_url) {
            $oldPath = $this->extractRelativeStoragePath($artist->cover_url);
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $path = $request->file('cover')->store('covers', 'public');
        $coverUrl = Storage::url($path);

        $artist->update([
            'cover_url' => $coverUrl,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Capa atualizada com sucesso.',
            'cover_url' => $artist->cover_url,
        ], 200);
    }

    /**
     * Remove a imagem de capa do artista e exclui fisicamente o arquivo do storage.
     * DELETE /api/v1/profile/cover
     */
    public function deleteCover(Request $request): JsonResponse
    {
        $artist = $request->user();

        if ($artist->cover_url) {
            $oldPath = $this->extractRelativeStoragePath($artist->cover_url);
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $artist->update([
            'cover_url' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Capa removida com sucesso.',
        ], 200);
    }

    /**
     * Extrai o caminho relativo dentro do disco 'public' a partir de uma URL ou caminho salvo.
     * Ex.: '/storage/avatars/hash.webp' -> 'avatars/hash.webp'
     *      'http://localhost:8000/storage/covers/banner.jpg' -> 'covers/banner.jpg'
     */
    protected function extractRelativeStoragePath(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $parsedPath = parse_url($url, PHP_URL_PATH);
        $path = ($parsedPath !== false && $parsedPath !== null) ? $parsedPath : $url;

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return !empty($path) ? $path : null;
    }
}
