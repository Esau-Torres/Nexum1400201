<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\CoordinadorFacultad\Seccion;
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

        // Obtener las secciones del docente con sus relaciones
        $secciones = Seccion::with(['materia', 'grupo', 'horarios.aula'])
            ->where('id_docente', $docente->docente_id)
            ->where('estado', 'ACTIVA')
            ->orderBy('seccion_id')
            ->get();

        // Contar estudiantes inscritos por sección
        $seccionesConDatos = $secciones->map(function ($seccion) {
            // Contar estudiantes inscritos en esta sección (estado CURSANDO)
            $estudiantesInscritos = DB::table('detalle_inscripcion')
                ->where('id_seccion', $seccion->seccion_id)
                ->where('estado_materia', 'CURSANDO')
                ->count();

            // Obtener horario resumido
            $horarioTexto = $seccion->horarios
                ->map(fn($h) => ($h->dia ?? '') . ' ' . substr($h->hora_inicio ?? '', 0, 5) . '-' . substr($h->hora_fin ?? '', 0, 5))
                ->implode(' | ');

            // Obtener aula
            $aulaTexto = $seccion->horarios
                ->pluck('aula.nombre_aula')
                ->filter()
                ->unique()
                ->implode(' | ');

            return (object) [
                'id' => $seccion->seccion_id,
                'nombre' => $seccion->materia->nombre ?? 'Sin nombre',
                'codigo' => $seccion->materia->codigo_materia ?? 'S/C',
                'grupo' => $seccion->nombre_grupo,
                'descripcion' => $this->getDescripcionMateria($seccion->materia->nombre ?? ''),
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
            'Matematica 1' => 'Fundamentos de matemáticas aplicadas a la resolución de problemas computacionales y análisis lógico.',
            'Algoritmos' => 'Diseño, análisis e implementación de algoritmos para la resolución eficiente de problemas.',
            'Informatica 1' => 'Introducción a los fundamentos de la informática, hardware, software y herramientas digitales.',
            'Seminario taller de competencias' => 'Desarrollo de competencias profesionales y habilidades blandas para el entorno laboral.',
            'Seminario de gramatica y ortografia' => 'Fortalecimiento de habilidades de redacción, gramática y ortografía académica.',
            'Programacion 1' => 'Fundamentos de programación, algoritmos y estructuras de datos básicas.',
            'Base de datos 1' => 'Diseño, implementación y administración de bases de datos relacionales con SQL.',
        ];

        foreach ($descripciones as $clave => $descripcion) {
            if (stripos($nombre, $clave) !== false) {
                return $descripcion;
            }
        }

        return 'Materia del plan de estudios vigente. Consulta el programa de la asignatura para más detalles.';
    }
}
