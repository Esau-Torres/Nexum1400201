<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\CicloLectivo\CicloLectivoService;
use App\Console\Commands\CerrarCicloLectivoCommand;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// llamada de tarea programada

Schedule::call(function () {
    app(CicloLectivoService::class)->cerrarCiclosVencidos();
})->daily()->at('00:05')->name('cierre-automatico-ciclos');

// Ejecutar cada día a las 02:00 AM, cerrando ciclos cuya fecha_fin ya pasó
Schedule::command('ciclo:cerrar-auto')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();