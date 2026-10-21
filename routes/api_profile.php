<?php

use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Profile & Media Routes (v1)
|--------------------------------------------------------------------------
| All endpoints require Sanctum authentication (Bearer token).
| Endpoints:
| - GET    /api/v1/profile        -> show
| - PUT    /api/v1/profile        -> update
| - POST   /api/v1/profile/avatar -> uploadAvatar
| - DELETE /api/v1/profile/avatar -> deleteAvatar
| - POST   /api/v1/profile/cover  -> uploadCover
| - DELETE /api/v1/profile/cover  -> deleteCover
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar']);
    Route::post('/profile/cover', [ProfileController::class, 'uploadCover']);
    Route::delete('/profile/cover', [ProfileController::class, 'deleteCover']);
});
