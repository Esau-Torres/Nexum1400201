@extends('layouts.app')

@section('title', 'Carga Masiva de Estudiantes')

@section('content')
<div class="container-fluid py-4">

    {{-- Encabezado --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3 p-3 mb-4 rounded-4 card">
        <div class="d-flex align-items-center">
            <div class="px-3 py-2 rounded-3 bg-danger bg-opacity-10" style="width:48px;height:48px;">
                <i class="bi bi-people-fill fs-4 text-danger"></i>
            </div>
            <div class="px-3">
                <h1 class="h4 fw-bold mb-0">CARGA MASIVA DE ESTUDIANTES</h1>
                <p class="text-muted mb-0 small">
                    Creación en lote desde CSV o Excel
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin-academico.create-student') }}"
               class="btn btn-outline-secondary px-3 py-2">
                <i class="bi bi-person-plus me-1"></i> Creación individual
            </a>
            <a href="{{ route('home') }}" class="btn bg-danger-subtle text-danger border border-danger px-3 py-2">
                <i class="fa-solid fa-home me-1"></i> Inicio
            </a>
        </div>
    </div>

    {{-- Resultados previos --}}
    @if (session('massive_result'))
        @php $r = session('massive_result'); @endphp

        <div class="alert alert-info border-0 shadow-sm mb-4">
            <strong>{{ $r['resumen'] }}</strong>
        </div>

        @if (!empty($r['creados']))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success bg-opacity-10 py-3">
                    <h2 class="h6 fw-bold mb-0 text-success">
                        <i class="bi bi-check-circle-fill me-1"></i>
                        Estudiantes creados ({{ count($r['creados']) }})
                    </h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Fila</th><th>Nombre</th><th>Correo</th><th>Código</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($r['creados'] as $c)
                                <tr>
                                    <td>{{ $c['fila'] }}</td>
                                    <td>{{ $c['nombre'] }}</td>
                                    <td>{{ $c['email'] }}</td>
                                    <td><code>{{ $c['codigo'] }}</code></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if (!empty($r['errores']))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-danger bg-opacity-10 py-3">
                    <h2 class="h6 fw-bold mb-0 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Filas con errores ({{ count($r['errores']) }})
                    </h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Fila</th><th>Correo</th><th>Detalles</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($r['errores'] as $e)
                                <tr>
                                    <td>{{ $e['fila'] }}</td>
                                    <td>{{ $e['email'] }}</td>
                                    <td>
                                        <ul class="mb-0 ps-3 small">
                                            @foreach ($e['errores'] as $msg)
                                                <li>{{ $msg }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    {{-- Formulario --}}
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-upload text-danger me-1"></i> Subir archivo
                    </h2>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin-academico.create-student.massive.store') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="archivo" class="form-label fw-medium small">
                                Archivo CSV o Excel <span class="text-danger">*</span>
                            </label>
                            <input type="file"
                                   name="archivo"
                                   id="archivo"
                                   accept=".csv,.xlsx,.xls"
                                   class="form-control @error('archivo') is-invalid @enderror"
                                   required>
                            @error('archivo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small">
                                Máximo 5 MB. Se procesan en bloques de 50 filas.
                            </div>
                        </div>

                        <div class="alert alert-warning border-0 small">
                            <i class="bi bi-info-circle-fill me-1"></i>
                            Los estudiantes se crean <strong>activos</strong> y con contraseña temporal
                            igual a su <strong>código de estudiante</strong>. Los documentos quedan
                            <strong>PENDIENTES</strong> y el Administrador los revisa de manera individual y los sube desde su portal.
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-people-fill me-1"></i> Procesar archivo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h2 class="h6 fw-bold mb-0">
                        <i class="bi bi-file-earmark-arrow-down text-danger me-1"></i> Plantilla oficial
                    </h2>
                </div>
                <div class="card-body p-4">

                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="rounded-3 bg-success bg-opacity-10 p-2">
                            <i class="bi bi-file-earmark-excel-fill text-success fs-5"></i>
                        </div>
                        <div class="small">
                            <strong class="d-block">Formato Excel (.xlsx)</strong>
                            <span class="text-muted">Incluye 3 hojas: datos, instrucciones y referencias.</span>
                        </div>
                    </div>

                    <ul class="list-unstyled small mb-3">
                        <li class="mb-1">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            Dropdowns de género, estado civil, carrera y regional
                        </li>
                        <li class="mb-1">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            Datos de ejemplo con IDs reales del sistema
                        </li>
                        <li class="mb-1">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            Hoja de instrucciones paso a paso
                        </li>
                        <li class="mb-0">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            Hoja de referencias con todos los IDs válidos
                        </li>
                    </ul>

                    <a href="{{ route('admin-academico.create-student.massive.template') }}"
                    class="btn btn-danger w-100">
                        <i class="bi bi-download me-1"></i> Descargar plantilla
                    </a>

                    <p class="text-muted small mb-0 mt-3 text-center">
                        Se regenera cada vez que la descargás, así siempre tiene los datos actuales.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection