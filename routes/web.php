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

// Rutas de Docente
    Route::get('/home-docente', function () {
        return view('docente.home_docente');
    })->name('home.docente');

    Route::get('/prueba-notas', function () {
        return view('docente.prueba-notas');
    })->name('prueba.notas');

    Route::get('/materias-docente', function () {
        return view('docente.materias_docente');
    })->name('docente.materias');

    Route::get('/notas/{materiaId}', function ($materiaId) {
        // Futuro: Aquí buscarás la materia en la BD por ID
        // y pasarás los datos a la vista
        return view('docente.notas_materia', [
            'materiaId' => $materiaId
        ]);
    })->name('docente.notas');

    Route::get('/asistencia/{materiaId}', function ($materiaId) {
        // Futuro: Aquí buscarás la materia en la BD por ID
        // y pasarás los datos a la vista
        return view('docente.asistencia_materia', [
            'materiaId' => $materiaId
        ]);
    })->name('docente.asistencia');

    Route::prefix('adfinanciero')->group(function () {

        // URL: nexum.test/adfinanciero/conceptospagos
        Route::get('/conceptospagos', [AdminfinancieroController::class, 'conceptosIndex'])->name('adfinanciero.conceptospagos.index');

        //modal de actualizacion de editar en concepto pago
        Route::put('/conceptospagos/{id}', [AdminfinancieroController::class, 'update'])->name('adfinanciero.conceptospagos.update');

        //modal para nuevo concepto
        Route::post('/conceptospagos', [AdminfinancieroController::class, 'store'])->name('adfinanciero.conceptospagos.store');

        //rutas para ciclos y aranceles
        Route::get('/ciclosarancel', [AdminfinancieroController::class, 'ciclosIndex'])->name('adfinanciero.ciclosarancel.index');

        Route::post('/ciclosarancel', [AdminfinancieroController::class, 'ciclosStore'])->name('adfinanciero.ciclosarancel.store');

        Route::put('/ciclosarancel/{id}', [AdminfinancieroController::class, 'ciclosUpdate'])->name('adfinanciero.ciclosarancel.update');

        // Gestión de Promociones
        Route::get('/adfinanciero/promociones', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'promocionesIndex'])->name('adfinanciero.promociones.index');

        Route::post('/adfinanciero/promociones', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'promocionesStore'])->name('adfinanciero.promociones.store');

        Route::put('/adfinanciero/promociones/{id}', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'promocionesUpdate'])->name('adfinanciero.promociones.update');

        // Gestion Cargos estudiantiles
        Route::get('/adfinanciero/cargosestudiante', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'cargosIndex'])->name('adfinanciero.cargos.index');

        Route::post('/adfinanciero/cargosestudiante/batch', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'cargosBatch'])->name('adfinanciero.cargos.batch');

        Route::put('/adfinanciero/cargosestudiante/{id}/estado', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'cargosUpdateEstado'])->name('adfinanciero.cargos.updateEstado');

        // Gestion de pagos y reportes
        // Dashboard Financiero y Auditoría (Solo Lectura)
        Route::get('/adfinanciero/reportes', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'reportesIndex'])->name('adfinanciero.reportes.index');

        // Reglas pagos
        Route::get('/reglas-pago', [App\Http\Controllers\Reglaspago\ReglasController::class, 'index'])->name('reglas.index');
        // Ruta para guardar (Crear)
        Route::post('/adfinanciero/reglas-pago', [\App\Http\Controllers\Reglaspago\ReglasController::class, 'store'])->name('reglas.store');
        // Ruta para actualizar (Editar)
        Route::put('/adfinanciero/reglas-pago/{id}', [\App\Http\Controllers\Reglaspago\ReglasController::class, 'update'])->name('reglas.update');
    });


    Route::prefix('cajero')->group(function () {

        Route::get('/deuda', [App\Http\Controllers\Cajero\CajeroController::class, 'deudaIndex'])->name('cajero.deuda.index');
        Route::put('/deuda/{id}/liquidar', [App\Http\Controllers\Cajero\CajeroController::class, 'deudaLiquidar'])->name('cajero.deuda.liquidar');

        //consulta de aranceles para el cajero
        Route::get('/cajero/ventanilla', [\App\Http\Controllers\Cajero\CajeroController::class, 'indexVentanilla'])->name('cajero.ventanilla.index');
        Route::get('/ventanilla', [\App\Http\Controllers\Cajero\CajeroController::class, 'indexVentanilla'])->name('cajero.ventanilla.index');

        //promociones cajero
        Route::get('/promociones', [\App\Http\Controllers\Cajero\CajeroController::class, 'promociones'])->name('cajero.promociones.index');

        // En routes/cajero.php (o web.php)
        Route::get('/cajero/pagos/nuevo', [\App\Http\Controllers\Cajero\CajeroController::class, 'crearPago'])->name('cajero.pagos.create');
        Route::post('/cajero/pagos/guardar', [\App\Http\Controllers\Cajero\CajeroController::class, 'guardarPago'])->name('cajero.pagos.store');
    });


});
