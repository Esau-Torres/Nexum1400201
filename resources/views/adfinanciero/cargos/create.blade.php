@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <!-- Encabezado -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-file-invoice-dollar fs-4"></i>
                </div>
                <div>
                    <h3 class="fw-bolder text-dark mb-0">GESTIÓN DE CARGOS ESTUDIANTILES</h3>
                    <p class="text-muted mb-0">Generación de matrículas y cuotas por ciclo lectivo</p>
                </div>
            </div>
            <!-- BOTÓN DE ESTADO -->
            <div>
                @if($cicloActivo)
                    <span class="btn fw-bold px-4 py-2 shadow-sm" style="background-color: #fde8e8; color: #dc3545; border: 1px solid #dc3545; cursor: default; border-radius: 10px;">
                        <i class="fa-solid fa-calendar-check me-2"></i>
                        Estado: {{ $cicloActivo->nombre_ciclo . ' (' . $cicloActivo->anio . ')' }}
                    </span>
                @else
                    <span class="btn fw-bold px-4 py-2 shadow-sm" style="background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; cursor: default; border-radius: 10px;">
                        <i class="fa-solid fa-lock me-2"></i>
                        Estado: SIN CICLO ACTIVO
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm fw-bold rounded-3"><i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm fw-bold rounded-3"><i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}</div>
    @endif

    <!-- VALIDACIÓN VISUAL: ¿HAY CICLO ACTIVO? -->
    @if(!$cicloActivo)
        <!-- PANTALLA DE BLOQUEO ELEGANTE -->
        <div class="card border-0 rounded-4 shadow-sm text-center p-5 mt-4" style="background-color: #fff9f9; border: 2px dashed #ffcdd2 !important;">
            <div class="mb-4">
                <div class="bg-white rounded-circle shadow-sm d-inline-flex justify-content-center align-items-center" style="width: 100px; height: 100px;">
                    <i class="fa-solid fa-lock text-danger" style="font-size: 3rem;"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-3">El Ciclo Lectivo está Cerrado</h3>
            <p class="text-muted fs-6 mx-auto mb-0" style="max-width: 600px;">
                Por medidas de seguridad financiera, no se pueden visualizar estudiantes ni generar cargos arancelarios cuando no hay un ciclo académico activo en curso. Por favor, <strong>activa un nuevo ciclo</strong> en la configuración general para habilitar este módulo.
            </p>
        </div>
    @else
        <!-- VISTA NORMAL: LA TABLA Y LOS CONTROLES -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-0">

                <!-- Controles Superiores -->
                <div class="p-4 d-flex justify-content-between align-items-center border-bottom bg-light flex-wrap gap-3" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">

                    <!-- Buscador y Selector -->
                    <div class="d-flex gap-3 align-items-center flex-grow-1">
                        <select class="form-select shadow-sm border-0 text-muted fw-bold" style="width: 80px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>

                        <form method="GET" action="{{ route('adfinanciero.cargos.create') }}" class="d-flex" style="min-width: 300px;">
                            <div class="input-group shadow-sm rounded-3 overflow-hidden">
                                <span class="input-group-text bg-white border-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" name="search" class="form-control border-0 shadow-none" placeholder="Buscar carnet o nombre..." value="{{ $search ?? '' }}">
                            </div>
                        </form>
                    </div>

                    <!-- NUEVO BOTÓN: GENERAR A TODOS -->
                    <div>
                        <!-- IMPORTANTE: Debes crear esta ruta en tu web.php y la función en tu controlador -->
                        <form action="{{ route('adfinanciero.cargos.storeMasivo') }}" method="POST" id="formGenerarTodos" class="m-0">
                            @csrf
                            <button type="button" class="btn fw-bold shadow-sm rounded-3 px-4 py-2" style="background-color: #dc3545; color: white;" onclick="confirmarGeneracionMasiva()">
                                <i class="fa-solid fa-bolt-lightning me-2"></i> Generar a Todos
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-white text-secondary">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase fw-bold" style="font-size: 0.8rem; letter-spacing: 1px;">Estudiante</th>
                                <th class="py-3 text-uppercase fw-bold" style="font-size: 0.8rem; letter-spacing: 1px;">Carnet</th>
                                <th class="py-3 text-uppercase fw-bold" style="font-size: 0.8rem; letter-spacing: 1px;">Carrera</th>
                                <th class="py-3 text-uppercase fw-bold text-center" style="font-size: 0.8rem; letter-spacing: 1px;">Beneficio</th>
                                <th class="pe-4 py-3 text-uppercase fw-bold text-center" style="font-size: 0.8rem; letter-spacing: 1px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($estudiantes as $estudiante)
                                <tr>
                                    <td class="ps-4 py-3">
                                        @php
                                            $partesNombre = explode(' ', trim($estudiante->name));
                                            $inicial1 = isset($partesNombre[0]) ? substr($partesNombre[0], 0, 1) : '';
                                            $inicial2 = isset($partesNombre[1]) ? substr($partesNombre[1], 0, 1) : '';
                                        @endphp
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold shadow-sm" style="width: 45px; height: 45px; background-color: #fde8e8; color: #dc3545;">
                                                {{ strtoupper($inicial1 . $inicial2) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $estudiante->name }}</div>
                                                <div class="text-muted" style="font-size: 0.85rem;"><i class="fa-regular fa-id-card me-1"></i> DUI: {{ $estudiante->documento_identidad ?? 'No registrado' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 fw-bold text-dark">{{ $estudiante->codigo_estudiante }}</td>
                                    <td class="py-3 text-muted" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $estudiante->nombre_carrera }}
                                    </td>
                                    <td class="py-3 text-center">
                                        @if($estudiante->tipo_beneficio)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-2 rounded-pill shadow-sm">
                                                {{ str_replace('_', ' ', strtoupper($estudiante->tipo_beneficio)) }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary fw-bold px-3 py-2 rounded-pill shadow-sm">
                                                SIN BENEFICIO
                                            </span>
                                        @endif
                                    </td>
                                    <td class="pe-4 py-3 text-center">
                                        <!-- Formulario invisible para procesar la generación individual -->
                                        <form action="{{ route('adfinanciero.cargos.store') }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="alumno_id" value="{{ $estudiante->alumno_id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger fw-bold shadow-sm rounded-3 px-3 py-2">
                                                <i class="fa-solid fa-bolt me-1"></i> Generar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted fw-bold">
                                        <i class="fa-solid fa-inbox fs-1 text-black-50 mb-3 d-block"></i>
                                        No se encontraron estudiantes para generar cargos.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="card-footer bg-white border-top p-4 d-flex justify-content-between align-items-center" style="border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
                    <div class="text-muted fw-bold" style="font-size: 0.9rem;">
                        Mostrando {{ $estudiantes->firstItem() ?? 0 }} a {{ $estudiantes->lastItem() ?? 0 }} de {{ $estudiantes->total() }} estudiantes
                    </div>
                    <div class="pagination-sm m-0">
                        {{ $estudiantes->appends(['search' => $search])->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>
        </div>
    @endif

</div>

<!-- SCRIPT PARA CONFIRMAR GENERACIÓN MASIVA -->
<script>
    function confirmarGeneracionMasiva() {
        if(confirm('¿Estás seguro que deseas generar la matrícula y cuotas para TODOS los estudiantes listados? Este proceso puede tardar unos segundos.')) {
            document.getElementById('formGenerarTodos').submit();
        }
    }
</script>
@endsection
