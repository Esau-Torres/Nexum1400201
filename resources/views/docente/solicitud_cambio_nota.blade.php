<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitud de Cambio de Calificación | NEXUM</title>
    <style>
        @page {
            size: letter portrait;
            margin: 1.5cm 1.8cm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #e5e7eb;
            font-family: Georgia, "Times New Roman", Times, serif;
            color: #111;
        }

        /* ========== HOJA IMPRIMIBLE ========== */
        .hoja {
            width: 21.6cm;
            min-height: 27.9cm;
            margin: 28px auto;
            padding: 1.4cm 1.6cm;
            background: #ffffff;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
        }

        /* ========== MEMBRETE ========== */
        .doc-header {
            display: flex;
            align-items: center;
            gap: 18px;
            border-bottom: 3px double #111;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .doc-header img {
            height: 92px;
            width: auto;
        }

        .doc-header .uni-info {
            flex: 1;
            text-align: center;
        }

        .uni-nombre {
            font-size: 1.3rem;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 0;
        }

        .uni-lema {
            font-size: 0.75rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 2px 0 0;
        }

        .uni-dep {
            font-size: 0.83rem;
            margin: 5px 0 0;
        }

        .doc-codigo {
            font-size: 0.7rem;
            text-align: right;
            line-height: 1.5;
            color: #333;
        }

        /* ========== TÍTULO Y DESCRIPCIÓN ========== */
        .doc-titulo {
            text-align: center;
            font-size: 1.12rem;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin: 22px 0 10px;
        }

        .doc-descripcion {
            font-size: 0.85rem;
            line-height: 1.65;
            text-align: justify;
            margin: 0 0 18px;
        }

        /* ========== DATOS PRELLENADOS DE LA SECCIÓN ========== */
        .doc-meta {
            border: 1px solid #111;
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: 0.88rem;
        }

        .doc-meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .doc-meta td {
            padding: 3px 4px;
            vertical-align: top;
        }

        .doc-meta td.etiqueta {
            width: 30%;
            font-weight: bold;
        }

        /* ========== CAMPOS PARA LLENAR A MANO ========== */
        .campo {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            margin-bottom: 16px;
            font-size: 0.9rem;
        }

        .campo-label {
            font-weight: bold;
            white-space: nowrap;
        }

        .linea {
            flex: 1;
            border-bottom: 1px solid #333;
            height: 24px;
        }

        .linea.corta {
            flex: 0 0 110px;
        }

        .linea.media {
            flex: 0 0 190px;
        }

        .motivos-label {
            font-weight: bold;
            font-size: 0.9rem;
            margin-bottom: 6px;
            display: block;
        }

        /* Cuadro rayado para escribir los motivos a mano */
        .motivos-box {
            border: 1px solid #333;
            height: 168px;
            margin-bottom: 10px;
            background-image: repeating-linear-gradient(to bottom,
                    transparent 0,
                    transparent 27px,
                    #c8c8c8 27px,
                    #c8c8c8 28px);
        }

        /* ========== FIRMAS ========== */
        .firmas {
            display: flex;
            gap: 48px;
            margin-top: 50px;
        }

        .firma {
            flex: 1;
            text-align: center;
            font-size: 0.85rem;
        }

        .firma-linea {
            border-top: 1px solid #111;
            padding-top: 6px;
            font-weight: bold;
        }

        .firma-rol {
            font-size: 0.78rem;
            color: #333;
            margin-top: 2px;
        }

        /* ========== USO INTERNO ========== */
        .uso-interno {
            margin-top: 32px;
            border: 1px dashed #555;
            padding: 12px 14px;
            font-size: 0.82rem;
        }

        .titulo-interno {
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.75rem;
            margin-bottom: 8px;
        }

        .check {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1px solid #111;
            vertical-align: -2px;
            margin: 0 6px 0 14px;
        }

        .linea-inline {
            display: inline-block;
            border-bottom: 1px solid #333;
            height: 18px;
            vertical-align: bottom;
        }

        .doc-footer {
            margin-top: 26px;
            border-top: 1px solid #999;
            padding-top: 8px;
            font-size: 0.7rem;
            color: #555;
            display: flex;
            justify-content: space-between;
        }

        /* ========== BOTONES DE PANTALLA (NO SALEN IMPRESOS) ========== */
        .no-print {
            position: fixed;
            top: 18px;
            right: 18px;
            display: flex;
            gap: 10px;
            z-index: 10;
        }

        .no-print button {
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            font-family: system-ui, sans-serif;
        }

        .btn-imprimir {
            background: #dc3545;
            color: #ffffff;
        }

        .btn-imprimir:hover {
            background: #b02a37;
        }

        .btn-cerrar {
            background: #ffffff;
            color: #333;
            border: 1px solid #ccc !important;
        }

        /* ========== MODO IMPRESIÓN ========== */
        @media print {
            body {
                background: #ffffff;
            }

            .hoja {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <!-- Botones visibles solo en pantalla -->
    <div class="no-print">
        <button type="button" class="btn-imprimir" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Imprimir / Guardar como PDF
        </button>
        <button type="button" class="btn-cerrar" onclick="window.close()">
            Cerrar
        </button>
    </div>

    <div class="hoja">

        <!-- MEMBRETE CON LOGO -->
        <header class="doc-header">
            <img src="{{ asset('images/uma_santa_ana.png') }}" alt="Logo UMA">
            <div class="uni-info">
                <p class="uni-nombre">UNIVERSIDAD MODULAR ABIERTA</p>
                <p class="uni-lema">Modular · Abierta · Accesible</p>
                <p class="uni-dep">Vicerrectoría Académica — Departamento de Registro y Control de Calificaciones</p>
                <p class="uni-dep">Santa Ana, El Salvador</p>
            </div>
            <div class="doc-codigo">
                Formulario FO-AC-07<br>
                Versión 1.0<br>
                Página 1 de 1
            </div>
        </header>

        <!-- TÍTULO DEL DOCUMENTO -->
        <h1 class="doc-titulo">Solicitud de Cambio de Calificación Registrada</h1>

        <!-- DESCRIPCIÓN DEL DOCUMENTO -->
        <p class="doc-descripcion">
            El presente formulario constituye el medio oficial mediante el cual el docente responsable de una
            sección solicita a la Administración Académica la modificación de una calificación que ya se encuentra
            <strong>registrada y bloqueada</strong> en el sistema NEXUM. Toda calificación cerrada únicamente podrá
            ser modificada mediante esta solicitud, la cual deberá presentarse firmada por el docente responsable y
            acompañada de la evidencia que sustente la petición. Los espacios en blanco deben completarse
            <strong>a mano, con tinta azul o negra y letra legible</strong>.
        </p>

        <!-- DATOS DE LA SECCIÓN (PRELLENADOS) -->
        <div class="doc-meta">
            <table>
                <tr>
                    <td class="etiqueta">Materia:</td>
                    <td>{{ $seccion->materia->nombre }} ({{ $seccion->materia->codigo_materia }})</td>
                    <td class="etiqueta">Grupo / Sección:</td>
                    <td>{{ $seccion->nombre_grupo }}</td>
                </tr>
                <tr>
                    <td class="etiqueta">Ciclo lectivo:</td>
                    <td>{{ $seccion->cicloLectivo->nombre_ciclo ?? '—' }}</td>
                    <td class="etiqueta">Docente responsable:</td>
                    <td>{{ $docenteNombre }}</td>
                </tr>
                <tr>
                    <td class="etiqueta">Código de empleado:</td>
                    <td>{{ $docenteCodigo }}</td>
                    <td class="etiqueta">Fecha de solicitud:</td>
                    <td>______ de ____________________ de 20______</td>
                </tr>
            </table>
        </div>

        <!-- CAMPOS PARA LLENAR A MANO -->
        <div class="campo">
            <span class="campo-label">Nombre del estudiante:</span>
            <span class="linea"></span>
        </div>

        <div class="campo">
            <span class="campo-label">Código de estudiante:</span>
            <span class="linea media"></span>
            <span class="campo-label">Evaluación a modificar:</span>
            <span class="linea"></span>
        </div>

        <div class="campo">
            <span class="campo-label">Nota registrada actualmente:</span>
            <span class="linea corta"></span>
            <span class="campo-label">Nueva nota solicitada:</span>
            <span class="linea corta"></span>
        </div>

        <span class="motivos-label">Motivos que justifican el cambio de calificación (describa detalladamente):</span>
        <div class="motivos-box"></div>

        <!-- FIRMAS -->
        <div class="firmas">
            <div class="firma">
                <div class="firma-linea">{{ $docenteNombre }}</div>
                <div class="firma-rol">Firma del docente responsable · Cód. {{ $docenteCodigo }}</div>
            </div>
            <div class="firma">
                <div class="firma-linea">&nbsp;</div>
                <div class="firma-rol">Nombre y firma de quien recibe (Administración Académica)</div>
            </div>
        </div>

        <!-- USO INTERNO DE ADMINISTRACIÓN -->
        <div class="uso-interno">
            <div class="titulo-interno">Para uso interno de la Administración Académica</div>
            Resolución:
            <span class="check"></span> APROBADA
            <span class="check"></span> DENEGADA
            &nbsp;&nbsp;N.º de resolución: <span class="linea-inline" style="width: 120px;"></span>
            &nbsp;&nbsp;Fecha: <span class="linea-inline" style="width: 110px;"></span>
            <div style="margin-top: 28px;">
                <div style="border-top: 1px solid #111; width: 60%; padding-top: 5px; font-weight: bold;">
                    Firma del Administrador Académico
                </div>
            </div>
        </div>

        <!-- PIE DEL DOCUMENTO -->
        <footer class="doc-footer">
            <span>Documento generado por el sistema NEXUM el {{ $fechaGeneracion }}</span>
            <span>FO-AC-07 · V1.0 — Válido únicamente en físico con firma original</span>
        </footer>

    </div>

</body>

</html>