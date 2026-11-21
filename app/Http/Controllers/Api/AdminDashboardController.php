<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminValidationInsightsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly AdminValidationInsightsService $insightsService,
    ) {}

    /**
     * Returns consolidated validation metrics, retention, leads, and top artists for admins.
     */
    public function validationDashboard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'in:7,30,90,365'],
        ]);

        $days = (int) ($validated['days'] ?? 30);

        return response()->json([
            'status' => 'success',
            'data' => $this->insightsService->generate($days),
        ]);
    }
}
