<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait ManagesProfileMedia
{
    use InteractsWithMediaStorage;

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120']);
        $artist = $request->user();

        $this->deleteFileFromStorage($artist->avatar_url);
        $path = $request->file('avatar')->store('avatars', 'public');
        $artist->update(['avatar_url' => Storage::url($path)]);

        return response()->json([
            'status' => 'success',
            'message' => 'Avatar atualizado com sucesso.',
            'avatar_url' => $artist->avatar_url,
            'media' => ['type' => 'upload', 'value' => $artist->avatar_url, 'url' => $artist->avatar_url],
        ], 200);
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $artist = $request->user();
        $this->deleteFileFromStorage($artist->avatar_url);
        $artist->update(['avatar_url' => null]);
        return response()->json(['status' => 'success', 'message' => 'Avatar removido com sucesso.'], 200);
    }

    public function uploadCover(Request $request): JsonResponse
    {
        $request->validate(['cover' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240']);
        $artist = $request->user();

        $this->deleteFileFromStorage($artist->cover_url);
        $path = $request->file('cover')->store('covers', 'public');
        $artist->update(['cover_url' => Storage::url($path)]);

        return response()->json([
            'status' => 'success',
            'message' => 'Capa atualizada com sucesso.',
            'cover_url' => $artist->cover_url,
            'media' => ['type' => 'upload', 'value' => $artist->cover_url, 'url' => $artist->cover_url],
        ], 200);
    }

    public function deleteCover(Request $request): JsonResponse
    {
        $artist = $request->user();
        $this->deleteFileFromStorage($artist->cover_url);
        $artist->update(['cover_url' => null, 'cover_color' => null]);
        return response()->json(['status' => 'success', 'message' => 'Capa removida com sucesso.'], 200);
    }

    public function uploadBackground(Request $request): JsonResponse
    {
        $fileKey = $request->hasFile('background') ? 'background' : 'wallpaper';
        $request->validate([$fileKey => 'required|image|mimes:jpeg,png,jpg,webp|max:10240']);
        $artist = $request->user();

        $this->deleteFileFromStorage($artist->wallpaper_url);
        $path = $request->file($fileKey)->store('wallpapers', 'public');
        $artist->update(['wallpaper_url' => Storage::url($path), 'background_id' => null]);

        return response()->json([
            'status' => 'success',
            'message' => 'Plano de fundo atualizado com sucesso.',
            'background_url' => $artist->wallpaper_url,
            'wallpaper_url' => $artist->wallpaper_url,
            'media' => ['type' => 'upload', 'value' => $artist->wallpaper_url, 'url' => $artist->wallpaper_url],
        ], 200);
    }

    public function deleteBackground(Request $request): JsonResponse
    {
        $artist = $request->user();
        $this->deleteFileFromStorage($artist->wallpaper_url);
        $artist->update(['wallpaper_url' => null, 'background_id' => null, 'background_color' => '#FFFFFF']);
        return response()->json(['status' => 'success', 'message' => 'Plano de fundo removido com sucesso.'], 200);
    }
}
