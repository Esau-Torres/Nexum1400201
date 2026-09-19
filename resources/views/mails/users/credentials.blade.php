<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciales de Acceso NEXUM</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f1f5f9; padding: 30px 0; }
        .main-table { width: 100%; max-width: 580px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
        .header { background-color: #ffffff; padding: 24px 32px; border-bottom: 2px solid #dc3545; }
        .title { color: #dc3545; font-size: 20px; font-weight: 700; margin: 0; }
        .content { padding: 32px; color: #1e293b; line-height: 1.6; }
        .soft-panel { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin: 24px 0; }
        .data-row { margin-bottom: 10px; font-size: 14px; }
        .data-label { color: #64748b; font-weight: 600; }
        .data-value { color: #0f172a; font-weight: 700; font-family: monospace; font-size: 15px; }
        .btn-access { display: inline-block; background-color: #dc3545; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: 600; font-size: 14px; margin-top: 10px; }
        .footer { padding: 20px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header">
                    <h1 class="title">UNIVERSIDAD MODULAR ABIERTA</h1>
                    <span style="font-size: 12px; color: #64748b; font-weight: 600;">Plataforma Integral de Gestión NEXUM</span>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <p style="margin-top: 0;">Estimado(a) <strong>{{ $user->name }}</strong>,</p>
                    <p>Se ha completado el alta de su cuenta institucional en el sistema NEXUM. Sus roles y privilegios de acceso han sido formalmente asignados.</p>
                    
                    <p style="font-size: 13px; color: #475569;">
                        <strong>Roles habilitados:</strong> {{ implode(', ', $roleNames) }}
                    </p>

                    <div class="soft-panel">
                        <div class="data-row">
                            <span class="data-label">Usuario institucional:</span><br>
                            <span class="data-value">{{ $user->email }}</span>
                        </div>
                        <div class="data-row" style="margin-bottom: 0;">
                            <span class="data-label">Contraseña provisional:</span><br>
                            <span class="data-value" style="background-color: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 4px;">{{ $plainPassword }}</span>
                        </div>
                    </div>

                    <p style="font-size: 13px; color: #64748b;">
                        Por directrices de seguridad de la UMA, deberá actualizar su clave temporal en el primer inicio y activar la autenticación en dos factores (2FA).
                    </p>

                    <table cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                        <tr>
                            <td align="center">
                                <a href="{{ route('login') }}" class="btn-access">Acceder al Portal NEXUM</a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    Este mensaje se ha generado automáticamente por los servicios informáticos de la UMA.<br>
                    Por favor no responda a esta dirección de correo.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>