<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Estudiante\registroController;
use App\Http\Controllers\Users\homeController;
use App\Http\Controllers\Adfinanciero\AdminfinancieroController;
use App\Http\Controllers\Cajero\CajeroController;
use App\Http\Controllers\Directivo\DirectivoController;
use App\Http\Controllers\Estudiante\CarnetController;
use App\Http\Controllers\Estudiante\FotoPerfilController;

// ============================================
// RUTAS PÚBLICAS
// ============================================
Route::view('/about', 'about')->name('about');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', [registroController::class, 'index'])->name('register');

// ============================================
// RUTAS PROTEGIDAS (Auth + Verified)
// ============================================
Route::middleware(['auth', 'verified'])->group(function () {

    // Rutas comunes
    Route::get('/home', [homeController::class, 'index'])->name('home');

    // estudiante
    Route::get('/profile', function () { $user = auth()->user();
        if ($user->alumno) {
            $user->alumno->load('documentos', 'carrera.facultad');
        }
        return view('profile'); })->name('profile');
    Route::get('/mi-foto', [FotoPerfilController::class, 'show'])->name('estudiante.foto');
    Route::post('/profile/carnet', [CarnetController::class, 'store'])->name('estudiante.carnet.store');

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

        // Gestion Cargos estudiantiles
        Route::get('/adfinanciero/cargosestudiante', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'cargosIndex'])->name('adfinanciero.cargos.index');

        Route::post('/adfinanciero/cargosestudiante/batch', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'cargosBatch'])->name('adfinanciero.cargos.batch');

        Route::put('/adfinanciero/cargosestudiante/{id}/estado', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'cargosUpdateEstado'])->name('adfinanciero.cargos.updateEstado');

        //nuevas rutas
        // NUEVA RUTA: Vista para crear un cargo manual
        Route::get('/adfinanciero/cargosestudiante/nuevo', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'cargosCreate'])->name('adfinanciero.cargos.create');

        // NUEVA RUTA: Guardar el cargo y calcular el beneficio
        Route::post('/adfinanciero/cargosestudiante/guardar', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'cargosStore'])->name('adfinanciero.cargos.store');

        // Gestion de pagos y reportes
        // (Solo Lectura)
        Route::get('/adfinanciero/reportes', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'reportesIndex'])->name('adfinanciero.reportes.index');

        Route::get('/reglas-pago', [\App\Http\Controllers\Reglaspago\ReglasController::class, 'index'])->name('reglas.index');
        Route::post('/adfinanciero/reglas-pago', [\App\Http\Controllers\Reglaspago\ReglasController::class, 'store'])->name('reglas.store');
        Route::put('/adfinanciero/reglas-pago/{id}', [\App\Http\Controllers\Reglaspago\ReglasController::class, 'update'])->name('reglas.update');

        Route::post('/adfinanciero/cargosestudiante/pagar', [\App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'procesarPagoEnLinea'])->name('adfinanciero.cargos.pagar');

        Route::post('/adfinanciero/cargos/store-masivo', [App\Http\Controllers\Adfinanciero\AdminfinancieroController::class, 'cargosStoreMasivo'])->name('adfinanciero.cargos.storeMasivo');
    });

    // MODULO CAJERO
    Route::prefix('cajero')->group(function () {
        Route::get('/deuda', [CajeroController::class, 'deudaIndex'])->name('cajero.deuda.index');
        Route::put('/deuda/{id}/liquidar', [CajeroController::class, 'deudaLiquidar'])->name('cajero.deuda.liquidar');

        Route::get('/cajero/ventanilla', [CajeroController::class, 'indexVentanilla'])->name('cajero.ventanilla.index');
        //Route::get('/ventanilla', [CajeroController::class, 'indexVentanilla'])->name('cajero.ventanilla.index');

        Route::get('/promociones', [CajeroController::class, 'promociones'])->name('cajero.promociones.index');

        // En routes/cajero.php (o web.php)
        Route::get('/cajero/pagos/nuevo', [\App\Http\Controllers\Cajero\CajeroController::class, 'crearPago'])->name('cajero.pagos.create');
        Route::post('/cajero/pagos/guardar', [\App\Http\Controllers\Cajero\CajeroController::class, 'guardarPago'])->name('cajero.pagos.store');


        Route::get('/api/verificar-alumno/{carnet}', [App\Http\Controllers\Cajero\CajeroController::class, 'verificarAlumnoAPI'])->name('cajero.api.verificar');
    });

    require __DIR__.'/modules/directivo.php';
});
