<?php

namespace App\Actions\Teacher;

use App\Models\Docente\Docentes;
use App\Models\Users\User;
use App\Rules\DocenteSyncRule;
use App\Rules\GeneradorCodigoDocente;
use Illuminate\Support\Facades\Log;

class SyncDocenteProfileAction
{
    public function __construct(
        private readonly DocenteSyncRule $rule,
    ) {}
    
    public function execute(User $user, bool $hasDocente): array
    {
        // 1. La Rule decide qué hacer
        $decision = $this->rule->evaluate($user, $hasDocente);

        // 2. La Action ejecuta la decisión
        return match ($decision['action']) {
            DocenteSyncRule::ACTION_CREATE     => $this->create($user, $decision['reason']),
            DocenteSyncRule::ACTION_REACTIVATE => $this->reactivate($decision['docente'], $decision['reason']),
            DocenteSyncRule::ACTION_DEACTIVATE => $this->deactivate($decision['docente'], $decision['reason']),
            DocenteSyncRule::ACTION_NOOP       => $this->noop($decision['docente'], $decision['reason']),
            default                             => throw new \RuntimeException(
                "Acción docente no reconocida: {$decision['action']}"
            ),
        };
    }

    /* ------------------------------------------------------------------
     * Handlers de cada acción
     * ------------------------------------------------------------------ */

    private function create(User $user, string $reason): array
    {
        $codigoEmpleado = GeneradorCodigoDocente::generar($user);
        $correo         = strtolower(trim($codigoEmpleado)) . '@uma.edu.sv';

        $docente = Docentes::create([
            'id_usuario'           => $user->id,
            'codigo_empleado'      => $codigoEmpleado,
            'correo_institucional' => $correo,
            'estado'               => 'ACTIVO',
        ]);

        Log::info('[NEXUM_RBAC] Perfil docente creado', [
            'user_id'    => $user->id,
            'docente_id' => $docente->docente_id,
            'reason'     => $reason,
            'actor_id'   => auth()->id(),
        ]);

        return [
            'action'     => DocenteSyncRule::ACTION_CREATE,
            'docente_id' => $docente->docente_id,
            'estado'     => $docente->estado,
            'reason'     => $reason,
        ];
    }

    private function reactivate(Docentes $docente, string $reason): array
    {
        $docente->update(['estado' => 'ACTIVO']);

        Log::info('[NEXUM_RBAC] Perfil docente reactivado', [
            'docente_id' => $docente->docente_id,
            'reason'     => $reason,
            'actor_id'   => auth()->id(),
        ]);

        return [
            'action'     => DocenteSyncRule::ACTION_REACTIVATE,
            'docente_id' => $docente->docente_id,
            'estado'     => 'ACTIVO',
            'reason'     => $reason,
        ];
    }

    private function deactivate(Docentes $docente, string $reason): array
    {
        $docente->update(['estado' => 'INACTIVO']);

        Log::info('[NEXUM_RBAC] Perfil docente inactivado', [
            'docente_id' => $docente->docente_id,
            'reason'     => $reason,
            'actor_id'   => auth()->id(),
        ]);

        return [
            'action'     => DocenteSyncRule::ACTION_DEACTIVATE,
            'docente_id' => $docente->docente_id,
            'estado'     => 'INACTIVO',
            'reason'     => $reason,
        ];
    }

    private function noop(?Docentes $docente, string $reason): array
    {
        return [
            'action'     => DocenteSyncRule::ACTION_NOOP,
            'docente_id' => $docente?->docente_id,
            'estado'     => $docente?->estado,
            'reason'     => $reason,
        ];
    }
}