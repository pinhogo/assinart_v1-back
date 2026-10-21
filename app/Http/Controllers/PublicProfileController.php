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

        // Estado 2: 200 OK com status 'no_links' (quando não há botões ativos cadastrados)
        if ($artist->activeButtons->isEmpty()) {
            return response()->json([
                'status' => 'no_links',
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
                    'buttons' => [],
                ],
            ], 200);
        }

        // Estado 3: 200 OK com status 'found' (quando há botões ativos com cascade de estilos)
        $buttons = $artist->activeButtons->map(function ($button) use ($artist) {
            // Vincula relação em memória para evitar queries N+1 durante resolução de cascade
            $button->setRelation('artist', $artist);

            return [
                'id' => $button->id,
                'title' => $button->title,
                'redirect_url' => url('/r/' . $button->id),
                'target_url' => $button->url,
                'platform' => $button->platform,
                'type' => $button->type,
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
                'buttons' => $buttons,
            ],
        ], 200);
    }
}
