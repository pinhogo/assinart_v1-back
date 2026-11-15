<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
|
| Modular route configuration for Assin.art v1 backend:
| - api_auth.php: Registration, login, logout, me, usernames/check, password reset, account deletion
| - api_onboarding.php: 3-step onboarding flow (links, background, buttons)
| - api_profile.php: Artist profile show/update (PUT/PATCH) and media uploads (avatar, cover, background)
| - api_buttons.php: Buttons CRUD (PATCH), card thumbnails, bulk clear, and drag & drop reordering
| - api_metrics.php: Anonymous telemetry tracking (/track) and analytical metrics summary
|
*/

Route::prefix('v1')->group(function () {
    require __DIR__.'/api_auth.php';
    require __DIR__.'/api_onboarding.php';
    require __DIR__.'/api_profile.php';
    require __DIR__.'/api_buttons.php';
    require __DIR__.'/api_metrics.php';
});
