<?php

use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Profile & Click Redirect Routes (Milestone 5)
|--------------------------------------------------------------------------
|
| Rotas públicas da Assin.art v1:
| 1. Redirecionamento atômico de cliques com rate limiting (/r/{id})
| 2. Perfil público do artista (/{username}) com proteção contra colisão de rotas
|
*/

// 1. Contador de Cliques & Redirecionamento Atômico (Throttled a 60 req/min por IP)
Route::get('/r/{id}', [RedirectController::class, 'redirect'])
    ->name('button.redirect')
    ->middleware('throttle:60,1')
    ->whereNumber('id');

// 2. Perfil Público do Artista (assin.art/{username})
// Garante que rotas reservadas de sistema (/api, /storage, /r, etc.) não colidam com o slug
Route::get('/{username}', [PublicProfileController::class, 'show'])
    ->name('artist.public')
    ->where('username', '(?!api$|storage$|r$|admin$|login$|register$|dashboard$|editor$|up$)[@a-zA-Z0-9_.-]+');
