<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingButtonsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize camelCase and alternative parameter keys.
     */
    protected function prepareForValidation(): void
    {
        $shape = $this->input('button_shape')
            ?? $this->input('buttonShape')
            ?? $this->input('selectedShape');

        $color = $this->input('button_color')
            ?? $this->input('buttonColor')
            ?? $this->input('customColor')
            ?? $this->input('customButtonColor')
            ?? $this->input('selectedColorId');

        $merge = [];
        if ($shape !== null) {
            $merge['button_shape'] = strtolower(trim((string) $shape));
        }
        if ($color !== null) {
            $merge['button_color'] = trim((string) $color);
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'button_shape' => [
                'required',
                'string',
                'in:square,rounded,pill',
            ],
            'button_color' => [
                'nullable',
                'string',
                'max:30',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'button_shape.required' => 'O formato dos botões é obrigatório.',
            'button_shape.in' => 'O formato dos botões deve ser square, rounded ou pill.',
            'button_color.max' => 'A cor dos botões não pode exceder 30 caracteres.',
        ];
    }
}
