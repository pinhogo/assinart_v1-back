<?php

namespace Database\Seeders;

use App\Models\Artist;
use App\Models\Button;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Artista de Demonstração Neo-Brutalista
        $artist = Artist::create([
            'name' => 'Gabriel Pinho',
            'username' => 'pinhogo',
            'email' => 'gabriel@assin.art',
            'password' => Hash::make('secret123'),
            'bio' => 'Designer Neo-Brutalista e Criador da Assin.art. Construindo a ponte visual entre artistas e seus fãs.',
            'avatar_url' => 'https://api.dicebear.com/7.x/bottts/svg?seed=pinhogo',
            'cover_url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80',
            'background_color' => '#FFE600',
            'background_id' => 'dots-grid',
            'button_shape' => 'square',
            'avatar_shape' => 'square',
            'font_family' => 'Space Grotesk',
            'font_color' => '#000000',
            'border_style' => 'solid-3px',
            'icon_variant' => 'default',
            'email_verified_at' => now(),
        ]);

        // 2. Botões e Redes Sociais do Artista
        $buttons = [
            [
                'title' => 'Assin.art — Plataforma Oficial',
                'url' => 'https://assin.art',
                'platform' => 'custom',
                'type' => 'card_featured',
                'button_color' => '#FFFFFF',
                'text_color' => '#000000',
                'font_family' => 'Space Grotesk',
                'image_url' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=600&q=80',
                'order_index' => 0,
                'clicks_count' => 1240,
                'is_active' => true,
            ],
            [
                'title' => 'Instagram Oficial',
                'url' => 'https://instagram.com/pinhogo',
                'platform' => 'instagram',
                'type' => 'standard',
                'button_color' => null, // herda amarelo do tema
                'text_color' => null,
                'font_family' => null,
                'image_url' => null,
                'order_index' => 1,
                'clicks_count' => 450,
                'is_active' => true,
            ],
            [
                'title' => 'Spotify Playlist / Lançamentos',
                'url' => 'https://open.spotify.com',
                'platform' => 'spotify',
                'type' => 'standard',
                'button_color' => '#1DB954', // botão com cor temática individual
                'text_color' => '#FFFFFF',
                'font_family' => null,
                'image_url' => null,
                'order_index' => 2,
                'clicks_count' => 890,
                'is_active' => true,
            ],
            [
                'title' => 'Canal no YouTube',
                'url' => 'https://youtube.com',
                'platform' => 'youtube',
                'type' => 'icon_pill',
                'button_color' => '#FF0000',
                'text_color' => '#FFFFFF',
                'font_family' => null,
                'image_url' => null,
                'order_index' => 3,
                'clicks_count' => 310,
                'is_active' => true,
            ],
            [
                'title' => 'Repositório GitHub',
                'url' => 'https://github.com/pinhogo',
                'platform' => 'github',
                'type' => 'icon_pill',
                'button_color' => '#000000',
                'text_color' => '#FFFFFF',
                'font_family' => null,
                'image_url' => null,
                'order_index' => 4,
                'clicks_count' => 195,
                'is_active' => true,
            ],
        ];

        foreach ($buttons as $b) {
            $artist->buttons()->create($b);
        }
    }
}
