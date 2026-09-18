<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cnpj implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $value);

        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            $fail('O campo :attribute não é um CNPJ válido.');

            return;
        }

        for ($t = 12; $t < 14; $t++) {
            $d = 0;
            $c = 0;
            $p = $t - 7;
            while ($p >= 2) {
                $d += $cnpj[$c++] * $p--;
            }
            $p = 9;
            while ($c < $t) {
                $d += $cnpj[$c++] * $p--;
            }
            $d = ($d % 11) < 2 ? 0 : 11 - ($d % 11);
            if ($cnpj[$c] != $d) {
                $fail('O campo :attribute não é um CNPJ válido.');

                return;
            }
        }
    }
}
