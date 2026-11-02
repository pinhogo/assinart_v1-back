<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Artist extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'artists';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'bio',
        'avatar_url',
        'cover_url',
        'cover_color',
        'background_color',
        'background_id',
        'wallpaper_url',
        'wallpaper_repeat',
        'wallpaper_preset_id',
        'button_shape',
        'button_shadow',
        'circular_icon_background',
        'avatar_shape',
        'font_family',
        'font_color',
        'profile_font_family',
        'profile_font_size',
        'profile_text_color',
        'profile_font_weight',
        'profile_font_style',
        'profile_text_border',
        'button_font_family',
        'button_font_size',
        'button_color',
        'button_text_color',
        'button_font_weight',
        'button_font_style',
        'button_text_border',
        'border_style',
        'border_color',
        'icon_variant',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar',
        'cover',
        'background',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'wallpaper_repeat' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    public function getAvatarAttribute(): array
    {
        if (!empty($this->avatar_url)) {
            return ['type' => 'upload', 'value' => $this->avatar_url, 'url' => $this->avatar_url];
        }
        return ['type' => 'preset', 'value' => 'avatar_01', 'url' => null];
    }

    public function getCoverAttribute(): ?array
    {
        if (!empty($this->cover_url)) {
            return ['type' => 'upload', 'value' => $this->cover_url, 'url' => $this->cover_url];
        }
        if (!empty($this->cover_color)) {
            return ['type' => 'color', 'value' => $this->cover_color, 'url' => null];
        }
        return null;
    }

    public function getBackgroundAttribute(): array
    {
        if (!empty($this->wallpaper_url)) {
            return ['type' => 'upload', 'value' => $this->wallpaper_url, 'url' => $this->wallpaper_url];
        }
        if (!empty($this->background_id)) {
            return ['type' => 'preset', 'value' => $this->background_id, 'url' => null];
        }
        return ['type' => 'color', 'value' => $this->background_color ?: '#FFFFFF', 'url' => null];
    }

    public function buttons(): HasMany
    {
        return $this->hasMany(Button::class, 'artist_id')->orderBy('order_index', 'asc');
    }

    public function activeButtons(): HasMany
    {
        return $this->hasMany(Button::class, 'artist_id')->where('is_active', true)->orderBy('order_index', 'asc');
    }

    public function circularButtons(): HasMany
    {
        return $this->hasMany(Button::class, 'artist_id')
            ->where('is_active', true)->where('display_mode', 'circular')->orderBy('order_index', 'asc');
    }

    public function stackButtons(): HasMany
    {
        return $this->hasMany(Button::class, 'artist_id')
            ->where('is_active', true)->where('display_mode', 'button')->orderBy('order_index', 'asc');
    }

    public function pageViews(): HasMany
    {
        return $this->hasMany(PageView::class, 'artist_id');
    }
}
