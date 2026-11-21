<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreFeatureLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('contact_value') && is_string($this->input('contact_value'))) {
            $sanitized = strip_tags($this->input('contact_value'));
            $sanitized = preg_replace('/[\x00-\x1F\x7F]/u', '', $sanitized) ?? '';
            $sanitized = trim($sanitized);

            $this->merge([
                'contact_value' => $sanitized === '' ? null : $sanitized,
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $isGuest = $this->user('sanctum') === null;

        $contactRules = [
            'bail',
            $isGuest ? 'required' : 'nullable',
            'string',
            'max:255',
            function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_string($value) || trim($value) === '') {
                    return;
                }
                $trimmed = trim($value);
                $isEmail = filter_var($trimmed, FILTER_VALIDATE_EMAIL) !== false;
                $isHandle = preg_match('/^@?(?!\.)(?!.*\.\.)[a-zA-Z0-9_.-]{2,30}(?<!\.)$/', $trimmed) === 1;

                if (! $isEmail && ! $isHandle) {
                    $fail('Informe um e-mail válido ou um @handle válido.');
                }
            },
        ];

        return [
            'feature' => ['sometimes', 'string', 'in:vitrine,assistente'],
            'source' => ['required', 'string', 'in:home_hero,home_footer,home_waitlist,editor_banner'],
            'contact_value' => $contactRules,
            'metadata' => ['nullable', 'array', 'max:10'],
            'metadata.selling_type' => ['nullable', 'string', 'in:prints,originais,comissoes'],
            'metadata.selling_types' => ['nullable', 'array', 'max:3'],
            'metadata.selling_types.*' => ['string', 'in:prints,originais,comissoes'],
        ];
    }
}
