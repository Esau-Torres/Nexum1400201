<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Estudiante\registroController;
use App\Http\Controllers\Users\homeController;
use App\Http\Controllers\Adfinanciero\AdminfinancieroController;
use App\Http\Controllers\Cajero\CajeroController;

Route::view('/about', 'about')->name('about');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', [registroController::class, 'index'])->name('register');
//Route::get('/carreras', [registroController::class, 'carrera'])->name('carreras');

// Rutas protegidas por autenticación y verificación de correo electrónico
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/home', [homeController::class, 'index'])->name('home');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    // Ruta de prueba para ver el layout
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ============================================
    // RUTAS DE DOCENTE (COMENTADAS)
    // Ahora están en routes/modules/docentes.php
    // ============================================

    // Route::get('/home-docente', function () {
    //     return view('docente.home_docente');
    // })->name('home.docente');

    // Route::get('/prueba-notas', function () {
    //     return view('docente.prueba-notas');
    // })->name('prueba.notas');

    // ELIMINADA: esta era la causa del error "Undefined variable $materias"
    // Ahora la ruta /materias la maneja MateriasDocenteController en routes/modules/docentes.php
    // Route::get('/materias-docente', function () {
    //     return view('docente.materias_docente');
    // })->name('docente.materias');

    // Route::get('/notas/{materiaId}', function ($materiaId) {
    //     return view('docente.notas_materia', [
    //         'materiaId' => $materiaId
    //     ]);
    // })->name('docente.notas');

    // Temporal: se mantendrá hasta conectar el backend de asistencia
    Route::get('/asistencia/{seccionId}', function ($seccionId) {
        return view('docente.asistencia_materia', [
            'seccionId' => $seccionId,
            'materia' => [
                'nombre' => 'Materia de Prueba',
                'codigo' => 'TEST-001',
                'grupo' => 'A',
                'ciclo' => 'Ciclo 02-2026',
                'total_estudiantes' => 6,
            ],
            'estudiantes' => collect([
                (object) ['detalle_inscripcion_id' => 1, 'nombre' => 'María González', 'codigo' => 'STU-001'],
                (object) ['detalle_inscripcion_id' => 2, 'nombre' => 'Juan Ramírez', 'codigo' => 'STU-002'],
                (object) ['detalle_inscripcion_id' => 3, 'nombre' => 'Ana López', 'codigo' => 'STU-003'],
                (object) ['detalle_inscripcion_id' => 4, 'nombre' => 'Carlos Mendoza', 'codigo' => 'STU-004'],
                (object) ['detalle_inscripcion_id' => 5, 'nombre' => 'Lucía Fernández', 'codigo' => 'STU-005'],
                (object) ['detalle_inscripcion_id' => 6, 'nombre' => 'Pedro Sánchez', 'codigo' => 'STU-006'],
            ]),
        ]);
    })->name('docente.asistencia');

    // ============================================
    // RUTAS ADMIN FINANCIERO
    // ============================================
    Route::prefix('adfinanciero')->group(function () {

        Route::get('/conceptospagos', [AdminfinancieroController::class, 'conceptosIndex'])->name('adfinanciero.conceptospagos.index');
        Route::put('/conceptospagos/{id}', [AdminfinancieroController::class, 'update'])->name('adfinanciero.conceptospagos.update');
        Route::post('/conceptospagos', [AdminfinancieroController::class, 'store'])->name('adfinanciero.conceptospagos.store');

        Route::get('/ciclosarancel', [AdminfinancieroController::class, 'ciclosIndex'])->name('adfinanciero.ciclosarancel.index');
        Route::post('/ciclosarancel', [AdminfinancieroController::class, 'ciclosStore'])->name('adfinanciero.ciclosarancel.store');
        Route::put('/ciclosarancel/{id}', [AdminfinancieroController::class, 'ciclosUpdate'])->name('adfinanciero.ciclosarancel.update');

        Route::get('/adfinanciero/promociones', [AdminfinancieroController::class, 'promocionesIndex'])->name('adfinanciero.promociones.index');
        Route::post('/adfinanciero/promociones', [AdminfinancieroController::class, 'promocionesStore'])->name('adfinanciero.promociones.store');
        Route::put('/adfinanciero/promociones/{id}', [AdminfinancieroController::class, 'promocionesUpdate'])->name('adfinanciero.promociones.update');

        Route::get('/adfinanciero/cargosestudiante', [AdminfinancieroController::class, 'cargosIndex'])->name('adfinanciero.cargos.index');
        Route::post('/adfinanciero/cargosestudiante/batch', [AdminfinancieroController::class, 'cargosBatch'])->name('adfinanciero.cargos.batch');
        Route::put('/adfinanciero/cargosestudiante/{id}/estado', [AdminfinancieroController::class, 'cargosUpdateEstado'])->name('adfinanciero.cargos.updateEstado');

        Route::get('/adfinanciero/reportes', [AdminfinancieroController::class, 'reportesIndex'])->name('adfinanciero.reportes.index');

        Route::get('/reglas-pago', [\App\Http\Controllers\Reglaspago\ReglasController::class, 'index'])->name('reglas.index');
        Route::post('/adfinanciero/reglas-pago', [\App\Http\Controllers\Reglaspago\ReglasController::class, 'store'])->name('reglas.store');
        Route::put('/adfinanciero/reglas-pago/{id}', [\App\Http\Controllers\Reglaspago\ReglasController::class, 'update'])->name('reglas.update');
    });

    // ============================================
    // RUTAS CAJERO
    // ============================================
    Route::prefix('cajero')->group(function () {

        Route::get('/deuda', [CajeroController::class, 'deudaIndex'])->name('cajero.deuda.index');
        Route::put('/deuda/{id}/liquidar', [CajeroController::class, 'deudaLiquidar'])->name('cajero.deuda.liquidar');

        Route::get('/cajero/ventanilla', [CajeroController::class, 'indexVentanilla'])->name('cajero.ventanilla.index');
        Route::get('/ventanilla', [CajeroController::class, 'indexVentanilla'])->name('cajero.ventanilla.index');

        Route::get('/promociones', [CajeroController::class, 'promociones'])->name('cajero.promociones.index');

        Route::get('/cajero/pagos/nuevo', [CajeroController::class, 'crearPago'])->name('cajero.pagos.create');
        Route::post('/cajero/pagos/guardar', [CajeroController::class, 'guardarPago'])->name('cajero.pagos.store');
    });
});
