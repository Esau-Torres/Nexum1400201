<?php

    use Illuminate\Support\Facades\Route;
    use App\Models\Users\Role;
    use App\Http\Controllers\AdminAcademico\ActiveStudentController;
    use App\Http\Controllers\AdminAcademico\StudentApplicationController;
    use App\Http\Controllers\AdminAcademico\StudentBenefitController;    

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::middleware('role:'.Role::ADMIN_ACADEMICO)->group(function () {

            // crear estudiante
            Route::get('/crearestudiante', [ActiveStudentController::class, 'index'])->name('create-student');
            // Procesar creación directa (activo)
            Route::post('/estudiantes/crear-activo', [ActiveStudentController::class, 'store'])->name('students.store');

            Route::get('/modificarestudiante', function () {return view('adminacademico.modify-student'); })->name('modify-student');
            
            // aprobar o rechazar solicitudes de usuarios que quieren crear cuentas de estudiante
            Route::get('/documentos/{alumno}/{campo}', [StudentApplicationController::class, 'downloadDocument'] )->name('documentos.download');
            
            Route::prefix('solicitudes')->name('admin.academico.solicitudes.')->group(function () {
                Route::get('/aprobaralumno', [StudentApplicationController::class, 'index'])->name('approve-student');
                Route::get('/{solicitud}',   [StudentApplicationController::class, 'show'])->name('show');
                Route::post('/{solicitud}/aprobar',  [StudentApplicationController::class, 'approve'])->name('approve');
                Route::delete('/{solicitud}/rechazar', [StudentApplicationController::class, 'reject'])->name('reject');
            });

            // aasignar o revocar beneficios de estudiante
            Route::prefix('beneficios')->name('admin-academico.beneficios.')->group(function () {
                Route::get('/modificarbeneficioestudiante', [StudentBenefitController::class, 'index'])->name('modify-benefit-student');
                Route::post('/{beneficio}/revocar',   [StudentBenefitController::class, 'revoke'])->name('revoke');
                Route::post('/{beneficio}/reasignar', [StudentBenefitController::class, 'reassign'])->name('reassign');
            });
        });
   
    });