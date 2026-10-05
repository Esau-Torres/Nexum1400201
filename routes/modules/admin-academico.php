<?php

    use Illuminate\Support\Facades\Route;
    use App\Models\Users\Role;
    use App\Http\Controllers\AdminAcademico\ActiveStudentController;
    use App\Http\Controllers\AdminAcademico\StudentApplicationController;
    use App\Http\Controllers\AdminAcademico\StudentBenefitController;  
    use App\Http\Controllers\AdminAcademico\StudentModifyController;    
    use App\Http\Controllers\AdminAcademico\CicloLectivoController;    

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::middleware('role:'.Role::ADMIN_ACADEMICO)->group(function () {

            // crear estudiante
            Route::get('/crearestudiante', [ActiveStudentController::class, 'index'])->name('create-student');
            // Procesar creación directa (activo)
            Route::post('/estudiantes/crear-activo', [ActiveStudentController::class, 'store'])->name('students.store');

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
                Route::post('/{beneficio}/revocar',         [StudentBenefitController::class, 'revoke'])->name('revoke');
                Route::post('/{beneficio}/reasignar',       [StudentBenefitController::class, 'reassign'])->name('reassign');
            });

            Route::prefix('alumnos')->name('alumnos.')->group(function () {
                Route::get('/modifcaralumno',                 [StudentModifyController::class, 'index'])->name('modify-student');
                Route::put('/{alumno}',                       [StudentModifyController::class, 'update'])->name('update');
                Route::delete('/{alumno}/documentos/{campo}', [StudentModifyController::class, 'deleteDocument'])->name('documentos.delete');
                Route::post('/{alumno}/documentos',           [StudentModifyController::class, 'uploadDocuments'])->name('documentos.upload');
            });
            // gestionamiento de pensum, materias y ciclo lectivo
            Route::prefix('pensum')->name('pensum.')->group(function () {
                Route::get('/pensum',  function () { return view('adminacademico.pensum'); })->name('pensum');
            });
            Route::prefix('materias')->name('materias.')->group(function () {
                Route::get('/materia', function () { return view('adminacademico.materias'); })->name('materias');
            });

            Route::prefix('ciclolectivo')->name('ciclolectivo.')->group(function () {
                Route::get('/ciclo-lectivo', [CicloLectivoController::class, 'index'])->name('ciclo');
                Route::post('/ciclo-lectivo-store', [CicloLectivoController::class, 'store'])->name('store');
                Route::put('/{ciclo}', [CicloLectivoController::class, 'update'])->name('update');
                Route::patch('/cambiodeestado/{ciclo}', [CicloLectivoController::class, 'changeStatus'])->name('changeStatus');
            });

            Route::prefix('facultades')->name('facultades.')->group(function () {
                Route::get('/facultad', function () { return view('adminacademico.facultad'); })->name('facultad');
            });
        });
    });