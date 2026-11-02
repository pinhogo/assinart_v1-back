<?php

namespace App\Http\Requests;

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
            'url' => ['sometimes', 'required', 'url', 'max:2048'],
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
            'image_url' => ['nullable', 'string', 'max:2048'],
            'order_index' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título do botão é obrigatório.',
            'title.max' => 'O título não pode ter mais de 100 caracteres.',
            'url.required' => 'A URL de destino é obrigatória.',
            'url.url' => 'A URL de destino deve ser um link válido.',
            'url.max' => 'A URL de destino não pode ter mais de 2048 caracteres.',
            'display_mode.in' => 'O modo de exibição deve ser button ou circular.',
            'icon_variant.in' => 'A variante do ícone deve ser default, black, white ou none.',
            'type.in' => 'O tipo de botão deve ser standard, featured, icon_pill ou card_featured.',
        ];
    }
}
