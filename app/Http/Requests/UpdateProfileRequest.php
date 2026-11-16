<?php

namespace App\Http\Requests;

use App\Rules\ArtistUsername;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize username and shadow inputs before running validation.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('username')) {
            $merge['username'] = ArtistUsername::normalize($this->input('username'));
        }

        $this->normalizeAliasedInput($merge, 'button_shadow_color', ['button_shadow_color', 'buttonShadowColor', 'shadowColor'], fn ($v) => is_string($v) ? trim($v) : $v);
        $this->normalizeAliasedInput($merge, 'circular_shadow_enabled', ['circular_shadow_enabled', 'circularShadowEnabled'], fn ($v) => filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $v);

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $artistId = $this->user()?->id;
        $mediaUrlRule = fn (string $attr, mixed $val, Closure $fail) => $this->validateMediaUrl($attr, $val, $fail);

        return [
            'name' => 'sometimes|required|string|max:100',
            'username' => ['sometimes', 'required', 'string', 'min:2', 'max:30', new ArtistUsername($artistId), Rule::unique('artists', 'username')->ignore($artistId)],
            'bio' => 'nullable|string|max:500',
            'avatar_url' => ['nullable', 'string', 'max:4096', $mediaUrlRule],
            'cover_url' => ['nullable', 'string', 'max:4096', $mediaUrlRule],
            'background_color' => 'nullable|string|max:20',
            'background_id' => 'nullable|string|max:50',
            'wallpaper_url' => ['nullable', 'string', 'max:4096', $mediaUrlRule],
            'wallpaper_repeat' => 'nullable|boolean',
            'wallpaper_preset_id' => 'nullable|string|max:50',
            'cover_color' => 'nullable|string|max:20',
            'button_shape' => 'nullable|in:square,rounded,pill',
            'button_shadow' => 'nullable|string|max:30',
            'button_shadow_color' => 'nullable|string|max:30',
            'circular_shadow_enabled' => 'nullable|boolean',
            'circular_icon_background' => 'nullable|string|max:20',
            'avatar_shape' => 'nullable|in:circle,square,rounded',
            'font_family' => 'nullable|string|max:50',
            'font_color' => 'nullable|string|max:20',
            'profile_font_family' => 'nullable|string|max:50',
            'profile_font_size' => 'nullable|in:sm,md,lg',
            'profile_text_color' => 'nullable|string|max:20',
            'profile_font_weight' => 'nullable|in:normal,bold',
            'profile_font_style' => 'nullable|in:normal,italic',
            'profile_text_border' => 'nullable|in:none,black,white,yellow',
            'button_font_family' => 'nullable|string|max:50',
            'button_font_size' => 'nullable|in:sm,md,lg',
            'button_color' => 'nullable|string|max:20',
            'button_text_color' => 'nullable|string|max:20',
            'button_font_weight' => 'nullable|in:normal,bold',
            'button_font_style' => 'nullable|in:normal,italic',
            'button_text_border' => 'nullable|in:none,black,white,yellow',
            'border_style' => 'nullable|string|max:30',
            'border_color' => 'nullable|string|max:20',
            'icon_variant' => 'nullable|string|max:30',
            'is_published' => 'sometimes|boolean',
            'publication_status' => 'sometimes|in:draft,published',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ArtistUsername::messages();
    }

    private function normalizeAliasedInput(array &$merge, string $target, array $aliases, callable $transform): void
    {
        foreach ($aliases as $key) {
            if (!$this->has($key)) {
                continue;
            }
            $value = $this->input($key);
            if ($value !== null) {
                $merge[$target] = $transform($value);
            } elseif (!$this->has($target)) {
                $merge[$target] = null;
            }
            return;
        }
    }

    private function validateMediaUrl(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }
        if (!is_string($value)) {
            $fail("O campo {$attribute} deve ser uma URL válida.");
            return;
        }
        $trimmed = trim($value);
        if (preg_match('/[\x00-\x1F\x7F]/', $trimmed)) {
            $fail("O campo {$attribute} contém caracteres inválidos.");
            return;
        }
        if (preg_match('#^https?://#i', $trimmed) || str_starts_with($trimmed, '/storage/')) {
            return;
        }
        if (preg_match('#^data:image/(svg\+xml|png|jpeg|jpg|webp)(;(?:charset=[^;,]+|utf8))?(;base64)?,#i', $trimmed)) {
            $decoded = rawurldecode($trimmed);
            if (preg_match('#(<script|javascript:|vbscript:|\bon[a-z]+\s*=)#i', $decoded)) {
                $fail("O campo {$attribute} contém conteúdo SVG/data inseguro.");
            }
            return;
        }
        $fail("O campo {$attribute} deve usar http(s), /storage/ ou imagem data:image válida.");
    }
}
