<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\InteractsWithMediaStorage;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use InteractsWithMediaStorage;

    public function show(Request $request): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $request->user()], 200);
    }

    public function update(Request $request): JsonResponse
    {
        $artist = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'username' => 'sometimes|required|string|max:50|unique:artists,username,' . $artist->id,
            'bio' => 'nullable|string|max:500',
            'background_color' => 'nullable|string|max:20',
            'background_id' => 'nullable|string|max:50',
            'wallpaper_url' => 'nullable|string|max:2048',
            'wallpaper_repeat' => 'nullable|boolean',
            'wallpaper_preset_id' => 'nullable|string|max:50',
            'cover_color' => 'nullable|string|max:20',
            'button_shape' => 'nullable|in:square,rounded,pill',
            'button_shadow' => 'nullable|string|max:30',
            'circular_icon_background' => 'nullable|string|max:20',
            'avatar_shape' => 'nullable|in:circle,square,rounded',
            'font_family' => 'nullable|string|max:50',
            'font_color' => 'nullable|string|max:20',
            'profile_font_family' => 'nullable|string|max:50',
            'profile_font_size' => 'nullable|in:sm,md,lg',
            'profile_text_color' => 'nullable|string|max:20',
            'profile_font_weight' => 'nullable|in:normal,bold',
            'profile_font_style' => 'nullable|in:normal,italic',
            'profile_text_border' => 'nullable|in:none,black,white,yellow',
            'button_font_family' => 'nullable|string|max:50',
            'button_font_size' => 'nullable|in:sm,md,lg',
            'button_color' => 'nullable|string|max:20',
            'button_text_color' => 'nullable|string|max:20',
            'button_font_weight' => 'nullable|in:normal,bold',
            'button_font_style' => 'nullable|in:normal,italic',
            'button_text_border' => 'nullable|in:none,black,white,yellow',
            'border_style' => 'nullable|string|max:30',
            'border_color' => 'nullable|string|max:20',
            'icon_variant' => 'nullable|string|max:30',
        ]);

        if ($request->has('avatar') && is_string($request->input('avatar')) && str_starts_with($request->input('avatar'), 'preset:')) {
            $validated['avatar_url'] = null;
        }

        if ($request->has('background') && is_string($bg = $request->input('background'))) {
            if (str_starts_with($bg, 'color:')) {
                $validated['background_color'] = substr($bg, 6);
                $validated['background_id'] = null;
            } elseif (str_starts_with($bg, 'preset:')) {
                $validated['background_id'] = substr($bg, 7);
            }
        }

        if ($request->has('cover')) {
            $cov = $request->input('cover');
            if ($cov === null || $cov === 'null') {
                $validated['cover_url'] = null;
                $validated['cover_color'] = null;
            } elseif (is_string($cov) && str_starts_with($cov, 'color:')) {
                $validated['cover_color'] = substr($cov, 6);
                $validated['cover_url'] = null;
            }
        }

        $artist->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Perfil atualizado com sucesso.',
            'data' => $artist->fresh(),
        ], 200);
    }

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
