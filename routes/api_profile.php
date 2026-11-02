<?php

use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Profile & Media Routes (v1)
|--------------------------------------------------------------------------
|
| Endpoints autenticados com suporte a GET, PUT, PATCH e uploads simétricos:
| - GET    /api/v1/profile
| - PUT    /api/v1/profile
| - PATCH  /api/v1/profile
| - POST   /api/v1/profile/avatar
| - DELETE /api/v1/profile/avatar
| - POST   /api/v1/profile/cover
| - DELETE /api/v1/profile/cover
| - POST   /api/v1/profile/background
| - DELETE /api/v1/profile/background
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar']);
    Route::post('/profile/cover', [ProfileController::class, 'uploadCover']);
    Route::delete('/profile/cover', [ProfileController::class, 'deleteCover']);
    Route::post('/profile/background', [ProfileController::class, 'uploadBackground']);
    Route::delete('/profile/background', [ProfileController::class, 'deleteBackground']);
    Route::post('/profile/wallpaper', [ProfileController::class, 'uploadBackground']);
    Route::delete('/profile/wallpaper', [ProfileController::class, 'deleteBackground']);
});
