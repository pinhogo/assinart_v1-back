<?php

namespace App\Http\Requests;

use App\Services\ButtonOrderService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
        $platforms = 'website,youtube,x,linkedin,soundcloud,instagram,spotify,threads,pinterest,discord,whatsapp,tiktok,facebook,behance,telegram,twitch,email,custom';

        $maxButtons = ButtonOrderService::MAX_BUTTONS_PER_ARTIST;

        return [
            'bio' => ['nullable', 'string', 'max:160'],
            'links' => ['nullable', 'array', "max:{$maxButtons}"],
            'links.*.platform' => [
                'required_with:links',
                'string',
                "in:{$platforms}",
            ],
            'links.*.url' => ['nullable', 'string', 'max:2048'],
            'links.*.value' => ['nullable', 'string', 'max:2048'],
            'links.*.title' => ['nullable', 'string', 'max:100'],
            'links.*.display_mode' => ['nullable', 'in:button,circular'],
            'links.*.icon_variant' => ['nullable', 'in:default,black,white,none'],
            'links.*.show_icon_background' => ['nullable', 'boolean'],
            'links.*.text_align' => ['nullable', 'in:left,center,right'],
            'links.*.is_active' => ['nullable', 'boolean'],
            'links.*.order_index' => ['nullable', 'integer', 'min:0'],
            'links.*.button_color' => ['nullable', 'string', 'max:20'],
            'links.*.text_color' => ['nullable', 'string', 'max:20'],
            'links.*.font_family' => ['nullable', 'string', 'max:50'],
            'links.*.font_weight' => ['nullable', 'in:normal,bold'],
            'links.*.font_style' => ['nullable', 'in:normal,italic'],
            'links.*.text_border' => ['nullable', 'in:none,black,white,yellow'],
            'selectedPlatforms' => ['nullable', 'array', "max:{$maxButtons}"],
            'selectedPlatforms.*' => [
                'string',
                "in:{$platforms}",
            ],
            'linkValues' => ['nullable', 'array'],
        ];
    }

    /**
     * Valida o teto máximo de botões circulares no payload normalizado.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $links = $this->input('links', []);

            if (!is_array($links)) {
                return;
            }

            $circularCount = 0;

            foreach ($links as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (($item['display_mode'] ?? 'button') === 'circular') {
                    $circularCount++;
                }

                $raw = trim((string) ($item['url'] ?? $item['value'] ?? ''));
                if ($raw === '') {
                    continue;
                }

                $compact = (string) preg_replace('/[\x00-\x20]+/', '', $raw);
                if (preg_match('/^(javascript|data|vbscript):/i', $compact)) {
                    $validator->errors()->add('links', 'O link informado contém um esquema de URL não permitido.');
                }

                if (($item['platform'] ?? null) === 'whatsapp' && preg_replace('/\D/', '', $raw) === '') {
                    $validator->errors()->add('links', 'Informe um número de WhatsApp válido com dígitos.');
                }
            }

            if ($circularCount > ButtonOrderService::MAX_CIRCULAR_BUTTONS) {
                $validator->errors()->add('links', 'Você pode ter no máximo 5 botões circulares.');
            }
        });
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
            'links.max' => 'Você pode cadastrar no máximo 30 links combinados entre redes sociais e personalizados.',
            'selectedPlatforms.max' => 'Você pode escolher até 30 plataformas combinadas no onboarding.',
            'links.*.platform.in' => 'A plataforma informada não é suportada.',
            'selectedPlatforms.*.in' => 'Uma das plataformas selecionadas não é suportada.',
        ];
    }
}
