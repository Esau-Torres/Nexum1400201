<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proyección de Secciones</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #212529;
            padding: 30px;
        }

        .header {
            border-bottom: 3px solid #dc3545;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            color: #dc3545;
            margin-bottom: 4px;
        }
        .header .subtitle {
            font-size: 12px;
            color: #6c757d;
        }

        .meta {
            background: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .meta table {
            width: 100%;
        }
        .meta td {
            padding: 2px 0;
        }
        .meta .label {
            color: #6c757d;
            width: 180px;
        }
        .meta .value {
            font-weight: bold;
        }

        .kpis {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .kpi {
            display: table-cell;
            width: 25%;
            padding: 12px;
            border: 1px solid #dee2e6;
            text-align: center;
            vertical-align: middle;
        }
        .kpi .number {
            font-size: 22px;
            font-weight: bold;
            color: #dc3545;
        }
        .kpi .label {
            font-size: 9px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #dc3545;
            padding: 10px 0 6px 0;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 10px;
            margin-top: 20px;
        }

        .ciclo-title {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table.data th {
            background: #e9ecef;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #dee2e6;
            font-size: 9px;
            text-transform: uppercase;
            color: #495057;
        }
        table.data td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
        }
        table.data tr:nth-child(even) {
            background: #f8f9fa;
        }
        table.data .center { text-align: center; }
        table.data .code { font-family: 'Courier New', monospace; color: #6c757d; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-danger { background: #dc3545; color: white; }
        .badge-light { background: #f8f9fa; color: #212529; border: 1px solid #dee2e6; }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 9px;
            color: #6c757d;
            text-align: center;
        }

        .empty {
            padding: 40px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h1>Proyección de Secciones</h1>
        <div class="subtitle">Planificación para el próximo ciclo lectivo</div>
    </div>

    {{-- Meta --}}
    <div class="meta">
        <table>
            <tr>
                <td class="label">Carrera:</td>
                <td class="value">{{ $carrera->nombre }}</td>
            </tr>
            <tr>
                <td class="label">Ciclo de referencia:</td>
                <td class="value">{{ $ciclo->nombre_ciclo }}</td>
            </tr>
            <tr>
                <td class="label">Fecha de generación:</td>
                <td class="value">{{ $fecha }}</td>
            </tr>
        </table>
    </div>

    {{-- KPIs --}}
    <div class="kpis">
        <div class="kpi">
            <div class="number">{{ $proyeccion['resumen']['alumnos_activos'] }}</div>
            <div class="label">Alumnos activos</div>
        </div>
        <div class="kpi">
            <div class="number">{{ $proyeccion['resumen']['alumnos_avanzan'] }}</div>
            <div class="label">Avanzan</div>
        </div>
        <div class="kpi">
            <div class="number">{{ $proyeccion['resumen']['alumnos_egresan'] }}</div>
            <div class="label">Egresan</div>
        </div>
        <div class="kpi">
            <div class="number">{{ $proyeccion['resumen']['secciones_totales'] ?? 0 }}</div>
            <div class="label">Secciones a crear</div>
        </div>
    </div>

    {{-- Detalle --}}
    <div class="section-title">Detalle de secciones a crear</div>

    @forelse($proyeccion['proyeccion'] as $bloque)
        <div class="ciclo-title">
            CICLO {{ $bloque['ciclo'] }} DEL PENSUM
            ({{ count($bloque['materias']) }} materia{{ count($bloque['materias']) != 1 ? 's' : '' }})
        </div>

        <table class="data">
            <thead>
                <tr>
                    <th style="width: 15%;">Código</th>
                    <th>Materia</th>
                    <th class="center" style="width: 15%;">Alumnos</th>
                    <th class="center" style="width: 20%;">Secciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bloque['materias'] as $materia)
                    <tr>
                        <td class="code">{{ $materia['codigo'] }}</td>
                        <td>{{ $materia['nombre'] }}</td>
                        <td class="center">
                            <span class="badge badge-light">{{ $materia['alumnos'] }}</span>
                        </td>
                        <td class="center">
                            <span class="badge badge-danger">
                                {{ $materia['secciones_sugeridas'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <div class="empty">
            No hay materias para proyectar en este ciclo.
        </div>
    @endforelse

    {{-- Footer --}}
    <div class="footer">
        Documento generado automáticamente por el Sistema Académico NEXUM<br>
        Cálculo basado en {{ $proyeccion['resumen']['capacidad_aula_maxima'] ?? 40 }} alumnos por aula
    </div>

</body>
</html>