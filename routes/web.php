<?php

use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'app' => 'Assin.art Backend API',
        'version' => '1.0.0',
        'status' => 'operational',
    ]);
});

// 1. Contador de Cliques & Redirecionamento
Route::get('/r/{id}', [RedirectController::class, 'redirect'])->name('button.redirect');

// 2. Perfil Público do Artista (assin.art/{username})
Route::get('/{username}', [PublicProfileController::class, 'show'])->name('artist.public');
