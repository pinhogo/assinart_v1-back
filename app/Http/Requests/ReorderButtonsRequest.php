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
            'ordered_ids' => ['required', 'array', 'min:1'],
            'ordered_ids.*' => ['required', 'integer', 'distinct'],
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
            'ordered_ids.required' => 'A lista de identificadores para ordenação é obrigatória.',
            'ordered_ids.array' => 'A lista de identificadores deve ser um array.',
            'ordered_ids.min' => 'A lista de ordenação não pode estar vazia.',
            'ordered_ids.*.required' => 'Cada identificador de botão é obrigatório.',
            'ordered_ids.*.integer' => 'Cada identificador de botão deve ser um número inteiro.',
            'ordered_ids.*.distinct' => 'A lista não pode conter identificadores duplicados.',
        ];
    }
}
