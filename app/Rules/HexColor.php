<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HexColor implements ValidationRule
{
    public function __construct(
        private readonly bool $allowAuto = false,
    ) {
    }

    public static function isValid(string $value, bool $allowAuto = false): bool
    {
        if ($allowAuto && strtolower($value) === 'auto') {
            return true;
        }

        return preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value) === 1;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value) || !self::isValid($value, $this->allowAuto)) {
            $fail("O campo {$attribute} deve ser uma cor hexadecimal válida (#RGB ou #RRGGBB).");
        }
    }
}
