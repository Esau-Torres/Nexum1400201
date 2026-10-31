<?php

    use Illuminate\Support\Facades\Route;
    use App\Models\Users\Role;
    use App\Http\Controllers\Estudiante\PensumController;
    use App\Http\Controllers\Estudiante\InscripcionController;
    use App\Http\Controllers\Estudiante\SituacionController;
    use App\Http\Controllers\Estudiante\RecordAcademicoController;

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::middleware('role:'.Role::ESTUDIANTE)->group(function () {
            Route::get('/alumno/pensum', [PensumController::class, 'index'])->name('pensum.index');
            Route::get('/alumno/pensum/pdf', [PensumController::class, 'descargarPdf'])->name('pensum.pdf');

            // logica de inscripciones de estudiante
            Route::get('/alumno/inscripcion', [InscripcionController::class, 'index'])->name('inscripcion.index');
            Route::post('/alumno/inscripcion', [InscripcionController::class, 'inscribir'])->name('inscripcion.store');

            // Buzon de quejas
            Route::get('/alumno/situaciones', [SituacionController::class, 'index'])->name('situaciones.index');
            Route::post('/alumno/situaciones', [SituacionController::class, 'store'])->name('situaciones.store');
            Route::delete('/alumno/situaciones/{id}', [SituacionController::class, 'destroy'])->whereNumber('id')->name('situaciones.destroy');

            // recursos bibliograficos
            Route::view('/alumno/recursos-bibliograficos', 'student.recursos_bibliograficos', [
                'recursos' => [
                    [
                        'nombre'      => 'EBSCOhost',
                        'categoria'   => 'Base de datos académica',
                        'descripcion' => 'Acceso a revistas científicas, libros electrónicos y bases de datos multidisciplinarias.',
                        'url'         => 'https://login.ebsco.com/?custId=ns139301&requestIdentifier=cf4a3a0a-1b1e-4cbd-aabe-6febbb646734&acrValues=uid,cpid&ui_locales&redirect_uri=https://logon.ebsco.zone/api/dispatcher/continue/prompted?state=NDZjNGNiYmMzYzczNDM1YTgyOTNlMjhhNTk5ZjI2OGY=&authRequest=eyJraWQiOiIxNzY5MTEwMjQ0MDQ3IiwiYWxnIjoiUlMyNTYifQ.eyJpc3MiOiJodHRwczovL2F1dGguZWJzY28uem9uZS9hcGkvZGlzcGF0Y2hlciIsImF1dGhSZXF1ZXN0Ijp7ImxvZ2luX2hpbnQiOiJjdXN0SWQ6bnMxMzkzMDEiLCJhY3JfdmFsdWVzIjoidWlkIGNwaWQiLCJzY29wZSI6Im9wZW5pZCBlbWFpbCBhZmZpbGlhdGlvbiIsInJlc3BvbnNlX3R5cGUiOiJjb2RlIiwicmVkaXJlY3RfdXJpIjoiaHR0cHM6Ly9zZWFyY2guZWJzY29ob3N0LmNvbS93ZWJhdXRoL1Byb21wdGVkQ2FsbGJhY2suYXNweCIsInN0YXRlIjoiQXhqQng0RTQ1VnNsWEJiVmNNS3VJRU9XR0JkSU5Mc21Wd0hESkNlUl9FVWZfekVNYVBuZWRtblVHcUNNY2d5VlJzcFZsSUhVT1ZEWlktZUQzOHBURF9Tbms2ZVd1b0VmWjhsM2oyeG1YSk1XMTVnTHMxOUFIVVBnR2lEbklUS0NiUlY5TURCWHZhVG9UTGk0c0E1WFBZbk5OUHhncjNYTjdodGkyTHBjblhVWWtPVlJNSDd6alluRUR0TUZzeTlqcHJzdWs1ZVJXek9BTUZxMjdSS2E2UlFzNXR1ZV9mQUh6SmZiYUhEUzhPZ0tQOW1PODF0cG96OXpxbDJvN084cDE1eThWNndPcDg3MGFlV0FQVkQ3cDhXRFpRWEhBcU83S0hLcll4cy02bmxLR250bEdwT3NtS0pzNXdwUm01QUxPYWdmMnlTWFd2a0VjVEN0eWtuRmp5bUp2LTBlVGotWlFYdVZvVEhMa2k1UWdzS1NFcURpcGNBTEVySFVCVHRPT050ZjlWSjJwUSIsImNsaWVudF9pZCI6ImF3Z3ljSXg1N01yd25EUTVoNFVlNnlDVkVQMHI1TXQ5In0sImlhdCI6MTc5MzQxNTg1NiwianRpIjoiNzlmZjZiMmMtNWE1YS00MDMwLWFjYjgtZDgyOTk2NmU0ZDZkIiwicmVxSWQiOiJjZjRhM2EwYS0xYjFlLTRjYmQtYWFiZS02ZmViYmI2NDY3MzQifQ.Ih8LNXYdafKNGCxcvvtDD6gInTH2s0QzyvvPICwHKbBeqmRFsk4PqCAiYHXiIy9nz7WdD7si7TDH03PLGQndgjZZKjke6Zn9KFnnpPQQqw9TwUPlFBJIOVCqU68t16PSILUbTvWbAwGoVAJo3sUFky8DL8HsChCZb2u9cRY_08-QBteOwaG9MUHkGJv7aexBC6gNEwudwayglAWxTR2SQYms9p7Nf30UzsO7HyfAcd-dpbBxm05SeMRe3onUSS5PUOPkAQ5KGQrevZoDKTlZ8qb2NSReGQDbbNw2KB_d-7IRnqLEbDkByHJvuSphvu26J_N9CkjwHCmem9Oa1ylS0w',
                        'icon'        => 'bi-journal-richtext',
                        'color'       => '#dc3545',
                        'bg'          => 'rgba(220,53,69,.1)',
                    ],
                    [
                        'nombre'      => 'eLibro UMA',
                        'categoria'   => 'Biblioteca digital',
                        'descripcion' => 'Plataforma de libros electrónicos de la Universidad Católica de El Salvador.',
                        'url'         => 'https://elibro.net/es/lc/umasv/inicio',
                        'icon'        => 'bi-book-half',
                        'color'       => '#0d6efd',
                        'bg'          => 'rgba(13,110,253,.1)',
                    ],
                    [
                        'nombre'      => 'eLibro CBUES',
                        'categoria'   => 'Biblioteca digital',
                        'descripcion' => 'Acceso al consorcio de bibliotecas universitarias de El Salvador.',
                        'url'         => 'https://elibro.net/es/lc/cbues/inicio',
                        'icon'        => 'bi-journals',
                        'color'       => '#198754',
                        'bg'          => 'rgba(25,135,84,.1)',
                    ],
                    [
                        'nombre'      => 'Catálogo en Línea UMA',
                        'categoria'   => 'Catálogo bibliográfico',
                        'descripcion' => 'Consulta la disponibilidad de libros físicos y tesis en la biblioteca.',
                        'url'         => 'https://catalogobiblio.uma.edu.sv/',
                        'icon'        => 'bi-search',
                        'color'       => '#6f42c1',
                        'bg'          => 'rgba(111,66,193,.1)',
                    ],
                ],
            ])->name('recursos.bibliograficos');

            // Record academico de un estudiante
            Route::get('/alumno/record-academico', [RecordAcademicoController::class, 'index'])->name('record.index');
        });
    });