<?php

namespace App\Jobs;

use App\Enums\EstadoCiclo;
use App\Models\Academico\CicloLectivo;
use App\Services\SuperAdmin\CalculadoraEstadoMateriaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class CerrarCicloLectivoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600; // 1 hora

    public function __construct(public int $cicloLectivoId) {}

    public function handle(): void
    {
        Artisan::call('ciclo:cerrar', [
            'ciclo_lectivo_id' => $this->cicloLectivoId,
        ]);
    }
}