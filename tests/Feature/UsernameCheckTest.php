<?php

namespace Tests\Feature;

use App\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsernameCheckTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tier 1: Happy path - Verificação de username disponível.
     */
    public function test_can_check_available_username(): void
    {
        $response = $this->getJson('/api/v1/usernames/check?username=novo_artista');

        $response->assertStatus(200)
            ->assertJson([
                'username' => 'novo_artista',
                'available' => true,
                'reason' => null,
                'message' => 'Disponível',
            ]);
    }

    /**
     * Tier 1: Suporte a caracteres válidos permitidos (letras, dígitos, pontos, hífens, sublinhados).
     */
    public function test_valid_usernames_with_dots_hyphens_and_numbers(): void
    {
        $response = $this->getJson('/api/v1/usernames/check?username=artista.legal-123_ok');

        $response->assertStatus(200)
            ->assertJson([
                'username' => 'artista.legal-123_ok',
                'available' => true,
                'reason' => null,
                'message' => 'Disponível',
            ]);
    }

    /**
     * Tier 2: Sanitização automática - remove '@' inicial, trim de espaços e minúsculas.
     */
    public function test_strips_leading_at_symbol_and_lowercases_and_trims(): void
    {
        $response = $this->getJson('/api/v1/usernames/check?username=%20@Artista_Teste%20');

        $response->assertStatus(200)
            ->assertJson([
                'username' => 'artista_teste',
                'available' => true,
                'reason' => null,
            ]);
    }

    /**
     * Tier 1: Conflito de unicidade com usuário existente no banco de dados.
     */
    public function test_returns_taken_when_username_already_exists(): void
    {
        Artist::factory()->create([
            'username' => 'artista_existente',
        ]);

        $response = $this->getJson('/api/v1/usernames/check?username=artista_existente');

        $response->assertStatus(200)
            ->assertJson([
                'username' => 'artista_existente',
                'available' => false,
                'reason' => 'taken',
                'message' => 'Esse @ já está em uso.',
            ]);
    }

    /**
     * Tier 2: Verificação insensível a maiúsculas/minúsculas para usuários existentes.
     */
    public function test_returns_taken_case_insensitively(): void
    {
        Artist::factory()->create([
            'username' => 'gabriel_art',
        ]);

        $response = $this->getJson('/api/v1/usernames/check?username=@GABRIEL_ART');

        $response->assertStatus(200)
            ->assertJson([
                'username' => 'gabriel_art',
                'available' => false,
                'reason' => 'taken',
            ]);
    }

    /**
     * Tier 2: Bloqueio de palavras reservadas do sistema (admin, editor, r, login, etc.).
     */
    public function test_returns_reserved_for_system_routes(): void
    {
        $reservedWords = ['admin', 'api', 'editor', 'login', 'register', 'dashboard', 'termos'];

        foreach ($reservedWords as $word) {
            $response = $this->getJson('/api/v1/usernames/check?username=' . $word);

            $response->assertStatus(200)
                ->assertJson([
                    'username' => $word,
                    'available' => false,
                    'reason' => 'reserved',
                    'message' => 'Este nome de usuário é reservado pelo sistema.',
                ]);
        }
    }

    /**
     * Tier 2: Limite inferior de comprimento (< 2 caracteres).
     */
    public function test_returns_invalid_format_for_too_short_username(): void
    {
        $response = $this->getJson('/api/v1/usernames/check?username=a');

        $response->assertStatus(200)
            ->assertJson([
                'username' => 'a',
                'available' => false,
                'reason' => 'invalid_format',
            ]);
    }

    /**
     * Tier 2: Limite superior de comprimento (> 30 caracteres).
     */
    public function test_returns_invalid_format_for_too_long_username(): void
    {
        $longUsername = str_repeat('a', 31);
        $response = $this->getJson('/api/v1/usernames/check?username=' . $longUsername);

        $response->assertStatus(200)
            ->assertJson([
                'username' => $longUsername,
                'available' => false,
                'reason' => 'invalid_format',
            ]);
    }

    /**
     * Tier 2: Caracteres especiais não permitidos (espaço interno, pontuação inválida, emojis).
     */
    public function test_returns_invalid_format_for_invalid_characters(): void
    {
        $invalidSamples = ['artist!cool', 'user name', 'artist$money', 'art@ist', 'pinho🎨'];

        foreach ($invalidSamples as $sample) {
            $response = $this->getJson('/api/v1/usernames/check?username=' . urlencode($sample));

            $response->assertStatus(200)
                ->assertJson([
                    'available' => false,
                    'reason' => 'invalid_format',
                ]);
        }
    }

    /**
     * Tier 2: Parâmetro username ausente retorna HTTP 400 Bad Request.
     */
    public function test_returns_empty_when_no_username_provided(): void
    {
        $response = $this->getJson('/api/v1/usernames/check');

        $response->assertStatus(400)
            ->assertJson([
                'available' => false,
                'reason' => 'empty',
            ]);
    }

    /**
     * Tier 2: Parâmetro username apenas com espaços em branco retorna HTTP 400 Bad Request.
     */
    public function test_returns_empty_when_only_whitespace_provided(): void
    {
        $response = $this->getJson('/api/v1/usernames/check?username=%20%20%20');

        $response->assertStatus(400)
            ->assertJson([
                'available' => false,
                'reason' => 'empty',
            ]);
    }
}
