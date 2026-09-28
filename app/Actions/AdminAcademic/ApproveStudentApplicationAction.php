<?php

namespace App\Actions\AdminAcademic;

use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\AlumnoDocumentos;
use App\Models\Users\User;
use App\Actions\AdminAcademic\AssignStudentBenefitAction;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use App\Mail\NewAccountStudentCredentialsMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ApproveStudentApplicationAction
{
    public function __construct(
        protected AssignStudentBenefitAction $assignBenefit,
    ) {}

    /**
     * Aprueba una solicitud:
     *  - Activa la cuenta del usuario (estado = 1)
     *  - Cambia estado_carrera a ACTIVO
     *  - Marca documentos como APROBADOS + revisado_por
     *  - Opcionalmente asigna un beneficio
     */
    public function execute(Alumnos $solicitud, array $data, User $admin): void
    {
        if ($solicitud->estado_carrera !== 'PENDIENTE') {
            throw new RuntimeException('Esta solicitud ya fue procesada.');
        }

        if (!$admin->hasRole('ADMIN_ACADEMICO')) {
            throw new RuntimeException('No tiene permisos para aprobar solicitudes.');
        }

        if (empty($data['id_regional_activo'])) {
            throw new RuntimeException('Debe seleccionar una sede antes de aprobar la solicitud.');
        }

        DB::transaction(function () use ($solicitud, $data, $admin) {

            // 1. Activar cuenta del usuario
            $solicitud->usuario->update([
                'estado' => 1, 
                'id_regional_activo' => $data['id_regional_activo'],
            ]);

            // 2. Activar perfil del alumno
            $solicitud->update(['estado_carrera' => 'ACTIVO']);

            // 3. Aprobar documentos
            if ($solicitud->documentos) {
                $solicitud->documentos->update([
                    'estado_documentos' => 'APROBADO',
                    'revisado_por'      => $admin->id,
                    'fecha_actualizacion'=> now(),
                ]);
            }

            // 4. Beneficio (opcional es decir puede ser null)
            if (!empty($data['aplicar_beneficio']) && !empty($data['tipo_beneficio'])) {
                $this->assignBenefit->execute(
                    alumno: $solicitud,
                    data: [
                        'id_ciclo_lectivo'       => $data['id_ciclo_lectivo'],
                        'tipo_beneficio'         => $data['tipo_beneficio'],
                        'nombre_convenio'        => $data['nombre_convenio'] ?? 'Sin convenio',
                        'porcentaje_estudiante'  => $data['porcentaje_estudiante']  ?? null,
                        'porcentaje_universidad' => $data['porcentaje_universidad'] ?? null,
                        'monto_fijo_cuota'       => $data['monto_fijo_cuota']       ?? null,
                        'resolucion_academica'   => $data['resolucion_academica']   ?? null,
                        'incluye_matricula'      => (bool) ($data['incluye_matricula']      ?? false),
                        'incluye_laboratorio'    => (bool) ($data['incluye_laboratorio']    ?? false),
                        'incluye_derechos_grado' => (bool) ($data['incluye_derechos_grado'] ?? false),
                    ],
                    asignadoPor: $admin,
                );
            }

            \Log::info("La solicitud del estudiante user id: {$solicitud->usuario->id} fue aprobada");
        });

        // (si falla el email, no se revierte la aprobación)
        $solicitud->refresh()->load(['usuario.regionalactivo', 'carrera.facultad']);
        Mail::to($solicitud->usuario->email)->send(new NewAccountStudentCredentialsMail($solicitud));

    }
}