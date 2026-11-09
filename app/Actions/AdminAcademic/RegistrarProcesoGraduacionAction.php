<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoProcesoGraduacion;
use App\Enums\ModalidadGraduacion;
use App\Enums\TamanoGrupo;
use App\Models\Academico\GrupoGraduacion;
use App\Models\Academico\ProcesoGraduacion;
use App\Models\Estudiante\Alumnos;
use App\Models\Users\User;
use App\Rules\Graduacion\ArancelAperturaPagadoRule;
use App\Rules\Graduacion\EgresadoRule;
use App\Rules\Graduacion\ModalidadPermitidaRule;
use App\Rules\Graduacion\SinProcesoActivoRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RegistrarProcesoGraduacionAction
{
    public function execute(Alumnos $alumno, array $datos, User $usuario ): ProcesoGraduacion {

        $tieneActivo = ProcesoGraduacion::where('id_alumno', $alumno->alumno_id)
        ->where('es_activo', true)
        ->exists();

        if ($tieneActivo) {
            throw ValidationException::withMessages([
                'proceso' => 'El alumno ya tiene un proceso de graduación activo.',
            ]);
        }
        
        $modalidad = ModalidadGraduacion::from($datos['modalidad']);

        $validator = Validator::make(
            ['modalidad' => $datos['modalidad']],
            [
                'modalidad' => [
                    new SinProcesoActivoRule($alumno),
                    new EgresadoRule($alumno),
                    new ModalidadPermitidaRule($alumno, $modalidad),
                    new ArancelAperturaPagadoRule($alumno),
                ],
            ]
        );

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'graduacion' => $validator->errors()->all(),
            ]);
        }

        return DB::transaction(function () use ($alumno, $datos, $modalidad) {
            // 1. Crear el grupo si es TRABAJO_GRADUACION y no se une a uno existente
            $idGrupo = null;

            if ($modalidad === ModalidadGraduacion::TRABAJO_GRADUACION) {
                if (!empty($datos['id_grupo'])) {
                    // Unirse a un grupo existente
                    $grupo = GrupoGraduacion::findOrFail($datos['id_grupo']);

                    // Validar que el grupo no tenga ya 3 integrantes
                    $integrantes = $grupo->procesos()->count();
                    if ($integrantes >= 3) {
                        throw ValidationException::withMessages([
                            'grupo' => 'El grupo ya tiene 3 integrantes (límite máximo).',
                        ]);
                    }

                    $idGrupo = $grupo->grupo_id;
                } else {
                    // Crear nuevo grupo (individual, pareja o grupo de 3)
                    $grupo = GrupoGraduacion::create([
                        'codigo_grupo'    => $this->generarCodigoGrupo(),
                        'titulo_tema'     => trim($datos['titulo_tema']),
                        'id_docente_asesor'=> $datos['id_docente_asesor'] ?? null,
                        'estado_grupo'    => 'ACTIVO',
                        'created_at'      => now(),
                    ]);

                    $idGrupo = $grupo->grupo_id;
                }
            }

            // 2. Crear el proceso
            return ProcesoGraduacion::create([
                'id_alumno'              => $alumno->alumno_id,
                'modalidad'              => $modalidad->value,
                'id_grupo'               => $idGrupo,
                'id_pago_derechos_grado' => null,
                'nombre_curso'           => $modalidad === ModalidadGraduacion::PRE_ESPECIALIZACION
                                            ? trim($datos['titulo_tema'])
                                            : null,
                'nota_defensa'           => null,
                'estado_proceso'         => EstadoProcesoGraduacion::INSCRITO->value,
                'fecha_defensa'          => null,
                'fecha_graduacion'       => null,
            ]);
        });
    }

    private function generarCodigoGrupo(): string
    {
        $ultimo = GrupoGraduacion::orderByDesc('grupo_id')->value('codigo_grupo');
        $numero = $ultimo ? ((int) substr($ultimo, 3)) + 1 : 1;
        return 'GRP-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
}