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
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user(),
        ]);
    }

    /**
     * Atualiza dados de perfil e tema visual Neo-Brutalista.
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
        ]);
    }

    /**
     * Upload de foto de avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $artist = $request->user();
        $path = $request->file('avatar')->store('avatars', 'public');

        $artist->update([
            'avatar_url' => Storage::url($path),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Avatar atualizado com sucesso.',
            'avatar_url' => $artist->avatar_url,
        ]);
    }

    /**
     * Upload de imagem de capa / banner.
     */
    public function uploadCover(Request $request): JsonResponse
    {
        $request->validate([
            'cover' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $artist = $request->user();
        $path = $request->file('cover')->store('covers', 'public');

        $artist->update([
            'cover_url' => Storage::url($path),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Capa atualizada com sucesso.',
            'cover_url' => $artist->cover_url,
        ]);
    }
}
