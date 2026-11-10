<?php

namespace App\Actions\Student;

use App\Enums\EstadoInscripcion;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\DetalleInscripcion;
use App\Models\Estudiante\Inscripcion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InscribirMateriaAction
{
    public function execute(Alumnos $alumno, Seccion $seccion, int $cicloLectivoId): DetalleInscripcion
    {
        return DB::transaction(function () use ($alumno, $seccion, $cicloLectivoId) {
            // 1. Inscripción del ciclo (crear o reutilizar)
            $inscripcion = Inscripcion::firstOrCreate(
                [
                    'id_alumno'        => $alumno->alumno_id,
                    'id_ciclo_lectivo' => $cicloLectivoId,
                ],
                [
                    'fecha_inscripcion'  => now()->toDateString(),
                    'estado_inscripcion' => EstadoInscripcion::ACTIVA->value,
                ]
            );

            // 2. Verificar que no exista ya ese detalle
            $existe = DetalleInscripcion::where('id_inscripcion', $inscripcion->inscripcion_id)
                ->where('id_seccion', $seccion->seccion_id)
                ->exists();

            if ($existe) {
                throw ValidationException::withMessages([
                    'seccion' => 'Ya estás inscrito en esta sección.',
                ]);
            }

            // 3. Número de matrícula
            $numeroMatricula = DetalleInscripcion::where('id_inscripcion', $inscripcion->inscripcion_id)
                ->max('numero_matricula') + 1;

            // 4. Crear detalle
            return DetalleInscripcion::create([
                'id_inscripcion'   => $inscripcion->inscripcion_id,
                'id_seccion'       => $seccion->seccion_id,
                'numero_matricula' => $numeroMatricula,
                'estado_materia'   => 'CURSANDO',
                'es_sobrecupo'     => $seccion->cupo_disponible <= 0,
            ]);
        });
    }
}