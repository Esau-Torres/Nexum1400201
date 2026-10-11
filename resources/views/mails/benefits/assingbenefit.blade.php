<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Beneficio asignado</title>
</head>
<body style="margin:0; padding:0; background-color:#f0f2f5; font-family: Arial, Helvetica, sans-serif; color:#121212;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="background-color:#f0f2f5; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
                       style="max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="background:#dc3545; padding:32px 24px;">
                            <img src="https://umasantaana.edu.sv/wp-content/uploads/2022/09/logowebumasantaana.png"
                                 alt="UMA" width="200"
                                 style="display:block; width:200px; max-width:200px; height:auto; border:0;">
                            <p style="margin: 12px 0 0 0; color:#ffe6e6; font-size:13px; text-transform:uppercase; letter-spacing:1px; font-weight:600;">
                                Sistema Académico UMA
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:32px;">

                            <p style="margin:0 0 16px 0; font-size:16px;">
                                Estimado/a <strong>{{ $usuario->name }}</strong>,
                            </p>

                            <p style="margin:0 0 16px 0; font-size:15px; line-height:1.6; color:#333;">
                                Le informamos que se le ha asignado un <strong>beneficio estudiantil</strong>
                                para el ciclo lectivo <strong>{{ $ciclo->nombre_ciclo ?? '—' }}</strong>.
                            </p>

                            {{-- Beneficio --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="background:#fff9e1; border:1px solid #f5d76e; border-radius:8px; margin:24px 0;">
                                <tr>
                                    <td style="padding:20px 24px;">

                                        <p style="margin:0 0 4px 0; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:#8a6d00; font-weight:700;">
                                            Tipo de beneficio
                                        </p>
                                        <p style="margin:0 0 16px 0; font-size:16px; font-weight:700;">
                                            {{ $beneficio->tipo_beneficio->label() }}
                                        </p>

                                        <p style="margin:0 0 4px 0; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:#8a6d00; font-weight:700;">
                                            Convenio / resolución
                                        </p>
                                        <p style="margin:0 0 16px 0; font-size:15px; font-weight:600;">
                                            {{ $beneficio->nombre_convenio }}
                                        </p>

                                        @if($beneficio->resolucion_academica)
                                            <p style="margin:0 0 4px 0; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:#8a6d00; font-weight:700;">
                                                Resolución académica
                                            </p>
                                            <p style="margin:0; font-size:15px; font-weight:600;">
                                                {{ $beneficio->resolucion_academica }}
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            {{-- Cobertura --}}
                            <h2 style="font-size:14px; text-transform:uppercase; letter-spacing:0.5px; color:#dc3545; margin:24px 0 12px 0;">
                                Cobertura del beneficio
                            </h2>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="font-size:14px; color:#333;">
                                @if($beneficio->porcentaje_estudiante !== null)
                                    <tr>
                                        <td style="padding:8px 0; border-bottom:1px solid #eee; width:60%; color:#666;">
                                            Porcentaje que cubre el estudiante
                                        </td>
                                        <td style="padding:8px 0; border-bottom:1px solid #eee; font-weight:600; text-align:right;">
                                            {{ number_format((float) $beneficio->porcentaje_estudiante, 2) }}%
                                        </td>
                                    </tr>
                                @endif
                                @if($beneficio->porcentaje_universidad !== null)
                                    <tr>
                                        <td style="padding:8px 0; border-bottom:1px solid #eee; color:#666;">
                                            Porcentaje que cubre la universidad
                                        </td>
                                        <td style="padding:8px 0; border-bottom:1px solid #eee; font-weight:600; text-align:right;">
                                            {{ number_format((float) $beneficio->porcentaje_universidad, 2) }}%
                                        </td>
                                    </tr>
                                @endif
                                @if($beneficio->monto_fijo_cuota !== null)
                                    <tr>
                                        <td style="padding:8px 0; border-bottom:1px solid #eee; color:#666;">
                                            Monto fijo de cuota
                                        </td>
                                        <td style="padding:8px 0; border-bottom:1px solid #eee; font-weight:600; text-align:right;">
                                            ${{ number_format((float) $beneficio->monto_fijo_cuota, 2) }}
                                        </td>
                                    </tr>
                                @endif

                                <tr>
                                    <td style="padding:8px 0; color:#666;">Incluye matrícula</td>
                                    <td style="padding:8px 0; text-align:right;">
                                        {{ $beneficio->incluye_matricula ? 'Sí' : 'No' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#666;">Incluye laboratorio</td>
                                    <td style="padding:8px 0; text-align:right;">
                                        {{ $beneficio->incluye_laboratorio ? 'Sí' : 'No' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#666;">Incluye derechos de grado</td>
                                    <td style="padding:8px 0; text-align:right;">
                                        {{ $beneficio->incluye_derechos_grado ? 'Sí' : 'No' }}
                                    </td>
                                </tr>
                            </table>

                            {{-- Motivo --}}
                            @if($motivo)
                                <div style="margin-top:24px; padding:16px; background:#f8f9fa; border-left:4px solid #dc3545; border-radius:4px;">
                                    <p style="margin:0; font-size:13px; color:#333;">
                                        <strong>Motivo:</strong> {{ $motivo }}
                                    </p>
                                </div>
                            @endif

                            {{-- CTA --}}
                            <div style="text-align:center; margin:32px 0 8px 0;">
                                <a href="{{ route('login') }}"
                                   style="display:inline-block; background:#dc3545; color:#ffffff; padding:14px 32px; border-radius:8px; text-decoration:none; font-weight:600; font-size:15px;">
                                    Ingresar al sistema
                                </a>
                            </div>

                            <p style="margin:24px 0 0 0; font-size:12px; color:#999; text-align:center;">
                                Fecha de asignación: {{ $fecha }}
                            </p>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f8f9fa; padding:20px 32px; text-align:center; font-size:12px; color:#666;">
                            Este correo fue enviado automáticamente por el Sistema Académico NEXUM.<br>
                            Por favor, no responda a este mensaje.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>