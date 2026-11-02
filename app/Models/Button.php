<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Button extends Model
{
    use HasFactory;

    protected $table = 'buttons';

    protected $fillable = [
        'artist_id',
        'title',
        'url',
        'platform',
        'display_mode',
        'icon_variant',
        'show_icon_background',
        'type',
        'button_color',
        'text_color',
        'font_family',
        'font_weight',
        'font_style',
        'text_border',
        'image_url',
        'order_index',
        'clicks_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'artist_id' => 'integer',
            'order_index' => 'integer',
            'clicks_count' => 'integer',
            'is_active' => 'boolean',
            'show_icon_background' => 'boolean',
        ];
    }

    protected $appends = [
        'resolved_font_family',
        'resolved_text_color',
        'resolved_button_color',
        'resolved_circular_bg',
        'resolved_shadow',
        'resolved_font_weight',
        'resolved_font_style',
        'resolved_text_border',
    ];

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    public function buttonClicks(): HasMany
    {
        return $this->hasMany(ButtonClick::class, 'button_id');
    }

    public function getResolvedFontFamilyAttribute(): string
    {
        return $this->font_family ?? $this->artist?->button_font_family ?? $this->artist?->font_family ?? 'Space Grotesk';
    }

    public function getResolvedTextColorAttribute(): string
    {
        if ($this->text_color) {
            return $this->text_color;
        }
        if ($this->artist && !empty($this->artist->button_text_color) && $this->artist->button_text_color !== '#000000') {
            return $this->artist->button_text_color;
        }
        return $this->artist?->font_color ?? $this->artist?->button_text_color ?? '#000000';
    }

    public function getResolvedButtonColorAttribute(): string
    {
        return $this->button_color ?? $this->artist?->button_color ?? '#FFE600';
    }

    public function getResolvedShadowAttribute(): string
    {
        return $this->artist?->button_shadow ?? 'hard_small';
    }

    public function getResolvedFontWeightAttribute(): string
    {
        return $this->font_weight ?? $this->artist?->button_font_weight ?? 'normal';
    }

    public function getResolvedFontStyleAttribute(): string
    {
        return $this->font_style ?? $this->artist?->button_font_style ?? 'normal';
    }

    public function getResolvedTextBorderAttribute(): string
    {
        return $this->text_border ?? $this->artist?->button_text_border ?? 'none';
    }

    public function getResolvedCircularBgAttribute(): string
    {
        $artistSetting = $this->artist?->circular_icon_background ?? 'auto';

        if ($artistSetting !== 'auto' && preg_match('/^#[a-fA-F0-9]{6}$/', $artistSetting)) {
            return $artistSetting;
        }

        return match ($this->icon_variant) {
            'white' => '#000000',
            'black' => '#FFFFFF',
            default => '#FFFFFF',
        };
    }
}
