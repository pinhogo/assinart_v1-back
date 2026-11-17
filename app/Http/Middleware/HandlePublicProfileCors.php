<?php

namespace App\Http\Middleware;

use App\Support\ReservedUsernames;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;

/**
 * Estende o middleware de CORS padrão do Laravel para incluir as rotas públicas
 * de perfil na raiz (/{username}), mantendo a restrição de paths sem curinga `*`.
 */
class HandlePublicProfileCors extends HandleCors
{
    protected function hasMatchingPath(Request $request): bool
    {
        if (parent::hasMatchingPath($request)) {
            return true;
        }

        if (!in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return false;
        }

        $path = trim($request->decodedPath(), '/');
        if ($path === '' || str_contains($path, '/')) {
            return false;
        }

        return preg_match('/^' . ReservedUsernames::routePattern() . '$/i', $path) === 1;
    }
}
