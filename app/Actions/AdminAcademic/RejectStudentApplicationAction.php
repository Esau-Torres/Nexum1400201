<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Alumnos;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Mail\StudentApplicationRejectedMail;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class RejectStudentApplicationAction
{
    /**
     * Rechaza una solicitud:
     *  - Elimina documentos físicos del storage
     *  - Elimina registro alumno (cascade elimina alumno_documentos)
     *  - Elimina usuario (cascade elimina usuario_rol)
     */
    public function execute(Alumnos $solicitud, array $data, User $admin): void
    {
        if ($solicitud->estado_carrera !== 'PENDIENTE') {
            throw new RuntimeException('Esta solicitud ya fue procesada.');
        }

        if (!$admin->hasRole('ADMIN_ACADEMICO')) {
            throw new RuntimeException('No tiene permisos para rechazar solicitudes.');
        }

        $nombre   = $solicitud->usuario->name;
        $email    = $solicitud->usuario->email;
        $motivo   = $data['motivo_rechazo'];

        DB::transaction(function () use ($solicitud, $data, $admin) {

            // 1. Eliminar archivos físicos del storage
            if ($solicitud->documentos) {
                $docs = $solicitud->documentos;
                foreach (['titulo_bachillerato', 'partida_nacimiento', 'fotografia_personal', 'constancia_paes'] as $campo) {
                    if ($docs->$campo && Storage::exists($docs->$campo)) {
                        Storage::delete($docs->$campo);
                    }
                }
                // También el disco público para foto
                if ($docs->fotografia_personal && Storage::disk('public')->exists($docs->fotografia_personal)) {
                    Storage::disk('public')->delete($docs->fotografia_personal);
                }
            }

            // 2. Guardar el motivo del rechazo (auditoría) antes de eliminar
            \Log::channel('daily')->info('Solicitud rechazada', [
                'alumno_id'      => $solicitud->alumno_id,
                'usuario_id'     => $solicitud->id_usuario,
                'email'          => $solicitud->usuario->email ?? null,
                'motivo'         => $data['motivo_rechazo'],
                'rechazado_por'  => $admin->id,
                'rechazado_en'   => now()->toDateTimeString(),
            ]);

            // 3. Eliminar usuario (cascade borra alumno y alumno_documentos por FK)
            $solicitud->usuario->delete();
        });

        Mail::to($email)->send(new StudentApplicationRejectedMail($nombre, $motivo));
    }
}