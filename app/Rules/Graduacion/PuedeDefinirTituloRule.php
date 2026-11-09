<?php

namespace App\Rules\Graduacion;

use App\Enums\TamanoGrupo;
use App\Models\Estudiante\Alumnos;
use App\Enums\ArancelesGraduacion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class PuedeDefinirTituloRule implements ValidationRule
{
    public function __construct(
        private Alumnos $alumno,
        private int $cantidadIntegrantes = 1,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $nivel = $this->nivelAcademico();
        if (!$nivel) {
            $fail('No se pudo determinar el nivel académico del alumno.');
            return;
        }

        // Arancel de revisión de anteproyecto según nivel y tamaño de grupo
        $arancel = $this->arancelRevisionAnteproyecto($nivel);

        $pagado = DB::table('pago as p')
            ->join('detalle_pago as dp', 'p.pago_id', '=', 'dp.id_pago')
            ->join('concepto_pago as cp', 'dp.id_concepto', '=', 'cp.concepto_pago_id')
            ->where('p.id_alumno', $this->alumno->alumno_id)
            ->where('cp.codigo_concepto', $arancel)
            ->exists();

        if (!$pagado) {
            $fail("Para definir o modificar el título debes cancelar el arancel de revisión de anteproyecto ({$arancel}).");
        }
    }

    private function nivelAcademico(): ?string
    {
        return DB::table('carreras as c')
            ->join('niveles_academicos as na', 'c.id_nivel_academico', '=', 'na.nivel_academico_id')
            ->where('c.carrera_id', $this->alumno->id_carrera)
            ->value('na.codigo');
    }

    private function arancelRevisionAnteproyecto(string $nivel): string
    {
        $tamano = match (true) {
            $this->cantidadIntegrantes <= 1 => TamanoGrupo::INDIVIDUAL,
            $this->cantidadIntegrantes === 2 => TamanoGrupo::PAREJA,
            default => TamanoGrupo::GRUPO_TRES,
        };

        if ($nivel === 'MAESTRIA') {
            return match ($tamano) {
                TamanoGrupo::INDIVIDUAL => 'MAE-005',
                TamanoGrupo::PAREJA     => 'MAE-006',
                TamanoGrupo::GRUPO_TRES => 'MAE-007',
            };
        }

        return match ($tamano) {
            TamanoGrupo::INDIVIDUAL => 'TES-002',
            TamanoGrupo::PAREJA     => 'TES-003',
            TamanoGrupo::GRUPO_TRES => 'TES-004',
        };
    }
}