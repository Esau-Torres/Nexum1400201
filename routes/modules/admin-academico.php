<?php

    use Illuminate\Support\Facades\Route;
    use App\Models\Users\Role;
    use App\Http\Controllers\AdminAcademico\ActiveStudentController;
    use App\Http\Controllers\AdminAcademico\StudentApplicationController;
    use App\Http\Controllers\AdminAcademico\StudentBenefitController;  
    use App\Http\Controllers\AdminAcademico\StudentModifyController;    
    use App\Http\Controllers\AdminAcademico\CicloLectivoController;    
    use App\Http\Controllers\AdminAcademico\MateriaController;
    use App\Http\Controllers\AdminAcademico\PensumController;
    use App\Http\Controllers\AdminAcademico\FacultadCarreraController;
    use App\Http\Controllers\AdminAcademico\SituacionGestionController;
    use App\Http\Controllers\AdminAcademico\EvaluacionDocenteController;
    use App\Http\Controllers\AdminAcademico\ReglaBeneficioController;
    use App\Http\Controllers\AdminAcademico\RequisitosEgresoController;
    use App\Http\Controllers\AdminAcademico\ProcesoGraduacionController;

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::middleware('role:'.Role::ADMIN_ACADEMICO)->group(function () {

            // crear estudiante
            Route::get('/crearestudiante', [ActiveStudentController::class, 'index'])->name('create-student');
            // Procesar creación directa (activo)
            Route::post('/estudiantes/crear-activo', [ActiveStudentController::class, 'store'])->name('students.store');

            // crear estudiante masivo
            Route::get('/crear-estudiante/masivo', [ActiveStudentController::class, 'massiveForm'])->name('create-student.massive');
            Route::post('/crear-estudiante/masivo', [ActiveStudentController::class, 'massiveStore'])->name('create-student.massive.store');
            Route::get('/crear-estudiante/masivo/plantilla', [ActiveStudentController::class, 'massiveTemplate'])->name('create-student.massive.template');

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
                Route::post('/{alumno}/beneficio',            [StudentModifyController::class, 'assignBenefit'])->name('beneficios.assign');
            });
            
            // gestionamiento de pensum, materias y ciclo lectivo

            Route::prefix('materias')->name('materias.')->group(function () {
                Route::get('/', [MateriaController::class, 'index'])->name('materias');
                Route::post('/materia', [MateriaController::class, 'store'])->name('store');
                Route::put('/materia/{materia}', [MateriaController::class, 'update'])->name('update');
            });

            Route::prefix('ciclolectivo')->name('ciclolectivo.')->group(function () {
                Route::get('/ciclo-lectivo', [CicloLectivoController::class, 'index'])->name('ciclo');
                Route::post('/ciclo-lectivo-store', [CicloLectivoController::class, 'store'])->name('store');
                Route::put('/{ciclo}', [CicloLectivoController::class, 'update'])->name('update');
                Route::patch('/cambiodeestado/{ciclo}', [CicloLectivoController::class, 'changeStatus'])->name('changeStatus');
            });

            Route::prefix('facultades')->name('facultades.')->group(function () {
                Route::get('/facu', [FacultadCarreraController::class, 'index'])->name('facultad');
                
                // Facultades
                Route::post('/facultad',                  [FacultadCarreraController::class, 'storeFacultad'])->name('facultad.store');
                Route::put('/facultad/{facultad}',        [FacultadCarreraController::class, 'updateFacultad'])->name('facultad.update');
                Route::delete('/facultad/{facultad}',     [FacultadCarreraController::class, 'deleteFacultad'])->name('facultad.delete');

                // Carreras
                Route::post('/carrera',                   [FacultadCarreraController::class, 'storeCarrera'])->name('carrera.store');
                Route::put('/carrera/{carrera}',          [FacultadCarreraController::class, 'updateCarrera'])->name('carrera.update');
                Route::delete('/carrera/{carrera}',       [FacultadCarreraController::class, 'deleteCarrera'])->name('carrera.delete');
            });

            
            Route::prefix('pensum')->name('pensum.')->group(function () {
                Route::get('/',                  [PensumController::class, 'index'])->name('pensum');
                Route::post('/',                 [PensumController::class, 'store'])->name('store');
                Route::get('/{pensum}',         [PensumController::class, 'show'])->name('show');
                Route::get('/{pensum}/editar',  [PensumController::class, 'edit'])->name('edit');

                Route::patch('/{pensum}/estado', [PensumController::class, 'changeEstado'])->name('changeEstado');

                // Materias dentro del pensum
                Route::post('/{pensum}/materia',              [PensumController::class, 'addMateria'])->name('addMateria');
                Route::delete('/{pensumMateria}',     [PensumController::class, 'removeMateria'])->name('removeMateria');

                // Prerequisitos
                Route::post('/pensum-materia/{pensumMateria}/prerequisito', [PensumController::class, 'addPrerequisito'])->name('addPrerequisito');
                Route::delete('/prerequisito/{prerequisito}',               [PensumController::class, 'removePrerequisito'])->name('removePrerequisito');
            });

            // gestionar quejas, sujerencia y observaciones de alumnos
            Route::get('/situaciones', [SituacionGestionController::class, 'index'])->name('situaciones.gestion');
            Route::patch('/situaciones/{id}/estado', [SituacionGestionController::class, 'cambiarEstado'])->whereNumber('id')->name('situaciones.gestion.cambiar-estado');

            // desempeño docente
            Route::prefix('admin-academico/evaluaciones-docente') ->name('evaluaciones.')->group(function () {
                Route::get('/desempeno', [EvaluacionDocenteController::class, 'index'])->name('index');
                Route::post('/store', [EvaluacionDocenteController::class, 'store'])->name('store');
                Route::put('/{evaluacion}', [EvaluacionDocenteController::class, 'update'])->name('update');
                Route::delete('/{evaluacion}', [EvaluacionDocenteController::class, 'destroy'])->name('destroy');
            });

            // reglas de beneficio estudiante 
            Route::prefix('reglas-beneficio')->name('reglas-beneficio.')->group(function () {
                Route::get('/', [ReglaBeneficioController::class, 'index'])->name('index');
                Route::put('/{regla}', [ReglaBeneficioController::class, 'update'])->name('update');
            });

            // logica de requisito de egresado
            Route::get('/egresos', [RequisitosEgresoController::class, 'index'])->name('egresos.index');
            Route::post('/egresos/{alumnoId}/registrar', [RequisitosEgresoController::class, 'registrar'])->whereNumber('alumnoId')->name('egresos.registrar');
            Route::post('/egresos/{alumnoId}/horas-sociales', [RequisitosEgresoController::class, 'gestionarHorasSociales'])->whereNumber('alumnoId')->name('egresos.horas-sociales');
            Route::post('/egresos/{alumnoId}/pasantia', [RequisitosEgresoController::class, 'gestionarPasantia'])->whereNumber('alumnoId')->name('egresos.pasantia');
            Route::get('/egresos/{alumnoId}/documento/{tipo}', [RequisitosEgresoController::class, 'descargarDocumento'])->whereNumber('alumnoId')->whereIn('tipo', ['social', 'pasantia'])->name('egresos.documento');
            Route::post('/egresos/{alumnoId}/solvencia', [RequisitosEgresoController::class, 'marcarSolvencia'])->whereNumber('alumnoId')->name('egresos.solvencia');

            // proceso de graduacion
            Route::get('/graduacion', [ProcesoGraduacionController::class, 'index'])->name('graduacion.index');
            Route::post('/graduacion/{alumnoId}', [ProcesoGraduacionController::class, 'store'])->whereNumber('alumnoId')->name('graduacion.store');
            Route::patch('/graduacion/{procesoId}/estado', [ProcesoGraduacionController::class, 'cambiarEstado'])->whereNumber('procesoId')->name('graduacion.cambiar-estado');
            Route::get('/graduacion/{alumnoId}/pagos-disponibles/{modalidad}', [ProcesoGraduacionController::class, 'pagosDisponibles'])->whereNumber('alumnoId')->name('graduacion.pagos-disponibles');
            Route::patch('/graduacion/{procesoId}/titulo', [ProcesoGraduacionController::class, 'actualizarTitulo'])->whereNumber('procesoId')->name('graduacion.actualizar-titulo');
            Route::get('/graduacion/{alumnoId}/historial', [ProcesoGraduacionController::class, 'historial'])->whereNumber('alumnoId')->name('graduacion.historial');
        });
    });
    