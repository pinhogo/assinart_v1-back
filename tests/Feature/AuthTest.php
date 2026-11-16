<?php

namespace Tests\Feature;

use App\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        RateLimiter::clear('login|127.0.0.1');
        RateLimiter::clear('login|brute@assin.art|127.0.0.1');
        Cache::flush();
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
                'artist' => ['id', 'name', 'username', 'email', 'is_published', 'publication_status'],
                'data' => [
                    'user' => ['id', 'name', 'username', 'email', 'is_published', 'publication_status'],
                    'token',
                ],
            ])
            ->assertJson([
                'status' => 'success',
                'artist' => [
                    'username' => 'gabriel_pinho',
                    'email' => 'gabriel@assin.art',
                    'is_published' => false,
                    'publication_status' => 'draft',
                ],
                'data' => [
                    'user' => [
                        'username' => 'gabriel_pinho',
                        'is_published' => false,
                        'publication_status' => 'draft',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('artists', [
            'username' => 'gabriel_pinho',
            'email' => 'gabriel@assin.art',
            'is_published' => 0,
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
     * Tier 2: Senha com menos de 8 caracteres é rejeitada (422 Unprocessable).
     */
    public function test_register_short_password_fails(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Senha Curta',
            'username' => 'senha_curta',
            'email' => 'curta@assin.art',
            'password' => '1234567',
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
        RateLimiter::clear('login|brute@assin.art|127.0.0.1');
        RateLimiter::clear('login|127.0.0.1');

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
                    'is_published' => true,
                    'publication_status' => 'published',
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
                'message' => 'Se o e-mail estiver cadastrado, enviaremos um link de recuperação.',
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
        $throttleKey = 'resend-email-verification|127.0.0.1|' . $email;
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
     * Tier 1 (SEC-14.2): Confirmação de e-mail via URL assinada válida marca email_verified_at.
     */
    public function test_email_verification_marks_verified(): void
    {
        $artist = Artist::factory()->create([
            'email' => 'unverified@assin.art',
            'email_verified_at' => null,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $artist->id,
                'hash' => sha1($artist->email),
            ]
        );

        $response = $this->getJson($signedUrl);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $artist->refresh();
        $this->assertNotNull($artist->email_verified_at);
    }

    /**
     * SEC-14.2: Requisição de verificação sem assinatura válida retorna 403 sem verificar o e-mail.
     */
    public function test_email_verification_without_valid_signature_returns_403(): void
    {
        $artist = Artist::factory()->create([
            'email' => 'tampered@assin.art',
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($artist, 'sanctum')
            ->postJson('/api/v1/auth/email/verify', [
                'email' => $artist->email,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Link de verificação inválido ou expirado.',
            ]);

        $artist->refresh();
        $this->assertNull($artist->email_verified_at);
    }

    /**
     * SEC-05.1: Endpoint de registro aplica throttle 10 req/min (429 na 11ª tentativa).
     */
    public function test_register_rate_limiting_triggers_429(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/v1/auth/register', [
                'name' => 'Invalid User',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Invalid User',
        ]);

        $response->assertStatus(429);
    }

    /**
     * SEC-05.2: Endpoint forgot-password aplica throttle 5 req/min.
     */
    public function test_forgot_password_rate_limiting_triggers_429(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/forgot-password', [
                'email' => 'rate@assin.art',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'rate@assin.art',
        ]);

        $response->assertStatus(429);
    }

    /**
     * SEC-05.2: Endpoint reset-password aplica throttle 5 req/min.
     */
    public function test_reset_password_rate_limiting_triggers_429(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/reset-password', [
                'email' => 'rate@assin.art',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'rate@assin.art',
        ]);

        $response->assertStatus(429);
    }

    /**
     * SEC-05.3: Reset de senha rejeita token armazenado em texto puro no banco.
     */
    public function test_reset_password_rejects_plaintext_token_in_database(): void
    {
        $artist = Artist::factory()->create([
            'email' => 'plaintext@assin.art',
            'password' => Hash::make('senha_antiga_123'),
        ]);

        $rawToken = 'token_em_texto_puro_nao_hash';
        DB::table('password_reset_tokens')->insert([
            'email' => $artist->email,
            'token' => $rawToken,
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $rawToken,
            'email' => $artist->email,
            'password' => 'nova_senha_segura',
            'password_confirmation' => 'nova_senha_segura',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
            ]);

        $artist->refresh();
        $this->assertTrue(Hash::check('senha_antiga_123', $artist->password));
    }

    /**
     * SEC-05.4: Redefinir senha revoga todos os tokens Sanctum ativos do artista.
     */
    public function test_reset_password_revokes_all_sanctum_tokens(): void
    {
        $artist = Artist::factory()->create([
            'email' => 'revoga_tokens@assin.art',
            'password' => Hash::make('antiga_1234'),
        ]);

        $tokenOne = $artist->createToken('device_1')->plainTextToken;
        $tokenTwo = $artist->createToken('device_2')->plainTextToken;
        $this->assertDatabaseCount('personal_access_tokens', 2);

        $rawToken = 'token_reset_valido_123';
        DB::table('password_reset_tokens')->insert([
            'email' => $artist->email,
            'token' => Hash::make($rawToken),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'token' => $rawToken,
            'email' => $artist->email,
            'password' => 'nova_senha_1234',
            'password_confirmation' => 'nova_senha_1234',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenOne)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);

        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenTwo)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }

    /**
     * SEC-06.2: Bloqueio de login em 429 para um e-mail não bloqueia outro e-mail no mesmo IP.
     */
    public function test_login_rate_limiting_isolates_different_emails_on_same_ip(): void
    {
        Artist::factory()->create([
            'email' => 'alvo1@assin.art',
            'password' => Hash::make('correta_1234'),
        ]);
        Artist::factory()->create([
            'email' => 'alvo2@assin.art',
            'password' => Hash::make('correta_1234'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'alvo1@assin.art',
                'password' => 'errada',
            ])->assertStatus(401);
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'alvo1@assin.art',
            'password' => 'correta_1234',
        ])->assertStatus(429);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'alvo2@assin.art',
            'password' => 'correta_1234',
        ])->assertStatus(200);
    }

    /**
     * SEC-06.3: Cooldown de reenvio de verificação é isolado por IP + e-mail normalizado.
     */
    public function test_email_resend_cooldown_is_scoped_by_ip_and_email(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->postJson('/api/v1/auth/email/resend', ['email' => 'vitima@assin.art'])
            ->assertStatus(200);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->postJson('/api/v1/auth/email/resend', ['email' => 'VITIMA@assin.art'])
            ->assertStatus(429);

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->postJson('/api/v1/auth/email/resend', ['email' => 'vitima@assin.art'])
            ->assertStatus(200);
    }

    /**
     * SEC-07.1: forgot-password retorna 200 genérico para e-mail inexistente sem gravar token.
     */
    public function test_forgot_password_returns_generic_200_for_nonexistent_email_without_creating_token(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'inexistente@assin.art',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Se o e-mail estiver cadastrado, enviaremos um link de recuperação.',
            ]);

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'inexistente@assin.art',
        ]);
    }

    /**
     * SEC-07.2: Endpoint /api/v1/usernames/check aplica throttle 60 req/min.
     */
    public function test_usernames_check_rate_limiting_triggers_429(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/v1/usernames/check?username=livre_teste')
                ->assertStatus(200);
        }

        $this->getJson('/api/v1/usernames/check?username=livre_teste')
            ->assertStatus(429);
    }

    /**
     * SEC-08.2: RegisterRequest e resetPassword rejeitam senhas com menos de 8 caracteres.
     */
    public function test_register_and_reset_password_reject_passwords_shorter_than_8_characters(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Artista Curto',
            'username' => 'artista_curto',
            'email' => 'curto8@assin.art',
            'password' => '1234567',
            'agree_terms' => true,
        ])->assertStatus(422)->assertJsonValidationErrors(['password']);

        $artist = Artist::factory()->create(['email' => 'reset8@assin.art']);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'qualquer_token',
            'email' => $artist->email,
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ])->assertStatus(422)->assertJsonValidationErrors(['password']);
    }

    /**
     * SEC-08.3: Logout com flag all=true revoga todos os tokens ativos do artista.
     */
    public function test_logout_with_all_flag_revokes_all_tokens(): void
    {
        $artist = Artist::factory()->create();
        $tokenOne = $artist->createToken('device_1')->plainTextToken;
        $tokenTwo = $artist->createToken('device_2')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $tokenOne)
            ->postJson('/api/v1/auth/logout', ['all' => true])
            ->assertStatus(200);

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenTwo)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }

    /**
     * SEC-08.1: Configuração sanctum.expiration possui tempo de expiração definido.
     */
    public function test_sanctum_expiration_is_configured(): void
    {
        $expiration = config('sanctum.expiration');

        $this->assertNotNull($expiration);
        $this->assertGreaterThan(0, (int) $expiration);
    }
}
