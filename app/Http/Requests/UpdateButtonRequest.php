<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateButtonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:100'],
            'url' => ['sometimes', 'required', 'url', 'max:2048'],
            'platform' => ['nullable', 'string', 'max:50'],
            'display_mode' => ['nullable', 'string', 'in:button,circular'],
            'icon_variant' => ['nullable', 'string', 'in:default,black,white,none'],
            'show_icon_background' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string', 'in:standard,icon_pill,card_featured'],
            'button_color' => ['nullable', 'string', 'max:20'],
            'text_color' => ['nullable', 'string', 'max:20'],
            'font_family' => ['nullable', 'string', 'max:50'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'order_index' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Mensagens de erro personalizadas em português.
     *
     * @return array<string, string>
     */
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
            'type.in' => 'O tipo de botão deve ser standard, icon_pill ou card_featured.',
        ];
    }
}
