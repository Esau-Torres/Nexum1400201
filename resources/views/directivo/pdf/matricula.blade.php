<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Análisis de Población (Matrícula)</title>
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
        .kpi-green { border-left: 4px solid #198754; }
        .kpi-gray { border-left: 4px solid #6c757d; }
        .kpi-amber { border-left: 4px solid #f59e0b; }
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
        .text-neutral { color: #6b7280; font-weight: bold; }
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
        .bar-fill-purple { background-color: #6f42c1; height: 7px; }
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
        $textoCiclo = $cicloSeleccionadoObj
            ? 'Ciclo ' . ($cicloSeleccionadoObj->numero_ciclo ?? $cicloSeleccionadoObj->nombre_ciclo ?? '') . '-' . $cicloSeleccionadoObj->anio . (isset($cicloSeleccionadoObj->estado) ? " ({$cicloSeleccionadoObj->estado})" : '')
            : 'Sin ciclo seleccionado';

        $porcNuevo = $totalMatriculados > 0 ? ($nuevoIngreso / $totalMatriculados) * 100 : 0;
        $porcRetenidos = $totalMatriculados > 0 ? ($alumnosRetenidos / $totalMatriculados) * 100 : 0;

        $claseVar = 'text-neutral';
        if (str_starts_with($varMatricula, '+')) {
            $claseVar = 'text-up';
        } elseif (str_starts_with($varMatricula, '-')) {
            $claseVar = 'text-down';
        }

        $maxHistorico = $evolucionHistorica->max('total') ?: 1;
        $etiquetaEstadoAlumno = $esCicloVivo ? 'Padrón vigente (ciclo en curso)' : 'Dentro del ciclo seleccionado';
    @endphp

    <!-- ENCABEZADO -->
    <table class="header-table">
        <tr>
            <td>
                <h1 class="title">Informe de Análisis de Población (Matrícula)</h1>
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
                <td style="width: 50%;"><span class="filter-label">Facultad:</span> {{ $nombreFacultad }}</td>
            </tr>
            <tr>
                <td colspan="2"><span class="filter-label">Carrera:</span> {{ $nombreCarrera }}</td>
            </tr>
        </table>
    </div>

    <!-- TARJETAS KPI (Fila 1: 3 tarjetas) -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card kpi-red" style="width: 33.33%;">
                <div class="kpi-title">Total Matriculados</div>
                <div class="kpi-value">{{ number_format($totalMatriculados) }}</div>
                <div class="kpi-comp">
                    <span class="{{ $claseVar }}">{{ $varMatricula }}</span> vs ciclo anterior
                </div>
            </td>
            <td class="kpi-card kpi-blue" style="width: 33.33%;">
                <div class="kpi-title">Nuevo Ingreso</div>
                <div class="kpi-value">{{ number_format($nuevoIngreso) }}</div>
                <div class="kpi-comp">{{ number_format($porcNuevo, 1) }}% de la matrícula del ciclo</div>
            </td>
            <td class="kpi-card kpi-purple" style="width: 33.33%;">
                <div class="kpi-title">Estudiantes Continuos</div>
                <div class="kpi-value">{{ number_format($alumnosRetenidos) }}</div>
                <div class="kpi-comp">{{ number_format($porcRetenidos, 1) }}% de la matrícula del ciclo</div>
            </td>
        </tr>
    </table>

    <!-- TARJETAS KPI (Fila 2: 3 tarjetas) -->
    <table class="kpi-table" style="margin-bottom: 14px;">
        <tr>
            <td class="kpi-card kpi-green" style="width: 33.33%;">
                <div class="kpi-title">Estudiantes Activos</div>
                <div class="kpi-value">{{ number_format($estudiantesActivos) }}</div>
                <div class="kpi-comp">{{ $etiquetaEstadoAlumno }}</div>
            </td>
            <td class="kpi-card kpi-amber" style="width: 33.33%;">
                <div class="kpi-title">Estudiantes Egresados</div>
                <div class="kpi-value">{{ number_format($estudiantesEgresados) }}</div>
                <div class="kpi-comp">Total de egresados registrados</div>
            </td>
            <td class="kpi-card kpi-gray" style="width: 33.33%;">
                <div class="kpi-title">Estudiantes Inactivos</div>
                <div class="kpi-value">{{ number_format($estudiantesInactivos) }}</div>
                <div class="kpi-comp">{{ $etiquetaEstadoAlumno }}</div>
            </td>
        </tr>
    </table>

    <!-- 1. COMPOSICIÓN POBLACIONAL -->
    <div class="section-title">1. Composición Poblacional del Ciclo</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 48%;">Categoría</th>
                <th style="width: 22%;" class="text-center">Proporción</th>
                <th style="width: 14%;" class="text-center">%</th>
                <th style="width: 16%;" class="text-center">Estudiantes</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Estudiantes de Nuevo Ingreso</td>
                <td>
                    <div class="bar-bg">
                        <div class="bar-fill-blue" style="width: {{ min($porcNuevo, 100) }}%;"></div>
                    </div>
                </td>
                <td class="text-center">{{ number_format($porcNuevo, 1) }}%</td>
                <td class="text-center">{{ number_format($nuevoIngreso) }}</td>
            </tr>
            <tr>
                <td>Estudiantes Continuos (Antiguo Ingreso)</td>
                <td>
                    <div class="bar-bg">
                        <div class="bar-fill-purple" style="width: {{ min($porcRetenidos, 100) }}%;"></div>
                    </div>
                </td>
                <td class="text-center">{{ number_format($porcRetenidos, 1) }}%</td>
                <td class="text-center">{{ number_format($alumnosRetenidos) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Matriculados en el Ciclo</td>
                <td class="text-center">{{ $totalMatriculados > 0 ? '100.0%' : '0.0%' }}</td>
                <td class="text-center">{{ number_format($totalMatriculados) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- 2. EVOLUCIÓN HISTÓRICA DE MATRÍCULA -->
    <div class="section-title">2. Evolución Histórica de Matrícula (Últimos Ciclos)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 34%;">Ciclo Lectivo</th>
                <th style="width: 14%;" class="text-center">Año</th>
                <th style="width: 36%;" class="text-center">Comparativa Relativa</th>
                <th style="width: 16%;" class="text-center">Matriculados</th>
            </tr>
        </thead>
        <tbody>
            @forelse($evolucionHistorica as $item)
                @php
                    $anchoBarra = ($item->total / $maxHistorico) * 100;
                @endphp
                <tr>
                    <td>{{ $item->nombre_ciclo }}</td>
                    <td class="text-center">{{ $item->anio }}</td>
                    <td>
                        <div class="bar-bg">
                            <div class="bar-fill-red" style="width: {{ min($anchoBarra, 100) }}%;"></div>
                        </div>
                    </td>
                    <td class="text-center"><strong>{{ number_format($item->total) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty-row">No existen registros históricos de matrícula.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 3. MATRÍCULA POR FACULTAD -->
    <div class="section-title">3. Distribución de Matrícula por Facultad</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 48%;">Facultad</th>
                <th style="width: 22%;" class="text-center">Participación</th>
                <th style="width: 14%;" class="text-center">%</th>
                <th style="width: 16%;" class="text-center">Estudiantes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($matriculaPorFacultad as $item)
                @php
                    $porcentaje = $totalMatriculados > 0 ? ($item->total / $totalMatriculados) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $item->facultad }}</td>
                    <td>
                        <div class="bar-bg">
                            <div class="bar-fill-green" style="width: {{ min($porcentaje, 100) }}%;"></div>
                        </div>
                    </td>
                    <td class="text-center">{{ number_format($porcentaje, 1) }}%</td>
                    <td class="text-center">{{ number_format($item->total) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty-row">No hay estudiantes matriculados por facultad para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        @if($matriculaPorFacultad->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2">Total General</td>
                    <td class="text-center">100.0%</td>
                    <td class="text-center">{{ number_format($totalMatriculados) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- 4. MATRÍCULA POR CARRERA -->
    <div class="section-title">4. Matrícula por Carrera (Ranking)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">#</th>
                <th style="width: 40%;">Carrera</th>
                <th style="width: 22%;" class="text-center">Participación</th>
                <th style="width: 14%;" class="text-center">%</th>
                <th style="width: 16%;" class="text-center">Estudiantes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($matriculaPorCarrera as $index => $item)
                @php
                    $porcentaje = $totalMatriculados > 0 ? ($item->total / $totalMatriculados) * 100 : 0;
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
                    <td class="text-center">{{ number_format($item->total) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-row">No hay estudiantes matriculados por carrera para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Documento generado automáticamente por Nexum · Uso institucional interno · {{ $fechaGeneracion }}
    </div>
</body>
</html>
