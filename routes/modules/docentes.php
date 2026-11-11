<?php

    use Illuminate\Support\Facades\Route;
    use App\Models\Users\Role;

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::middleware('role:'.Role::DOCENTE)->group(function () {

            Route::get('/prueba-notas', function () {
                return view('docente.prueba-notas');
            })->name('prueba.notas');
        });
   
    });