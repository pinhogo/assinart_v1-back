<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingBackgroundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare inputs for validation: normalize camelCase & alternative keys.
     */
    protected function prepareForValidation(): void
    {
        $color = $this->input('background_color')
            ?? $this->input('backgroundColor')
            ?? $this->input('customColor')
            ?? $this->input('custom_color')
            ?? $this->input('customBackgroundColor');

        $id = $this->input('background_id')
            ?? $this->input('backgroundId')
            ?? $this->input('selectedBackgroundId');

        $merge = [];
        if ($color !== null) {
            $merge['background_color'] = trim((string) $color);
        }
        if ($id !== null) {
            $merge['background_id'] = trim((string) $id);
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
            'background_color' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
            'background_id' => [
                'nullable',
                'string',
                'max:50',
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
            'background_color.regex' => 'A cor de fundo deve ser um código hexadecimal válido (ex: #FFFFFF ou #FFF).',
            'background_id.max' => 'O identificador do padrão de fundo não pode exceder 50 caracteres.',
        ];
    }
}
