<?php

namespace App\Actions\Student;

use App\Mail\DarDeBajaMail;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\DetalleInscripcion;
use App\Models\Users\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RetirarMateriaAction
{
    public function __construct(
        private ValidarPuedeRetirarMateriaAction $validarAction,
    ) {}

    public function execute(
        Alumnos $alumno,
        DetalleInscripcion $detalle,
        User $usuario,
        ?string $motivo = null
    ): DetalleInscripcion {
        // 1. Revalidar TODO en backend (defensa en profundidad)
        $validacion = $this->validarAction->execute($alumno, $detalle);

        if (!$validacion['puede']) {
            throw ValidationException::withMessages([
                'retiro' => $validacion['bloqueadores'],
            ]);
        }

        return DB::transaction(function () use ($detalle, $alumno, $usuario, $motivo) {
            // 2. Actualizar el detalle
            $detalle->estado_materia = 'RETIRADA';
            $detalle->fecha_retiro   = now();
            $detalle->promedio_final = null; // ← NULL, no 0.00
            $detalle->save();

            // 3. Registrar en historial de retiros (opcional)
            try {
                DB::table('retiros_materia')->insert([
                    'id_detalle_inscripcion' => $detalle->detalle_inscripcion_id,
                    'id_alumno'              => $alumno->alumno_id,
                    'id_concepto_pago'       => DB::table('concepto_pago')
                        ->where('codigo_concepto', 'TRA-011')
                        ->value('concepto_pago_id'),
                    'motivo'                 => $motivo ? trim($motivo) : null,
                    'retirado_en'            => now(),
                    'procesado_por'          => $usuario->id,
                ]);
            } catch (\Throwable $e) {
                report($e);
            }

            return $detalle->fresh();
        });

        $this->enviarCorreo($alumno, $detalle, $usuario, $motivo);

        return $detalle;
    }
    
     private function enviarCorreo(
        Alumnos $alumno,
        DetalleInscripcion $detalle,
        User $usuario,
        ?string $motivo
    ): void {
        try {
            $detalle->loadMissing([
                'seccion.materia',
                'seccion.docente.user',
                'seccion.horarios.horario',
                'seccion.horarios.aula',
                'inscripcion.cicloLectivo',
            ]);

            $seccion = $detalle->seccion;
            $materia = $seccion?->materia;
            $ciclo   = $detalle->inscripcion?->cicloLectivo;

            if (!$materia || !$ciclo) {
                Log::warning('Retiro: no se pudo cargar la materia o ciclo para enviar correo.', [
                    'detalle_id' => $detalle->detalle_inscripcion_id,
                ]);
                return;
            }

            $horarios = $seccion->horarios
                ->map(fn ($sh) => [
                    'dia'  => $sh->horario?->dia?->value ?? (string) $sh->horario?->dia,
                    'ini'  => substr((string) $sh->horario?->hora_inicio, 0, 5),
                    'fin'  => substr((string) $sh->horario?->hora_fin, 0, 5),
                    'aula' => $sh->aula?->nombre ?? 'Sin aula',
                ])
                ->filter()
                ->values()
                ->toArray();

            Mail::to($usuario->email)->send(new DarDeBajaMail(
                user:     $usuario,
                alumno:   $alumno,
                detalle:  $detalle,
                materia:  $materia->nombre,
                ciclo:    $ciclo->nombre_ciclo,
                docente:  $seccion->docente?->user?->name ?? 'Sin asignar',
                grupo:    $seccion->nombre_grupo,
                horarios: $horarios,
                motivo:   $motivo,
            ));
        } catch (\Throwable $e) {
            report($e);
            Log::error('Error al enviar correo de retiro de materia', [
                'detalle_id' => $detalle->detalle_inscripcion_id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}