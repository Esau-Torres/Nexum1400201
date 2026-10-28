<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pensum Académico</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #dc3545; padding-bottom: 10px; }
        .header h1 { color: #dc3545; margin: 0; font-size: 22px; }
        .header p { margin: 5px 0; color: #666; }
        .info-alumno { margin-bottom: 20px; background-color: #f8f9fa; padding: 10px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #dee2e6; padding: 6px; text-align: left; }
        th { background-color: #f1f3f5; font-weight: bold; }
        .badge { padding: 2px 6px; border-radius: 3px; color: #fff; font-size: 10px; }
        .bg-actual { background-color: #0369a1; }
        .bg-aprobada { background-color: #15803d; }
        .bg-reprobada { background-color: #b91c1c; }
        .bg-retirada { background-color: #b45309; }
        .bg-por-cursar { background-color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pensum Académico</h1>
        <p>
            {{ $alumno->usuario?->name ?? 'Nombre no disponible' }}
            - {{ $alumno->codigo_estudiante }}
        </p>
        <p>Carrera: {{ $alumno->carrera->nombre ?? 'N/A' }}</p>
    </div>

    @foreach($pensumPorCiclos as $ciclo => $materias)
        <h3 style="color: #dc3545; margin-top: 15px;">Ciclo {{ $ciclo }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Materia</th>
                    <th>UV</th>
                    <th>Estado</th>
                    <th>Pre-requisito</th>
                </tr>
            </thead>
            <tbody>
                @foreach($materias as $materia)
                    <tr>
                        <td>{{ $materia->codigo_materia }}</td>
                        <td>{{ $materia->nombre_materia }}</td>
                        <td>{{ $materia->unidades_valorativas }}</td>
                        <td>
                            @php
                                $bgClass = match($materia->estado_visual) {
                                    'ACTUAL' => 'bg-actual',
                                    'APROBADA' => 'bg-aprobada',
                                    'REPROBADA' => 'bg-reprobada',
                                    'RETIRADA' => 'bg-retirada',
                                    default => 'bg-por-cursar'
                                };
                            @endphp
                            <span class="badge {{ $bgClass }}">{{ ucfirst(strtolower($materia->estado_visual)) }}</span>
                        </td>
                        <td>
                            {{ $materia->nombre_prerequisito ?? 'Ninguno' }}
                            @if($materia->uv_minimas_requeridas > 0)
                                (Min. {{ $materia->uv_minimas_requeridas }} UV)
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</body>
</html>