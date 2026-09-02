<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
});

Route::view('/about', 'about')->name('about');

// Ruta de prueba para ver el layout
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');