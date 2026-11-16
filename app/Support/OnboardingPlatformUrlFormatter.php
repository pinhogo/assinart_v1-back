<?php

namespace App\Support;

use App\Services\ButtonOrderService;

class OnboardingPlatformUrlFormatter
{
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
        'custom' => 'Link Personalizado',
    ];

    private const PLATFORM_URL_TEMPLATES = [
        'instagram' => ['#^(https?://)?(www\.)?instagram\.com/#i', true, 'https://instagram.com/'],
        'tiktok' => ['#^(https?://)?(www\.)?tiktok\.com/@?#i', true, 'https://tiktok.com/@'],
        'youtube' => ['#^(https?://)?(www\.)?youtube\.com/(@|c/|channel/|user/)?#i', true, 'https://youtube.com/@'],
        'x' => ['#^(https?://)?(www\.)?(x\.com|twitter\.com)/#i', true, 'https://x.com/'],
        'linkedin' => ['#^(?:(?:https?://)?(?:www\.)?linkedin\.com/)?(?:in/)?#i', false, 'https://linkedin.com/in/'],
        'spotify' => ['#^(?:(?:https?://)?(?:open\.)?spotify\.com/)?(?:artist/)?#i', false, 'https://open.spotify.com/artist/'],
        'threads' => ['#^(https?://)?(www\.)?threads\.net/@?#i', true, 'https://threads.net/@'],
        'pinterest' => ['#^(https?://)?(www\.)?pinterest\.com/#i', false, 'https://pinterest.com/'],
        'discord' => ['#^(https?://)?(www\.)?(discord\.gg|discord\.com/invite)/#i', false, 'https://discord.gg/'],
        'facebook' => ['#^(https?://)?(www\.)?facebook\.com/#i', false, 'https://facebook.com/'],
        'behance' => ['#^(https?://)?(www\.)?behance\.net/#i', false, 'https://behance.net/'],
        'telegram' => ['#^(https?://)?(www\.)?(t\.me|telegram\.me)/#i', true, 'https://t.me/'],
        'twitch' => ['#^(https?://)?(www\.)?twitch\.tv/#i', false, 'https://twitch.tv/'],
        'soundcloud' => ['#^(https?://)?(www\.)?soundcloud\.com/#i', false, 'https://soundcloud.com/'],
    ];

    public static function formatPlatformUrl(string $platform, string $input): string
    {
        $input = trim($input);
        if ($input === '') {
            return '';
        }

        if ($platform === 'email') {
            return str_starts_with(strtolower($input), 'mailto:') ? $input : 'mailto:' . $input;
        }

        if ($platform === 'whatsapp') {
            $digits = preg_replace('/\D/', '', $input);
            if (empty($digits)) {
                return $input;
            }
            $prefix = (strlen($digits) === 10 || strlen($digits) === 11) ? '55' : '';

            return 'https://wa.me/' . $prefix . $digits;
        }

        if (preg_match('#^https?://#i', $input)) {
            return $input;
        }

        if (!isset(self::PLATFORM_URL_TEMPLATES[$platform])) {
            return 'https://' . ltrim($input, '/');
        }

        [$pattern, $stripAt, $base] = self::PLATFORM_URL_TEMPLATES[$platform];
        $clean = trim((string) preg_replace($pattern, '', $input), '/');

        return $base . ($stripAt ? ltrim($clean, '@') : $clean);
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: int}
     */
    public static function parseLinks(array $rawLinks, ButtonOrderService $orderService): array
    {
        $parsedButtons = [];
        $count = 0;
        $circularCount = 0;

        foreach ($rawLinks as $item) {
            if ($count >= ButtonOrderService::MAX_BUTTONS_PER_ARTIST) {
                break;
            }
            if (!is_array($item)) {
                continue;
            }

            $platform = (string) ($item['platform'] ?? 'custom');
            $platform = array_key_exists($platform, self::PLATFORM_TITLES) ? $platform : 'custom';

            $rawInput = trim((string) ($item['url'] ?? $item['value'] ?? ''));
            $isActive = array_key_exists('is_active', $item) ? (bool) $item['is_active'] : true;
            $formattedUrl = self::formatPlatformUrl($platform, $rawInput);
            if ($formattedUrl === '' && !$isActive) {
                $formattedUrl = 'https://assin.art';
            }

            $displayMode = in_array($item['display_mode'] ?? null, ['button', 'circular'], true) ? $item['display_mode'] : 'button';
            $iconVariant = in_array($item['icon_variant'] ?? null, ['default', 'black', 'white', 'none'], true) ? $item['icon_variant'] : 'default';
            $showBackground = array_key_exists('show_icon_background', $item) && $item['show_icon_background'] !== null
                ? (bool) $item['show_icon_background']
                : ($displayMode === 'circular');

            $normalizedDisplay = $orderService->normalizeDisplayAttributes($displayMode, $iconVariant, $showBackground);
            if ($normalizedDisplay['display_mode'] === 'circular') {
                $circularCount++;
            }

            $parsedButtons[] = [
                'title' => !empty($item['title']) ? trim((string) $item['title']) : (self::PLATFORM_TITLES[$platform] ?? ucfirst($platform)),
                'url' => $formattedUrl,
                'platform' => $platform,
                'display_mode' => $normalizedDisplay['display_mode'],
                'icon_variant' => $normalizedDisplay['icon_variant'],
                'show_icon_background' => $normalizedDisplay['show_icon_background'],
                'text_align' => in_array($item['text_align'] ?? null, ['left', 'center', 'right'], true) ? $item['text_align'] : 'left',
                'button_color' => self::trimmedOrNull($item['button_color'] ?? null),
                'text_color' => self::trimmedOrNull($item['text_color'] ?? null),
                'font_family' => self::trimmedOrNull($item['font_family'] ?? null),
                'font_weight' => in_array($item['font_weight'] ?? null, ['normal', 'bold'], true) ? $item['font_weight'] : null,
                'font_style' => in_array($item['font_style'] ?? null, ['normal', 'italic'], true) ? $item['font_style'] : null,
                'text_border' => in_array($item['text_border'] ?? null, ['none', 'black', 'white', 'yellow'], true) ? $item['text_border'] : null,
                'type' => 'standard',
                'order_index' => $count++,
                'is_active' => $isActive,
            ];
        }

        return [$parsedButtons, $circularCount];
    }

    private static function trimmedOrNull(mixed $value): ?string
    {
        return isset($value) && trim((string) $value) !== '' ? trim((string) $value) : null;
    }
}
