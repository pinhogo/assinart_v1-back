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
        return $this->font_family
            ?? $this->artist?->font_family
            ?? 'Space Grotesk';
    }

    /**
     * Retorna a cor do texto resolvida (Individual ?? Herdada do Artista ?? '#000000').
     */
    public function getResolvedTextColorAttribute(): string
    {
        return $this->text_color
            ?? $this->artist?->font_color
            ?? '#000000';
    }

    /**
     * Retorna a cor de fundo do botão resolvida (Individual ?? Default '#FFE600').
     */
    public function getResolvedButtonColorAttribute(): string
    {
        return $this->button_color ?? '#FFE600';
    }
}
