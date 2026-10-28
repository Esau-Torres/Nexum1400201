<?php

use App\Http\Controllers\Docente\AsistenciaMateriaController;
use App\Http\Controllers\Docente\MateriasDocenteController;
use App\Http\Controllers\Docente\NotasMateriaController;
use App\Models\Users\Role;
use Illuminate\Support\Facades\Route;

// El bootstrap/app.php automáticamente aplicará el prefijo URL '/docente' 
// y el prefijo de nombre 'docente.' a todo lo que esté dentro de este archivo.

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('role:' . Role::DOCENTE)->group(function () {

        Route::get('/prueba-notas', function () {
            return view('docente.prueba-notas');
        })->name('prueba.notas'); // Nombre final: docente.prueba.notas

        Route::get('/home', function () {
            return view('docente.home_docente');
        })->name('home'); // Nombre final: docente.home

        Route::get('/materias', [MateriasDocenteController::class, 'index'])
            ->name('materias'); // Nombre final: docente.materias

        // ============================================
        // RUTAS DE NOTAS
        // ============================================
        Route::get('/notas/{seccionId}', [NotasMateriaController::class, 'index'])
            ->whereNumber('seccionId')
            ->name('notas'); // Nombre final: docente.notas

        Route::post('/notas/{seccionId}/guardar', [NotasMateriaController::class, 'store'])
            ->whereNumber('seccionId')
            ->name('notas.store'); // Nombre final: docente.notas.store

        // ============================================
        // RUTAS DE ASISTENCIA
        // ============================================
        Route::get('/asistencia/{seccionId}', [AsistenciaMateriaController::class, 'index'])
            ->whereNumber('seccionId')
            ->name('asistencia'); // Nombre final: docente.asistencia

        Route::post('/asistencia/{seccionId}/guardar', [AsistenciaMateriaController::class, 'store'])
            ->whereNumber('seccionId')
            ->name('asistencia.store'); // Nombre final: docente.asistencia.store
    });
});
