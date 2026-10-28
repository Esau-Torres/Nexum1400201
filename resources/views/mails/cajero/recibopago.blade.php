<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago</title>
    <style>
        /* Reglas de oro para clientes de correo */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; background-color: #f4f6f9; width: 100% !important; }

        /* Media Queries seguras para móviles */
        @media screen and (max-width: 600px) {
            .responsive-td {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .mobile-spacer { margin-bottom: 20px !important; }
            .mobile-center { text-align: center !important; }

            /* Ajuste de márgenes para que no choque en celular */
            .box-observaciones { margin-right: 0 !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: Arial, sans-serif; font-size: 12px; color: #333333;">

    <!-- Tabla base que centra el correo en cualquier pantalla -->
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f6f9; padding: 15px;">
        <tr>
            <td align="center">

                <!-- CONTENEDOR PRINCIPAL BLANCO -->
                <table width="100%" border="0" cellspacing="0" cellpadding="20" style="max-width: 700px; background-color: #ffffff; border: 1px solid #cccccc; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <tr>
                        <td>

                            <!-- ENCABEZADO: DIVIDIDO EN 2 -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <!-- MITAD IZQUIERDA: LOGO -->
                                    <td class="responsive-td mobile-spacer mobile-center" width="55%" valign="top">
                                        <img src="{{ $message->embed(public_path('images/uma_santa_ana.png')) }}" alt="UMA Santa Ana" style="max-height: 75px; margin-bottom: 10px;">
                                        <p style="margin: 0; line-height: 1.4; font-size: 11px;">
                                            SÉPTIMA CALLE ORIENTE, ENTRE QUINTA Y SÉPTIMA<br>
                                            AVENIDA, SANTA ANA CENTRO, SANTA ANA, EL SALVADOR<br>
                                            Teléfono: 22605320<br>
                                            Correo: uma.santaana.informacion@gmail.com
                                        </p>
                                        <table width="100%" border="0" cellspacing="0" cellpadding="2" style="margin-top: 10px; font-size: 11px; text-align: left;">
                                            <tr><td width="35%"><strong>NIT:</strong></td><td>0614-260782-003-2</td></tr>
                                            <tr><td><strong>NRC:</strong></td><td>474240</td></tr>
                                            <tr><td><strong>Act. Económica:</strong></td><td>Enseñanza formal</td></tr>
                                        </table>
                                    </td>

                                    <!-- MITAD DERECHA: RECIBO -->
                                    <!-- Se usa table-layout: fixed para que no se desborde -->
                                    <td class="responsive-td" width="45%" valign="top">
                                        <table width="100%" border="1" cellspacing="0" cellpadding="6" style="border-collapse: collapse; font-size: 12px; table-layout: fixed; width: 100%; background-color: #ffffff;">
                                            <tr>
                                                <td colspan="2" align="center" style="background-color: #e9ecef; font-weight: bold; font-size: 13px; color: #333; border-bottom: 2px solid #000;">
                                                    COMPROBANTE DE PAGO<br>
                                                    <span style="font-size: 10px; font-weight: normal; color: #ff7575;">SISTEMA NEXUM</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="40%"><strong>N° recibo:</strong></td>
                                                <td width="60%" style="word-break: break-all;">{{ $numeroRecibo }}</td>
                                            </tr>
                                            <tr><td><strong>Fecha:</strong></td><td>{{ date('Y-m-d') }}</td></tr>
                                            <tr><td><strong>Hora:</strong></td><td>{{ date('H:i:s') }}</td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <br>

                            <!-- SECCIÓN RECEPTOR -->
                            <div style="text-align: center; font-weight: bold; border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 5px 0; margin-bottom: 15px; font-size: 12px; text-transform: uppercase;">
                                Receptor
                            </div>

                            <table width="100%" border="0" cellspacing="0" cellpadding="4" style="font-size: 12px; margin-bottom: 20px;">
                                <tr>
                                    <!-- width: 1% y nowrap hacen que la columna mida exactamente lo mismo que el texto -->
                                    <td valign="top" style="width: 1%; white-space: nowrap; padding-right: 10px;">
                                        <strong>Estudiante o razón social:</strong>
                                    </td>
                                    <td valign="top" style="word-wrap: break-word;">
                                        {{ strtoupper($nombreEstudiante) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" style="width: 1%; white-space: nowrap; padding-right: 10px;">
                                        <strong>Carnet:</strong>
                                    </td>
                                    <td valign="top" style="word-wrap: break-word;">
                                        {{ strtoupper($codigoEstudiante) }}
                                    </td>
                                </tr>
                            </table>

                            <!-- SECCIÓN DETALLES -->
                            <div style="text-align: center; font-weight: bold; border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 5px 0; margin-bottom: 10px; font-size: 12px; text-transform: uppercase;">
                                Cuerpo del Documento
                            </div>

                            <!-- LA TABLA MÁS CRÍTICA: Se define el porcentaje exacto de cada columna -->
                            <table width="100%" border="1" cellspacing="0" cellpadding="4" style="border-collapse: collapse; font-size: 10px; text-align: center; margin-bottom: 20px; table-layout: fixed; width: 100%; word-wrap: break-word;">
                                <tr style="background-color: #f8f9fa; font-weight: bold; color: #333;">
                                    <td width="5%">N°</td>
                                    <td width="8%">Cant.</td>
                                    <td width="10%">Unidad</td>
                                    <td width="41%">Descripción</td>
                                    <td width="13%">P. Unit.</td>
                                    <td width="10%">Exentas</td>
                                    <td width="13%">Gravadas</td>
                                </tr>

                                @foreach($detalles as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>1</td>
                                    <td>Otra</td>
                                    <!-- Aquí se ajusta automáticamente el texto hacia abajo sin romper la tabla -->
                                    <td align="left" style="line-height: 1.3;">{{ strtoupper($item['nombre']) }}</td>
                                    <td>${{ number_format($item['monto'], 2) }}</td>
                                    <td>$0.00</td>
                                    <td>${{ number_format($item['monto'], 2) }}</td>
                                </tr>
                                @endforeach
                            </table>

                            <!-- SECCIÓN TOTALES Y OBSERVACIONES -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <!-- OBSERVACIONES -->
                                    <td class="responsive-td mobile-spacer" width="55%" valign="top">
                                        <div class="box-observaciones" style="border: 1px solid #000; padding: 8px; font-size: 11px; text-align: left; margin-right: 15px; box-sizing: border-box;">
                                            <strong>Operación:</strong> Contado - {{ $metodoPago }}<br><br>
                                            <strong>Facturador:</strong> {{ strtoupper($nombreCajero) }}<br><br>
                                            <strong>Observaciones:</strong> Comprobante generado electrónicamente. Válido para trámites internos.
                                        </div>
                                    </td>

                                    <!-- TOTALES -->
                                    <td class="responsive-td" width="45%" valign="top">
                                        <table width="100%" border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; font-size: 11px; text-align: right; table-layout: fixed; width: 100%; background-color: #ffffff;">
                                            <tr>
                                                <td width="60%">Sumatoria ventas</td>
                                                <td width="40%">${{ number_format($total, 2) }}</td>
                                            </tr>
                                            <tr><td>Sub-Total</td><td>${{ number_format($total, 2) }}</td></tr>
                                            <tr><td>IVA Retenido</td><td>$0.00</td></tr>
                                            <tr><td>Monto total operación:</td><td>${{ number_format($total, 2) }}</td></tr>
                                            <tr style="background-color: #f8f9fa; font-weight: bold; font-size: 13px; color: #333;">
                                                <td>TOTAL A PAGAR</td>
                                                <td>${{ number_format($total, 2) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>
                <!-- FIN DEL CONTENEDOR PRINCIPAL -->

            </td>
        </tr>
    </table>

</body>
</html>
