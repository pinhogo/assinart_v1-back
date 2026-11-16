<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ManagesProfileMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Artist;
use App\Rules\HexColor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ManagesProfileMedia;

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->serializeArtist($request->user()),
        ], 200);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $artist = $request->user();
        $validated = $request->validated();

        if ($request->has('avatar') && is_string($request->input('avatar')) && str_starts_with($request->input('avatar'), 'preset:') && !$request->has('avatar_url')) {
            $validated['avatar_url'] = null;
        }

        if ($request->has('cover_url') && !empty($validated['cover_url']) && !$request->has('cover_color') && !$request->has('cover')) {
            $validated['cover_color'] = null;
        }
        if ($request->has('cover_color') && !empty($validated['cover_color']) && !$request->has('cover_url') && !$request->has('cover')) {
            $validated['cover_url'] = null;
        }

        if ($request->has('background') && is_string($bg = $request->input('background'))) {
            if (str_starts_with($bg, 'color:')) {
                $hexColor = substr($bg, 6);
                if (!HexColor::isValid($hexColor)) {
                    return response()->json([
                        'message' => 'A cor de fundo deve ser um código hexadecimal válido (#RGB ou #RRGGBB).',
                        'errors' => ['background' => ['A cor de fundo deve ser um código hexadecimal válido (#RGB ou #RRGGBB).']],
                    ], 422);
                }
                $validated['background_color'] = $hexColor;
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
                $hexColor = substr($cov, 6);
                if (!HexColor::isValid($hexColor)) {
                    return response()->json([
                        'message' => 'A cor da capa deve ser um código hexadecimal válido (#RGB ou #RRGGBB).',
                        'errors' => ['cover' => ['A cor da capa deve ser um código hexadecimal válido (#RGB ou #RRGGBB).']],
                    ], 422);
                }
                $validated['cover_color'] = $hexColor;
                $validated['cover_url'] = null;
            }
        }

        if (array_key_exists('publication_status', $validated)) {
            if (!array_key_exists('is_published', $validated)) {
                $validated['is_published'] = $validated['publication_status'] === 'published';
            }
            unset($validated['publication_status']);
        }

        if (array_key_exists('is_published', $validated)) {
            $validated['is_published'] = (bool) $validated['is_published'];
        }

        if (($validated['is_published'] ?? false) === true && $artist->email_verified_at === null) {
            $message = 'Confirme seu e-mail antes de publicar seu perfil.';

            return response()->json([
                'status' => 'error',
                'code' => 'email_not_verified',
                'message' => $message,
                'errors' => [
                    'is_published' => [$message],
                ],
            ], 422);
        }

        if (array_key_exists('circular_shadow_enabled', $validated)) {
            if ($validated['circular_shadow_enabled'] === null) {
                unset($validated['circular_shadow_enabled']);
            } else {
                $validated['circular_shadow_enabled'] = (bool) $validated['circular_shadow_enabled'];
            }
        }

        $artist->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Perfil atualizado com sucesso.',
            'data' => $this->serializeArtist($artist->fresh()),
        ], 200);
    }

    private function serializeArtist(Artist $artist): array
    {
        return array_merge($artist->toArray(), [
            'button_shadow_color' => $artist->button_shadow_color ?? '#000000',
            'circular_shadow_enabled' => (bool) ($artist->circular_shadow_enabled ?? true),
        ]);
    }
}
