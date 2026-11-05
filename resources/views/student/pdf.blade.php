<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PENSUM ACADEMICO</title>
    <style>
        /* Configuración para forzar orientación horizontal y bordes ajustados a 1 página */
        @page {
            size: letter landscape;
            margin: 6mm 6mm 4mm 6mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }

        /* Encabezado ultra compacto */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.5px solid #0f172a;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }

        .title-h1 {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: -0.2px;
            margin: 0;
        }

        .carrera-txt {
            font-size: 9px;
            font-weight: 700;
            color: #b91c1c;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .student-box {
            text-align: right;
            font-size: 8.5px;
        }

        .student-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 9.5px;
        }

        .student-code {
            color: #475569;
        }

        .student-code strong {
            background-color: #f1f5f9;
            padding: 1px 4px;
            border-radius: 3px;
            color: #0f172a;
        }

        /* Barra de simbología / leyenda */
        .legend-bar {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 3px 6px;
            margin-bottom: 8px;
            font-size: 7.5px;
        }

        .legend-tag {
            display: inline-block;
            margin-right: 8px;
            font-weight: 600;
        }

        .legend-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin-right: 3px;
            vertical-align: middle;
        }

        /* Estructura de Malla en Columnas por Ciclo (100% compatible con Dompdf) */
        .malla-container {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px 0;
            table-layout: fixed;
        }

        .malla-col {
            vertical-align: top;
            padding: 0;
        }

        .ciclo-header {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 800;
            font-size: 8px;
            text-align: center;
            padding: 3px 2px;
            border-radius: 4px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Tarjeta individual de Materia */
        .materia-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 3px 4px;
            margin-bottom: 4px;
            page-break-inside: avoid;
        }

        /* Fila de Código y UV */
        .card-top {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .card-top td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .card-code {
            font-size: 7.5px;
            font-weight: 800;
            color: #334155;
            background: #f1f5f9;
            padding: 1px 3px;
            border-radius: 2px;
            display: inline-block;
        }

        .card-uv {
            text-align: right;
            font-size: 7px;
            font-weight: 700;
            color: #0284c7;
        }

        /* Nombre de Materia */
        .card-name {
            font-size: 7.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.15;
            margin: 2px 0 3px 0;
            word-wrap: break-word;
        }

        /* Prerrequisitos */
        .card-prereq {
            border-top: 0.5px dashed #e2e8f0;
            padding-top: 2px;
            margin-bottom: 3px;
            font-size: 6.8px;
            color: #64748b;
            line-height: 1.1;
        }

        .card-prereq strong {
            color: #475569;
        }

        .prereq-uv {
            color: #b91c1c;
            font-weight: 600;
        }

        /* Badge de Estado */
        .badge {
            display: block;
            width: 100%;
            text-align: center;
            font-size: 6.8px;
            font-weight: 800;
            padding: 1.5px 0;
            border-radius: 2px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        /* Estados de Borde Lateral y Badges */
        .status-actual { border-left: 3px solid #0284c7; }
        .status-aprobada { border-left: 3px solid #16a34a; }
        .status-reprobada { border-left: 3px solid #dc2626; }
        .status-retirada { border-left: 3px solid #d97706; }
        .status-por-cursar { border-left: 3px solid #64748b; }

        .badge-actual { background-color: #0284c7; color: #ffffff; }
        .badge-aprobada { background-color: #16a34a; color: #ffffff; }
        .badge-reprobada { background-color: #dc2626; color: #ffffff; }
        .badge-retirada { background-color: #d97706; color: #ffffff; }
        .badge-por-cursar { background-color: #64748b; color: #ffffff; }

        .dot-actual { background-color: #0284c7; }
        .dot-aprobada { background-color: #16a34a; }
        .dot-reprobada { background-color: #dc2626; }
        .dot-retirada { background-color: #d97706; }
        .dot-por-cursar { background-color: #64748b; }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <table class="header-table">
        <tr>
            <td>
                <div class="title-h1">Malla Curricular / Pensum Académico</div>
                <div class="carrera-txt">Carrera: {{ $alumno->carrera->nombre ?? 'N/A' }}</div>
            </td>
            <td class="student-box">
                <div class="student-name">{{ $alumno->usuario?->name ?? 'Nombre no disponible' }}</div>
                <div class="student-code">Código: <strong>{{ $alumno->codigo_estudiante }}</strong></div>
            </td>
        </tr>
    </table>

    <!-- Leyenda de Estados en línea -->
    <div class="legend-bar">
        <strong style="color: #475569; margin-right: 6px;">ESTADOS:</strong>
        <span class="legend-tag"><span class="legend-dot dot-aprobada"></span> Aprobada</span>
        <span class="legend-tag"><span class="legend-dot dot-actual"></span> Cursando</span>
        <span class="legend-tag"><span class="legend-dot dot-por-cursar"></span> Por cursar</span>
        <span class="legend-tag"><span class="legend-dot dot-reprobada"></span> Reprobada</span>
        <span class="legend-tag"><span class="legend-dot dot-retirada"></span> Retirada</span>
    </div>

    <!-- Malla en Columnas (Cada TD es un Ciclo) -->
    <table class="malla-container">
        <tr>
            @foreach($pensumPorCiclos as $ciclo => $materias)
                <td class="malla-col">
                    <div class="ciclo-header">Ciclo {{ $ciclo }}</div>

                    @foreach($materias as $materia)
                        @php
                            $estado = strtoupper($materia->estado_visual ?? '');
                            
                            $estadoTexto = match($estado) {
                                'ACTUAL' => 'Cursando',
                                'APROBADA' => 'Aprobada',
                                'REPROBADA' => 'Reprobada',
                                'RETIRADA' => 'Retirada',
                                default => 'Por cursar'
                            };

                            $estadoEstilo = match($materia->estado_visual) {
                                'ACTUAL' => ['card' => 'status-actual', 'badge' => 'badge-actual'],
                                'APROBADA' => ['card' => 'status-aprobada', 'badge' => 'badge-aprobada'],
                                'REPROBADA' => ['card' => 'status-reprobada', 'badge' => 'badge-reprobada'],
                                'RETIRADA' => ['card' => 'status-retirada', 'badge' => 'badge-retirada'],
                                default => ['card' => 'status-por-cursar', 'badge' => 'badge-por-cursar']
                            };
                        @endphp

                        <div class="materia-card {{ $estadoEstilo['card'] }}">
                            <!-- Código y UV -->
                            <table class="card-top">
                                <tr>
                                    <td><span class="card-code">{{ $materia->codigo_materia }}</span></td>
                                    <td class="card-uv">{{ $materia->unidades_valorativas }} UV</td>
                                </tr>
                            </table>

                            <!-- Nombre Materia -->
                            <div class="card-name">
                                {{ $materia->nombre_materia }}
                            </div>

                            <!-- Prerrequisitos -->
                            <div class="card-prereq">
                                <strong>Pre:</strong> {{ $materia->nombre_prerequisito ?? 'Ninguno' }}
                                @if($materia->uv_minimas_requeridas > 0)
                                    <span class="prereq-uv">({{ $materia->uv_minimas_requeridas }} UV)</span>
                                @endif
                            </div>

                            <!-- Estado -->
                            <span class="badge {{ $estadoEstilo['badge'] }}">
                                {{ ucfirst(strtolower($estadoTexto)) }}
                            </span>
                        </div>
                    @endforeach
                </td>
            @endforeach
        </tr>
    </table>

</body>
</html>