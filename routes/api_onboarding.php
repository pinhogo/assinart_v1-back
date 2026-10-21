<?php

use App\Http\Controllers\Api\OnboardingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Onboarding Routes (v1)
|--------------------------------------------------------------------------
|
| 3-Step Onboarding and Profile Publishing endpoints:
| - PUT /api/v1/onboarding/links
| - PUT /api/v1/onboarding/background
| - PUT /api/v1/onboarding/buttons
|
*/

Route::middleware('auth:sanctum')->prefix('onboarding')->group(function () {
    Route::put('/links', [OnboardingController::class, 'saveLinks']);
    Route::put('/background', [OnboardingController::class, 'saveBackground']);
    Route::put('/buttons', [OnboardingController::class, 'saveButtons']);
});
