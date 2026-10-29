<?php

    use Illuminate\Support\Facades\Route;
    use App\Models\Users\Role;
    use App\Http\Controllers\Estudiante\PensumController;
    use App\Http\Controllers\Estudiante\InscripcionController;

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::middleware('role:'.Role::ESTUDIANTE)->group(function () {
            Route::get('/alumno/pensum', [PensumController::class, 'index'])->name('pensum.index');
            Route::get('/alumno/pensum/pdf', [PensumController::class, 'descargarPdf'])->name('pensum.pdf');

            // logica de inscripciones de estudiante
            Route::get('/alumno/inscripcion', [InscripcionController::class, 'index'])->name('inscripcion.index');
            Route::post('/alumno/inscripcion', [InscripcionController::class, 'inscribir'])->name('inscripcion.store');
        });
    });