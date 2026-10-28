<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficio revocado</title>
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
                                Beneficio revocado
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

                            <p style="margin:0 0 16px 0; font-size:15px; line-height:1.6; color:#333;">
                                Le informamos que su beneficio estudiantil ha sido
                                <strong style="color:#dc3545;">REVOCADO</strong> por el siguiente motivo:
                            </p>

                            {{-- Motivo --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="background:#fff5f5; border-left:4px solid #dc3545; border-radius:4px; margin:24px 0;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <p style="margin:0; font-size:14px; line-height:1.6; color:#5a1a1a; font-style:italic;">
                                            "{{ $motivo }}"
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Info del beneficio revocado --}}
                            <h2 style="font-size:14px; text-transform:uppercase; letter-spacing:0.5px; color:#dc3545; margin:24px 0 12px 0;">
                                Beneficio revocado
                            </h2>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="font-size:14px; color:#333;">
                                <tr>
                                    <td style="padding:8px 0; border-bottom:1px solid #eee; width:40%; color:#666;">
                                        Tipo
                                    </td>
                                    <td style="padding:8px 0; border-bottom:1px solid #eee; font-weight:600;">
                                        {{ $beneficio->tipo_beneficio->label() }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; border-bottom:1px solid #eee; color:#666;">
                                        Convenio / Beca
                                    </td>
                                    <td style="padding:8px 0; border-bottom:1px solid #eee; font-weight:600;">
                                        {{ $beneficio->nombre_convenio }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; border-bottom:1px solid #eee; color:#666;">
                                        Ciclo lectivo
                                    </td>
                                    <td style="padding:8px 0; border-bottom:1px solid #eee; font-weight:600;">
                                        {{ $beneficio->cicloLectivo->nombre_ciclo ?? '—' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 0; color:#666;">
                                        Fecha de revocación
                                    </td>
                                    <td style="padding:8px 0; font-weight:600;">
                                        {{ $fecha }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 16px 0; font-size:15px; line-height:1.6; color:#333;">
                                A partir de la fecha indicada, los cargos académicos aplicables se emitirán
                                sin la cobertura del beneficio revocado. Si considera que esta decisión es
                                un error o desea presentar documentación de respaldo, comuníquese con
                                Administración Académica.
                            </p>

                            <p style="margin:0 0 24px 0; font-size:14px; color:#666; line-height:1.6;">
                                Agradecemos su atención.
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