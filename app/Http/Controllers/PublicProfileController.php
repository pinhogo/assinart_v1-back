<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\JsonResponse;

class PublicProfileController extends Controller
{
    /**
     * Lista de rotas reservadas de sistema que não podem ser resolvidas como @username.
     */
    protected const RESERVED_ROUTES = [
        'api',
        'storage',
        'r',
        'admin',
        'login',
        'register',
        'dashboard',
        'editor',
        'up',
    ];

    /**
     * Exibe os dados do perfil público do artista para renderização no frontend.
     * Rota: GET /{username}
     *
     * Estados estritos de resposta:
     * 1. 404 Not Found: artista inexistente ou username reservado/inválido.
     * 2. 200 OK com status 'no_links': artista existe, mas sem botões ativos.
     * 3. 200 OK com status 'found': artista existe com botões ativos e estilos resolvidos.
     *
     * @param string $username
     * @return JsonResponse
     */
    public function show(string $username): JsonResponse
    {
        // 1. Normaliza o username de entrada: remove '@' inicial, trim de espaços e minúsculas
        $cleanUsername = strtolower(trim($username));
        $cleanUsername = ltrim($cleanUsername, '@');

        // Se vazio ou rota reservada, retorna 404 Not Found imediatamente
        if ($cleanUsername === '' || in_array($cleanUsername, self::RESERVED_ROUTES, true)) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Artista não encontrado.',
            ], 404);
        }

        // 2. Consulta otimizada em 1 query única com eager loading dos botões ativos
        $artist = Artist::where('username', $cleanUsername)
            ->with(['activeButtons'])
            ->first();

        // Estado 1: 404 Not Found (quando $artist === null)
        if (! $artist) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Artista não encontrado.',
            ], 404);
        }

        $allButtons = $artist->activeButtons;

        $artistSummary = [
            'name' => $artist->name,
            'username' => $artist->username,
            'bio' => $artist->bio,
            'avatar_url' => $artist->avatar_url,
            'avatar_shape' => $artist->avatar_shape,
            'cover_url' => $artist->cover_url,
            'background_color' => $artist->background_color,
            'background_id' => $artist->background_id,
            'button_shape' => $artist->button_shape,
            'button_shadow' => $artist->button_shadow,
            'circular_icon_background' => $artist->circular_icon_background,
            'font_family' => $artist->font_family,
            'font_color' => $artist->font_color,
            'button_color' => $artist->button_color,
            'button_text_color' => $artist->button_text_color,
            'border_style' => $artist->border_style,
            'border_color' => $artist->border_color,
        ];

        // Estado 2: 200 OK com status 'no_links' (quando não há botões ativos cadastrados)
        if ($allButtons->isEmpty()) {
            return response()->json([
                'status' => 'no_links',
                'artist' => $artistSummary,
                'circular_links' => [],
                'button_links' => [],
                'data' => [
                    'id' => $artist->id,
                    'name' => $artist->name,
                    'username' => $artist->username,
                    'bio' => $artist->bio,
                    'avatar_url' => $artist->avatar_url,
                    'cover_url' => $artist->cover_url,
                    'theme' => array_merge($artistSummary, [
                        'icon_variant' => $artist->icon_variant,
                    ]),
                    'buttons' => [],
                    'circular_links' => [],
                    'button_links' => [],
                ],
            ], 200);
        }

        // Estado 3: 200 OK com status 'found' (quando há botões ativos com cascade de estilos)
        // Particionamento lógico de alta performance (especificação backend.md)
        $circularLinks = $allButtons->where('display_mode', 'circular')->values()->map(function ($btn) use ($artist) {
            $btn->setRelation('artist', $artist);

            return [
                'id' => $btn->id,
                'title' => $btn->title,
                'redirect_url' => url("/r/{$btn->id}"),
                'target_url' => $btn->url,
                'platform' => $btn->platform,
                'display_mode' => 'circular',
                'icon_variant' => $btn->icon_variant,
                'show_icon_background' => true,
                'resolved_circular_bg' => $btn->resolved_circular_bg,
                'order_index' => $btn->order_index,
            ];
        })->values()->all();

        $buttonLinks = $allButtons->where('display_mode', 'button')->values()->map(function ($btn) use ($artist) {
            $btn->setRelation('artist', $artist);

            return [
                'id' => $btn->id,
                'title' => $btn->title,
                'redirect_url' => url("/r/{$btn->id}"),
                'target_url' => $btn->url,
                'platform' => $btn->platform,
                'display_mode' => 'button',
                'icon_variant' => $btn->icon_variant,
                'show_icon_background' => $btn->show_icon_background,
                'resolved_button_color' => $btn->resolved_button_color,
                'resolved_text_color' => $btn->resolved_text_color,
                'resolved_font_family' => $btn->resolved_font_family,
                'resolved_shadow' => $btn->resolved_shadow,
                'order_index' => $btn->order_index,
                'styles' => [
                    'button_color' => $btn->resolved_button_color,
                    'text_color' => $btn->resolved_text_color,
                    'font_family' => $btn->resolved_font_family,
                ],
            ];
        })->values()->all();

        $legacyButtons = $allButtons->map(function ($button) use ($artist) {
            $button->setRelation('artist', $artist);

            return [
                'id' => $button->id,
                'title' => $button->title,
                'redirect_url' => url('/r/' . $button->id),
                'target_url' => $button->url,
                'platform' => $button->platform,
                'type' => $button->type,
                'display_mode' => $button->display_mode,
                'icon_variant' => $button->icon_variant,
                'show_icon_background' => $button->show_icon_background,
                'image_url' => $button->image_url,
                'styles' => [
                    'button_color' => $button->resolved_button_color,
                    'text_color' => $button->resolved_text_color,
                    'font_family' => $button->resolved_font_family,
                ],
            ];
        })->values()->all();

        return response()->json([
            'status' => 'found',
            'artist' => $artistSummary,
            'circular_links' => $circularLinks,
            'button_links' => $buttonLinks,
            'data' => [
                'id' => $artist->id,
                'name' => $artist->name,
                'username' => $artist->username,
                'bio' => $artist->bio,
                'avatar_url' => $artist->avatar_url,
                'cover_url' => $artist->cover_url,
                'theme' => array_merge($artistSummary, [
                    'icon_variant' => $artist->icon_variant,
                ]),
                'buttons' => $legacyButtons,
                'circular_links' => $circularLinks,
                'button_links' => $buttonLinks,
            ],
        ], 200);
    }
}
