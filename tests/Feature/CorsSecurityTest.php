<?php

namespace Tests\Feature;

use App\Models\Artist;
use App\Models\PageView;
use App\Support\ReservedUsernames;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CorsSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tier 1: Requisição preflight OPTIONS de origem local do frontend Vite (http://localhost:5173).
     */
    public function test_preflight_options_request_from_localhost_vite_returns_cors_headers(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type, Authorization',
        ])->options('/api/v1/auth/login');

        $response->assertStatus(204)
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
    }

    /**
     * Tier 1: Requisição preflight OPTIONS do domínio oficial de produção (https://assin.art).
     */
    public function test_preflight_options_request_from_production_domain_returns_cors_headers(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'https://assin.art',
            'Access-Control-Request-Method' => 'GET',
            'Access-Control-Request-Headers' => 'Accept, Authorization',
        ])->options('/api/v1/auth/me');

        $response->assertStatus(204)
            ->assertHeader('Access-Control-Allow-Origin', 'https://assin.art');
    }

    /**
     * Tier 1 (SEC-09 / API-14): Preflight OPTIONS de https://www.assin.art retorna CORS sem credentials.
     */
    public function test_preflight_options_request_from_www_production_domain_returns_cors_without_credentials(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'https://www.assin.art',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type, Authorization',
        ])->options('/api/v1/auth/login');

        $response->assertStatus(204)
            ->assertHeader('Access-Control-Allow-Origin', 'https://www.assin.art')
            ->assertHeaderMissing('Access-Control-Allow-Credentials');
    }

    /**
     * Tier 1: Requisição regular GET/POST inclui o cabeçalho Access-Control-Allow-Origin.
     */
    public function test_regular_get_request_includes_cors_header(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:5173',
        ])->getJson('/api/v1/usernames/check?username=teste_cors');

        $response->assertStatus(200)
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->assertHeaderMissing('Access-Control-Allow-Credentials');
    }

    /**
     * Tier 1 (SEC-09): CORS restringe paths (sem '*') e mantém supports_credentials = false, lendo CORS_ALLOWED_ORIGINS.
     */
    public function test_cors_config_restricts_paths_disables_credentials_and_supports_env_origins(): void
    {
        $this->assertFalse(config('cors.supports_credentials'));
        $this->assertNotContains('*', config('cors.paths'));
        $this->assertSame(['api/*', 'sanctum/csrf-cookie', 'up', 'r/*'], config('cors.paths'));

        $previousGetenv = getenv('CORS_ALLOWED_ORIGINS');
        $hadEnv = array_key_exists('CORS_ALLOWED_ORIGINS', $_ENV);
        $previousEnv = $_ENV['CORS_ALLOWED_ORIGINS'] ?? null;
        $hadServer = array_key_exists('CORS_ALLOWED_ORIGINS', $_SERVER);
        $previousServer = $_SERVER['CORS_ALLOWED_ORIGINS'] ?? null;

        $override = 'https://preview.assin.art, https://staging.assin.art';
        putenv('CORS_ALLOWED_ORIGINS=' . $override);
        $_ENV['CORS_ALLOWED_ORIGINS'] = $override;
        $_SERVER['CORS_ALLOWED_ORIGINS'] = $override;

        try {
            $loadedConfig = require base_path('config/cors.php');
        } finally {
            if ($previousGetenv === false) {
                putenv('CORS_ALLOWED_ORIGINS');
            } else {
                putenv('CORS_ALLOWED_ORIGINS=' . $previousGetenv);
            }
            if ($hadEnv) {
                $_ENV['CORS_ALLOWED_ORIGINS'] = $previousEnv;
            } else {
                unset($_ENV['CORS_ALLOWED_ORIGINS']);
            }
            if ($hadServer) {
                $_SERVER['CORS_ALLOWED_ORIGINS'] = $previousServer;
            } else {
                unset($_SERVER['CORS_ALLOWED_ORIGINS']);
            }
        }

        $this->assertContains('https://assin.art', $loadedConfig['allowed_origins']);
        $this->assertContains('https://www.assin.art', $loadedConfig['allowed_origins']);
        $this->assertContains('https://preview.assin.art', $loadedConfig['allowed_origins']);
        $this->assertContains('https://staging.assin.art', $loadedConfig['allowed_origins']);
        $this->assertFalse($loadedConfig['supports_credentials']);
    }

    /**
     * Tier 2: Origem não autorizada (ex.: http://site-malicioso.com) não recebe Access-Control-Allow-Origin.
     */
    public function test_unauthorized_origin_does_not_receive_cors_header(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://site-malicioso.com',
            'Access-Control-Request-Method' => 'GET',
        ])->options('/api/v1/usernames/check');

        $response->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    /**
     * Tier 1 (SEC-09): Respostas incluem Security Headers HTTP obrigatórios e HSTS sob HTTPS.
     */
    public function test_responses_include_security_headers_and_hsts_over_https(): void
    {
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->getJson('/');

        $response->assertStatus(200)
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    /**
     * Tier 1 (API-14): trustProxies preserva o IP real do cliente via X-Forwarded-For.
     */
    public function test_trust_proxies_resolves_real_client_ip_from_x_forwarded_for(): void
    {
        Artist::factory()->create([
            'username' => 'proxy_ip_artist',
            'is_published' => true,
        ]);

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.5',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.42',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->postJson('/api/v1/track', [
            'type' => 'pageview',
            'username' => 'proxy_ip_artist',
        ]);

        $response->assertStatus(204);
        $this->assertSame('203.0.113.42', request()->ip());
        $this->assertSame('203.0.113.42', PageView::query()->latest('id')->firstOrFail()->visitor_id);
    }

    /**
     * Tier 2 (API-14): /storage/x não casa com ReservedUsernames::routePattern() nem com a rota pública de artista.
     */
    public function test_storage_path_does_not_match_reserved_usernames_route_pattern(): void
    {
        $pattern = '/^' . ReservedUsernames::routePattern() . '$/';

        $this->assertSame(0, preg_match($pattern, 'storage'));
        $this->assertSame(0, preg_match($pattern, 'storage/x'));

        $response = $this->get('/storage/x');
        $response->assertStatus(404);
        $this->assertNull(Route::currentRouteName());
    }
}
