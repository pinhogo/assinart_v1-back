<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateButtonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:100'],
            'url' => $this->urlRules(),
            'platform' => ['nullable', 'string', 'max:50'],
            'display_mode' => ['nullable', 'string', 'in:button,circular'],
            'icon_variant' => ['nullable', 'string', 'in:default,black,white,none'],
            'show_icon_background' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string', 'in:standard,featured,icon_pill,card_featured'],
            'button_color' => ['nullable', 'string', 'max:20'],
            'text_color' => ['nullable', 'string', 'max:20'],
            'font_family' => ['nullable', 'string', 'max:50'],
            'font_weight' => ['nullable', 'string', 'in:normal,bold'],
            'font_style' => ['nullable', 'string', 'in:normal,italic'],
            'text_border' => ['nullable', 'string', 'in:none,black,white,yellow'],
            'text_align' => ['nullable', 'string', 'in:left,center,right'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título do botão é obrigatório.',
            'title.max' => 'O título não pode ter mais de 100 caracteres.',
            'url.required' => 'A URL de destino é obrigatória.',
            'url.string' => 'Informe um endereço de e-mail válido.',
            'url.url' => 'A URL de destino deve ser um link válido.',
            'url.max' => 'A URL de destino não pode ter mais de 2048 caracteres.',
            'display_mode.in' => 'O modo de exibição deve ser button ou circular.',
            'icon_variant.in' => 'A variante do ícone deve ser default, black, white ou none.',
            'type.in' => 'O tipo de botão deve ser standard, featured, icon_pill ou card_featured.',
        ];
    }

    private function urlRules(): array
    {
        if ($this->resolveTargetPlatform() === 'email') {
            return [
                'sometimes',
                'required',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $cleanEmail = trim((string) preg_replace('/^mailto:/i', '', trim((string) $value)));

                    if (filter_var($cleanEmail, FILTER_VALIDATE_EMAIL) === false) {
                        $fail('Informe um endereço de e-mail válido.');
                    }
                },
            ];
        }

        return ['sometimes', 'required', 'url', 'max:2048'];
    }

    private function resolveTargetPlatform(): ?string
    {
        return $this->input('platform')
            ?? $this->user()?->buttons()->whereKey($this->route('id'))->value('platform');
    }
}
