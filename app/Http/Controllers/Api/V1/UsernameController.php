<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsernameController extends Controller
{
    /**
     * Lista de palavras reservadas do sistema que não podem ser usadas como @username.
     * Inclui rotas do frontend SPA, termos de sistema e endpoints internos.
     */
    protected const RESERVED_WORDS = [
        'admin',
        'api',
        'app',
        'assinart',
        'auth',
        'cadastro',
        'confirmacao-email',
        'contato',
        'dashboard',
        'editor',
        'email',
        'entrar',
        'esqueci-senha',
        'home',
        'login',
        'logout',
        'null',
        'perfil',
        'playground',
        'privacidade',
        'r',
        'register',
        'root',
        'sac',
        'settings',
        'signin',
        'signup',
        'status',
        'termos',
        'undefined',
    ];

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
        $clean = strtolower(trim($raw));
        $clean = ltrim($clean, '@');

        // 2. Validação de tamanho e caracteres (letras, números, '.', '_' e '-')
        if (!preg_match('/^[a-z0-9_.-]{2,30}$/', $clean)) {
            return response()->json([
                'username' => $clean,
                'available' => false,
                'reason' => 'invalid_format',
                'message' => 'O @ deve ter entre 2 e 30 caracteres (letras, números, pontos, traços ou sublinhados).',
            ], 200);
        }

        // 3. Verificação de colisão com palavras reservadas
        if (in_array($clean, self::RESERVED_WORDS, true)) {
            return response()->json([
                'username' => $clean,
                'available' => false,
                'reason' => 'reserved',
                'message' => 'Este nome de usuário é reservado pelo sistema.',
            ], 200);
        }

        // 4. Verificação de colisão no banco de dados
        $exists = Artist::where('username', $clean)->exists();

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
