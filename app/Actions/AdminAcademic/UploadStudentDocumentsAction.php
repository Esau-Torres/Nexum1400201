<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\AlumnoDocumentos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
        // ─────────────────────────────────────────────────────────
        // 1. Obtener documentos propios o heredar del origen
        // ─────────────────────────────────────────────────────────
        $documentos = $alumno->documentos;

        if (!$documentos) {
            $origenDocs = $alumno->documentos_heredados;

            $documentos = new AlumnoDocumentos([
                'id_alumno'           => $alumno->alumno_id,
                'titulo_bachillerato' => $origenDocs?->titulo_bachillerato,
                'partida_nacimiento'  => $origenDocs?->partida_nacimiento,
                'fotografia_personal' => $origenDocs?->fotografia_personal,
                'constancia_paes'     => $origenDocs?->constancia_paes,
                'estado_documentos'   => $estadoDocumentos,
                'observaciones'       => $origenDocs?->observaciones,
            ]);

            Log::info('Documentos heredados del expediente origen al crear fila propia', [
                'alumno_id'         => $alumno->alumno_id,
                'id_alumno_origen'  => $alumno->id_alumno_origen,
                'tenia_origen'      => $origenDocs !== null,
            ]);
        }

        // ─────────────────────────────────────────────────────────
        // 2. Procesar cada archivo subido
        // ─────────────────────────────────────────────────────────
        foreach (self::MAPA as $campo => [$base, $disco]) {
        if (!$request->hasFile($campo)) {
            continue;
        }

        $carpeta = "{$base}/{$alumno->alumno_id}";

        $rutaAnterior = $documentos->$campo;

        // Solo borrar si el archivo anterior está en la carpeta de ESTE expediente
        // (si es del origen, no lo tocamos)
        $esDelOrigen = $rutaAnterior
            && $alumno->id_alumno_origen
            && str_contains($rutaAnterior, "/{$alumno->id_alumno_origen}/");

        if ($rutaAnterior && !$esDelOrigen && Storage::disk($disco)->exists($rutaAnterior)) {
            Storage::disk($disco)->delete($rutaAnterior);
        }

        $documentos->$campo = $request->file($campo)->store($carpeta, $disco);
    }

        // ─────────────────────────────────────────────────────────
        // 3. Actualizar metadatos
        // ─────────────────────────────────────────────────────────
        $documentos->observaciones = $request->input(
            'observaciones',
            $documentos->observaciones
        );

        $documentos->revisado_por = $revisadoPor ?? $documentos->revisado_por;

        $documentos->fecha_actualizacion = now();

        // Recalcular estado según documentos completos
        $documentos->estado_documentos = $documentos->estaCompleto()
            ? 'APROBADO'
            : 'PENDIENTE';

        $documentos->save();

        return $documentos;
    }
}