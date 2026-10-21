<?php

use App\Http\Controllers\Api\ButtonController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Perfil e Customização Visual do Artista
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::post('/profile/cover', [ProfileController::class, 'uploadCover']);

    // Gerenciamento e CRUD de Botões
    Route::get('/buttons', [ButtonController::class, 'index']);
    Route::post('/buttons', [ButtonController::class, 'store']);
    Route::put('/buttons/{id}', [ButtonController::class, 'update']);
    Route::delete('/buttons/{id}', [ButtonController::class, 'destroy']);
    Route::post('/buttons/reorder', [ButtonController::class, 'reorder']);
});
