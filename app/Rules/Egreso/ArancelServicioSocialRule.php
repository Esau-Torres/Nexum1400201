<?php

namespace App\Rules\Egreso;

use App\Models\Estudiante\Alumnos;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ArancelServicioSocialRule implements ValidationRule
{
    private const CONCEPTO = 'TRA-014';

    public function __construct(private Alumnos $alumno) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pagado = DB::table('pago as p')
            ->join('detalle_pago as dp', 'p.pago_id', '=', 'dp.id_pago')
            ->join('concepto_pago as cp', 'dp.id_concepto', '=', 'cp.concepto_pago_id')
            ->where('p.id_alumno', $this->alumno->alumno_id)
            ->where('cp.codigo_concepto', self::CONCEPTO)
            ->exists();

        if (!$pagado) {
            $fail(' (TRA-014) No has cancelado el arancel de Inscripción del Servicio Social.');
        }
    }
}