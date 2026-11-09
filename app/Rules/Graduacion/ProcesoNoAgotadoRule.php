<?php

namespace App\Rules\Graduacion;

use App\Models\Academico\ProcesoGraduacion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProcesoNoAgotadoRule implements ValidationRule
{
    public function __construct(private ProcesoGraduacion $proceso) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->proceso->intentos_defensa >= 2) {
            $fail('El proceso ya fue reprobado dos veces y no admite más cambios.');
        }
    }
}