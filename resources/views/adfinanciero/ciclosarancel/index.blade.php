@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <!-- Encabezado y botón de Crear -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Ciclos y Aranceles</h2>
            <p class="text-muted">Configuración de ventanas de pago ordinarias y recargos por mora extemporánea.</p>
        </div>
        <div>
            <button type="button" class="btn text-white shadow-sm" style="background-color: #ff7575;" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-calendar-plus-fill me-1"></i> Configurar nueva regla de cobro
            </button>
        </div>
    </div>

    <!-- Contenedor Principal de la Tabla -->
    <div class="card shadow-sm border-0">

        <!-- Buscador y Filtros -->
        <div class="card-header bg-white py-3">
            <form method="GET" action="{{ route('adfinanciero.ciclosarancel.index') }}" class="m-0">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-lg-4">
                        <h5 class="mb-0">Ventanas de cobros Registrados</h5>
                    </div>
                    <div class="col-12 col-lg-8">
                        <div class="d-flex justify-content-lg-end gap-2 flex-wrap flex-md-nowrap">

                            <!-- Filtro Desplegable por Ciclo (Con auto-submit) -->
                            <select name="ciclo_filter" class="form-select w-auto text-secondary shadow-sm" onchange="this.form.submit()">
                                <option value="">Todos los ciclos</option>
                                @foreach($ciclos as $c)
                                    <option value="{{ $c->ciclo_lectivo_id }}" {{ request('ciclo_filter') == $c->ciclo_lectivo_id ? 'selected' : '' }}>
                                        {{ $c->nombre_ciclo }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Buscador de Texto (Ciclo o Concepto) -->
                            <div class="input-group shadow-sm" style="max-width: 350px;">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Buscar concepto o código..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-outline-secondary bg-light text-dark">Buscar</button>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive d-flex flex-column justify-content-between" style="min-height: 600px;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ciclo Lectivo</th>
                            <th class="text-center">Periodo Ordinario (Sin Recargo)</th>
                            <th class="text-center">Periodo Extraordinario (Mora)</th>
                            <th>Concepto de Recargo Vinculado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($periodos) > 0)
                            @foreach($periodos as $periodo)
                            <tr class="border-bottom">
                                <!-- Ciclo -->
                                <td class="py-3 fw-bold text-uppercase" style="color: #121212;">
                                    <i class="bi bi-mortarboard-fill text-muted me-2"></i>{{ $periodo->ciclo->nombre_ciclo ?? 'Sin Asignar' }}
                                </td>

                                <!-- Fechas Ordinarias -->
                                <td class="py-3 text-center">
                                    <span class="badge px-3 py-2" style="background-color: #f4f9fd; color: #5dade2; border: 1px solid #5dade2; font-size: 0.85rem;">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        {{ \Carbon\Carbon::parse($periodo->fecha_inicio_ordinario)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($periodo->fecha_fin_ordinario)->format('d/m/Y') }}
                                    </span>
                                </td>

                                <!-- Fechas Extraordinarias -->
                                <td class="py-3 text-center">
                                    <span class="badge px-3 py-2" style="background-color: #fff5f5; color: #ff7575; border: 1px solid #ff7575; font-size: 0.85rem;">
                                        <i class="bi bi-calendar-exclamation me-1"></i>
                                        {{ \Carbon\Carbon::parse($periodo->fecha_inicio_extraordinario)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($periodo->fecha_fin_extraordinario)->format('d/m/Y') }}
                                    </span>
                                </td>

                                <!-- Concepto de Recargo -->
                                <td class="py-3 text-muted text-uppercase" style="font-size: 0.85rem;">
                                    <i class="bi bi-link-45deg me-1 fs-5"></i>
                                    {{ $periodo->recargo->nombre ?? 'Sin Recargo Vinculado' }}
                                </td>

                                <!-- Acciones -->
                                <td class="py-3 text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal{{ $periodo->periodo_id }}" title="Editar Fechas">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal de Edición -->
                            <div class="modal fade" id="editModal{{ $periodo->periodo_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header border-bottom-0 pb-2">
                                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                                                <i class="bi bi-pencil-fill me-2" style="color: #ff7575;"></i>
                                                Editar Ventanas de Pago - {{ $periodo->ciclo->nombre_ciclo ?? '' }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <form action="{{ route('adfinanciero.ciclosarancel.update', $periodo->periodo_id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body pt-0">
                                                <div class="mb-4 p-3 bg-light rounded" style="border-left: 4px solid #ff7575;">
                                                    <i class="bi bi-info-circle-fill me-2" style="color: #ff7575;"></i>
                                                    <span class="text-secondary" style="font-size: 0.9rem;">Ajuste las fechas. Recuerde que el periodo extraordinario debe iniciar obligatoriamente después de que finalice el periodo ordinario.</span>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <label class="form-label text-dark fw-medium">Ciclo Lectivo</label>
                                                        <select class="form-select text-uppercase" name="id_ciclo_lectivo" required>
                                                            @foreach($ciclos as $ciclo)
                                                                <option value="{{ $ciclo->ciclo_lectivo_id }}" {{ $periodo->id_ciclo_lectivo ==$ciclo->ciclo_lectivo_id ? 'selected' : '' }}>
                                                                    {{ $ciclo->nombre_ciclo }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-dark fw-medium">Inicio Ordinario</label>
                                                        <input type="date" class="form-control" name="fecha_inicio_ordinario" value="{{ $periodo->fecha_inicio_ordinario }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-dark fw-medium">Fin Ordinario</label>
                                                        <input type="date" class="form-control" name="fecha_fin_ordinario" value="{{ $periodo->fecha_fin_ordinario }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-medium" style="color: #ff7575;">Inicio Extraordinario (Mora)</label>
                                                        <input type="date" class="form-control" name="fecha_inicio_extraordinario" value="{{ $periodo->fecha_inicio_extraordinario }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-medium" style="color: #ff7575;">Fin Extraordinario</label>
                                                        <input type="date" class="form-control" name="fecha_fin_extraordinario" value="{{ $periodo->fecha_fin_extraordinario }}" required>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label text-dark fw-medium">Concepto de Recargo Aplicable</label>
                                                        <select class="form-select text-uppercase" name="id_concepto_recargo_extra" required>
                                                            @foreach($conceptos as $concepto)
                                                                <option value="{{ $concepto->concepto_pago_id }}" {{ $periodo->id_concepto_recargo_extra ==$concepto->concepto_pago_id ? 'selected' : '' }}>
                                                                    {{ $concepto->codigo_concepto }} - {{$concepto->nombre }} (${{ number_format($concepto->monto_base, 2) }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 pt-0">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn text-white px-4" style="background-color: #ff7575;">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                    No se encontraron ciclos configurados con los filtros actuales.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <!-- Paginación -->
                <div class="mt-auto p-3 border-top bg-white d-flex justify-content-between align-items-center">
                    <small class="text-muted">Mostrando resultados de la base de datos.</small>

                    @if($periodos->hasPages())
                        <ul class="pagination mb-0 shadow-sm">
                            <li class="page-item {{ $periodos->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $periodos->previousPageUrl() }}" {!! $periodos->onFirstPage() ? 'tabindex="-1" aria-disabled="true"' : '' !!}>&lsaquo;</a>
                            </li>

                            @php
                                $start = max(1, $periodos->currentPage() - 1);$end = min($periodos->lastPage(),$start + 2);
                                if ($end - $start < 2 &&$start > 1) {
                                    $start = max(1,$end - 2);
                                }
                            @endphp

                            @foreach($periodos->getUrlRange($start,$end) as $page =>$url)
                                <li class="page-item {{ $page ==$periodos->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endforeach

                            <li class="page-item {{ !$periodos->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $periodos->nextPageUrl() }}" {!! !$periodos->hasMorePages() ? 'tabindex="-1" aria-disabled="true"' : '' !!}>&rsaquo;</a>
                            </li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Creación -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header border-bottom-0 pb-2">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-calendar-plus-fill me-2" style="color: #5dade2;"></i>
                    Configurar Nuevo Periodo de Arancel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('adfinanciero.ciclosarancel.store') }}" method="POST">
                @csrf
                <div class="modal-body pt-0">

                    <div class="mb-4 p-3 bg-light rounded" style="border-left: 4px solid #5dade2;">
                        <i class="bi bi-info-circle-fill me-2" style="color: #5dade2;"></i>
                        <span class="text-secondary" style="font-size: 0.9rem;">Configure las fechas límite de pago. Una vez superada la fecha ordinaria, el sistema Nexum aplicará automáticamente el concepto de recargo por mora seleccionado.</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-dark fw-medium">Ciclo Lectivo</label>
                            <select class="form-select text-uppercase" name="id_ciclo_lectivo" required>
                                <option value="" selected disabled>Seleccione un ciclo...</option>
                                @foreach($ciclos as $ciclo)
                                    <option value="{{ $ciclo->ciclo_lectivo_id }}">{{ $ciclo->nombre_ciclo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-dark fw-medium">Inicio Ordinario</label>
                            <input type="date" class="form-control" name="fecha_inicio_ordinario" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-dark fw-medium">Fin Ordinario</label>
                            <input type="date" class="form-control" name="fecha_fin_ordinario" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium" style="color: #ff7575;">Inicio Extraordinario (Mora)</label>
                            <input type="date" class="form-control" name="fecha_inicio_extraordinario" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium" style="color: #ff7575;">Fin Extraordinario</label>
                            <input type="date" class="form-control" name="fecha_fin_extraordinario" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-dark fw-medium">Concepto de Recargo Aplicable</label>
                            <select class="form-select text-uppercase" name="id_concepto_recargo_extra" required>
                                <option value="" selected disabled>Seleccione el recargo por mora...</option>
                                @foreach($conceptos as $concepto)
                                    <option value="{{ $concepto->concepto_pago_id }}">{{ $concepto->codigo_concepto }} - {{$concepto->nombre }} (${{ number_format($concepto->monto_base, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white px-4" style="background-color: #ff7575;">Sí, continuar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toastHTML = `
                <div class="toast-container position-fixed top-0 end-0 p-3 mt-2 me-2" style="z-index: 1100">
                    <div id="errorToast" class="toast bg-white border-0 border-start border-4 border-danger shadow-sm rounded-end" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex p-3 align-items-start">
                            <i class="bi bi-x-circle-fill text-danger fs-5 me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">Error de validación</div>
                                <div class="text-secondary" style="font-size: 0.85rem;">
                                    {{ $errors->first() }}
                                </div>
                            </div>
                            <button type="button" class="btn-close ms-2 mb-auto" data-bs-dismiss="toast" aria-label="Close" style="font-size: 0.75rem;"></button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', toastHTML);
            new bootstrap.Toast(document.getElementById('errorToast'), { delay: 6000 }).show();
            if(!'{{ old('periodo_id') }}'){
                new bootstrap.Modal(document.getElementById('createModal')).show();
            }
        });
    </script>
    @endif
@endpush
@endsection
