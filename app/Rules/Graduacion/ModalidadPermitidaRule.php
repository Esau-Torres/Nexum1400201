<?php

namespace App\Rules\Graduacion;

use App\Enums\ModalidadGraduacion;
use App\Models\Estudiante\Alumnos;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ModalidadPermitidaRule implements ValidationRule
{
    public function __construct(
        private Alumnos $alumno,
        private ModalidadGraduacion $modalidad,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $nivel = DB::table('carreras as c')
            ->join('niveles_academicos as na', 'c.id_nivel_academico', '=', 'na.nivel_academico_id')
            ->where('c.carrera_id', $this->alumno->id_carrera)
            ->value('na.codigo');

        if (!$nivel) {
            $fail('No se pudo determinar el nivel académico del alumno.');
            return;
        }

        if (!$this->modalidad->permitidaParaNivel($nivel)) {
            $fail("La modalidad '{$this->modalidad->label()}' no está permitida para el nivel {$nivel}.");
        }
    }
}