<?php

use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::view('/about', 'about')->name('about');

// Rutas autenticadas
Route::middleware(['auth'])->group(function () {

    // Ruta /home con redirección inteligente
    Route::get('/home', function () {
        // Si es el docente (ID = 4), redirigir a home_docente
        if (auth()->id() === 4) {
            return redirect()->route('home.docente');
        }
        // Para otros usuarios, mostrar home normal
        return view('home');
    })->name('home');

    Route::get('/home-docente', function () {
        return view('auth.home_docente');
    })->name('home.docente');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
});
