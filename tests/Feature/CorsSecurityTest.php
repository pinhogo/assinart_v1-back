<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
     * Tier 1: Requisição regular GET/POST inclui o cabeçalho Access-Control-Allow-Origin.
     */
    public function test_regular_get_request_includes_cors_header(): void
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:5173',
        ])->getJson('/api/v1/usernames/check?username=teste_cors');

        $response->assertStatus(200)
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
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
}
