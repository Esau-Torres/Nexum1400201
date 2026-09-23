<?php

    use Illuminate\Support\Facades\Route;
    use App\Models\Users\Role;

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::middleware('role:'.Role::DOCENTE)->group(function () {

            // Rutas de Docente
            Route::get('/home-docente', function () {
                return view('home.home_docente');
            })->name('home.docente');

            Route::get('/prueba-notas', function () {
                return view('docente.prueba-notas');
            })->name('prueba.notas');
        });
   
    });