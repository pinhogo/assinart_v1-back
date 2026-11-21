<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\InteractsWithMediaStorage;
use App\Http\Controllers\Api\Concerns\ManagesPasswordAndEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Artist;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    use ManagesPasswordAndEmail, InteractsWithMediaStorage;

    public function register(RegisterRequest $request, EmailVerificationService $verificationService): JsonResponse
    {
        $cleanUsername = ltrim(strtolower(trim($request->input('username'))), '@');

        $artist = Artist::create([
            'name' => trim($request->input('name')),
            'username' => $cleanUsername,
            'email' => strtolower(trim($request->input('email'))),
            'password' => Hash::make($request->input('password')),
            'background_color' => '#FFFFFF',
            'button_shape' => 'square',
            'button_shadow_color' => '#000000',
            'circular_shadow_enabled' => true,
            'avatar_shape' => 'circle',
            'font_family' => 'Space Grotesk',
            'font_color' => '#000000',
            'border_style' => 'solid-3px',
            'icon_variant' => 'default',
            'is_published' => false,
        ]);

        $artist = $artist->fresh() ?? $artist;
        $verificationService->sendVerificationEmail($artist);

        return $this->respondWithArtistSession($artist, 'Conta criada com sucesso.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $emailKey = strtolower(trim((string) $request->input('email', '')));
        $ip = $request->ip() ?? '127.0.0.1';
        $emailIpKey = 'login|' . $emailKey . '|' . $ip;
        $ipOnlyKey = 'login|' . $ip;

        if (RateLimiter::tooManyAttempts($emailIpKey, 5) || RateLimiter::tooManyAttempts($ipOnlyKey, 20)) {
            $seconds = max(RateLimiter::availableIn($emailIpKey), RateLimiter::availableIn($ipOnlyKey));
            return response()->json([
                'status' => 'error',
                'message' => "Muitas tentativas de login. Tente novamente em {$seconds} segundos.",
                'retry_after' => $seconds,
            ], 429);
        }

        $artist = Artist::where('email', $emailKey)->first();

        if (!$artist || !Hash::check($request->input('password'), $artist->password)) {
            RateLimiter::hit($emailIpKey, 60);
            RateLimiter::hit($ipOnlyKey, 60);
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciais inválidas. Verifique seu e-mail e senha.',
            ], 401);
        }

        RateLimiter::clear($emailIpKey);

        return $this->respondWithArtistSession($artist, 'Login realizado com sucesso.', 200);
    }

    private function respondWithArtistSession(Artist $artist, string $message, int $status): JsonResponse
    {
        $token = $artist->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'token' => $token,
            'artist' => $artist,
            'data' => [
                'user' => $artist->only(['id', 'name', 'username', 'email', 'email_verified_at', 'is_published', 'publication_status', 'is_admin']),
                'token' => $token,
            ],
        ], $status);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            if ($request->boolean('all') || $request->boolean('revoke_all')) {
                $user->tokens()->delete();
            } elseif (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sessão encerrada com sucesso.',
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()?->fresh() ?? $request->user(),
        ], 200);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate(['password' => ['required', 'string']], [
            'password.required' => 'A confirmação de senha é obrigatória para excluir a conta.',
        ]);

        $artist = $request->user();

        if (!Hash::check($request->input('password'), $artist->password)) {
            return response()->json([
                'message' => 'Senha incorreta.',
                'errors' => ['password' => ['A senha informada está incorreta.']],
            ], 422);
        }

        DB::transaction(function () use ($artist) {
            foreach ($artist->buttons as $button) {
                if ($button->image_url) {
                    $this->deleteFileFromStorage($button->image_url);
                }
            }

            if ($artist->avatar_url) $this->deleteFileFromStorage($artist->avatar_url);
            if ($artist->cover_url) $this->deleteFileFromStorage($artist->cover_url);
            if ($artist->wallpaper_url) $this->deleteFileFromStorage($artist->wallpaper_url);

            $artist->buttons()->delete();
            $artist->tokens()->delete();
            $artist->delete();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Conta excluída permanentemente.',
        ], 200);
    }
}
