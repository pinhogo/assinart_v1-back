<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Rules\ArtistUsername;
use App\Support\ReservedUsernames;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsernameController extends Controller
{
    /**
     * Verifica em tempo real a disponibilidade e validade de um @username.
     * Rota: GET /api/v1/usernames/check?username=...
     */
    public function check(Request $request): JsonResponse
    {
        $raw = $request->query('username', '');

        if (!is_string($raw) || trim($raw) === '') {
            return response()->json([
                'username' => '',
                'available' => false,
                'reason' => 'empty',
                'message' => 'Informe um nome de usuário para verificação.',
            ], 400);
        }

        // 1. Sanitização: remove espaços e o '@' inicial, convertendo para minúsculas
        $clean = ArtistUsername::normalize($raw);

        // 2. Validação compartilhada de tamanho, caracteres e posição de pontos
        $formatError = ArtistUsername::formatError($clean);
        if ($formatError !== null) {
            return response()->json([
                'username' => $clean,
                'available' => false,
                'reason' => 'invalid_format',
                'message' => $formatError,
            ], 200);
        }

        // 3. Verificação de colisão com palavras reservadas
        if (ReservedUsernames::contains($clean)) {
            return response()->json([
                'username' => $clean,
                'available' => false,
                'reason' => 'reserved',
                'message' => 'Este nome de usuário é reservado pelo sistema.',
            ], 200);
        }

        // 4. Verificação de colisão no banco de dados (insensível a maiúsculas)
        $exists = Artist::whereRaw('LOWER(username) = ?', [$clean])->exists();

        if ($exists) {
            return response()->json([
                'username' => $clean,
                'available' => false,
                'reason' => 'taken',
                'message' => 'Esse @ já está em uso.',
            ], 200);
        }

        // 5. Nome disponível com sucesso
        return response()->json([
            'username' => $clean,
            'available' => true,
            'reason' => null,
            'message' => 'Disponível',
        ], 200);
    }
}
