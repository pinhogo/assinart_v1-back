<?php

use App\Http\Controllers\Api\ButtonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Buttons & Links Routes (v1)
|--------------------------------------------------------------------------
|
| Endpoints autenticados para gestão de botões do artista:
| - GET    /api/v1/buttons
| - POST   /api/v1/buttons
| - POST   /api/v1/buttons/reorder
| - POST   /api/v1/buttons/{id}/image
| - PUT    /api/v1/buttons/{id}
| - PATCH  /api/v1/buttons/{id}
| - DELETE /api/v1/buttons/{id}
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/buttons', [ButtonController::class, 'index']);
    Route::post('/buttons', [ButtonController::class, 'store']);
    Route::post('/buttons/reorder', [ButtonController::class, 'reorder']);
    Route::post('/buttons/{id}/image', [ButtonController::class, 'uploadImage']);
    Route::put('/buttons/{id}', [ButtonController::class, 'update']);
    Route::patch('/buttons/{id}', [ButtonController::class, 'update']);
    Route::delete('/buttons/{id}', [ButtonController::class, 'destroy']);
});
