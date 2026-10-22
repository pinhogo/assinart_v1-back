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

    /**
     * Tabela associada ao Model.
     *
     * @var string
     */
    protected $table = 'artists';

    /**
     * Atributos que podem ser preenchidos em massa (Mass Assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'bio',
        'avatar_url',
        'cover_url',
        'background_color',
        'background_id',
        'button_shape',
        'button_shadow',
        'circular_icon_background',
        'avatar_shape',
        'font_family',
        'font_color',
        'button_color',
        'button_text_color',
        'border_style',
        'border_color',
        'icon_variant',
        'email_verified_at',
    ];

    /**
     * Atributos ocultados na serialização (JSON / Arrays).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts de tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Customização da chave de resolução em rotas (Route Model Binding).
     * Permite rotas como /{username} automaticamente.
     */
    public function getRouteKeyName(): string
    {
        return 'username';
    }

    /**
     * Relacionamento: Todos os botões do artista (ordenados por order_index).
     */
    public function buttons(): HasMany
    {
        return $this->hasMany(Button::class, 'artist_id')->orderBy('order_index', 'asc');
    }

    /**
     * Relacionamento: Apenas botões ativos e visíveis para a página pública.
     */
    public function activeButtons(): HasMany
    {
        return $this->hasMany(Button::class, 'artist_id')
            ->where('is_active', true)
            ->orderBy('order_index', 'asc');
    }

    /**
     * Coleção de botões em modo circular (abaixo da bio).
     */
    public function circularButtons(): HasMany
    {
        return $this->hasMany(Button::class, 'artist_id')
            ->where('is_active', true)
            ->where('display_mode', 'circular')
            ->orderBy('order_index', 'asc');
    }

    /**
     * Coleção de botões em modo regular (pilha de botões).
     */
    public function stackButtons(): HasMany
    {
        return $this->hasMany(Button::class, 'artist_id')
            ->where('is_active', true)
            ->where('display_mode', 'button')
            ->orderBy('order_index', 'asc');
    }
}
