<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderButtonsRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'distinct'],
            'items.*.display_mode' => ['required', 'string', 'in:button,circular'],
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
            'items.required' => 'A lista de botões para ordenação é obrigatória.',
            'items.array' => 'A lista de botões deve ser um array.',
            'items.min' => 'A lista de ordenação não pode estar vazia.',
            'items.*.required' => 'Cada item de ordenação é obrigatório.',
            'items.*.array' => 'Cada item de ordenação deve ser um objeto válido.',
            'items.*.id.required' => 'Cada botão deve possuir um identificador.',
            'items.*.id.integer' => 'Cada identificador de botão deve ser um número inteiro.',
            'items.*.id.distinct' => 'A lista não pode conter identificadores duplicados.',
            'items.*.display_mode.required' => 'O modo de exibição de cada botão é obrigatório.',
            'items.*.display_mode.string' => 'O modo de exibição deve ser um texto válido.',
            'items.*.display_mode.in' => 'O modo de exibição deve ser button ou circular.',
        ];
    }
}
