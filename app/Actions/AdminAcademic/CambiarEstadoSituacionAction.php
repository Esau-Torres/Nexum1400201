<?php

namespace App\Actions\AdminAcademic;

use App\Enums\EstadoSituacion;
use App\Models\Estudiante\SituacionUsuario;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CambiarEstadoSituacionAction
{
    public function execute(
        SituacionUsuario $situacion,
        EstadoSituacion $nuevoEstado,
        User $admin,
        ?string $comentario = null
    ): SituacionUsuario {
        // 1. Validaciones de negocio
        if (!in_array($nuevoEstado, [EstadoSituacion::EN_OBSERVACION, EstadoSituacion::CERRADO], true)) {
            throw ValidationException::withMessages([
                'estado' => 'Solo puedes mover a "En observación" o "Cerrado".',
            ]);
        }

        if ($situacion->estado === EstadoSituacion::CERRADO) {
            throw ValidationException::withMessages([
                'estado' => 'Una situación cerrada no puede cambiar de estado.',
            ]);
        }

        $estadoAnterior = $situacion->estado;

        // 2. Persistir el cambio de estado (esto NUNCA debe fallar silenciosamente)
        $situacion->estado          = $nuevoEstado;
        $situacion->actualizado_por = $admin->id;
        $situacion->actualizado_en  = now();
        $situacion->save();

        // 3. Registrar en historial SOLO si la tabla existe (no crítico)
        $this->registrarHistorial($situacion, $estadoAnterior, $nuevoEstado, $admin, $comentario);

        return $situacion;
    }

    private function registrarHistorial(
        SituacionUsuario $situacion,
        EstadoSituacion $estadoAnterior,
        EstadoSituacion $nuevoEstado,
        User $admin,
        ?string $comentario
    ): void {
        try {
            if (!Schema::hasTable('situaciones_usuario_historial')) {
                return;
            }

            DB::table('situaciones_usuario_historial')->insert([
                'id_situacion'    => $situacion->id,
                'estado_anterior' => $estadoAnterior->value,
                'estado_nuevo'    => $nuevoEstado->value,
                'cambiado_por'    => $admin->id,
                'comentario'      => $comentario ? trim($comentario) : null,
                'cambiado_en'     => now(),
            ]);
        } catch (\Throwable $e) {
            // No bloquear el flujo por un fallo en el historial.
            report($e);
        }
    }
}