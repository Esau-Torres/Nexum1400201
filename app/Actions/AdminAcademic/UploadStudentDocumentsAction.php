<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\AlumnoDocumentos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadStudentDocumentsAction
{
    private const MAPA = [
        'titulo_bachillerato' => ['documentos/alumnos-ingreso', 'local'],
        'partida_nacimiento'  => ['documentos/alumnos-ingreso', 'local'],
        'fotografia_personal' => ['estudiantes/imagenes',       'public'],
        'constancia_paes'     => ['documentos/alumnos-ingreso', 'local'],
    ];

    public function execute(
        Alumnos $alumno,
        Request $request,
        string $estadoDocumentos = 'PENDIENTE',
        ?int $revisadoPor = null
    ): AlumnoDocumentos {
        $documentos = $alumno->documentos
            ?? new AlumnoDocumentos(['id_alumno' => $alumno->alumno_id]);

        foreach (self::MAPA as $campo => [$base, $disco]) {
            if (!$request->hasFile($campo)) {
                continue;
            }

            $carpeta = "{$base}/{$alumno->alumno_id}";

            // Borrar archivo anterior si existe
            $rutaAnterior = $documentos->$campo;
            if ($rutaAnterior && Storage::disk($disco)->exists($rutaAnterior)) {
                Storage::disk($disco)->delete($rutaAnterior);
            }

            // Guardar el nuevo
            $documentos->$campo = $request->file($campo)->store($carpeta, $disco);
        }

        $documentos->observaciones        = $request->input('observaciones', $documentos->observaciones);
        $documentos->revisado_por         = $revisadoPor ?? $documentos->revisado_por;
        $documentos->fecha_actualizacion  = now();
        $documentos->estado_documentos = $documentos->estaCompleto() ? 'APROBADO' : 'PENDIENTE';


        $documentos->save();

        return $documentos;
    }
}