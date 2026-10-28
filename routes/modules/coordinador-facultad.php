<?php

use Illuminate\Support\Facades\Route;
use App\Models\Users\Role;
use App\Http\Controllers\CoordinadorFacultad\InfraestructuraHorarioController;
use App\Http\Controllers\CoordinadorFacultad\HorariosController;
use App\Http\Controllers\CoordinadorFacultad\SeccionesController;
use App\Http\Controllers\CoordinadorFacultad\DashboardController;


Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('role:' . Role::COORDINADOR_FACULTAD)->group(function () {

            // ---- Views ----
            Route::get('/infraestructura-horarios', [InfraestructuraHorarioController::class, 'index'])->name('infraestructura-horarios.index');
            Route::get('/Grupohorarios-horarios',   [HorariosController::class, 'index'])->name('horarios-grupohorario.index');

            // ---- Edificios ----
            Route::post('/edificios',               [InfraestructuraHorarioController::class, 'storeEdificio'])->name('edificios.store');
            Route::put('/edificios/{edificio}',     [InfraestructuraHorarioController::class, 'updateEdificio'])->name('edificios.update');
            Route::delete('/edificios/{edificio}',  [InfraestructuraHorarioController::class, 'deleteEdificio'])->name('edificios.destroy');

            // ---- Aulas ----
            Route::post('/aulas',          [InfraestructuraHorarioController::class, 'storeAula'])->name('aulas.store');
            Route::put('/aulas/{aula}',    [InfraestructuraHorarioController::class, 'updateAula'])->name('aulas.update');
            Route::delete('/aulas/{aula}', [InfraestructuraHorarioController::class, 'deleteAula'])->name('aulas.destroy');

            // ---- Horarios ----
            Route::post('/horarios',             [HorariosController::class, 'storeHorario'])->name('horarios.store');
            Route::put('/horarios/{horario}',    [HorariosController::class, 'updateHorario'])->name('horarios.update');
            Route::delete('/horarios/{horario}', [HorariosController::class, 'deleteHorario'])->name('horarios.destroy');

            // ---- Grupo de Horarios ----
            Route::post('/grupos',           [HorariosController::class, 'storeGrupo'])->name('grupos.store');
            Route::put('/grupos/{grupo}',    [HorariosController::class, 'updateGrupo'])->name('grupos.update');
            Route::delete('/grupos/{grupo}', [HorariosController::class, 'deleteGrupo'])->name('grupos.destroy');

            // ---- Secciones ----
            Route::get('/secciones-store',  [SeccionesController::class, 'index'])->name('secciones.index');
            Route::post('/secciones',       [SeccionesController::class, 'store'])->name('secciones.store');
            Route::put('/{seccion}',        [SeccionesController::class, 'update'])->name('secciones.update');
            Route::delete('/{seccion}',     [SeccionesController::class, 'destroy'])->name('secciones.destroy');

            // ---- Dashboard ----
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });
});