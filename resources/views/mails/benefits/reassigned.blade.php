<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficio actualizado</title>
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
                        <td style="background:#dc3545; padding:28px 32px;">
                            <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:700;">
                                Beneficio actualizado
                            </h1>
                            <p style="margin:6px 0 0 0; color:#ffd7d7; font-size:14px;">
                                Sistema Académico UMA — NEXUM
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:32px;">

                            <p style="margin:0 0 16px 0; font-size:16px;">
                                Estimado/a <strong>{{ $usuario->name }}</strong>,
                            </p>

                            <p style="margin:0 0 24px 0; font-size:15px; line-height:1.6; color:#333;">
                                Le informamos que su beneficio estudiantil ha sido
                                <strong style="color:#198754;">ACTUALIZADO</strong>.
                                A continuación encontrará el detalle del cambio:
                            </p>

                            {{-- Motivo --}}
                            @if(!empty($motivo))
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                       style="background:#fff9e1; border-left:4px solid #f5d76e; border-radius:4px; margin:0 0 24px 0;">
                                    <tr>
                                        <td style="padding:16px 20px;">
                                            <p style="margin:0 0 4px 0; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:#8a6d00; font-weight:700;">
                                                Motivo del cambio
                                            </p>
                                            <p style="margin:0; font-size:14px; line-height:1.6; color:#5a4700;">
                                                {{ $motivo }}
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            {{-- Beneficio anterior --}}
                            <h2 style="font-size:14px; text-transform:uppercase; letter-spacing:0.5px; color:#666; margin:0 0 12px 0;">
                                Beneficio anterior
                            </h2>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="font-size:14px; color:#333; background:#f8f9fa; border-radius:8px; margin:0 0 24px 0;">
                                <tr>
                                    <td style="padding:14px 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding:6px 0; color:#666; width:45%;">
                                                    Tipo
                                                </td>
                                                <td style="padding:6px 0; font-weight:600;">
                                                    {{ $anterior->tipo_beneficio->label() }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0; color:#666;">
                                                    Convenio
                                                </td>
                                                <td style="padding:6px 0; font-weight:600;">
                                                    {{ $anterior->nombre_convenio }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0; color:#666;">
                                                    Cobertura (% Est / % UMA)
                                                </td>
                                                <td style="padding:6px 0; font-weight:600;">
                                                    {{ $anterior->porcentaje_estudiante }}% / {{ $anterior->porcentaje_universidad }}%
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Beneficio nuevo --}}
                            <h2 style="font-size:14px; text-transform:uppercase; letter-spacing:0.5px; color:#198754; margin:0 0 12px 0;">
                                Beneficio nuevo
                            </h2>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="font-size:14px; color:#333; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; margin:0 0 24px 0;">
                                <tr>
                                    <td style="padding:14px 20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding:6px 0; color:#666; width:45%;">
                                                    Tipo
                                                </td>
                                                <td style="padding:6px 0; font-weight:600;">
                                                    {{ $nuevo->tipo_beneficio->label() }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0; color:#666;">
                                                    Convenio / Beca
                                                </td>
                                                <td style="padding:6px 0; font-weight:600;">
                                                    {{ $nuevo->nombre_convenio }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0; color:#666;">
                                                    Cobertura (% Est / % UMA)
                                                </td>
                                                <td style="padding:6px 0; font-weight:600;">
                                                    {{ $nuevo->porcentaje_estudiante }}% / {{ $nuevo->porcentaje_universidad }}%
                                                </td>
                                            </tr>
                                            @if($nuevo->monto_fijo_cuota)
                                                <tr>
                                                    <td style="padding:6px 0; color:#666;">
                                                        Monto fijo de cuota
                                                    </td>
                                                    <td style="padding:6px 0; font-weight:600;">
                                                        ${{ number_format($nuevo->monto_fijo_cuota, 2) }}
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td style="padding:6px 0; color:#666;">
                                                    Ciclo lectivo
                                                </td>
                                                <td style="padding:6px 0; font-weight:600;">
                                                    {{ $nuevo->cicloLectivo->nombre_ciclo ?? '—' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0; color:#666;">
                                                    Resolución académica
                                                </td>
                                                <td style="padding:6px 0; font-weight:600;">
                                                    {{ $nuevo->resolucion_academica ?? '—' }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Cobertura adicional --}}
                            <h2 style="font-size:14px; text-transform:uppercase; letter-spacing:0.5px; color:#666; margin:0 0 12px 0;">
                                Cobertura adicional
                            </h2>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="font-size:14px; color:#333; margin:0 0 24px 0;">
                                <tr>
                                    <td style="padding:6px 0;">
                                        <span style="display:inline-block; padding:2px 8px; border-radius:12px; font-size:12px; {{ $nuevo->incluye_matricula ? 'background:#d1fae5; color:#065f46;' : 'background:#f3f4f6; color:#6b7280;' }}">
                                            {{ $nuevo->incluye_matricula ? '✓' : '✗' }} Matrícula
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;">
                                        <span style="display:inline-block; padding:2px 8px; border-radius:12px; font-size:12px; {{ $nuevo->incluye_laboratorio ? 'background:#d1fae5; color:#065f46;' : 'background:#f3f4f6; color:#6b7280;' }}">
                                            {{ $nuevo->incluye_laboratorio ? '✓' : '✗' }} Laboratorio de informática
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0;">
                                        <span style="display:inline-block; padding:2px 8px; border-radius:12px; font-size:12px; {{ $nuevo->incluye_derechos_grado ? 'background:#d1fae5; color:#065f46;' : 'background:#f3f4f6; color:#6b7280;' }}">
                                            {{ $nuevo->incluye_derechos_grado ? '✓' : '✗' }} Derechos de grado
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 16px 0; font-size:15px; line-height:1.6; color:#333;">
                                Este cambio aplica a partir del ciclo lectivo indicado. Los cargos académicos
                                futuros se generarán con la nueva cobertura.
                            </p>

                            <p style="margin:0 0 24px 0; font-size:14px; color:#666; line-height:1.6;">
                                Si tiene dudas sobre este cambio, comuníquese con Administración Académica.
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