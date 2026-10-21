<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    /**
     * Exibe os dados do perfil público do artista para renderização.
     * Rota: GET /{username}
     */
    public function show(string $username): JsonResponse
    {
        $artist = Artist::where('username', $username)
            ->with(['activeButtons'])
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $artist->id,
                'name' => $artist->name,
                'username' => $artist->username,
                'bio' => $artist->bio,
                'avatar_url' => $artist->avatar_url,
                'cover_url' => $artist->cover_url,
                'theme' => [
                    'background_color' => $artist->background_color,
                    'background_id' => $artist->background_id,
                    'button_shape' => $artist->button_shape,
                    'avatar_shape' => $artist->avatar_shape,
                    'font_family' => $artist->font_family,
                    'font_color' => $artist->font_color,
                    'border_style' => $artist->border_style,
                    'icon_variant' => $artist->icon_variant,
                ],
                'buttons' => $artist->activeButtons->map(function ($button) use ($artist) {
                    return [
                        'id' => $button->id,
                        'title' => $button->title,
                        'redirect_url' => url('/r/' . $button->id),
                        'target_url' => $button->url,
                        'platform' => $button->platform,
                        'type' => $button->type,
                        'image_url' => $button->image_url,
                        'styles' => [
                            'button_color' => $button->button_color ?? '#FFE600',
                            'text_color' => $button->text_color ?? $artist->font_color,
                            'font_family' => $button->font_family ?? $artist->font_family,
                        ],
                    ];
                }),
            ],
        ]);
    }
}
