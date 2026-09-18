<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Cnh implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cnh = preg_replace('/[^0-9]/', '', (string) $value);

        if (strlen($cnh) !== 11 || preg_match('/(\d)\1{10}/', $cnh)) {
            $fail('O campo :attribute não é uma CNH válida.');

            return;
        }

        $dsc = 0;
        for ($i = 0, $j = 9; $i < 9; $i++, $j--) {
            $dsc += ((int) $cnh[$i] * $j);
        }

        $vl1 = $dsc % 11;
        $incr = 0;

        if ($vl1 > 9) {
            $vl1 = 0;
            $incr = 2;
        }

        $dsc = 0;
        for ($i = 0, $j = 1; $i < 9; $i++, $j++) {
            $dsc += ((int) $cnh[$i] * $j);
        }

        $vl2 = ($dsc % 11) >= 9 ? 0 : ($dsc % 11) - $incr;

        if ((int) $cnh[9] !== $vl1 || (int) $cnh[10] !== $vl2) {
            $fail('O campo :attribute não é uma CNH válida.');
        }
    }
}
