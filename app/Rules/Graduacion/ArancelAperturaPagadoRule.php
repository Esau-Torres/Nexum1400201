<?php

namespace App\Rules\Graduacion;

use App\Models\Estudiante\Alumnos;
use App\Enums\ArancelesGraduacion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ArancelAperturaPagadoRule implements ValidationRule
{
    public function __construct(private Alumnos $alumno) {}

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

        $codigo = ArancelesGraduacion::aperturaExpediente($nivel);

        $pagado = DB::table('pago as p')
            ->join('detalle_pago as dp', 'p.pago_id', '=', 'dp.id_pago')
            ->join('concepto_pago as cp', 'dp.id_concepto', '=', 'cp.concepto_pago_id')
            ->where('p.id_alumno', $this->alumno->alumno_id)
            ->where('cp.codigo_concepto', $codigo)
            ->where('p.fecha_pago', '>=', now()->subYear())
            ->exists();

        if (!$pagado) {
            $fail("No se ha cancelado el arancel de apertura de expediente ({$codigo}).");
        }
    }
}