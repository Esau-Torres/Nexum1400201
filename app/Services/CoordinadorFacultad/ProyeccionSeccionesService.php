<?php

namespace App\Services\CoordinadorFacultad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProyeccionSeccionesService
{
    public function execute(int $idCarrera, int $idCicloActual, bool $incluirRecursantes = false): array
    {
        \Log::info('=== Dashboard debug ===', [
            'id_carrera' => $idCarrera,
            'id_ciclo'   => $idCicloActual,
            'incluir_recursantes' => $incluirRecursantes,
        ]);

        // 1. Pensum activo de la carrera
        $pensum = DB::table('pensum')
            ->where('id_carrera', $idCarrera)
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$pensum) {
            \Log::warning('No hay pensum activo para carrera ' . $idCarrera);
            return [
                'resumen'    => $this->resumenVacio(),
                'proyeccion' => [],
            ];
        }

        \Log::info('Pensum encontrado', ['pensum_id' => $pensum->pensum_id]);

        // 2. Alumnos activos de la carrera
        $alumnos = DB::table('alumnos')
            ->where('id_carrera', $idCarrera)
            ->where('estado_carrera', 'ACTIVO')
            ->pluck('alumno_id')
            ->toArray();

        \Log::info('Alumnos activos', [
            'total' => count($alumnos),
            'ids'   => $alumnos,
        ]);

        if (empty($alumnos)) {
            \Log::warning('No hay alumnos activos para carrera ' . $idCarrera);
            return [
                'resumen'    => $this->resumenVacio(),
                'proyeccion' => [],
            ];
        }

        // 3. Ciclo actual de cada alumno
        $estadosValidos = ['CURSANDO', 'APROBADA'];
        if ($incluirRecursantes) {
            $estadosValidos[] = 'RECURSANDO';
        }

        $ciclosPorAlumno = DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'i.inscripcion_id', '=', 'di.id_inscripcion')
            ->join('seccion as s', 's.seccion_id', '=', 'di.id_seccion')
            ->join('pensum_materia as pm', 'pm.id_materia', '=', 's.id_materia')
            ->where('pm.id_pensum', $pensum->pensum_id)
            ->whereIn('i.id_alumno', $alumnos)
            ->where('i.id_ciclo_lectivo', $idCicloActual)
            ->whereIn('di.estado_materia', $estadosValidos)
            ->selectRaw('i.id_alumno, MAX(pm.ciclo_sugerido) as ciclo_actual')
            ->groupBy('i.id_alumno')
            ->pluck('ciclo_actual', 'id_alumno')
            ->toArray();

        \Log::info('Ciclos por alumno', $ciclosPorAlumno);

        // 4. Proyección de ciclo destino
        $cicloMaxPensum = DB::table('pensum_materia')
            ->where('id_pensum', $pensum->pensum_id)
            ->max('ciclo_sugerido');

        $alumnosPorCicloProyectado = [];
        $alumnosEgresan = 0;

        foreach ($alumnos as $idAlumno) {
            $cicloActual     = $ciclosPorAlumno[$idAlumno] ?? 0;
            $cicloProyectado = $cicloActual === 0 ? 1 : $cicloActual + 1;

            if ($cicloProyectado > $cicloMaxPensum) {
                $alumnosEgresan++;
                continue;
            }

            $alumnosPorCicloProyectado[$cicloProyectado][] = $idAlumno;
        }

        \Log::info('Proyección por ciclo', [
            'por_ciclo' => $alumnosPorCicloProyectado,
            'egresan'   => $alumnosEgresan,
        ]);

        // 5. Materias del pensum por ciclo proyectado
        $materiasPorCiclo = DB::table('pensum_materia as pm')
            ->join('materias as m', 'm.materia_id', '=', 'pm.id_materia')
            ->where('pm.id_pensum', $pensum->pensum_id)
            ->whereIn('pm.ciclo_sugerido', array_keys($alumnosPorCicloProyectado))
            ->where('pm.es_obligatoria', true)
            ->orderBy('pm.ciclo_sugerido')
            ->orderBy('m.codigo_materia')
            ->select('pm.ciclo_sugerido', 'm.materia_id', 'm.codigo_materia', 'm.nombre')
            ->get()
            ->groupBy('ciclo_sugerido');

        // 6. Capacidad máxima de aula
        $capacidadMaximaAula = DB::table('aula')
            ->where('activo', true)
            ->where('tipo', 'AULA')
            ->max('capacidad');

        if (!$capacidadMaximaAula) {
            $capacidadMaximaAula = DB::table('aula')
                ->where('activo', true)
                ->max('capacidad') ?? 40;
        }

        // 7. Armar proyección final
        $proyeccion = [];
        $totalSecciones = 0;

        foreach ($alumnosPorCicloProyectado as $ciclo => $alumnosCiclo) {
            $cantAlumnos   = count($alumnosCiclo);
            $materiasCiclo = [];

            foreach ($materiasPorCiclo[$ciclo] ?? [] as $materia) {
                $seccionesSugeridas = max(1, (int) ceil($cantAlumnos / $capacidadMaximaAula));
                $totalSecciones += $seccionesSugeridas;

                $materiasCiclo[] = [
                    'id_materia'          => $materia->materia_id,
                    'codigo'              => $materia->codigo_materia,
                    'nombre'              => $materia->nombre,
                    'alumnos'             => $cantAlumnos,
                    'secciones_sugeridas' => $seccionesSugeridas,
                ];
            }

            if (!empty($materiasCiclo)) {
                $proyeccion[] = [
                    'ciclo'    => (int) $ciclo,
                    'materias' => $materiasCiclo,
                ];
            }
        }

        // 8. Return FINAL con la estructura correcta
        return [
            'resumen' => [
                'alumnos_activos'       => count($alumnos),
                'alumnos_avanzan'       => count($alumnos) - $alumnosEgresan,
                'alumnos_egresan'       => $alumnosEgresan,
                'secciones_totales'     => $totalSecciones,
                'capacidad_aula_maxima' => $capacidadMaximaAula,
            ],
            'proyeccion' => $proyeccion,
        ];
    }

    private function resumenVacio(): array
    {
        return [
            'alumnos_activos'       => 0,
            'alumnos_avanzan'       => 0,
            'alumnos_egresan'       => 0,
            'secciones_totales'     => 0,
            'capacidad_aula_maxima' => 0,
        ];
    }
}