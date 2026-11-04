<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPasswordRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $password = (string) $value;

        if (mb_strlen($password) < 14) {
            $fail('A nova senha deve possuir no mínimo 14 caracteres.');
            return;
        }

        if (! preg_match('/[A-Z]/', $password)) {
            $fail('A nova senha deve conter pelo menos uma letra maiúscula (A-Z).');
            return;
        }

        if (! preg_match('/[a-z]/', $password)) {
            $fail('A nova senha deve conter pelo menos uma letra minúscula (a-z).');
            return;
        }

        if (! preg_match('/[0-9]/', $password)) {
            $fail('A nova senha deve conter pelo menos um número (0-9).');
            return;
        }

        if (! preg_match('/[!@#$%&*]/', $password)) {
            $fail('A nova senha deve conter pelo menos um caractere especial permitido: ! @ # $ % & *');
            return;
        }
    }
}
