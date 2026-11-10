<?php

namespace App\Actions\AdminAcademic;

use App\Models\Academico\ConfiguracionAcademica;
use App\Models\Academico\Pensum;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\RequisitosEgreso;
use App\Rules\Egreso\ArancelServicioSocialRule;
use App\Rules\Egreso\ConstanciaCumRule;
use App\Rules\Egreso\ConstanciaNotasRule;
use App\Rules\Egreso\CumMinimoRule;
use App\Rules\Egreso\HorasSocialesRule;
use App\Rules\Egreso\MateriasAprobadasRule;
use App\Rules\Egreso\PasantiaAprobadaRule;
use App\Rules\Egreso\SolvenciaFinancieraRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ValidarEgresoAction
{
    public function execute(Alumnos $alumno): array
    {
        $rules = [
            'materias'      => [new MateriasAprobadasRule($alumno)],
            'cum'           => [new CumMinimoRule($alumno)],
            'horas_sociales'=> [new HorasSocialesRule($alumno)],
            'solvencia'     => [new SolvenciaFinancieraRule($alumno)],
            'arancel_ss'    => [new ArancelServicioSocialRule($alumno)],
            'constancia_notas' => [new ConstanciaNotasRule($alumno)],
            'constancia_cum'   => [new ConstanciaCumRule($alumno)],
            'pasantia'         => [new PasantiaAprobadaRule($alumno)],
        ];

        // Validación con todas las Rules al mismo tiempo
        $validator = Validator::make(
            ['materias' => true, 'cum' => true, 'horas_sociales' => true,
             'solvencia' => true, 'arancel_ss' => true, 'constancia_notas' => true,
             'constancia_cum' => true, 'pasantia' => true],
            $rules
        );

        $bloqueadores = $validator->fails() ? $validator->errors()->all() : [];

        // Construir resumen para la vista (independiente de las Rules)
        $req = RequisitosEgreso::paraAlumno($alumno->alumno_id);
        $detalles = $this->construirDetalles($alumno, $req);

        return [
            'puede'        => empty($bloqueadores),
            'bloqueadores' => $bloqueadores,
            'detalles'     => $detalles,
            'req'          => $req,
        ];
    }

    /**
     * Construye el resumen visual de cada requisito.
     */
    private function construirDetalles(Alumnos $alumno, RequisitosEgreso $req): array
    {
        $pensum = Pensum::where('id_carrera', $alumno->id_carrera)
            ->where('estado', 'ACTIVO')->first();

        $totalMaterias = $pensum ? $pensum->pensumMaterias()->count() : 0;
        $aprobadas = $pensum
            ? DB::table('detalle_inscripcion as di')
                ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
                ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
                ->join('pensum_materia as pm', 's.id_materia', '=', 'pm.id_materia')
                ->where('i.id_alumno', $alumno->alumno_id)
                ->where('pm.id_pensum', $pensum->pensum_id)
                ->where('di.estado_materia', 'APROBADA')
                ->distinct()->count('pm.id_materia')
            : 0;

        $cum = round((float) DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->whereIn('di.estado_materia', ['APROBADA', 'REPROBADA'])
            ->whereNotNull('di.promedio_final')->avg('di.promedio_final'), 2);

        $config = ConfiguracionAcademica::paraCarrera($alumno->id_carrera);
        $horasRequeridas = (int) $config->uv_requeridas_horas_sociales;

        $pagoArSS  = $this->tienePagoConcepto($alumno, 'TRA-014');
        $pagoCER1  = $this->tienePagoConcepto($alumno, 'CER-001');
        $pagoCER9  = $this->tienePagoConcepto($alumno, 'CER-009');

        return [
            'materias' => [
                'aprobadas' => $aprobadas,
                'total'     => $totalMaterias,
                'cumple'    => $aprobadas >= $totalMaterias && $totalMaterias > 0,
            ],
            'cum' => [
                'valor'  => $cum,
                'minimo' => 7.0,
                'cumple' => $cum >= 7.0,
            ],
            'horas_sociales' => [
                'completadas' => $req->horas_sociales_completadas,
                'requeridas'  => $horasRequeridas,
                'aprobadas'   => $req->horas_sociales_aprobadas,
                'cumple'      => $req->horas_sociales_aprobadas
                                 && $req->horas_sociales_completadas >= $horasRequeridas,
            ],
            'solvencia' => [
                'aprobada' => (bool) $req->solvencia_financiera,
                'cumple'   => (bool) $req->solvencia_financiera,
            ],
            'arancel_servicio_social' => [
                'pagado' => $pagoArSS,
                'cumple' => $pagoArSS,
            ],
            'constancia_notas' => [
                'pagada' => $pagoCER1,
                'cumple' => $pagoCER1,
            ],
            'constancia_cum' => [
                'pagada' => $pagoCER9,
                'cumple' => $pagoCER9,
            ],
            'pasantia' => [
                'requerida'  => (bool) $req->requiere_pasantia,
                'aprobada'   => (bool) $req->pasantia_aprobada,
                'institucion'=> $req->institucion_pasantia,
                'cumple'     => !$req->requiere_pasantia || $req->pasantia_aprobada,
            ],
        ];
    }

    private function tienePagoConcepto(Alumnos $alumno, string $codigo): bool
    {
        return DB::table('pago as p')
            ->join('detalle_pago as dp', 'p.pago_id', '=', 'dp.id_pago') 
            ->join('concepto_pago as cp', 'dp.id_concepto', '=', 'cp.concepto_pago_id')
            ->where('p.id_alumno', $alumno->alumno_id)
            ->where('cp.codigo_concepto', $codigo)
            ->exists();
    }
}