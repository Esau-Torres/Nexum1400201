<?php

    use Illuminate\Support\Facades\Route;
    use App\Models\Users\Role;
    use App\Http\Controllers\Users\CreateUserController;
    use App\Http\Controllers\Users\ModifyUserController;
    use App\Http\Controllers\Users\CreateRolController;

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::middleware('role:'.Role::SUPER_ADMIN)->group(function () {
            
            Route::get('create-rol', [CreateRolController::class, 'index'])->name('create-rol');

            Route::get('/createuser', [CreateUserController::class, 'index'])->name('createuser');
            Route::post('/createuser', [CreateUserController::class, 'store'])->name('users.store');

            Route::get('/manage-ruler', function () {
                return view('superadmin.manage-ruler');
            })->name('manage-ruler');

            Route::get('/panel-administrativo', [ModifyUserController::class, 'index'])->name('panel-administrativo');
            Route::put('/usuarios/{id}', [ModifyUserController::class, 'update'])->name('users.update');
        });
   
    });