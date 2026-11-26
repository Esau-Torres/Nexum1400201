<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Ejecutivo de Ingresos</title>
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
            margin: 0 -8px 14px -8px;
        }
        .kpi-card {
            width: 33.33%;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 8px 10px;
            background-color: #ffffff;
            vertical-align: top;
        }
        .kpi-red { border-left: 4px solid #dc3545; }
        .kpi-blue { border-left: 4px solid #0d6efd; }
        .kpi-green { border-left: 4px solid #198754; }
        .kpi-title {
            font-size: 8px;
            font-weight: bold;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 15.5px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 3px;
        }
        .kpi-comp {
            font-size: 8.5px;
            color: #6b7280;
        }
        .text-up { color: #198754; font-weight: bold; }
        .text-down { color: #dc3545; font-weight: bold; }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #111827;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 4px;
            margin: 14px 0 8px 0;
            text-transform: uppercase;
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
            padding: 6px 8px;
            border-top: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
            vertical-align: middle;
        }
        table.data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9.2px;
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
        .bar-fill-blue { background-color: #0d6efd; height: 7px; }
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
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $textoMes = $mesSeleccionado ? ($meses[(int) $mesSeleccionado] ?? $mesSeleccionado) : 'Todos los meses del ciclo';
        $textoCiclo = $cicloSeleccionadoObj
            ? "{$cicloSeleccionadoObj->nombre_ciclo} - {$cicloSeleccionadoObj->anio} ({$cicloSeleccionadoObj->estado})"
            : "Año {$anioSeleccionado}";
    @endphp

    <!-- ENCABEZADO -->
    <table class="header-table">
        <tr>
            <td>
                <h1 class="title">Informe Ejecutivo de Ingresos</h1>
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
                <td style="width: 50%;"><span class="filter-label">Mes:</span> {{ $textoMes }}</td>
            </tr>
            <tr>
                <td><span class="filter-label">Facultad:</span> {{ $nombreFacultad }}</td>
                <td><span class="filter-label">Carrera:</span> {{ $nombreCarrera }}</td>
            </tr>
        </table>
    </div>

    <!-- TARJETAS KPI -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card kpi-red">
                <div class="kpi-title">Recaudación Total (Cuotas)</div>
                <div class="kpi-value">${{ number_format($totalIngresos, 2) }}</div>
                <div class="kpi-comp">
                    <span class="{{ $difIngresos >= 0 ? 'text-up' : 'text-down' }}">
                        {{ $difIngresos >= 0 ? '+$' : '-$' }}{{ number_format(abs($difIngresos), 2) }}
                        ({{ $porcIngresos >= 0 ? '+' : '' }}{{ number_format($porcIngresos, 1) }}%)
                    </span>
                    vs ciclo anterior
                </div>
            </td>
            <td class="kpi-card kpi-blue">
                <div class="kpi-title">Transacciones de Pago</div>
                <div class="kpi-value">{{ number_format($totalTransacciones) }}</div>
                <div class="kpi-comp">
                    <span class="{{ $difTransacciones >= 0 ? 'text-up' : 'text-down' }}">
                        {{ $difTransacciones >= 0 ? '+' : '' }}{{ number_format($difTransacciones) }} tr.
                        ({{ $porcTransacciones >= 0 ? '+' : '' }}{{ number_format($porcTransacciones, 1) }}%)
                    </span>
                    vs ciclo anterior
                </div>
            </td>
            <td class="kpi-card kpi-green">
                <div class="kpi-title">Ingreso Promedio por Pago</div>
                <div class="kpi-value">${{ number_format($ingresoPromedio, 2) }}</div>
                <div class="kpi-comp">
                    <span class="{{ $difPromedio >= 0 ? 'text-up' : 'text-down' }}">
                        {{ $difPromedio >= 0 ? '+$' : '-$' }}{{ number_format(abs($difPromedio), 2) }}
                        ({{ $porcPromedio >= 0 ? '+' : '' }}{{ number_format($porcPromedio, 1) }}%)
                    </span>
                    vs ciclo anterior
                </div>
            </td>
        </tr>
    </table>

    <!-- 1. EVOLUCIÓN MENSUAL -->
    <div class="section-title">1. Evolución Mensual de Ingresos</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 28%;">Periodo (Año-Mes)</th>
                <th style="width: 40%;" class="text-center">Distribución</th>
                <th style="width: 14%;" class="text-center">% del Total</th>
                <th style="width: 18%;" class="text-center">Monto Recaudado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($evolucion as $item)
                @php
                    $porcentaje = $totalIngresos > 0 ? ($item->total / $totalIngresos) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $item->mes_anio }}</td>
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
                    <td colspan="4" class="empty-row">No se registran ingresos para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        @if($evolucion->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2">Total del periodo</td>
                    <td class="text-center">100.0%</td>
                    <td class="text-center">${{ number_format($totalIngresos, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- 2. RECAUDACIÓN POR FACULTAD -->
    <div class="section-title">2. Recaudación por Facultad</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 48%;">Facultad</th>
                <th style="width: 20%;" class="text-center">Participación</th>
                <th style="width: 14%;" class="text-center">%</th>
                <th style="width: 18%;" class="text-center">Total Recaudado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ingresosPorFacultad as $item)
                @php
                    $porcentaje = $totalIngresos > 0 ? ($item->total / $totalIngresos) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $item->facultad }}</td>
                    <td>
                        <div class="bar-bg">
                            <div class="bar-fill-green" style="width: {{ min($porcentaje, 100) }}%;"></div>
                        </div>
                    </td>
                    <td class="text-center">{{ number_format($porcentaje, 1) }}%</td>
                    <td class="text-center">${{ number_format($item->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty-row">No hay registros por facultad para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        @if($ingresosPorFacultad->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2">Total Recaudado por Facultad</td>
                    <td class="text-center">100.0%</td>
                    <td class="text-center">${{ number_format($ingresosPorFacultad->sum('total'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- 3. RANKING Y COMPARATIVA POR CARRERA -->
    <div class="section-title">3. Recaudación por Carrera (Ranking)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">#</th>
                <th style="width: 40%;">Carrera</th>
                <th style="width: 20%;" class="text-center">Participación</th>
                <th style="width: 14%;" class="text-center">%</th>
                <th style="width: 18%;" class="text-center">Total Recaudado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ingresosPorCarrera as $index => $item)
                @php
                    $porcentaje = $totalIngresos > 0 ? ($item->total / $totalIngresos) * 100 : 0;
                @endphp
                <tr>
                    <td class="text-center">
                        <span class="rank-badge {{ $index === 0 ? 'rank-first' : '' }}">{{ $index + 1 }}</span>
                    </td>
                    <td>{{ $item->carrera }}</td>
                    <td>
                        <div class="bar-bg">
                            <div class="bar-fill-blue" style="width: {{ min($porcentaje, 100) }}%;"></div>
                        </div>
                    </td>
                    <td class="text-center">{{ number_format($porcentaje, 1) }}%</td>
                    <td class="text-center">${{ number_format($item->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-row">No hay registros por carrera para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        @if($ingresosPorCarrera->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3">Total Recaudado por Carrera</td>
                    <td class="text-center">100.0%</td>
                    <td class="text-center">${{ number_format($ingresosPorCarrera->sum('total'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        Documento generado automáticamente por Nexum · Uso institucional interno · {{ $fechaGeneracion }}
    </div>
</body>
</html>
