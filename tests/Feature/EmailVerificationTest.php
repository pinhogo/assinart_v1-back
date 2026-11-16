<?php

namespace Tests\Feature;

use App\Mail\VerifyEmailMail;
use App\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Cache::flush();
        RateLimiter::clear('resend-email-verification|127.0.0.1|reenvio@assin.art');
        RateLimiter::clear('resend-email-verification|127.0.0.1|verificado@assin.art');
        RateLimiter::clear('resend-email-verification|127.0.0.1|inexistente@assin.art');
        parent::tearDown();
    }

    public function test_register_sends_verification_email_and_stores_sha256_token(): void
    {
        Mail::fake();
        Config::set('app.frontend_url', 'https://assin.art/');

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Clara Artes',
            'username' => 'clara_artes',
            'email' => 'Clara@Assin.Art',
            'password' => 'segredo123',
            'agree_terms' => true,
        ]);

        $response->assertStatus(201);

        $capturedToken = null;
        Mail::assertSent(VerifyEmailMail::class, function (VerifyEmailMail $mail) use (&$capturedToken) {
            $capturedToken = $mail->plainToken;
            $expectedUrl = 'https://assin.art/confirmacao-email?email='
                . urlencode('clara@assin.art')
                . '&token=' . urlencode($mail->plainToken);

            $html = html_entity_decode($mail->render(), ENT_QUOTES, 'UTF-8');

            return $mail->hasTo('clara@assin.art')
                && $mail->verificationUrl === $expectedUrl
                && str_contains($html, $expectedUrl);
        });

        $this->assertNotNull($capturedToken);
        $this->assertDatabaseHas('email_verification_tokens', [
            'email' => 'clara@assin.art',
            'token_hash' => hash('sha256', $capturedToken),
        ]);
        $this->assertDatabaseMissing('email_verification_tokens', [
            'email' => 'clara@assin.art',
            'token_hash' => $capturedToken,
        ]);
    }

    public function test_onboarding_completion_keeps_email_verified_at_null(): void
    {
        Mail::fake();

        $register = $this->postJson('/api/v1/auth/register', [
            'name' => 'Pedro Onboarding',
            'username' => 'pedro_onboarding',
            'email' => 'pedro_onb@assin.art',
            'password' => 'segredo123',
            'agree_terms' => true,
        ])->assertStatus(201);

        $token = $register->json('token');

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/onboarding/links', [
                'bio' => 'Bio inicial',
                'links' => [
                    ['platform' => 'instagram', 'url' => 'pedro_arte'],
                ],
            ])->assertStatus(200);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/onboarding/background', [
                'background_id' => 'lavender',
            ])->assertStatus(200);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/onboarding/buttons', [
                'button_shape' => 'rounded',
            ])->assertStatus(200);

        $artist = Artist::where('email', 'pedro_onb@assin.art')->firstOrFail();
        $this->assertNull($artist->email_verified_at);
    }

    public function test_verify_email_with_valid_token_marks_verified_and_is_single_use(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Marina Token',
            'username' => 'marina_token',
            'email' => 'marina@assin.art',
            'password' => 'segredo123',
            'agree_terms' => true,
        ])->assertStatus(201);

        $plainToken = null;
        Mail::assertSent(VerifyEmailMail::class, function (VerifyEmailMail $mail) use (&$plainToken) {
            $plainToken = $mail->plainToken;
            return true;
        });

        $verifyResponse = $this->postJson('/api/v1/auth/email/verify', [
            'email' => 'marina@assin.art',
            'token' => $plainToken,
        ]);

        $verifyResponse->assertStatus(200)
            ->assertJson(['status' => 'success']);
        $this->assertNotNull($verifyResponse->json('email_verified_at'));

        $artist = Artist::where('email', 'marina@assin.art')->firstOrFail();
        $this->assertNotNull($artist->email_verified_at);
        $this->assertDatabaseMissing('email_verification_tokens', ['email' => 'marina@assin.art']);

        $reuseResponse = $this->postJson('/api/v1/auth/email/verify', [
            'email' => 'marina@assin.art',
            'token' => $plainToken,
        ]);

        $reuseResponse->assertStatus(422)
            ->assertJson(['status' => 'error']);
    }

    public function test_verify_email_with_invalid_or_nonexistent_email_token_returns_422(): void
    {
        $artist = Artist::factory()->create([
            'email' => 'invalido@assin.art',
            'email_verified_at' => null,
        ]);

        DB::table('email_verification_tokens')->insert([
            'email' => $artist->email,
            'token_hash' => hash('sha256', 'token-correto'),
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
        ]);

        $this->postJson('/api/v1/auth/email/verify', [
            'email' => $artist->email,
            'token' => 'token-errado',
        ])->assertStatus(422)->assertJson(['status' => 'error']);

        $this->postJson('/api/v1/auth/email/verify', [
            'email' => 'desconhecido@assin.art',
            'token' => 'token-correto',
        ])->assertStatus(422)->assertJson(['status' => 'error']);

        $this->assertNull($artist->fresh()->email_verified_at);
    }

    public function test_verify_email_with_expired_token_returns_422(): void
    {
        $artist = Artist::factory()->create([
            'email' => 'expirado@assin.art',
            'email_verified_at' => null,
        ]);

        $plainToken = 'token-expirado-123';
        DB::table('email_verification_tokens')->insert([
            'email' => $artist->email,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->subMinute(),
            'created_at' => now()->subHours(25),
        ]);

        $this->postJson('/api/v1/auth/email/verify', [
            'email' => $artist->email,
            'token' => $plainToken,
        ])->assertStatus(422)->assertJson(['status' => 'error']);

        $this->assertNull($artist->fresh()->email_verified_at);
        $this->assertDatabaseMissing('email_verification_tokens', ['email' => $artist->email]);
    }

    public function test_resend_verification_sends_mail_for_unverified_and_throttles_with_429(): void
    {
        Mail::fake();

        $artist = Artist::factory()->create([
            'email' => 'reenvio@assin.art',
            'email_verified_at' => null,
        ]);

        $this->postJson('/api/v1/auth/email/resend', [
            'email' => $artist->email,
        ])->assertStatus(200)->assertJson(['status' => 'success']);

        Mail::assertSent(VerifyEmailMail::class, 1);

        $this->postJson('/api/v1/auth/email/resend', [
            'email' => $artist->email,
        ])->assertStatus(429)->assertJsonStructure(['status', 'message', 'retry_after']);

        Mail::assertSent(VerifyEmailMail::class, 1);
    }

    public function test_resend_verification_returns_generic_200_without_sending_for_verified_or_nonexistent(): void
    {
        Mail::fake();

        Artist::factory()->create([
            'email' => 'verificado@assin.art',
            'email_verified_at' => now(),
        ]);

        $verifiedResp = $this->postJson('/api/v1/auth/email/resend', [
            'email' => 'verificado@assin.art',
        ]);

        $missingResp = $this->postJson('/api/v1/auth/email/resend', [
            'email' => 'inexistente@assin.art',
        ]);

        $verifiedResp->assertStatus(200);
        $missingResp->assertStatus(200);
        $this->assertSame($verifiedResp->json('message'), $missingResp->json('message'));

        Mail::assertNothingSent();
    }

    public function test_register_login_and_me_return_email_verified_at(): void
    {
        Mail::fake();

        $registerResp = $this->postJson('/api/v1/auth/register', [
            'name' => 'Lucas Status',
            'username' => 'lucas_status',
            'email' => 'lucas@assin.art',
            'password' => 'segredo123',
            'agree_terms' => true,
        ])->assertStatus(201);

        $this->assertArrayHasKey('email_verified_at', $registerResp->json('artist'));
        $this->assertArrayHasKey('email_verified_at', $registerResp->json('data.user'));
        $this->assertNull($registerResp->json('artist.email_verified_at'));
        $this->assertNull($registerResp->json('data.user.email_verified_at'));

        $token = $registerResp->json('token');

        $meUnverified = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200);

        $this->assertArrayHasKey('email_verified_at', $meUnverified->json('data'));
        $this->assertNull($meUnverified->json('data.email_verified_at'));

        $plainToken = null;
        Mail::assertSent(VerifyEmailMail::class, function (VerifyEmailMail $mail) use (&$plainToken) {
            $plainToken = $mail->plainToken;
            return true;
        });

        $this->postJson('/api/v1/auth/email/verify', [
            'email' => 'lucas@assin.art',
            'token' => $plainToken,
        ])->assertStatus(200);

        $loginResp = $this->postJson('/api/v1/auth/login', [
            'email' => 'lucas@assin.art',
            'password' => 'segredo123',
        ])->assertStatus(200);

        $this->assertNotNull($loginResp->json('artist.email_verified_at'));
        $this->assertNotNull($loginResp->json('data.user.email_verified_at'));

        $this->app['auth']->forgetGuards();

        $meVerified = $this->withHeader('Authorization', 'Bearer ' . $loginResp->json('token'))
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200);

        $this->assertNotNull($meVerified->json('data.email_verified_at'));
    }
}
