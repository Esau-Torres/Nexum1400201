<?php

namespace App\Rules\Graduacion;

use App\Enums\TamanoGrupo;
use App\Models\Academico\ProcesoGraduacion;
use App\Enums\ArancelesGraduacion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ArancelRevisionAnteproyectoPagadoRule implements ValidationRule
{
    public function __construct(private ProcesoGraduacion $proceso) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Pre-especialización no requiere este arancel
        if ($this->proceso->modalidad->value === 'PRE_ESPECIALIZACION') {
            return;
        }

        $nivel = $this->nivelAcademico();
        if (!$nivel) { $fail('No se pudo determinar el nivel académico.'); return; }

        $tamano = $this->tamanoGrupo();
        $codigo = ArancelesGraduacion::revisionAnteproyecto($nivel, $tamano);

        $pagado = ArancelesGraduacion::queryPagosDesde($this->proceso)
            ->where('p.id_alumno', $this->proceso->id_alumno)
            ->where('cp.codigo_concepto', $codigo)
            ->exists();

        if (!$pagado) {
            $fail("No se ha cancelado el arancel de revisión de anteproyecto ({$codigo}) en este proceso.");
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