<?php

namespace App\Services;

use App\Models\Button;
use Illuminate\Support\Collection;

class ButtonOrderService
{
    public const MAX_BUTTONS_PER_ARTIST = 30;

    public const MAX_CIRCULAR_BUTTONS = 5;

    /**
     * Verifica se todos os identificadores informados pertencem à coleção de botões do artista.
     *
     * @param  Collection<int, Button>  $artistButtons
     * @param  array<int, array{id: int, display_mode: string}>  $items
     */
    public function allItemsBelongToArtist(Collection $artistButtons, array $items): bool
    {
        $ownedIds = $artistButtons->pluck('id')->flip();

        foreach ($items as $item) {
            if (!$ownedIds->has((int) $item['id'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gera o plano normalizado de atualização agrupando `circular` (0..K-1) e `button` (K..M-1).
     *
     * @param  Collection<int, Button>  $artistButtons
     * @param  array<int, array{id: int, display_mode: string}>  $items
     * @return array<int, array{button: Button, attributes: array<string, mixed>}>
     */
    public function buildNormalizedUpdates(Collection $artistButtons, array $items = []): array
    {
        $buttonsById = $artistButtons->keyBy('id');
        $circularEntries = [];
        $buttonEntries = [];
        $seenIds = [];

        foreach ($items as $item) {
            $id = (int) $item['id'];
            /** @var Button|null $button */
            $button = $buttonsById->get($id);

            if ($button === null) {
                continue;
            }

            $targetMode = $this->normalizeDisplayMode((string) $item['display_mode']);
            $seenIds[$id] = true;

            $entry = [
                'button' => $button,
                'display_mode' => $targetMode,
            ];

            if ($targetMode === 'circular') {
                $circularEntries[] = $entry;
            } else {
                $buttonEntries[] = $entry;
            }
        }

        // Botões remanescentes não enviados no payload são preservados ao final de sua seção.
        foreach ($artistButtons as $button) {
            if (isset($seenIds[$button->id])) {
                continue;
            }

            $existingMode = $this->normalizeDisplayMode((string) $button->display_mode);
            $entry = [
                'button' => $button,
                'display_mode' => $existingMode,
            ];

            if ($existingMode === 'circular') {
                $circularEntries[] = $entry;
            } else {
                $buttonEntries[] = $entry;
            }
        }

        return $this->assignSequentialIndexes(array_merge($circularEntries, $buttonEntries));
    }

    /**
     * Normaliza a ordenação de todos os botões existentes de um artista sem alteração de seção.
     *
     * @param  Collection<int, Button>  $artistButtons
     * @return array<int, array{button: Button, attributes: array<string, mixed>}>
     */
    public function normalizeArtistButtons(Collection $artistButtons): array
    {
        return $this->buildNormalizedUpdates($artistButtons, []);
    }

    /**
     * Persiste apenas os botões alterados de um plano normalizado.
     *
     * @param  array<int, array{button: Button, attributes: array<string, mixed>}>  $plan
     */
    public function applyNormalizedPlan(array $plan): void
    {
        foreach ($plan as $entry) {
            /** @var Button $button */
            $button = $entry['button'];
            $button->fill($entry['attributes']);

            if ($button->isDirty()) {
                $button->save();
            }
        }
    }

    /**
     * Ordena payloads de criação em lote colocando `circular` (0..K-1) antes de `button` (K..M-1),
     * preservando a ordem relativa dentro de cada seção.
     *
     * @param  array<int, array<string, mixed>>  $parsedButtons
     * @return array<int, array<string, mixed>>
     */
    public function sortAndIndexParsedButtons(array $parsedButtons): array
    {
        $circulars = [];
        $buttons = [];

        foreach ($parsedButtons as $buttonData) {
            if (($buttonData['display_mode'] ?? 'button') === 'circular') {
                $circulars[] = $buttonData;
            } else {
                $buttons[] = $buttonData;
            }
        }

        $ordered = array_merge($circulars, $buttons);

        foreach ($ordered as $index => $buttonData) {
            $ordered[$index]['order_index'] = $index;
        }

        return $ordered;
    }

    /**
     * Conta quantos itens ficarão no modo `circular` após a normalização.
     *
     * @param  array<int, array{button: Button, attributes: array<string, mixed>}>  $plan
     */
    public function countCircularInPlan(array $plan): int
    {
        $count = 0;

        foreach ($plan as $entry) {
            if (($entry['attributes']['display_mode'] ?? null) === 'circular') {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Normaliza os atributos visuais obrigatórios de acordo com o display_mode alvo.
     *
     * @return array{display_mode: string, icon_variant: string, show_icon_background: bool}
     */
    public function normalizeDisplayAttributes(
        string $displayMode,
        ?string $iconVariant,
        bool $showIconBackground,
    ): array {
        $normalizedMode = $this->normalizeDisplayMode($displayMode);

        if ($normalizedMode === 'circular') {
            $resolvedIconVariant = ($iconVariant === null || $iconVariant === '' || $iconVariant === 'none')
                ? 'default'
                : $iconVariant;

            return [
                'display_mode' => 'circular',
                'icon_variant' => $resolvedIconVariant,
                'show_icon_background' => true,
            ];
        }

        return [
            'display_mode' => 'button',
            'icon_variant' => ($iconVariant === null || $iconVariant === '') ? 'default' : $iconVariant,
            'show_icon_background' => $showIconBackground,
        ];
    }

    /**
     * @param  array<int, array{button: Button, display_mode: string}>  $orderedEntries
     * @return array<int, array{button: Button, attributes: array<string, mixed>}>
     */
    private function assignSequentialIndexes(array $orderedEntries): array
    {
        $plan = [];

        foreach ($orderedEntries as $index => $entry) {
            /** @var Button $button */
            $button = $entry['button'];
            $attributes = $this->normalizeDisplayAttributes(
                $entry['display_mode'],
                $button->icon_variant,
                (bool) $button->show_icon_background,
            );
            $attributes['order_index'] = $index;

            $plan[] = [
                'button' => $button,
                'attributes' => $attributes,
            ];
        }

        return $plan;
    }

    private function normalizeDisplayMode(string $displayMode): string
    {
        return $displayMode === 'circular' ? 'circular' : 'button';
    }
}
