<?php

namespace App\Services;

use App\Mail\VerifyEmailMail;
use App\Models\Artist;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmailVerificationService
{
    public const TOKEN_TTL_HOURS = 24;
    public const MIN_TOKEN_TTL_MINUTES = 60;
    public const DEFAULT_TOKEN_TTL_MINUTES = 1440;

    public function resolveTtlMinutes(): int
    {
        $configured = env('EMAIL_VERIFICATION_TTL_MINUTES', config('auth.verification.expire'));

        return max(self::MIN_TOKEN_TTL_MINUTES, (int) ($configured ?: self::DEFAULT_TOKEN_TTL_MINUTES));
    }

    /**
     * Gera token único SHA-256, persiste em email_verification_tokens e envia o e-mail.
     */
    public function sendVerificationEmail(Artist $artist): void
    {
        $email = $this->normalizeEmail($artist->email);
        $plainToken = Str::random(64);
        $tokenHash = hash('sha256', $plainToken);
        $ttlMinutes = $this->resolveTtlMinutes();
        $now = now();

        DB::transaction(function () use ($email, $tokenHash, $ttlMinutes, $now) {
            DB::table('email_verification_tokens')->where('email', $email)->delete();
            DB::table('email_verification_tokens')->insert([
                'email' => $email,
                'token_hash' => $tokenHash,
                'expires_at' => $now->copy()->addMinutes($ttlMinutes),
                'created_at' => $now,
            ]);
        });

        Mail::to($email)->send(new VerifyEmailMail($artist->name, $email, $plainToken, $ttlMinutes));
    }

    /**
     * Orquestra a validação de verificação via URL assinada (legado) ou { email, token }.
     */
    public function verifyFromRequest(Request $request): JsonResponse
    {
        if ($request->hasValidSignature()) {
            $artist = $this->verifyBySignedUrl($request);
            return $artist ? $this->verifiedResponse($artist) : $this->errorResponse(403, 'Link de verificação inválido ou expirado.');
        }

        if (!$request->has('token')) {
            return $this->errorResponse(403, 'Link de verificação inválido ou expirado.');
        }

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'token' => ['required', 'string'],
        ], [
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'token.required' => 'O token de verificação é obrigatório.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $artist = $this->verifyByToken((string) $request->input('email'), (string) $request->input('token'));

        if (!$artist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token de verificação inválido ou expirado.',
                'errors' => ['token' => ['Token de verificação inválido ou expirado.']],
            ], 422);
        }

        return $this->verifiedResponse($artist);
    }

    /**
     * Valida o par { email, token } via hash SHA-256 e invalida o token após o uso.
     */
    public function verifyByToken(string $rawEmail, string $plainToken): ?Artist
    {
        $email = $this->normalizeEmail($rawEmail);
        $record = DB::table('email_verification_tokens')->where('email', $email)->orderByDesc('id')->first();

        if (!$record) {
            return null;
        }

        if (!$record->expires_at || Carbon::parse($record->expires_at)->isPast()) {
            DB::table('email_verification_tokens')->where('email', $email)->delete();
            return null;
        }

        if (!hash_equals((string) $record->token_hash, hash('sha256', $plainToken))) {
            return null;
        }

        $artist = Artist::where('email', $email)->first();
        if (!$artist) {
            DB::table('email_verification_tokens')->where('email', $email)->delete();
            return null;
        }

        return DB::transaction(function () use ($artist, $email) {
            if ($artist->email_verified_at === null) {
                $artist->email_verified_at = now();
                $artist->save();
            }

            DB::table('email_verification_tokens')->where('email', $email)->delete();

            return $artist->fresh() ?? $artist;
        });
    }

    public function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function verifyBySignedUrl(Request $request): ?Artist
    {
        $artist = $request->user('sanctum') ?? $request->user();
        if (!$artist && $request->filled('id')) {
            $artist = Artist::find($request->input('id'));
        }
        if (!$artist && $request->filled('email')) {
            $artist = Artist::where('email', $this->normalizeEmail((string) $request->input('email')))->first();
        }
        if (!$artist || ($request->filled('hash') && !hash_equals(sha1($this->normalizeEmail($artist->email)), (string) $request->input('hash')))) {
            return null;
        }

        $artist->email_verified_at = now();
        $artist->save();

        return $artist;
    }

    private function verifiedResponse(Artist $artist): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'E-mail confirmado com sucesso.',
            'email_verified_at' => $artist->email_verified_at,
        ], 200);
    }

    private function errorResponse(int $status, string $message): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $status);
    }
}
