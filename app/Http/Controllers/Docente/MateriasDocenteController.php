<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MateriasDocenteController extends Controller
{
    /**
     * Muestra las materias/secciones asignadas al docente autenticado.
     */
    public function index()
    {
        $user = Auth::user();
        $docente = $user->docente;

        if (!$docente) {
            abort(403, 'No tienes un perfil de docente asociado.');
        }

        // Obtener todas las secciones activas del docente con sus datos base
        $secciones = DB::table('seccion')
            ->join('materias', 'seccion.id_materia', '=', 'materias.materia_id')
            ->leftJoin('grupo', 'seccion.id_grupo', '=', 'grupo.grupo_id')
            ->where('seccion.id_docente', $docente->docente_id)
            ->where('seccion.estado', 'ACTIVA')
            ->select(
                'seccion.seccion_id',
                'materias.nombre as nombre_materia',
                'materias.codigo_materia',
                'grupo.nombre as nombre_grupo'
            )
            ->orderBy('seccion.seccion_id')
            ->get();

        // Procesar cada sección y obtener sus datos complementarios
        $seccionesConDatos = $secciones->map(function ($seccion) {
            // 1. Contar estudiantes inscritos (estado CURSANDO)
            $estudiantesInscritos = DB::table('detalle_inscripcion')
                ->where('id_seccion', $seccion->seccion_id)
                ->where('estado_materia', 'CURSANDO')
                ->count();

            // 2. Obtener los horarios con JOIN directo a la tabla horarios + aula
            $horariosConAula = DB::table('seccion_horario')
                ->join('horarios', 'seccion_horario.id_horario', '=', 'horarios.horario_id')
                ->leftJoin('aula', 'seccion_horario.id_aula', '=', 'aula.aula_id')
                ->where('seccion_horario.id_seccion', $seccion->seccion_id)
                ->select(
                    'horarios.dia',
                    'horarios.hora_inicio',
                    'horarios.hora_fin',
                    'aula.nombre_aula'
                )
                ->get();

            // 3. Generar texto de horario formateado: "LUNES 06:10-07:50 | MARTES 06:10-07:50"
            $horarioTexto = $horariosConAula
                ->map(function ($h) {
                    $dia = $h->dia ?? '—';
                    $inicio = substr($h->hora_inicio ?? '', 0, 5);
                    $fin = substr($h->hora_fin ?? '', 0, 5);
                    return "$dia $inicio-$fin";
                })
                ->implode(' | ');

            // 4. Obtener nombres de aulas únicas
            $aulaTexto = $horariosConAula
                ->pluck('nombre_aula')
                ->filter()
                ->unique()
                ->implode(' | ');

            return (object) [
                'id' => $seccion->seccion_id,
                'nombre' => $seccion->nombre_materia ?? 'Sin nombre',
                'codigo' => $seccion->codigo_materia ?? 'S/C',
                'grupo' => $seccion->nombre_grupo ?? '—',
                'descripcion' => $this->getDescripcionMateria($seccion->nombre_materia ?? ''),
                'estudiantes' => $estudiantesInscritos,
                'aula' => $aulaTexto ?: 'Sin aula asignada',
                'horario' => $horarioTexto ?: 'Sin horario asignado',
            ];
        });

        return view('docente.materias_docente', [
            'materias' => $seccionesConDatos,
        ]);
    }

    /**
     * Genera una descripción breve para la materia (temporal).
     */
    private function getDescripcionMateria(string $nombre): string
    {
        $descripciones = [
            'Matematica' => 'Fundamentos de matemáticas aplicadas a la resolución de problemas computacionales y análisis lógico.',
            'Algoritmos' => 'Diseño, análisis e implementación de algoritmos para la resolución eficiente de problemas.',
            'Informatica' => 'Introducción a los fundamentos de la informática, hardware, software y herramientas digitales.',
            'Seminario taller de competencias' => 'Desarrollo de competencias profesionales y habilidades blandas para el entorno laboral.',
            'Seminario de gramatica y ortografia' => 'Fortalecimiento de habilidades de redacción, gramática y ortografía académica.',
            'Programacion' => 'Fundamentos de programación, algoritmos y estructuras de datos básicas.',
            'Base de datos' => 'Diseño, implementación y administración de bases de datos relacionales con SQL.',
        ];

        foreach ($descripciones as $clave => $descripcion) {
            if (stripos($nombre, $clave) !== false) {
                return $descripcion;
            }
        }

        return 'Materia del plan de estudios vigente. Consulta el programa de la asignatura para más detalles.';
    }
}
