<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Artist;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

trait ManagesPasswordAndEmail
{
    /**
     * Inicia o fluxo de recuperação de senha gerando um token seguro.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'exists:artists,email'],
        ], [
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.exists' => 'Não encontramos nenhum cadastro com este e-mail.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->input('email')));
        $rawToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($rawToken),
                'created_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Link de recuperação enviado com sucesso.',
        ], 200);
    }

    /**
     * Redefine a senha do artista utilizando o token de recuperação.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'exists:artists,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'token.required' => 'O token de redefinição é obrigatório.',
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.exists' => 'E-mail não encontrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve conter pelo menos 6 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->input('email')));
        $token = $request->input('token');

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token de recuperação inválido ou expirado.',
            ], 400);
        }

        $isValid = Hash::check($token, $record->token)
            || hash_equals((string) $record->token, (string) $token)
            || hash_equals((string) $record->token, hash('sha256', (string) $token));

        if (!$isValid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token de recuperação inválido ou expirado.',
            ], 400);
        }

        if ($record->created_at && Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token de recuperação inválido ou expirado.',
            ], 400);
        }

        $artist = Artist::where('email', $email)->first();
        if ($artist) {
            $artist->password = Hash::make($request->input('password'));
            $artist->save();
        }

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Senha redefinida com sucesso.',
        ], 200);
    }

    /**
     * Reenvia e-mail de confirmação com throttling estrito de 60 segundos.
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $identifier = $request->user()?->id 
            ?? $request->input('email') 
            ?? $request->ip() 
            ?? '127.0.0.1';

        $throttleKey = 'resend-email-verification|' . $identifier;

        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'status' => 'error',
                'message' => "Aguarde {$seconds} segundos antes de solicitar um novo e-mail.",
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($throttleKey, 60);

        return response()->json([
            'status' => 'success',
            'message' => 'E-mail de confirmação reenviado.',
        ], 200);
    }

    /**
     * Confirma o e-mail do artista marcando email_verified_at.
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $artist = $request->user();

        if (!$artist && $request->filled('email')) {
            $artist = Artist::where('email', strtolower(trim($request->input('email'))))->first();
        }

        if ($artist) {
            $artist->email_verified_at = now();
            $artist->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'E-mail confirmado com sucesso.',
            'email_verified_at' => $artist?->email_verified_at,
        ], 200);
    }
}
