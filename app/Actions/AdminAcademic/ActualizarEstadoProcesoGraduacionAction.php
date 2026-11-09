<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoProcesoGraduacion;
use App\Models\Academico\ProcesoGraduacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Enums\ModalidadGraduacion;
use App\Rules\Graduacion\TienePagoDerechosGradoRule;
use App\Enums\ArancelesGraduacion;
use Illuminate\Support\Facades\Validator;
use App\Rules\Graduacion\ArancelDefensaPagadoRule;
use App\Rules\Graduacion\CuotasAsesoriaCompletasRule;
use App\Rules\Graduacion\ArancelRevisionAnteproyectoPagadoRule;
use App\Rules\Graduacion\ArancelRevisionTesisPagadoRule;
use App\Rules\Graduacion\PagoExamenSuficienciaRule;
use App\Rules\Graduacion\PuedeReprogramarDefensaRule;
use App\Rules\Graduacion\ProcesoNoAgotadoRule;

class ActualizarEstadoProcesoGraduacionAction
{
    public function execute(
        ProcesoGraduacion $proceso,
        EstadoProcesoGraduacion $nuevoEstado,
        ?float $notaDefensa = null
    ): ProcesoGraduacion {
        if ($proceso->estado_proceso === EstadoProcesoGraduacion::GRADUADO) {
            throw ValidationException::withMessages([
                'estado' => 'Un proceso graduado no puede cambiar de estado.',
            ]);
        }

        // 1. El proceso no debe estar agotado (>= 2 intentos)
        $validator = Validator::make(
            ['proceso' => true],
            ['proceso' => [new ProcesoNoAgotadoRule($proceso)]]
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages(['estado' => $validator->errors()->all()]);
        }

        // 2. Transiciones válidas
        $transicionesValidas = [
            EstadoProcesoGraduacion::INSCRITO->value      => [EstadoProcesoGraduacion::EN_DESARROLLO->value],
            EstadoProcesoGraduacion::EN_DESARROLLO->value => [EstadoProcesoGraduacion::EN_DEFENSA->value],
            EstadoProcesoGraduacion::EN_DEFENSA->value    => [EstadoProcesoGraduacion::APROBADO->value, EstadoProcesoGraduacion::REPROBADO->value],
            EstadoProcesoGraduacion::APROBADO->value      => [EstadoProcesoGraduacion::GRADUADO->value],
            EstadoProcesoGraduacion::REPROBADO->value     => [
                EstadoProcesoGraduacion::EN_DEFENSA->value,
                EstadoProcesoGraduacion::EN_DESARROLLO->value,
            ],
        ];

        $estadoActual = $proceso->estado_proceso->value;
        if (!isset($transicionesValidas[$estadoActual]) ||
            !in_array($nuevoEstado->value, $transicionesValidas[$estadoActual], true)) {
            throw ValidationException::withMessages([
                'estado' => "No puedes cambiar de '{$proceso->estado_proceso->label()}' a '{$nuevoEstado->label()}'.",
            ]);
        }

        // ─────────────────────────────────────────────
        // INSCRITO → EN_DESARROLLO: arancel anteproyecto
        // ─────────────────────────────────────────────
        if ($nuevoEstado === EstadoProcesoGraduacion::EN_DESARROLLO
            && $estadoActual === EstadoProcesoGraduacion::INSCRITO->value) {

            $validator = Validator::make(
                ['transicion' => true],
                ['transicion' => [new ArancelRevisionAnteproyectoPagadoRule($proceso)]]
            );

            if ($validator->fails()) {
                throw ValidationException::withMessages(['pago' => $validator->errors()->all()]);
            }
        }

        // ─────────────────────────────────────────────
        // EN_DESARROLLO → EN_DEFENSA: cuotas + revisión + defensa
        // ─────────────────────────────────────────────
        if ($nuevoEstado === EstadoProcesoGraduacion::EN_DEFENSA
            && $estadoActual === EstadoProcesoGraduacion::EN_DESARROLLO->value) {

            $validator = Validator::make(
                ['transicion' => true],
                [
                    'transicion' => [
                        new CuotasAsesoriaCompletasRule($proceso),
                        new ArancelRevisionTesisPagadoRule($proceso),
                        new ArancelDefensaPagadoRule($proceso),
                    ],
                ]
            );

            if ($validator->fails()) {
                throw ValidationException::withMessages(['pago' => $validator->errors()->all()]);
            }
        }

        // ─────────────────────────────────────────────
        // EN_DEFENSA → APROBADO / REPROBADO
        // ─────────────────────────────────────────────
        if (in_array($nuevoEstado, [EstadoProcesoGraduacion::APROBADO, EstadoProcesoGraduacion::REPROBADO], true)) {
            if ($notaDefensa === null || $notaDefensa < 0 || $notaDefensa > 10) {
                throw ValidationException::withMessages([
                    'nota' => 'La nota de defensa debe estar entre 0 y 10.',
                ]);
            }

            $estadoEsperado = $notaDefensa >= 6.0
                ? EstadoProcesoGraduacion::APROBADO
                : EstadoProcesoGraduacion::REPROBADO;

            if ($nuevoEstado !== $estadoEsperado) {
                throw ValidationException::withMessages([
                    'nota' => $nuevoEstado === EstadoProcesoGraduacion::APROBADO
                        ? 'Para aprobar, la nota debe ser mayor o igual a 6.0.'
                        : 'Para reprobar, la nota debe ser menor a 6.0.',
                ]);
            }
        }

        // ─────────────────────────────────────────────
        // REPROBADO → EN_DEFENSA (segunda convocatoria tesis)
        // ─────────────────────────────────────────────
        if ($nuevoEstado === EstadoProcesoGraduacion::EN_DEFENSA
            && $estadoActual === EstadoProcesoGraduacion::REPROBADO->value) {

            if ($proceso->modalidad !== ModalidadGraduacion::TRABAJO_GRADUACION) {
                throw ValidationException::withMessages([
                    'estado' => 'La pre-especialización no usa segunda convocatoria de defensa.',
                ]);
            }

            $validator = Validator::make(
                ['transicion' => true],
                ['transicion' => [new PuedeReprogramarDefensaRule($proceso)]]
            );

            if ($validator->fails()) {
                throw ValidationException::withMessages(['estado' => $validator->errors()->all()]);
            }
        }

        // ─────────────────────────────────────────────
        // REPROBADO → EN_DESARROLLO (recuperación pre-especialización)
        // ─────────────────────────────────────────────
        if ($nuevoEstado === EstadoProcesoGraduacion::EN_DESARROLLO
            && $estadoActual === EstadoProcesoGraduacion::REPROBADO->value) {

            if ($proceso->modalidad !== ModalidadGraduacion::PRE_ESPECIALIZACION) {
                throw ValidationException::withMessages([
                    'estado' => 'El trabajo de graduación no usa recuperación en desarrollo.',
                ]);
            }

            $validator = Validator::make(
                ['transicion' => true],
                ['transicion' => [new PagoExamenSuficienciaRule($proceso)]]
            );

            if ($validator->fails()) {
                throw ValidationException::withMessages(['pago' => $validator->errors()->all()]);
            }
        }

        // ─────────────────────────────────────────────
        // APROBADO → GRADUADO: derechos de grado
        // ─────────────────────────────────────────────
        if ($nuevoEstado === EstadoProcesoGraduacion::GRADUADO) {
            $validator = Validator::make(
                ['estado' => $nuevoEstado->value],
                ['estado' => [new TienePagoDerechosGradoRule($proceso)]]
            );

            if ($validator->fails()) {
                throw ValidationException::withMessages(['pago' => $validator->errors()->all()]);
            }
        }

        // ─────────────────────────────────────────────
        // Persistir
        // ─────────────────────────────────────────────
        return DB::transaction(function () use ($proceso, $nuevoEstado, $notaDefensa) {
            $proceso->estado_proceso = $nuevoEstado;

            if ($nuevoEstado === EstadoProcesoGraduacion::EN_DEFENSA) {
                $proceso->fecha_defensa = now()->toDateString();
            }

            if (in_array($nuevoEstado, [EstadoProcesoGraduacion::APROBADO, EstadoProcesoGraduacion::REPROBADO], true)
                && $notaDefensa !== null) {
                $proceso->nota_defensa = $notaDefensa;
            }

            if ($nuevoEstado === EstadoProcesoGraduacion::GRADUADO) {
                $proceso->fecha_graduacion = now()->toDateString();
                if (!$proceso->id_pago_derechos_grado) {
                    $proceso->id_pago_derechos_grado = $this->buscarPagoDerechosGrado($proceso);
                }
            }

            if ($nuevoEstado === EstadoProcesoGraduacion::REPROBADO) {
                $proceso->intentos_defensa = $proceso->intentos_defensa + 1;

                if ($proceso->modalidad === ModalidadGraduacion::TRABAJO_GRADUACION) {
                    if ($proceso->intentos_defensa >= 2) {
                        $proceso->nota_defensa = null;
                        if ($proceso->grupo) {
                            $proceso->grupo->estado_grupo = 'CANCELADO';
                            $proceso->grupo->save();
                        }
                    }
                }

                if ($proceso->modalidad === ModalidadGraduacion::PRE_ESPECIALIZACION) {
                    if ($proceso->intentos_defensa >= 2) {
                        $proceso->nota_defensa = null;
                    }
                }
            }

            $proceso->save();

            // ─────────────────────────────────────────────
            // Verificar cancelación del grupo al graduar
            // ─────────────────────────────────────────────
            if ($nuevoEstado === EstadoProcesoGraduacion::GRADUADO) {
                $this->verificarCancelacionGrupo($proceso);
            }

            return $proceso->fresh();
        });
    }

    private function buscarPagoDerechosGrado(ProcesoGraduacion $proceso): ?int
    {
        $nivel = DB::table('carreras as c')
            ->join('niveles_academicos as na', 'c.id_nivel_academico', '=', 'na.nivel_academico_id')
            ->where('c.carrera_id', $proceso->alumno->id_carrera)
            ->value('na.codigo');

        $codigo = \App\Support\ArancelesGraduacion::derechosGrado($nivel, $proceso->modalidad->value);

        return DB::table('pago as p')
            ->join('detalle_pago as dp', 'p.pago_id', '=', 'dp.id_pago')
            ->join('concepto_pago as cp', 'dp.id_concepto', '=', 'cp.concepto_pago_id')
            ->where('p.id_alumno', $proceso->id_alumno)
            ->where('cp.codigo_concepto', $codigo)
            ->orderByDesc('p.pago_id')
            ->value('p.pago_id');
    }

    private function verificarCancelacionGrupo(ProcesoGraduacion $proceso): void
    {
        // Solo aplica si el alumno pertenece a un grupo (TRABAJO_GRADUACION)
        if (!$proceso->id_grupo || !$proceso->grupo) {
            return;
        }

        $grupo = $proceso->grupo;

        // Contar cuántos integrantes están graduados
        $totalIntegrantes = $grupo->procesos()->count();
        $graduados = $grupo->procesos()
            ->where('estado_proceso', EstadoProcesoGraduacion::GRADUADO->value)
            ->count();

        // Si todos los integrantes se graduaron → cancelar el grupo
        if ($totalIntegrantes > 0 && $graduados >= $totalIntegrantes) {
            $grupo->estado_grupo = 'CANCELADO';
            $grupo->save();
        }
    }
}