<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Button extends Model
{
    use HasFactory;

    /**
     * Tabela associada ao Model.
     *
     * @var string
     */
    protected $table = 'buttons';

    /**
     * Atributos que podem ser preenchidos em massa (Mass Assignment).
     *
     * @var array<int, string>
     */
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
        'image_url',
        'order_index',
        'clicks_count',
        'is_active',
    ];

    /**
     * Casts de tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order_index' => 'integer',
            'clicks_count' => 'integer',
            'is_active' => 'boolean',
            'show_icon_background' => 'boolean',
        ];
    }

    /**
     * Atributos adicionais anexados à serialização JSON.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'resolved_font_family',
        'resolved_text_color',
        'resolved_button_color',
        'resolved_circular_bg',
        'resolved_shadow',
    ];

    /**
     * Relacionamento: Artista dono deste botão.
     */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class, 'artist_id');
    }

    /**
     * Retorna a fonte tipográfica resolvida (Individual ?? Herdada do Artista ?? 'Space Grotesk').
     */
    public function getResolvedFontFamilyAttribute(): string
    {
        return $this->font_family ?? $this->artist?->font_family ?? 'Space Grotesk';
    }

    /**
     * Retorna a cor do texto resolvida (Individual ?? Herdada do Artista ?? '#000000').
     */
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

    /**
     * Retorna a cor de fundo do botão resolvida (Individual ?? Default '#FFE600').
     */
    public function getResolvedButtonColorAttribute(): string
    {
        return $this->button_color ?? $this->artist?->button_color ?? '#FFE600';
    }

    /**
     * Retorna o estilo de sombra do botão (Herdada do Artista ?? 'hard_small').
     */
    public function getResolvedShadowAttribute(): string
    {
        return $this->artist?->button_shadow ?? 'hard_small';
    }

    /**
     * Accessor para resolução do fundo dos ícones circulares ('auto' vs custom hex).
     */
    public function getResolvedCircularBgAttribute(): string
    {
        $artistSetting = $this->artist?->circular_icon_background ?? 'auto';

        if ($artistSetting !== 'auto' && preg_match('/^#[a-fA-F0-9]{6}$/', $artistSetting)) {
            return $artistSetting;
        }

        // Lógica 'auto' de alto contraste
        return match ($this->icon_variant) {
            'white' => '#000000',
            'black' => '#FFFFFF',
            default => '#FFFFFF',
        };
    }
}
