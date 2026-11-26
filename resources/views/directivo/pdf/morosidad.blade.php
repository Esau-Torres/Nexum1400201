<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Morosidad y Cuentas por Cobrar</title>
    <style>
        @page {
            margin: 22px 24px 36px 24px;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .title {
            font-size: 14.5px;
            font-weight: bold;
            color: #111827;
            margin: 0;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 9.5px;
            color: #6b7280;
            margin: 3px 0 0 0;
        }
        .header-right {
            text-align: right;
            font-size: 9px;
            color: #4b5563;
            vertical-align: top;
        }
        .badge-inst {
            display: inline-block;
            background-color: #6c757d;
            color: #ffffff;
            padding: 3px 7px;
            border-radius: 3px;
            font-size: 8.5px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .filters-box {
            background-color: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 14px;
        }
        .filters-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        .filters-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .filter-label {
            font-weight: bold;
            color: #4b5563;
        }
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px 8px -8px;
        }
        .kpi-card {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 8px 10px;
            background-color: #ffffff;
            vertical-align: top;
        }
        .kpi-red { border-left: 4px solid #dc3545; }
        .kpi-blue { border-left: 4px solid #0d6efd; }
        .kpi-purple { border-left: 4px solid #6f42c1; }
        .kpi-amber { border-left: 4px solid #f59e0b; }
        .kpi-green { border-left: 4px solid #198754; }
        .kpi-title {
            font-size: 8px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 3px;
        }
        .kpi-comp {
            font-size: 8.5px;
            color: #6b7280;
        }
        .text-bad { color: #dc3545; font-weight: bold; }
        .text-good { color: #198754; font-weight: bold; }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 4px;
            margin: 14px 0 8px 0;
            text-transform: uppercase;
        }
        .nueva-pagina {
            page-break-before: always;
            margin-top: 0;
        }
        table.data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.data-table thead {
            display: table-header-group;
        }
        table.data-table tr {
            page-break-inside: avoid;
        }
        table.data-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-size: 8.5px;
            text-transform: uppercase;
            text-align: left;
            padding: 6px 7px;
            border-top: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
            vertical-align: middle;
        }
        table.data-table td {
            padding: 6px 7px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
            vertical-align: middle;
            word-wrap: break-word;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #fafafa;
        }
        table.data-table tfoot td {
            font-weight: bold;
            background-color: #f3f4f6;
            border-top: 1.5px solid #9ca3af;
            border-bottom: 1.5px solid #9ca3af;
        }
        table.data-table th.text-center,
        table.data-table td.text-center,
        .text-center {
            text-align: center !important;
        }
        table.data-table th.text-right,
        table.data-table td.text-right,
        .text-right {
            text-align: right !important;
        }
        .bar-bg {
            width: 100%;
            background-color: #e5e7eb;
            height: 7px;
            border-radius: 3px;
            overflow: hidden;
        }
        .bar-fill-red { background-color: #dc3545; height: 7px; }
        .bar-fill-green { background-color: #198754; height: 7px; }
        .rank-badge {
            display: inline-block;
            width: 16px;
            padding: 3px 0 5px 0;
            line-height: 8px;
            text-align: center;
            border-radius: 50%;
            background-color: #6c757d;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
        }
        .rank-first { background-color: #dc3545; }
        .status-pill {
            display: inline-block;
            padding: 2px 6px 3px 6px;
            line-height: 10px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            white-space: nowrap;
        }
        .status-danger { background-color: #fde8e8; color: #c81e1e; border: 1px solid #f8b4b4; }
        .status-warning { background-color: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
        .status-info { background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
        .status-secondary { background-color: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }
        .empty-row {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 12px;
        }
        .footer {
            position: fixed;
            bottom: -22px;
            left: 0;
            right: 0;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
            font-size: 8.5px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $textoCiclo = $cicloSeleccionadoObj
            ? 'Ciclo ' . ($cicloSeleccionadoObj->numero_ciclo ?? $cicloSeleccionadoObj->nombre_ciclo ?? '') . '-' . $cicloSeleccionadoObj->anio . (isset($cicloSeleccionadoObj->estado) ? " ({$cicloSeleccionadoObj->estado})" : '')
            : 'Todos los ciclos';

        $totalDeudaNeta = $evolucion->sum('deuda_neta');
        $totalRecargos = $evolucion->sum('recargos');
        $totalEvolucion = $totalDeudaNeta + $totalRecargos;
        $totalMoraFacultades = $facultadesData->sum('total_deuda');
    @endphp

    <!-- ENCABEZADO -->
    <table class="header-table">
        <tr>
            <td>
                <h1 class="title">Informe de Morosidad y Cuentas por Cobrar</h1>
                <p class="subtitle">Universidad Modular Abierta · Sistema de Gestión Nexum</p>
            </td>
            <td class="header-right">
                <span class="badge-inst">Modo Consulta Institucional</span><br>
                <strong>Generado:</strong> {{ $fechaGeneracion }}
            </td>
        </tr>
    </table>

    <!-- FILTROS APLICADOS -->
    <div class="filters-box">
        <table class="filters-table">
            <tr>
                <td style="width: 50%;"><span class="filter-label">Ciclo Académico:</span> {{ $textoCiclo }}</td>
                <td style="width: 50%;"><span class="filter-label">Rango de Fechas:</span> {{ $rangoFechas }}</td>
            </tr>
            <tr>
                <td><span class="filter-label">Facultad:</span> {{ $nombreFacultad }}</td>
                <td><span class="filter-label">Carrera:</span> {{ $nombreCarrera }}</td>
            </tr>
        </table>
    </div>

    <!-- TARJETAS KPI (Fila 1: 3 tarjetas) -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card kpi-red" style="width: 33.33%;">
                <div class="kpi-title">Deuda Total Pendiente</div>
                <div class="kpi-value">${{ number_format($deudaTotal, 2) }}</div>
                <div class="kpi-comp">
                    <span class="{{ $varDeuda > 0 ? 'text-bad' : 'text-good' }}">
                        {{ $varDeuda > 0 ? '+' : '' }}{{ number_format($varDeuda, 1) }}%
                    </span>
                    vs ciclo anterior
                </div>
            </td>
            <td class="kpi-card kpi-blue" style="width: 33.33%;">
                <div class="kpi-title">Estudiantes con Cuotas Vencidas</div>
                <div class="kpi-value">{{ number_format($estudiantesMorosos) }}</div>
                <div class="kpi-comp">
                    <span class="{{ $varEstudiantes > 0 ? 'text-bad' : 'text-good' }}">
                        {{ $varEstudiantes > 0 ? '+' : '' }}{{ number_format($varEstudiantes, 1) }}%
                    </span>
                    vs ciclo anterior
                </div>
            </td>
            <td class="kpi-card kpi-purple" style="width: 33.33%;">
                <div class="kpi-title">Cuotas Pendientes</div>
                <div class="kpi-value">{{ number_format($cuotasPendientes) }}</div>
                <div class="kpi-comp">
                    <span class="{{ $varCuotas > 0 ? 'text-bad' : 'text-good' }}">
                        {{ $varCuotas > 0 ? '+' : '' }}{{ number_format($varCuotas, 1) }}%
                    </span>
                    vs ciclo anterior
                </div>
            </td>
        </tr>
    </table>

    <!-- TARJETAS KPI (Fila 2: 2 tarjetas) -->
    <table class="kpi-table" style="margin-bottom: 14px;">
        <tr>
            <td class="kpi-card kpi-amber" style="width: 50%;">
                <div class="kpi-title">Monto Vencido (Con Mora)</div>
                <div class="kpi-value">${{ number_format($cuotasVencidas, 2) }}</div>
                <div class="kpi-comp">
                    <span class="{{ $varVencidas > 0 ? 'text-bad' : 'text-good' }}">
                        {{ $varVencidas > 0 ? '+' : '' }}{{ number_format($varVencidas, 1) }}%
                    </span>
                    vs ciclo anterior
                </div>
            </td>
            <td class="kpi-card kpi-green" style="width: 50%;">
                <div class="kpi-title">Índice de Morosidad</div>
                <div class="kpi-value">{{ number_format($indiceMorosidad, 1) }}%</div>
                <div class="kpi-comp">
                    <span class="{{ $varIndice > 0 ? 'text-bad' : 'text-good' }}">
                        {{ $varIndice > 0 ? '+' : '' }}{{ number_format($varIndice, 1) }} pp
                    </span>
                    vs ciclo anterior
                </div>
            </td>
        </tr>
    </table>

    <!-- 1. EVOLUCIÓN DE LA DEUDA INSTITUCIONAL -->
    <div class="section-title">1. Evolución de la Deuda Institucional</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 28%;">Periodo (Año-Mes)</th>
                <th style="width: 24%;" class="text-center">Deuda Principal</th>
                <th style="width: 24%;" class="text-center">Recargos por Mora</th>
                <th style="width: 24%;" class="text-center">Total del Mes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($evolucion as $item)
                @php
                    $totalMes = $item->deuda_neta + $item->recargos;
                @endphp
                <tr>
                    <td>{{ $item->mes_anio }}</td>
                    <td class="text-center">${{ number_format($item->deuda_neta, 2) }}</td>
                    <td class="text-center">${{ number_format($item->recargos, 2) }}</td>
                    <td class="text-center"><strong>${{ number_format($totalMes, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty-row">No se registra deuda pendiente para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        @if($evolucion->isNotEmpty())
            <tfoot>
                <tr>
                    <td>Total Acumulado</td>
                    <td class="text-center">${{ number_format($totalDeudaNeta, 2) }}</td>
                    <td class="text-center">${{ number_format($totalRecargos, 2) }}</td>
                    <td class="text-center">${{ number_format($totalEvolucion, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- 2. MOROSIDAD POR FACULTAD (SOLO CARGOS VENCIDOS) -->
    <div class="section-title">2. Morosidad por Facultad (Cargos Vencidos)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 48%;">Facultad</th>
                <th style="width: 20%;" class="text-center">Participación</th>
                <th style="width: 14%;" class="text-center">%</th>
                <th style="width: 18%;" class="text-center">Monto Vencido</th>
            </tr>
        </thead>
        <tbody>
            @forelse($facultadesData as $item)
                @php
                    $porcentaje = $totalMoraFacultades > 0 ? ($item->total_deuda / $totalMoraFacultades) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $item->nombre_facultad }}</td>
                    <td>
                        <div class="bar-bg">
                            <div class="bar-fill-green" style="width: {{ min($porcentaje, 100) }}%;"></div>
                        </div>
                    </td>
                    <td class="text-center">{{ number_format($porcentaje, 1) }}%</td>
                    <td class="text-center">${{ number_format($item->total_deuda, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty-row">No hay cargos vencidos por facultad para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        @if($facultadesData->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2">Total Mora Vencida</td>
                    <td class="text-center">100.0%</td>
                    <td class="text-center">${{ number_format($totalMoraFacultades, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- 3. MAYOR DEUDA POR CARRERA -->
    <div class="section-title">3. Mayor Deuda por Carrera</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">#</th>
                <th style="width: 40%;">Carrera</th>
                <th style="width: 20%;" class="text-center">Participación</th>
                <th style="width: 14%;" class="text-center">%</th>
                <th style="width: 18%;" class="text-center">Deuda Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deudaPorCarrera as $index => $item)
                @php
                    $porcentaje = $deudaTotal > 0 ? ($item->total / $deudaTotal) * 100 : 0;
                @endphp
                <tr>
                    <td class="text-center">
                        <span class="rank-badge {{ $index === 0 ? 'rank-first' : '' }}">{{ $index + 1 }}</span>
                    </td>
                    <td>{{ $item->carrera }}</td>
                    <td>
                        <div class="bar-bg">
                            <div class="bar-fill-red" style="width: {{ min($porcentaje, 100) }}%;"></div>
                        </div>
                    </td>
                    <td class="text-center">{{ number_format($porcentaje, 1) }}%</td>
                    <td class="text-center">${{ number_format($item->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-row">No hay registros de deuda por carrera para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. LISTADO DE ESTUDIANTES CON CUOTAS PENDIENTES -->
    <div class="section-title nueva-pagina">
        4. Estudiantes con Cuotas Pendientes ({{ count($estudiantesMorososLista) }})
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 23%;">Estudiante</th>
                <th style="width: 14%;" class="text-center">Código</th>
                <th style="width: 25%;">Facultad</th>
                <th style="width: 8%;" class="text-center">Cuotas</th>
                <th style="width: 14%;" class="text-center">Monto</th>
                <th style="width: 16%;" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($estudiantesMorososLista as $est)
                <tr>
                    <td><strong>{{ $est->nombre }}</strong></td>
                    <td class="text-center">{{ $est->codigo }}</td>
                    <td>{{ $est->facultad }}</td>
                    <td class="text-center">{{ $est->cuotas }}</td>
                    <td class="text-center">${{ number_format($est->monto, 2) }}</td>
                    <td class="text-center">
                        <span class="status-pill status-{{ $est->color }}">{{ $est->estado }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-row">No hay estudiantes con cuotas pendientes para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Documento generado automáticamente por Nexum · Uso institucional interno · {{ $fechaGeneracion }}
    </div>
</body>
</html>
