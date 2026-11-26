<?php

use Illuminate\Support\Facades\Route;
use App\Models\Users\Role; // Importamos tu modelo de roles
use App\Http\Controllers\Directivo\DirectivoController;

// 1. Capa base: Debe tener sesión iniciada y correo verificado
Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('role:'.Role::DIRECTIVO)->prefix('directivo')->group(function () {

        // Dasboard de financiera
        Route::get('/dashboard', [DirectivoController::class, 'index'])->name('directivo.dashboard.index');
        // pdf para la dashboard
        Route::get('/dashboard/pdf', [DirectivoController::class, 'pdfIngresos'])->name('directivo.dashboard.pdf');


        //dashboard de matricula
        Route::get('/matricula', [DirectivoController::class, 'matricula'])->name('directivo.matricula.index');
        //pdf para la dashboard
        Route::get('/matricula/pdf', [DirectivoController::class, 'pdfMatricula'])->name('directivo.matricula.pdf');


        //dashboard de morosidad
        Route::get('/morosidad', [App\Http\Controllers\Directivo\DirectivoController::class, 'morosidad'])->name('directivo.morosidad.index');
        // pdf para la dashboard
        Route::get('/morosidad/pdf', [DirectivoController::class, 'pdfMorosidad'])->name('directivo.morosidad.pdf');

    });
});


