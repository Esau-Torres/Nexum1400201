<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Estudiante\registroController;
use App\Http\Controllers\Users\homeController;

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
});
