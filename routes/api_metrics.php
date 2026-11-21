<?php

use App\Http\Controllers\Api\FeatureLeadController;
use App\Http\Controllers\Api\MetricsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Metrics & Telemetry Routes (v1)
|--------------------------------------------------------------------------
|
| - POST /api/v1/track                  (telemetria pública de pageview, cliques e funil)
| - POST /api/v1/feature-leads          (captura de leads com autenticação Sanctum opcional)
| - GET  /api/v1/feature-leads/status   (consulta status de inscrição autenticado)
| - GET  /api/v1/metrics/summary        (sumário analítico consolidado autenticado)
|
*/

// Rota pública para coleta anônima de telemetria
Route::post('/track', [MetricsController::class, 'track'])->middleware('throttle:120,1');

// Captura de leads para novas funcionalidades (autenticação Sanctum opcional)
Route::post('/feature-leads', [FeatureLeadController::class, 'store'])->middleware('throttle:20,1');

// Rotas protegidas para consulta pelo artista logado
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/feature-leads/status', [FeatureLeadController::class, 'status']);
    Route::get('/metrics/summary', [MetricsController::class, 'summary']);
});
