<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retiro de materia</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #212529;
        }
        .wrapper {
            max-width: 600px;
            margin: 24px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        }
        .header {
            background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
            color: #ffffff;
            padding: 28px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .body {
            padding: 24px;
        }
        .body p {
            font-size: 14px;
            line-height: 1.6;
            color: #495057;
            margin: 0 0 12px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 16px;
            border-radius: 8px;
            margin: 16px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
            border-bottom: 1px dashed #e9ecef;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-row strong {
            color: #212529;
            font-weight: 600;
        }
        .info-row span {
            color: #6c757d;
            text-align: right;
            max-width: 60%;
        }
        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffe69c;
            color: #664d03;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin: 16px 0;
        }
        .btn {
            display: inline-block;
            background-color: #dc3545;
            color: #ffffff !important;
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            margin-top: 16px;
        }
        .footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 16px 24px;
            font-size: 11px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Retiro de Materia Confirmado</h1>
            <p>{{ $ciclo }}</p>
        </div>

        <div class="body">
            <p>Hola <strong>{{ $usuario->name }}</strong>,</p>
            <p>
                Te confirmamos que se ha registrado el <strong>retiro</strong> de la siguiente materia
                en tu récord académico:
            </p>

            <div class="info-box">
                <div class="info-row">
                    <strong>Materia</strong>
                    <span>{{ $materia }}</span>
                </div>
                <div class="info-row">
                    <strong>Código</strong>
                    <span>{{ $detalle->seccion->materia->codigo_materia }}</span>
                </div>
                <div class="info-row">
                    <strong>Unidades Valorativas</strong>
                    <span>{{ $detalle->seccion->materia->unidades_valorativas }} UV</span>
                </div>
                <div class="info-row">
                    <strong>Ciclo Lectivo</strong>
                    <span>{{ $ciclo }}</span>
                </div>
                <div class="info-row">
                    <strong>Grupo</strong>
                    <span>{{ $grupo }}</span>
                </div>
                <div class="info-row">
                    <strong>Docente</strong>
                    <span>{{ $docente }}</span>
                </div>
                <div class="info-row">
                    <strong>Carnet</strong>
                    <span>{{ $alumno->codigo_estudiante }}</span>
                </div>
                <div class="info-row">
                    <strong>Fecha del retiro</strong>
                    <span>{{ $fecha }}</span>
                </div>
            </div>

            @if(!empty($horarios))
                <p style="margin-top: 16px;"><strong>Horarios que se liberan:</strong></p>
                <div class="info-box" style="border-left-color: #6c757d;">
                    @foreach($horarios as $h)
                        <div class="info-row">
                            <strong>{{ $h['dia'] }}</strong>
                            <span>{{ $h['ini'] }} - {{ $h['fin'] }} ·  {{ $h['aula'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($motivo)
                <p style="margin-top: 16px;"><strong>Motivo registrado:</strong></p>
                <p style="background-color: #f8f9fa; padding: 12px; border-radius: 8px; font-style: italic;">
                    "{{ $motivo }}"
                </p>
            @endif

            <div class="alert">
                <strong>Importante:</strong> La materia quedó marcada como <strong>RETIRADA</strong>.
                No podrás volver a cursarla en el ciclo lectivo actual. El promedio final quedó
                sin calificación y <strong>no afectará tu CUM académico</strong>.
            </div>

            <p style="text-align: center;">
                <a href="{{ route('student.record.index') }}" class="btn">
                    Ver mi récord académico
                </a>
            </p>
        </div>

        <div class="footer">
            Este correo fue generado automáticamente por NEXUM.<br>
            Si tienes dudas, contacta a Administración Académica a través del Buzón de Situaciones.
        </div>
    </div>
</body>
</html>