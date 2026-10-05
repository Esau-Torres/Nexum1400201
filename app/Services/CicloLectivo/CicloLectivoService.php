<?php

namespace App\Services;

use App\Models\Academico\CicloLectivo;
use App\Enums\EstadoCiclo;
use Illuminate\Support\Facades\Log;

class CicloLectivoService
{
    /**
     * Cierra automáticamente todos los ciclos ACTIVOS cuya fecha_fin ya pasó.
     * Retorna la cantidad de ciclos cerrados.
     */
    public function cerrarCiclosVencidos(): int
    {
        $ciclosVencidos = CicloLectivo::where('estado', EstadoCiclo::ACTIVO)
            ->whereDate('fecha_fin', '<', now()->toDateString())
            ->get();

        $cerrados = 0;

        foreach ($ciclosVencidos as $ciclo) {
            try {
                $ciclo->cerrar();
                $cerrados++;
                Log::info("[CicloLectivoService] Ciclo cerrado automáticamente: {$ciclo->nombre_completo}");
            } catch (\Exception $e) {
                Log::error("[CicloLectivoService] Error al cerrar ciclo {$ciclo->ciclo_lectivo_id}: {$e->getMessage()}");
            }
        }

        return $cerrados;
    }

    /**
     * Verifica si un ciclo puede ser modificado según su estado.
     * Un ciclo CERRADO no permite modificaciones.
     */
    public function puedeSerModificado(CicloLectivo $ciclo): bool
    {
        return $ciclo->estado !== EstadoCiclo::CERRADO;
    }
}