<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingLinksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize payload before running validation rules.
     * Supports both standard API format and frontend draft format ({ bio, selectedPlatforms, linkValues }).
     */
    protected function prepareForValidation(): void
    {
        // Support frontend draft format: { bio, selectedPlatforms, linkValues }
        if (!$this->has('links') && ($this->has('selectedPlatforms') || $this->has('linkValues'))) {
            $selected = $this->input('selectedPlatforms', []);
            $values = $this->input('linkValues', []);
            $links = [];

            if (is_array($selected)) {
                foreach ($selected as $platform) {
                    $val = is_array($values) ? ($values[$platform] ?? '') : '';
                    $links[] = [
                        'platform' => (string) $platform,
                        'value' => (string) $val,
                    ];
                }
            }

            $this->merge(['links' => $links]);
        } elseif ($this->has('links') && is_array($this->input('links'))) {
            $rawLinks = $this->input('links');
            // If passed as associative array [ 'instagram' => 'handle' ]
            $isAssoc = !empty($rawLinks) && array_keys($rawLinks) !== range(0, count($rawLinks) - 1);
            if ($isAssoc) {
                $normalized = [];
                foreach ($rawLinks as $platform => $value) {
                    if (is_array($value)) {
                        $normalized[] = array_merge(['platform' => (string) $platform], $value);
                    } else {
                        $normalized[] = [
                            'platform' => (string) $platform,
                            'value' => (string) $value,
                        ];
                    }
                }
                $this->merge(['links' => $normalized]);
            }
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
            'bio' => ['nullable', 'string', 'max:160'],
            'links' => ['nullable', 'array', 'max:5'],
            'links.*.platform' => [
                'required_with:links',
                'string',
                'in:website,youtube,x,linkedin,soundcloud,instagram,spotify,threads,pinterest,discord,whatsapp,tiktok,facebook,behance,telegram,twitch,email',
            ],
            'links.*.url' => ['nullable', 'string', 'max:2048'],
            'links.*.value' => ['nullable', 'string', 'max:2048'],
            'links.*.title' => ['nullable', 'string', 'max:100'],
            'selectedPlatforms' => ['nullable', 'array', 'max:5'],
            'selectedPlatforms.*' => [
                'string',
                'in:website,youtube,x,linkedin,soundcloud,instagram,spotify,threads,pinterest,discord,whatsapp,tiktok,facebook,behance,telegram,twitch,email',
            ],
            'linkValues' => ['nullable', 'array'],
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
            'bio.max' => 'A biografia não pode ultrapassar 160 caracteres.',
            'links.max' => 'Você pode cadastrar no máximo 5 links iniciais no onboarding.',
            'selectedPlatforms.max' => 'Você pode escolher até 5 plataformas para começar.',
            'links.*.platform.in' => 'A plataforma informada não é suportada.',
            'selectedPlatforms.*.in' => 'Uma das plataformas selecionadas não é suportada.',
        ];
    }
}
