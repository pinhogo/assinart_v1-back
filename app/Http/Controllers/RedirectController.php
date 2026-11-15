<?php

namespace App\Http\Controllers;

use App\Models\Button;
use App\Models\ButtonClick;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    /**
     * Incrementa atomicamente o contador de cliques, registra analítico e redireciona (HTTP 302).
     * Rota: GET /r/{id}
     */
    public function redirect(Request $request, $id): RedirectResponse
    {
        $button = Button::where('id', $id)
            ->where('is_active', true)
            ->first();

        if (!$button) {
            abort(404, 'Link não encontrado ou inativo.');
        }

        $button->increment('clicks_count');

        ButtonClick::create([
            'button_id' => $button->id,
            'visitor_id' => $request->cookie('visitor_id') ?? $request->header('X-Visitor-Id'),
            'clicked_at' => now(),
        ]);

        return redirect()->away($button->url, 302);
    }
}
