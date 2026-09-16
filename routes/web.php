<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Estudiante\registroController;

Route::view('/about', 'about')->name('about');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', [registroController::class, 'index'])->name('register');
Route::get('/carreras', [registroController::class, 'carrera'])->name('carreras');

// Rutas protegidas por autenticación y verificación de correo electrónico

Route::middleware(['auth', 'verified'])->group(function () {


    Route::get('/home', function () {
        return view('home');
    })->name('home');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    // Ruta de prueba para ver el layout
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
});
