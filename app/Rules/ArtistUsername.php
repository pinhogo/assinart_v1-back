<?php

namespace App\Rules;

use App\Models\Artist;
use App\Support\ReservedUsernames;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ArtistUsername implements ValidationRule
{
    public const REGEX = '/^(?!\.)(?!.*\.\.)[a-z0-9_.-]{2,30}(?<!\.)$/';

    public function __construct(
        private readonly ?int $ignoreArtistId = null,
    ) {
    }

    /**
     * Normalize raw username input before validation or persistence.
     */
    public static function normalize(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return ltrim(strtolower(trim($value)), '@');
    }

    /**
     * Check whether a normalized username satisfies format and dot placement rules.
     */
    public static function isValidFormat(string $value): bool
    {
        return preg_match(self::REGEX, $value) === 1;
    }

    /**
     * Return a pt-BR format error message when invalid, or null when valid.
     */
    public static function formatError(string $value): ?string
    {
        if (preg_match('/^[a-z0-9_.-]{2,30}$/', $value) !== 1) {
            return 'O @ deve ter entre 2 e 30 caracteres (letras, números, pontos, traços ou sublinhados).';
        }

        if (!self::isValidFormat($value)) {
            return 'O @ não pode começar nem terminar com ponto, nem conter pontos seguidos.';
        }

        return null;
    }

    /**
     * Shared pt-BR validation messages matching registration rules.
     *
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'username.required' => 'O nome de usuário (@) é obrigatório.',
            'username.min' => 'O nome de usuário deve ter no mínimo 2 caracteres.',
            'username.max' => 'O nome de usuário deve ter no máximo 30 caracteres.',
            'username.regex' => 'O @ deve ter entre 2 e 30 caracteres (letras, números, pontos, traços ou sublinhados).',
            'username.unique' => 'Esse @ já está em uso.',
        ];
    }

    /**
     * Validate format, dot placement, reserved words, and case-insensitive uniqueness.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('O nome de usuário (@) é obrigatório.');
            return;
        }

        $formatError = self::formatError($value);
        if ($formatError !== null) {
            $fail($formatError);
            return;
        }

        if (ReservedUsernames::contains($value)) {
            $fail('Este nome de usuário é reservado pelo sistema.');
            return;
        }

        $exists = Artist::query()
            ->whereRaw('LOWER(username) = ?', [strtolower($value)])
            ->when(
                $this->ignoreArtistId !== null,
                fn ($query) => $query->where('id', '!=', $this->ignoreArtistId)
            )
            ->exists();

        if ($exists) {
            $fail('Esse @ já está em uso.');
        }
    }
}
