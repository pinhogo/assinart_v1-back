<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\InteractsWithMediaStorage;
use App\Http\Controllers\Api\Concerns\ManagesPasswordAndEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Artist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    use ManagesPasswordAndEmail, InteractsWithMediaStorage;

    public function register(RegisterRequest $request): JsonResponse
    {
        $cleanUsername = ltrim(strtolower(trim($request->input('username'))), '@');

        $artist = Artist::create([
            'name' => trim($request->input('name')),
            'username' => $cleanUsername,
            'email' => strtolower(trim($request->input('email'))),
            'password' => Hash::make($request->input('password')),
            'background_color' => '#FFFFFF',
            'button_shape' => 'square',
            'avatar_shape' => 'circle',
            'font_family' => 'Space Grotesk',
            'font_color' => '#000000',
            'border_style' => 'solid-3px',
            'icon_variant' => 'default',
        ]);

        $token = $artist->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Conta criada com sucesso.',
            'token' => $token,
            'artist' => $artist,
            'data' => [
                'user' => ['id' => $artist->id, 'name' => $artist->name, 'username' => $artist->username, 'email' => $artist->email],
                'token' => $token,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $ip = $request->ip() ?? '127.0.0.1';
        $throttleKey = 'login|' . $ip;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'status' => 'error',
                'message' => "Muitas tentativas de login. Tente novamente em {$seconds} segundos.",
                'retry_after' => $seconds,
            ], 429);
        }

        $artist = Artist::where('email', strtolower(trim($request->input('email'))))->first();

        if (!$artist || !Hash::check($request->input('password'), $artist->password)) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciais inválidas. Verifique seu e-mail e senha.',
            ], 401);
        }

        RateLimiter::clear($throttleKey);
        $token = $artist->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login realizado com sucesso.',
            'token' => $token,
            'artist' => $artist,
            'data' => [
                'user' => ['id' => $artist->id, 'name' => $artist->name, 'username' => $artist->username, 'email' => $artist->email],
                'token' => $token,
            ],
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
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
            'data' => $request->user(),
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
