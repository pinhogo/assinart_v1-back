<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
|
| Modular route configuration for Assin.art v1 backend:
| - api_auth.php: Registration, login, logout, me, usernames/check, password reset
| - api_onboarding.php: 3-step onboarding flow (links, background, buttons)
| - api_profile.php: Artist profile show/update and media uploads (avatar, cover)
| - api_buttons.php: Buttons CRUD, card thumbnails, and drag & drop reordering
|
*/

Route::prefix('v1')->group(function () {
    require __DIR__.'/api_auth.php';
    require __DIR__.'/api_onboarding.php';
    require __DIR__.'/api_profile.php';
    require __DIR__.'/api_buttons.php';
});
