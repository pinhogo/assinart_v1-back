<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OnboardingBackgroundRequest;
use App\Http\Requests\OnboardingButtonsRequest;
use App\Http\Requests\OnboardingLinksRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    /**
     * 17 Neo-Brutalist background palette presets (Figma & Frontend design tokens).
     */
    public const PALETTE_PRESETS = [
        'paper' => '#F7F6F2',
        'linen' => '#EFECE6',
        'butter' => '#F3E7B8',
        'peach' => '#F6D5BC',
        'clay' => '#E9C5B5',
        'rose' => '#E8D5D7',
        'lavender' => '#DAD3EC',
        'snow' => '#D5E0EA',
        'sky' => '#A8CCE4',
        'sage' => '#C9D9D0',
        'celadon' => '#ACE1AF',
        'olive' => '#A9AE86',
        'terracotta' => '#E15B3A',
        'wine' => '#6B2D3C',
        'forest' => '#2F4A3C',
        'navy' => '#1F2B47',
        'noir' => '#121316',
    ];

    /**
     * Default human-readable labels for the 17 supported networks.
     */
    public const PLATFORM_TITLES = [
        'website' => 'Website',
        'youtube' => 'YouTube',
        'x' => 'X',
        'linkedin' => 'LinkedIn',
        'soundcloud' => 'SoundCloud',
        'instagram' => 'Instagram',
        'spotify' => 'Spotify',
        'threads' => 'Threads',
        'pinterest' => 'Pinterest',
        'discord' => 'Discord',
        'whatsapp' => 'WhatsApp',
        'tiktok' => 'TikTok',
        'facebook' => 'Facebook',
        'behance' => 'Behance',
        'telegram' => 'Telegram',
        'twitch' => 'Twitch',
        'email' => 'E-mail',
    ];

    /**
     * Etapa 1: Salva a bio e sincroniza até 5 links iniciais dentre as 17 redes suportadas.
     * Endpoint: PUT /api/v1/onboarding/links
     */
    public function saveLinks(OnboardingLinksRequest $request): JsonResponse
    {
        $artist = $request->user();

        // 1. Atualiza a bio se presente no payload
        if ($request->has('bio')) {
            $artist->bio = $request->input('bio');
            $artist->save();
        }

        // 2. Extrai e normaliza a lista de links (suporta payload padrão e formato draft do frontend)
        $rawLinks = $request->input('links');

        if ($rawLinks === null && ($request->has('selectedPlatforms') || $request->has('linkValues'))) {
            $selected = $request->input('selectedPlatforms', []);
            $values = $request->input('linkValues', []);
            $rawLinks = [];

            if (is_array($selected)) {
                foreach ($selected as $platform) {
                    $val = is_array($values) ? ($values[$platform] ?? '') : '';
                    $rawLinks[] = [
                        'platform' => (string) $platform,
                        'value' => (string) $val,
                    ];
                }
            }
        }

        $rawLinks = is_array($rawLinks) ? $rawLinks : [];

        // 3. Processa até 5 links válidos
        $parsedButtons = [];
        $count = 0;

        foreach ($rawLinks as $item) {
            if ($count >= 5) {
                break;
            }

            if (!is_array($item)) {
                continue;
            }

            $platform = (string) ($item['platform'] ?? '');
            if (!array_key_exists($platform, self::PLATFORM_TITLES)) {
                continue;
            }

            $rawInput = trim((string) ($item['url'] ?? $item['value'] ?? ''));
            $formattedUrl = $this->formatPlatformUrl($platform, $rawInput);

            $title = !empty($item['title'])
                ? trim((string) $item['title'])
                : (self::PLATFORM_TITLES[$platform] ?? ucfirst($platform));

            $parsedButtons[] = [
                'title' => $title,
                'url' => $formattedUrl,
                'platform' => $platform,
                'type' => 'standard',
                'order_index' => $count,
                'clicks_count' => 0,
                'is_active' => true,
            ];

            $count++;
        }

        // 4. Sincroniza atomicamente no banco de dados
        $createdButtons = DB::transaction(function () use ($artist, $parsedButtons) {
            $artist->buttons()->delete();

            $results = [];
            foreach ($parsedButtons as $btnData) {
                $results[] = $artist->buttons()->create($btnData);
            }

            return $results;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Links e bio salvos com sucesso.',
            'data' => [
                'bio' => $artist->fresh()->bio,
                'buttons' => $createdButtons,
            ],
        ], 200);
    }

    /**
     * Etapa 2: Personalização do plano de fundo (cor e padrão/textura).
     * Endpoint: PUT /api/v1/onboarding/background
     */
    public function saveBackground(OnboardingBackgroundRequest $request): JsonResponse
    {
        $artist = $request->user();

        $backgroundId = $request->input('background_id');
        $backgroundColor = $request->input('background_color');

        // Se a cor não foi passada explicitamente, mapeia pelo preset selecionado
        if (empty($backgroundColor) && !empty($backgroundId) && isset(self::PALETTE_PRESETS[$backgroundId])) {
            $backgroundColor = self::PALETTE_PRESETS[$backgroundId];
        }

        $updateData = [];
        if (!empty($backgroundColor)) {
            $updateData['background_color'] = $backgroundColor;
        }
        if ($backgroundId !== null) {
            $updateData['background_id'] = $backgroundId;
        }

        if (!empty($updateData)) {
            $artist->update($updateData);
        }

        $fresh = $artist->fresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Fundo atualizado com sucesso.',
            'data' => [
                'background_color' => $fresh->background_color,
                'background_id' => $fresh->background_id,
            ],
        ], 200);
    }

    /**
     * Etapa 3: Personalização dos botões, conclusão do onboarding e publicação do perfil.
     * Endpoint: PUT /api/v1/onboarding/buttons
     */
    public function saveButtons(OnboardingButtonsRequest $request): JsonResponse
    {
        $artist = $request->user();

        $shape = $request->input('button_shape');
        $color = $request->input('button_color');

        $artist->button_shape = $shape;
        if ($artist->email_verified_at === null) {
            $artist->email_verified_at = now();
        }
        $artist->save();

        if (!empty($color)) {
            $artist->buttons()->update(['button_color' => $color]);
        }

        $fresh = $artist->fresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Onboarding concluído com sucesso.',
            'redirect_url' => '/editor',
            'data' => [
                'button_shape' => $fresh->button_shape,
            ],
        ], 200);
    }

    /**
     * Formata e sanitiza URL de acordo com a plataforma social.
     */
    protected function formatPlatformUrl(string $platform, string $input): string
    {
        $input = trim($input);

        if (empty($input)) {
            return '';
        }

        switch ($platform) {
            case 'website':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                return 'https://' . ltrim($input, '/');

            case 'email':
                if (str_starts_with(strtolower($input), 'mailto:')) {
                    return $input;
                }
                return 'mailto:' . $input;

            case 'whatsapp':
                $digits = preg_replace('/\D/', '', $input);
                if (empty($digits)) {
                    return $input;
                }
                if (str_starts_with($digits, '55') && strlen($digits) > 11) {
                    return 'https://wa.me/' . $digits;
                }
                if (strlen($digits) === 10 || strlen($digits) === 11) {
                    return 'https://wa.me/55' . $digits;
                }
                return 'https://wa.me/' . $digits;

            case 'instagram':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?instagram\.com/#i', '', $input);
                $clean = ltrim(trim($clean, '/'), '@');
                return 'https://instagram.com/' . $clean;

            case 'tiktok':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?tiktok\.com/@?#i', '', $input);
                $clean = ltrim(trim($clean, '/'), '@');
                return 'https://tiktok.com/@' . $clean;

            case 'youtube':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?youtube\.com/(@|c/|channel/|user/)?#i', '', $input);
                $clean = ltrim(trim($clean, '/'), '@');
                return 'https://youtube.com/@' . $clean;

            case 'x':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?(x\.com|twitter\.com)/#i', '', $input);
                $clean = ltrim(trim($clean, '/'), '@');
                return 'https://x.com/' . $clean;

            case 'linkedin':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?linkedin\.com/(in/)?#i', '', $input);
                $clean = preg_replace('#^in/#i', '', $clean);
                $clean = trim($clean, '/');
                return 'https://linkedin.com/in/' . $clean;

            case 'spotify':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(open\.)?spotify\.com/(artist/)?#i', '', $input);
                $clean = preg_replace('#^artist/#i', '', $clean);
                $clean = trim($clean, '/');
                return 'https://open.spotify.com/artist/' . $clean;

            case 'threads':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?threads\.net/@?#i', '', $input);
                $clean = ltrim(trim($clean, '/'), '@');
                return 'https://threads.net/@' . $clean;

            case 'pinterest':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?pinterest\.com/#i', '', $input);
                $clean = trim($clean, '/');
                return 'https://pinterest.com/' . $clean;

            case 'discord':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?(discord\.gg|discord\.com/invite)/#i', '', $input);
                $clean = trim($clean, '/');
                return 'https://discord.gg/' . $clean;

            case 'facebook':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?facebook\.com/#i', '', $input);
                $clean = trim($clean, '/');
                return 'https://facebook.com/' . $clean;

            case 'behance':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?behance\.net/#i', '', $input);
                $clean = trim($clean, '/');
                return 'https://behance.net/' . $clean;

            case 'telegram':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?(t\.me|telegram\.me)/#i', '', $input);
                $clean = ltrim(trim($clean, '/'), '@');
                return 'https://t.me/' . $clean;

            case 'twitch':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?twitch\.tv/#i', '', $input);
                $clean = trim($clean, '/');
                return 'https://twitch.tv/' . $clean;

            case 'soundcloud':
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                $clean = preg_replace('#^(https?://)?(www\.)?soundcloud\.com/#i', '', $input);
                $clean = trim($clean, '/');
                return 'https://soundcloud.com/' . $clean;

            default:
                if (preg_match('#^https?://#i', $input)) {
                    return $input;
                }
                return 'https://' . ltrim($input, '/');
        }
    }
}
