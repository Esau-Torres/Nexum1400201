<?php

namespace App\Rules\Graduacion;

use App\Enums\TamanoGrupo;
use App\Models\Estudiante\Alumnos;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use App\Models\Academico\ProcesoGraduacion;
use App\Enums\ArancelesGraduacion;

class CuotasAsesoriaCompletasRule implements ValidationRule
{
    private const CUOTAS_REQUERIDAS = 12;

    public function __construct(private ProcesoGraduacion $proceso) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->proceso->modalidad->value === 'PRE_ESPECIALIZACION') {
            return;
        }

        $nivel = $this->nivelAcademico();
        if (!$nivel) {
            $fail('No se pudo determinar el nivel académico.');
            return;
        }

        $tamano = $this->tamanoGrupo();
        $codigo = ArancelesGraduacion::cuotasAsesoria($nivel, $tamano);

        $pagadas = ArancelesGraduacion::queryPagosDesde($this->proceso)
            ->where('p.id_alumno', $this->proceso->id_alumno)
            ->where('cp.codigo_concepto', $codigo)
            ->distinct()
            ->count('p.pago_id');

        if ($pagadas < self::CUOTAS_REQUERIDAS) {
            $faltantes = self::CUOTAS_REQUERIDAS - $pagadas;
            $fail("Faltan {$faltantes} cuota(s) de asesoría por cancelar en este proceso ({$pagadas}/12).");
        }
    }

    private function nivelAcademico(): ?string
    {
        return DB::table('carreras as c')
            ->join('niveles_academicos as na', 'c.id_nivel_academico', '=', 'na.nivel_academico_id')
            ->where('c.carrera_id', $this->proceso->alumno->id_carrera)
            ->value('na.codigo');
    }

    private function tamanoGrupo(): TamanoGrupo
    {
        if (!$this->proceso->grupo) {
            return TamanoGrupo::INDIVIDUAL;
        }
        return TamanoGrupo::desdeIntegrantes($this->proceso->grupo->procesos()->count());
    }
}