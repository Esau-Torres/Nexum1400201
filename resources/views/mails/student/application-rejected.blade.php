<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud rechazada</title>
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
                                Solicitud rechazada
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
                                Estimado/a <strong>{{ $nombre }}</strong>,
                            </p>

                            <p style="margin:0 0 16px 0; font-size:15px; line-height:1.6; color:#333;">
                                Le informamos que su solicitud de inscripción al Sistema Académico NEXUM
                                ha sido <strong style="color:#dc3545;">RECHAZADA</strong> por el siguiente motivo:
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

                            <p style="margin:0 0 16px 0; font-size:15px; line-height:1.6; color:#333;">
                                Si considera que esta decisión es un error o desea presentar la documentación
                                corregida, puede comunicarse directamente con la Administración Académica:
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="font-size:14px; color:#333; margin:16px 0;">
                                <tr>
                                    <td style="padding:6px 0; color:#666; width:30%;">
                                        <strong>Contacto:</strong>
                                    </td>
                                    <td style="padding:6px 0;">
                                        {{ $contacto }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:6px 0; color:#666;">
                                        <strong>Fecha del dictamen:</strong>
                                    </td>
                                    <td style="padding:6px 0;">
                                        {{ $fecha }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0 0; font-size:14px; color:#666; line-height:1.6;">
                                Agradecemos su interés en formar parte de nuestra institución y le invitamos
                                a intentarlo nuevamente en el futuro.
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