<?php

use App\Http\Controllers\Api\AdminDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (v1)
|--------------------------------------------------------------------------
|
| - GET /api/v1/admin/validation-dashboard (painel consolidado de validação)
|
*/

Route::middleware(['auth:sanctum', 'admin', 'throttle:60,1'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/validation-dashboard', [AdminDashboardController::class, 'validationDashboard']);
    });
