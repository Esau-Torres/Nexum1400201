<?php

    use Illuminate\Support\Facades\Route;
    use App\Models\Users\Role;
    use App\Http\Controllers\Users\CreateUserController;
    use App\Http\Controllers\Users\ModifyUserController;
    use App\Http\Controllers\Users\RoleManagementController;

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::middleware('role:'.Role::SUPER_ADMIN)->group(function () {
            
            //roles 
            Route::get('/roles', [RoleManagementController::class, 'index'])->name('roles.manageroles');
             // Actualizar estado de rol
            Route::put('/roles/status', [RoleManagementController::class, 'updateStatus'])->name('roles.update-status');

            // Previsualización de impacto (AJAX)
            Route::get('/roles/{roleId}/impact', [RoleManagementController::class, 'impact'])->whereNumber('roleId')->name('roles.impact');

            // Asignar roles a usuario
            Route::put('/users/{userId}/roles', [RoleManagementController::class, 'assignRoles'])->whereNumber('userId')->name('users.assign-roles');

            //  crear usuario siendo super admin
            Route::get('/createuser', [CreateUserController::class, 'index'])->name('createuser');
            Route::post('/createuser', [CreateUserController::class, 'store'])->name('users.store');

            // gestion de reglas academicas globales 
            Route::get('/manage-ruler', function () {
                return view('superadmin.manage-ruler');
            })->name('manage-ruler');

            // panel administrativo de usuarios del sistema
            Route::get('/panel-administrativo', [ModifyUserController::class, 'index'])->name('panel-administrativo');
            Route::put('/usuarios/{id}', [ModifyUserController::class, 'update'])->name('users.update');
        });
   
    });