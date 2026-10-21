<?php

namespace App\Http\Controllers;

use App\Models\Button;
use Illuminate\Http\RedirectResponse;

class RedirectController extends Controller
{
    /**
     * Incrementa atomicamente o contador de cliques e redireciona (HTTP 302).
     * Rota: GET /r/{id}
     *
     * @param int|string $id
     * @return RedirectResponse
     */
    public function redirect($id): RedirectResponse
    {
        // 1. Escopo estrito ao botão ativo
        $button = Button::where('id', $id)
            ->where('is_active', true)
            ->first();

        // 2. Se inexistente ou inativo, aborta com 404
        if (! $button) {
            abort(404, 'Link não encontrado ou inativo.');
        }

        // 3. Incremento atômico leve sem locking de tabela
        $button->increment('clicks_count');

        // 4. Redirecionamento imediato HTTP 302 (Found) para a URL de destino
        return redirect()->away($button->url, 302);
    }
}
