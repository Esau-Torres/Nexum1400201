<?php

namespace App\Rules\Egreso;

use App\Models\Academico\ConfiguracionAcademica;
use App\Models\Estudiante\Alumnos;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HorasSocialesDentroDelLimiteRule implements ValidationRule
{
    public function __construct(private Alumnos $alumno) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $max = $this->horasMaximas();

        if (!is_numeric($value) || (int) $value < 0) {
            $fail('Las horas completadas deben ser un número entero positivo.');
            return;
        }

        if ((int) $value > $max) {
            $fail("No puedes registrar {$value} horas. El límite para tu carrera es de {$max} horas.");
        }
    }

    private function horasMaximas(): int
    {
        $config = ConfiguracionAcademica::paraCarrera($this->alumno->id_carrera);
        return (int) $config->uv_requeridas_horas_sociales;
    }
}