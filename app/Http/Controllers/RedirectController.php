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
            ->whereHas('artist', fn ($q) => $q->where('is_published', true))
            ->first();

        if (!$button || !preg_match('#^(https?://|mailto:)#i', (string) $button->url)) {
            abort(404, 'Link não encontrado ou inativo.');
        }

        $button->increment('clicks_count');

        $rawVisitorId = $request->cookie('visitor_id') ?? $request->header('X-Visitor-Id');

        ButtonClick::create([
            'button_id' => $button->id,
            'visitor_id' => $rawVisitorId !== null ? mb_substr((string) $rawVisitorId, 0, 64) : null,
            'clicked_at' => now(),
        ]);

        return redirect()->away($button->url, 302);
    }
}
