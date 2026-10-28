<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\CicloLectivo\CicloLectivoService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// llamada de tarea programada

Schedule::call(function () {
    app(CicloLectivoService::class)->cerrarCiclosVencidos();
})->daily()->at('00:05')->name('cierre-automatico-ciclos');