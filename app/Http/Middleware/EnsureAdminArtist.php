<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminArtist
{
    public function handle(Request $request, Closure $next): Response
    {
        $artist = $request->user();

        if (!$artist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sessão não autenticada.',
            ], 401);
        }

        if (!$artist->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acesso restrito ao administrador.',
            ], 403);
        }

        return $next($request);
    }
}
