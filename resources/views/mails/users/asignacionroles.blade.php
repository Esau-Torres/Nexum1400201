<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ match($type) {
        'assigned' => 'Roles Asignados',
        'revoked'  => 'Roles Revocados',
        'mixed'    => 'Actualización de Roles',
        default    => 'Roles de Usuario',
    } }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; -webkit-font-smoothing: antialiased;">

    @php
        // Configuración visual del header según el tipo de notificación
        $headerConfig = match($type) {
            'assigned' => [
                'gradient' => '#75ff7c 0%, #23a844 100%',
                'icon'     => '✓',
                'title'    => 'Roles Asignados',
                'subtitle' => 'Sistema de Gestión Institucional NEXUM',
            ],
            'revoked'  => [
                'gradient' => '#dc2626 0%, #991b1b 100%',
                'icon'     => '!',
                'title'    => 'Roles Revocados',
                'subtitle' => 'Sistema de Gestión Institucional NEXUM',
            ],
            'mixed'    => [
                'gradient' => '#4f46e5 0%, #7c3aed 100%',
                'icon'     => '↻',
                'title'    => 'Actualización de Roles',
                'subtitle' => 'Se han realizado cambios en su cuenta',
            ],
            default    => [
                'gradient' => '#6b7280 0%, #374151 100%',
                'icon'     => 'i',
                'title'    => 'Roles Actualizados',
                'subtitle' => 'Sistema de Gestión Institucional NEXUM',
            ],
        };
    @endphp

    <!-- Wrapper exterior -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f3f4f6; padding: 30px 15px;">
        <tr>
            <td align="center">

                <!-- Contenedor principal -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);">

                    <!-- ==============================================
                         HEADER CON GRADIENTE
                         ============================================== -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, {{ $headerConfig['gradient'] }}); padding: 40px 30px 30px; text-align: center;">

                            <!-- Icono circular -->
                            <div style="display: inline-block; width: 70px; height: 70px; background-color: rgba(255, 255, 255, 0.2); border-radius: 50%; line-height: 70px; margin-bottom: 18px; border: 2px solid rgba(255, 255, 255, 0.3);">
                                <span style="font-size: 34px; color: #ffffff; line-height: 70px;">{{ $headerConfig['icon'] }}</span>
                            </div>

                            <!-- Título principal -->
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 700; letter-spacing: -0.3px;">
                                {{ $headerConfig['title'] }}
                            </h1>

                            <!-- Subtítulo -->
                            <p style="margin: 8px 0 0; color: rgba(255, 255, 255, 0.85); font-size: 14px;">
                                {{ $headerConfig['subtitle'] }}
                            </p>
                        </td>
                    </tr>

                    <!-- ==============================================
                         CUERPO DEL MENSAJE
                         ============================================== -->
                    <tr>
                        <td style="padding: 35px 40px 20px;">

                            <!-- Saludo -->
                            <p style="margin: 0 0 20px; font-size: 16px; color: #1f2937; line-height: 1.6;">
                                Estimado/a <strong style="color: #111827;">{{ $user->name }}</strong>,
                            </p>

                            <!-- Mensaje contextual según tipo -->
                            @if($type === 'mixed')
                                <p style="margin: 0 0 25px; font-size: 15px; color: #4b5563; line-height: 1.7;">
                                    Le informamos que se han realizado cambios en sus roles dentro del sistema institucional:
                                </p>
                            @elseif($type === 'assigned')
                                <p style="margin: 0 0 25px; font-size: 15px; color: #4b5563; line-height: 1.7;">
                                    Nos complace informarle que se le han <strong style="color: #2563eb;">asignado</strong> los siguientes roles en el sistema institucional:
                                </p>
                            @elseif($type === 'revoked')
                                <p style="margin: 0 0 25px; font-size: 15px; color: #4b5563; line-height: 1.7;">
                                    Le informamos que se le han <strong style="color: #dc2626;">revocado</strong> los siguientes roles en el sistema institucional:
                                </p>
                            @endif

                            <!-- ==============================================
                                 TARJETA DE ROLES ASIGNADOS
                                 ============================================== -->
                            @if(!empty($assignedRoles))
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #eff6ff; border-left: 4px solid #2563eb; border-radius: 8px; margin-bottom: 20px;">
                                    <tr>
                                        <td style="padding: 20px 25px;">
                                            <p style="margin: 0 0 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #2563eb;">
                                                ✓ Roles otorgados
                                            </p>

                                            @foreach($assignedRoles as $roleName)
                                                <div style="display: block; padding: 8px 0; {{ !$loop->last ? 'border-bottom: 1px dashed #bfdbfe;' : '' }}">
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                        <tr>
                                                            <td width="24" valign="middle" style="font-size: 14px; color: #2563eb;">
                                                                ●
                                                            </td>
                                                            <td style="font-size: 15px; color: #1f2937; font-weight: 600; padding-left: 8px;">
                                                                {{ $roleName }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <!-- ==============================================
                                 TARJETA DE ROLES REVOCADOS
                                 ============================================== -->
                            @if(!empty($revokedRoles))
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #fef2f2; border-left: 4px solid #dc2626; border-radius: 8px; margin-bottom: 20px;">
                                    <tr>
                                        <td style="padding: 20px 25px;">
                                            <p style="margin: 0 0 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #dc2626;">
                                                ✗ Roles removidos
                                            </p>

                                            @foreach($revokedRoles as $roleName)
                                                <div style="display: block; padding: 8px 0; {{ !$loop->last ? 'border-bottom: 1px dashed #fecaca;' : '' }}">
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                                        <tr>
                                                            <td width="24" valign="middle" style="font-size: 14px; color: #dc2626;">
                                                                ○
                                                            </td>
                                                            <td style="font-size: 15px; color: #1f2937; font-weight: 600; padding-left: 8px;">
                                                                {{ $roleName }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <!-- ==============================================
                                 NOTA INFORMATIVA (solo si hay revocaciones)
                                 ============================================== -->
                            @if($type === 'revoked' || $type === 'mixed')
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #fffbeb; border-radius: 8px; border: 1px solid #fde68a; margin-bottom: 25px;">
                                    <tr>
                                        <td style="padding: 15px 20px;">
                                            <p style="margin: 0; font-size: 13px; color: #92400e; line-height: 1.6;">
                                                <strong style="color: #78350f;">ℹ Información importante:</strong><br>
                                                Si considera que esta revocación es un error o tiene dudas al respecto, le recomendamos contactar a <strong>Administración Académica</strong>.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <!-- ==============================================
                                 MENSAJE DE CIERRE
                                 ============================================== -->
                            <p style="margin: 0 0 5px; font-size: 15px; color: #4b5563; line-height: 1.7;">
                                @if($type === 'assigned')
                                    Le deseamos éxito en sus nuevas responsabilidades.
                                @elseif($type === 'revoked')
                                    Agradecemos su colaboración durante el tiempo en que desempeñó dichas funciones.
                                @else
                                    Agradecemos su compromiso continuo con la institución.
                                @endif
                            </p>
                            <p style="margin: 0; font-size: 15px; color: #4b5563; line-height: 1.7;">
                                Atentamente,
                            </p>
                            <p style="margin: 5px 0 0; font-size: 15px; color: #1f2937; font-weight: 600;">
                                Universidad Modular Abierta — UMA
                            </p>
                        </td>
                    </tr>

                    <!-- ==============================================
                         FOOTER
                         ============================================== -->
                    <tr>
                        <td style="padding: 20px 40px 30px;">
                            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 0 0 20px;">

                            <p style="margin: 0 0 8px; font-size: 12px; color: #9ca3af; text-align: center; line-height: 1.6;">
                                Este es un mensaje automático generado por <strong style="color: #6b7280;">NEXUM UMA</strong>.<br>
                                Por favor, no responda directamente a este correo.
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #d1d5db; text-align: center;">
                                © {{ date('Y') }} Universidad Modular Abierta. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>

                </table>

                <!-- Nota inferior fuera de la tarjeta -->
                <p style="margin: 20px 0 0; font-size: 11px; color: #9ca3af; text-align: center;">
                    ¿Tiene problemas para ver este correo? Contacte al área de soporte técnico.
                </p>

            </td>
        </tr>
    </table>

</body>
</html>