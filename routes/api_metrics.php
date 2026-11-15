<?php

use App\Http\Controllers\Api\MetricsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Metrics & Telemetry Routes (v1)
|--------------------------------------------------------------------------
|
| - POST /api/v1/track           (telemetria pública de pageview e cliques)
| - GET  /api/v1/metrics/summary (sumário analítico consolidado autenticado)
|
*/

// Rota pública para coleta anônima de telemetria
Route::post('/track', [MetricsController::class, 'track']);

// Rota protegida para consulta de métricas pelo artista logado
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/metrics/summary', [MetricsController::class, 'summary']);
});
