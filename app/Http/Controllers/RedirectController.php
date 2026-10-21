<?php

namespace App\Http\Controllers;

use App\Models\Button;
use Illuminate\Http\RedirectResponse;

class RedirectController extends Controller
{
    /**
     * Incrementa atomicamente o contador de cliques e redireciona (302).
     * Rota: GET /r/{id}
     */
    public function redirect(int $id): RedirectResponse
    {
        $button = Button::where('id', $id)
            ->where('is_active', true)
            ->firstOrFail();

        // Incremento atômico de performance leve
        $button->increment('clicks_count');

        // Redirecionamento padrão HTTP 302 (Found)
        return redirect()->away($button->url, 302);
    }
}
