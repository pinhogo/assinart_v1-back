<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Artist;
use Illuminate\Support\Collection;

trait FormatsPublicProfile
{
    protected function buildThemeArray(Artist $artist): array
    {
        return [
            'avatar_shape' => $artist->avatar_shape,
            'cover_color' => $artist->cover_color,
            'wallpaper_url' => $artist->wallpaper_url,
            'wallpaper_repeat' => $artist->wallpaper_repeat,
            'wallpaper_preset_id' => $artist->wallpaper_preset_id,
            'background_color' => $artist->background_color,
            'background_id' => $artist->background_id,
            'button_shape' => $artist->button_shape,
            'button_shadow' => $artist->button_shadow,
            'circular_icon_background' => $artist->circular_icon_background,
            'font_family' => $artist->font_family,
            'font_color' => $artist->font_color,
            'profile_font_family' => $artist->profile_font_family,
            'profile_font_size' => $artist->profile_font_size,
            'profile_text_color' => $artist->profile_text_color,
            'profile_font_weight' => $artist->profile_font_weight,
            'profile_font_style' => $artist->profile_font_style,
            'profile_text_border' => $artist->profile_text_border,
            'button_font_family' => $artist->button_font_family ?? $artist->font_family ?? 'Space Grotesk',
            'button_font_size' => $artist->button_font_size,
            'button_color' => $artist->button_color,
            'button_text_color' => $artist->button_text_color,
            'button_font_weight' => $artist->button_font_weight,
            'button_font_style' => $artist->button_font_style,
            'button_text_border' => $artist->button_text_border,
            'border_style' => $artist->border_style,
            'border_color' => $artist->border_color,
            'icon_variant' => $artist->icon_variant,
        ];
    }

    protected function formatCircularLinks(Collection $buttons, Artist $artist): array
    {
        return $buttons->where('display_mode', 'circular')->values()->map(function ($btn) use ($artist) {
            $btn->setRelation('artist', $artist);
            return [
                'id' => (int) $btn->id,
                'title' => $btn->title,
                'redirect_url' => url("/r/{$btn->id}"),
                'target_url' => $btn->url,
                'platform' => $btn->platform,
                'display_mode' => 'circular',
                'icon_variant' => $btn->icon_variant,
                'show_icon_background' => true,
                'resolved_circular_bg' => $btn->resolved_circular_bg,
                'order_index' => (int) $btn->order_index,
            ];
        })->values()->all();
    }

    protected function formatButtonLinks(Collection $buttons, Artist $artist): array
    {
        return $buttons->where('display_mode', 'button')->values()->map(function ($btn) use ($artist) {
            $btn->setRelation('artist', $artist);
            return [
                'id' => (int) $btn->id,
                'title' => $btn->title,
                'redirect_url' => url("/r/{$btn->id}"),
                'target_url' => $btn->url,
                'platform' => $btn->platform,
                'type' => $btn->type,
                'display_mode' => 'button',
                'icon_variant' => $btn->icon_variant,
                'show_icon_background' => $btn->show_icon_background,
                'image_url' => $btn->image_url,
                'resolved_button_color' => $btn->resolved_button_color,
                'resolved_text_color' => $btn->resolved_text_color,
                'resolved_font_family' => $btn->resolved_font_family,
                'resolved_shadow' => $btn->resolved_shadow,
                'order_index' => (int) $btn->order_index,
                'styles' => [
                    'button_color' => $btn->resolved_button_color,
                    'text_color' => $btn->resolved_text_color,
                    'font_family' => $btn->resolved_font_family,
                    'font_weight' => $btn->resolved_font_weight,
                    'font_style' => $btn->resolved_font_style,
                    'text_border' => $btn->resolved_text_border,
                ],
            ];
        })->values()->all();
    }
}
