<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Web routes for Assin.art v1 backend.
| Public profile and redirect tracker are loaded via web_public.php.
|
*/

Route::get('/', function () {
    return response()->json([
        'app' => 'Assin.art Backend API',
        'version' => '1.0.0',
        'status' => 'operational',
    ]);
});

require __DIR__.'/web_public.php';
