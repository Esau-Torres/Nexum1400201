<?php

namespace App\Rules\Graduacion;

use App\Enums\ModalidadGraduacion;
use App\Models\Academico\ProcesoGraduacion;
use App\Enums\ArancelesGraduacion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class TienePagoDerechosGradoRule implements ValidationRule
{
    public function __construct(private ProcesoGraduacion $proceso) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Si ya tiene pago asociado, cumple
        if ($this->proceso->id_pago_derechos_grado) {
            return;
        }

        $nivel = DB::table('carreras as c')
            ->join('niveles_academicos as na', 'c.id_nivel_academico', '=', 'na.nivel_academico_id')
            ->where('c.carrera_id', $this->proceso->alumno->id_carrera)
            ->value('na.codigo');

        $codigo = ArancelesGraduacion::derechosGrado($nivel, $this->proceso->modalidad->value);

        $pago = ArancelesGraduacion::queryPagosDesde($this->proceso)
            ->where('p.id_alumno', $this->proceso->id_alumno)
            ->where('cp.codigo_concepto', $codigo)
            ->orderByDesc('p.pago_id')
            ->select('p.pago_id')
            ->first();

        if (!$pago) {
            $fail("No se ha cancelado el arancel de derechos de grado ({$codigo}). Regístralo antes de graduar al alumno.");
        }
    }
}