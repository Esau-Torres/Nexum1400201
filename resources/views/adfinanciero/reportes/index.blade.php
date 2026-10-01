@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Informes Contables y Conciliación</h2>
            <p class="text-muted">Visión global de ingresos institucionales.</p>
        </div>
        <!-- Se eliminó el botón de Exportar Informe -->
    </div>

    <!-- Panel de Filtros Administrativos -->
    <div class="card shadow-sm border-0 mb-4 bg-white">
        <div class="card-body">
            <form method="GET" action="{{ route('adfinanciero.reportes.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-dark fw-medium small">Analizar desde:</label>
                    <input type="date" class="form-control" name="fecha_inicio" value="{{ $fechaInicio }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-dark fw-medium small">Hasta:</label>
                    <input type="date" class="form-control" name="fecha_fin" value="{{ $fechaFin }}" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn text-white w-100 shadow-sm" style="background-color: #5dade2;">
                        <i class="bi bi-bar-chart-fill me-1"></i> Generar Análisis
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI's Administrativos -->
    <div class="row g-3 mb-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100" style="background-color: #121212;">
                <div class="card-body p-4">
                    <h6 class="text-white-50 mb-1 text-uppercase fw-bold" style="letter-spacing: 1px;">Ingreso total Procesado</h6>
                    <h2 class="fw-bold text-white mb-0">${{ number_format($totalIngresos, 2) }}</h2>
                    <small class="text-success"><i class="bi bi-graph-up-arrow"></i> {{ $cantidadTransacciones }} transacciones</small>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm bg-white h-100">
                <div class="card-body p-4">
                    <h6 class="text-muted mb-3 text-uppercase fw-bold" style="letter-spacing: 1px;">Top Recaudación por Arancel</h6>
                    @forelse($ingresosPorConcepto->take(3) as $ingreso)
                        <div class="d-flex justify-content-between mb-2 border-bottom pb-1">
                            <span class="text-dark small text-truncate">{{ $ingreso->nombre }}</span>
                            <span class="fw-bold text-dark small">${{ number_format($ingreso->total_recaudado, 2) }}</span>
                        </div>
                    @empty
                        <span class="text-muted small">Sin datos en este periodo.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Libro Mayor / Conciliación -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Libro de recibos globales</h5>
            <!-- Se eliminó la etiqueta de Solo Lectura -->
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="min-height: 400px;">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Recibo / Ref</th>
                            <th>Fecha de proceso</th>
                            <th>ID Alumno</th>
                            <th class="text-center">Canal</th>
                            <th class="text-center">Método</th>
                            <th class="text-end pe-4">Total</th>
                            <!-- Se eliminó la columna Desglose -->
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($pagos) > 0)
                            @foreach($pagos as $pago)
                            <tr class="border-bottom">
                                <td class="py-3 fw-bold text-dark">{{ $pago->numero_recibo ?? 'S/N' }}</td>
                                <td class="py-3 text-muted" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y h:i A') }}</td>
                                <td class="py-3 text-dark">#{{ $pago->id_alumno }}</td>
                                <td class="py-3 text-center text-muted" style="font-size: 0.85rem;">{{ $pago->canal_pago }}</td>
                                <td class="py-3 text-center text-muted" style="font-size: 0.85rem;">{{ $pago->metodo_pago }}</td>
                                <!-- Total alineado a la derecha -->
                                <td class="py-3 text-end pe-4 fw-bold" style="color: #5dade2;">${{ number_format($pago->total, 2) }}</td>
                            </tr>
                            <!-- Se eliminó el Modal de Detalles -->
                            @endforeach
                        @else
                            <tr>
                                <!-- Colspan ajustado a 6 por la eliminación de la columna Desglose -->
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-folder-x fs-2 d-block mb-2"></i>No hay registros contables en este periodo.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top bg-white d-flex justify-content-between align-items-center">
                <small class="text-muted">Mostrando datos de la base de datos.</small>
                {{ $pagos->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
