<?php

namespace Tests\Feature;

use App\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        RateLimiter::clear('login|127.0.0.1');
        parent::tearDown();
    }

    /**
     * Tier 1: Cadastro de artista com sucesso (201 Created).
     */
    public function test_can_register_new_artist(): void
    {
        $payload = [
            'name' => 'Gabriel Pinho',
            'username' => 'gabriel_pinho',
            'email' => 'gabriel@assin.art',
            'password' => 'secret123',
            'agree_terms' => true,
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'token',
                'artist' => ['id', 'name', 'username', 'email'],
            ])
            ->assertJson([
                'status' => 'success',
                'artist' => [
                    'username' => 'gabriel_pinho',
                    'email' => 'gabriel@assin.art',
                ],
            ]);

        $this->assertDatabaseHas('artists', [
            'username' => 'gabriel_pinho',
            'email' => 'gabriel@assin.art',
        ]);
    }

    /**
     * Tier 2: Cadastro com agreeTerms em camelCase (compatibilidade com frontend).
     */
    public function test_can_register_with_camel_case_agree_terms(): void
    {
        $payload = [
            'name' => 'Artista Camel',
            'username' => 'artista_camel',
            'email' => 'camel@assin.art',
            'password' => 'secret123',
            'agreeTerms' => true,
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('artists', [
            'username' => 'artista_camel',
        ]);
    }

    /**
     * Tier 2: Cadastro com username duplicado é rejeitado (422 Unprocessable).
     */
    public function test_register_duplicate_username_fails(): void
    {
        Artist::factory()->create([
            'username' => 'duplicado',
            'email' => 'original@assin.art',
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Outro Artista',
            'username' => 'duplicado',
            'email' => 'novo@assin.art',
            'password' => 'secret123',
            'agree_terms' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    /**
     * Tier 2: Cadastro com e-mail duplicado é rejeitado (422 Unprocessable).
     */
    public function test_register_duplicate_email_fails(): void
    {
        Artist::factory()->create([
            'username' => 'artista_um',
            'email' => 'mesmo@assin.art',
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Artista Dois',
            'username' => 'artista_dois',
            'email' => 'mesmo@assin.art',
            'password' => 'secret123',
            'agree_terms' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Tier 2: Cadastro sem aceitar os termos é rejeitado (422 Unprocessable).
     */
    public function test_register_without_agree_terms_fails(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Sem Termos',
            'username' => 'sem_termos',
            'email' => 'termos@assin.art',
            'password' => 'secret123',
            'agree_terms' => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['agree_terms']);
    }

    /**
     * Tier 2: Senha com menos de 6 caracteres é rejeitada (422 Unprocessable).
     */
    public function test_register_short_password_fails(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Senha Curta',
            'username' => 'senha_curta',
            'email' => 'curta@assin.art',
            'password' => '123',
            'agree_terms' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Tier 1: Login com credenciais válidas retorna HTTP 200 e token.
     */
    public function test_can_login_with_valid_credentials(): void
    {
        $artist = Artist::factory()->create([
            'email' => 'login_valido@assin.art',
            'password' => Hash::make('senha_segura_123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login_valido@assin.art',
            'password' => 'senha_segura_123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'token',
                'artist' => ['id', 'email', 'username'],
            ])
            ->assertJson([
                'status' => 'success',
                'artist' => [
                    'id' => $artist->id,
                    'email' => 'login_valido@assin.art',
                ],
            ]);
    }

    /**
     * Tier 2: Login com senha inválida retorna HTTP 401 Unauthorized.
     */
    public function test_login_with_invalid_password_returns_401(): void
    {
        Artist::factory()->create([
            'email' => 'artista@assin.art',
            'password' => Hash::make('correta123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'artista@assin.art',
            'password' => 'senha_errada',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Credenciais inválidas. Verifique seu e-mail e senha.',
            ]);
    }

    /**
     * Tier 2: Login com e-mail inexistente retorna HTTP 401 Unauthorized.
     */
    public function test_login_with_nonexistent_email_returns_401(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'naoexiste@assin.art',
            'password' => 'qualquer_senha',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
            ]);
    }

    /**
     * Tier 2: Rate limit de login - 6ª tentativa consecutiva dentro de 1 minuto retorna HTTP 429.
     */
    public function test_login_rate_limiting_triggers_429(): void
    {
        $throttleKey = 'login|127.0.0.1';
        RateLimiter::clear($throttleKey);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'brute@assin.art',
                'password' => 'wrong',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'brute@assin.art',
            'password' => 'wrong',
        ]);

        $response->assertStatus(429)
            ->assertJsonStructure(['status', 'message', 'retry_after']);
    }

    /**
     * Tier 1: Rota autenticada /api/v1/auth/me retorna dados do perfil.
     */
    public function test_authenticated_me_returns_artist_profile(): void
    {
        $artist = Artist::factory()->create([
            'username' => 'artista_me',
            'email' => 'me@assin.art',
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $artist->id,
                    'username' => 'artista_me',
                    'email' => 'me@assin.art',
                ],
            ]);
    }

    /**
     * Tier 2: Rota /api/v1/auth/me sem autenticação retorna HTTP 401 Unauthorized.
     */
    public function test_unauthenticated_me_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    /**
     * Tier 1: Logout revoga token ativo e bloqueia requisições subsequentes.
     */
    public function test_logout_revokes_token(): void
    {
        $artist = Artist::factory()->create();
        $token = $artist->createToken('auth_token')->plainTextToken;

        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Sessão encerrada com sucesso.',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Reseta cache do guard de autenticação do container de teste
        $this->app['auth']->forgetGuards();

        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(401);
    }

    /**
     * Tier 1 & 2: Fluxo completo de recuperação e redefinição de senha.
     */
    public function test_forgot_and_reset_password_flow(): void
    {
        $artist = Artist::factory()->create([
            'email' => 'recupera@assin.art',
            'password' => Hash::make('antiga123'),
        ]);

        // 1. Solicita recuperação
        $forgotResponse = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'recupera@assin.art',
        ]);

        $forgotResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Link de recuperação enviado com sucesso.',
            ]);

        $record = DB::table('password_reset_tokens')->where('email', 'recupera@assin.art')->first();
        $this->assertNotNull($record);

        // 2. Cria token válido para o teste de reset
        $rawToken = 'token_de_teste_seguro_1234567890';
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'recupera@assin.art'],
            ['token' => Hash::make($rawToken), 'created_at' => now()]
        );

        // 3. Redefine a senha
        $resetResponse = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $rawToken,
            'email' => 'recupera@assin.art',
            'password' => 'novasenha123',
            'password_confirmation' => 'novasenha123',
        ]);

        $resetResponse->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Senha redefinida com sucesso.',
            ]);

        // 4. Confirma nova senha via login
        $artist->refresh();
        $this->assertTrue(Hash::check('novasenha123', $artist->password));
    }

    /**
     * Tier 2: Resend de confirmação de e-mail possui cooldown de 60s (retorna 429).
     */
    public function test_email_resend_cooldown_returns_429(): void
    {
        $email = 'cooldown@assin.art';
        $throttleKey = 'resend-email-verification|' . $email;
        RateLimiter::clear($throttleKey);

        $firstResponse = $this->postJson('/api/v1/auth/email/resend', [
            'email' => $email,
        ]);

        $firstResponse->assertStatus(200);

        $secondResponse = $this->postJson('/api/v1/auth/email/resend', [
            'email' => $email,
        ]);

        $secondResponse->assertStatus(429)
            ->assertJsonStructure(['status', 'message', 'retry_after']);
    }

    /**
     * Tier 1: Confirmação de e-mail marca email_verified_at.
     */
    public function test_email_verification_marks_verified(): void
    {
        $artist = Artist::factory()->create([
            'email' => 'unverified@assin.art',
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/auth/email/verify');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $artist->refresh();
        $this->assertNotNull($artist->email_verified_at);
    }
}
