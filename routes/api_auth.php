<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\UsernameController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication & Account Routes (v1)
|--------------------------------------------------------------------------
|
| Este arquivo é incluído dentro de Route::prefix('v1') em routes/api.php.
| Todas as rotas abaixo são automaticamente prefixadas com /api/v1.
|
*/

// Disponibilidade de @username (Pública - Landing Page e Cadastro)
Route::get('/usernames/check', [UsernameController::class, 'check']);

// Endpoints de Autenticação e Gestão de Conta (M1 Auth)
Route::prefix('auth')->group(function () {
    // Rotas Públicas
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/email/resend', [AuthController::class, 'resendVerification']);
    Route::match(['get', 'post'], '/email/verify', [AuthController::class, 'verifyEmail']);

    // Rotas Protegidas por Token Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});
