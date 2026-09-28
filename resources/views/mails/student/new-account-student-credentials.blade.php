<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a UMA</title>
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
                                ¡Bienvenido a NEXUM!
                            </h1>
                            <p style="margin:6px 0 0 0; color:#ffd7d7; font-size:14px;">
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
                                Nos complace informarle que su solicitud de inscripción ha sido
                                <strong style="color:#198754;">APROBADA</strong>. Su cuenta ya está activa
                                y puede ingresar al sistema académico con las siguientes credenciales:
                            </p>

                            {{-- Credenciales --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="background:#fff9e1; border:1px solid #f5d76e; border-radius:8px; margin:24px 0;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <p style="margin:0 0 4px 0; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:#8a6d00; font-weight:700;">
                                            Código de estudiante
                                        </p>
                                        <p style="margin:0 0 16px 0; font-size:18px; font-family: 'Courier New', monospace; font-weight:700; color:#121212;">
                                            {{ $codigo }}
                                        </p>

                                        <p style="margin:0 0 4px 0; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:#8a6d00; font-weight:700;">
                                            Correo de acceso
                                        </p>
                                        <p style="margin:0 0 16px 0; font-size:15px; font-weight:600; color:#121212;">
                                            {{ $emailAcceso }}
                                        </p>

                                        <p style="margin:0 0 4px 0; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; color:#8a6d00; font-weight:700;">
                                            Contraseña temporal
                                        </p>
                                        <p style="margin:0; font-size:18px; font-family: 'Courier New', monospace; font-weight:700; color:#121212;">
                                            {{ $password }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 24px 0; font-size:14px; color:#666; line-height:1.6;">
                                <strong>Importante:</strong> Por seguridad, le recomendamos cambiar su contraseña
                                al iniciar sesión por primera vez.
                            </p>

                            {{-- Info académica --}}
                            <h2 style="font-size:14px; text-transform:uppercase; letter-spacing:0.5px; color:#dc3545; margin:24px 0 12px 0;">
                                Información Académica
                            </h2>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="font-size:14px; color:#333;">
                                <tr>
                                    <td style="padding:8px 0; border-bottom:1px solid #eee; width:40%; color:#666;">
                                        Carrera
                                    </td>
                                    <td style="padding:8px 0; border-bottom:1px solid #eee; font-weight:600;">
                                        {{ $carrera->nombre ?? '—' }}
                                    </td>
                                </tr>
                                @if($facultad)
                                    <tr>
                                        <td style="padding:8px 0; border-bottom:1px solid #eee; color:#666;">
                                            Facultad
                                        </td>
                                        <td style="padding:8px 0; border-bottom:1px solid #eee; font-weight:600;">
                                            {{ $facultad->nombre }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:8px 0; border-bottom:1px solid #eee; color:#666;">
                                        Correo institucional
                                    </td>
                                    <td style="padding:8px 0; border-bottom:1px solid #eee; font-weight:600;">
                                        {{ $alumno->correo_institucional }}
                                    </td>
                                </tr>
                                @if($sede)
                                    <tr>
                                        <td style="padding:8px 0; color:#666;">
                                            Sede asignada
                                        </td>
                                        <td style="padding:8px 0; font-weight:600;">
                                            {{ $sede->sede }}
                                        </td>
                                    </tr>
                                @endif
                            </table>

                            {{-- CTA --}}
                            <div style="text-align:center; margin:32px 0 8px 0;">
                                <a href="{{ $loginUrl }}"
                                   style="display:inline-block; background:#dc3545; color:#ffffff; padding:14px 32px; border-radius:8px; text-decoration:none; font-weight:600; font-size:15px;">
                                    Ingresar al sistema
                                </a>
                            </div>

                            <p style="margin:24px 0 0 0; font-size:13px; color:#999; text-align:center;">
                                Si el botón no funciona, copie y pegue este enlace en su navegador:<br>
                                <a href="{{ $loginUrl }}" style="color:#dc3545;">{{ $loginUrl }}</a>
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