<?php

namespace App\Http\Requests;

use App\Rules\HexColor;
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

        $shadow = $this->input('button_shadow')
            ?? $this->input('buttonShadow')
            ?? $this->input('selectedShadow');

        // Retrocompatibilidade com has_shadow booleano
        if ($shadow === null && $this->has('has_shadow')) {
            $shadow = $this->boolean('has_shadow') ? 'hard_small' : 'none';
        }

        $merge = [];
        if ($shape !== null) {
            $merge['button_shape'] = strtolower(trim((string) $shape));
        }
        if ($color !== null) {
            $merge['button_color'] = trim((string) $color);
        }
        if ($shadow !== null) {
            $merge['button_shadow'] = strtolower(trim((string) $shadow));
        }
        $shadowColor = $this->firstPresentInput(['button_shadow_color', 'buttonShadowColor', 'shadowColor']);
        if ($shadowColor !== null) {
            $merge['button_shadow_color'] = is_string($shadowColor) ? trim($shadowColor) : $shadowColor;
        } elseif ($this->hasAny(['buttonShadowColor', 'shadowColor']) && !$this->has('button_shadow_color')) {
            $merge['button_shadow_color'] = null;
        }

        if ($this->hasAny(['circular_shadow_enabled', 'circularShadowEnabled'])) {
            $circularShadow = $this->firstPresentInput(['circular_shadow_enabled', 'circularShadowEnabled']);
            if ($circularShadow !== null) {
                $parsed = filter_var($circularShadow, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                $merge['circular_shadow_enabled'] = $parsed ?? $circularShadow;
            } elseif (!$this->has('circular_shadow_enabled')) {
                $merge['circular_shadow_enabled'] = null;
            }
        }

        $resetIndividual = $this->input('reset_individual_colors') ?? $this->input('resetIndividualColors');
        if ($resetIndividual !== null) {
            $merge['reset_individual_colors'] = filter_var($resetIndividual, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    private function firstPresentInput(array $keys): mixed
    {
        foreach ($keys as $key) {
            if ($this->has($key)) {
                return $this->input($key);
            }
        }

        return null;
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
            'button_shadow' => [
                'nullable',
                'string',
                'in:none,hard_small,hard_large,soft,diffuse,elevated,colored',
            ],
            'button_shadow_color' => ['nullable', 'string', 'max:30', new HexColor()],
            'circular_shadow_enabled' => ['nullable', 'boolean'],
            'circular_icon_background' => ['nullable', 'string', 'max:20', new HexColor(allowAuto: true)],
            'button_color' => ['nullable', 'string', 'max:30', new HexColor()],
            'button_text_color' => ['nullable', 'string', 'max:30', new HexColor()],
            'border_style' => ['nullable', 'string', 'max:30'],
            'border_color' => ['nullable', 'string', 'max:30', new HexColor()],
            'reset_individual_colors' => ['nullable', 'boolean'],
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
            'button_shadow.in' => 'O estilo de sombra deve ser none, hard_small, hard_large, soft, diffuse, elevated ou colored.',
            'button_color.max' => 'A cor dos botões não pode exceder 30 caracteres.',
        ];
    }
}
