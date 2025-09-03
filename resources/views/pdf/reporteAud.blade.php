<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Auditoría Interna</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }
        header table {
            width: 100%;
            border: none;
            margin-bottom: 20px;
        }
        header td {
            vertical-align: middle;
            text-align: center;
            border: none;
        }
        header img {
            height: 45px;
        }
        header h1 {
            color: #004A98;
            font-size: 16px;
            margin: 0;
        }

        /* ==== ESTILOS DE TABLAS ==== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0 20px 0;
            font-size: 12px;
        }

        /* Centrar todo el contenido de las tablas */
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center; /* Cambiado de left a center */
        }

        /* Encabezados principales (azules) */
        .table-header {
            background-color: #1f3864;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }

        /* Sub-encabezados grises (como en "PERSONAL DIGC (OBSERVADORES)") */
        .table-subheader {
            background-color: #d9d9d9;
            font-weight: bold;
            text-align: center;
        }

        .pegadas {
            margin: 0; /* elimina espacio entre tablas */
            border-collapse: collapse;
        }

        /* Encabezado gris de las columnas de Equipo Auditor y Personal Auditado */
        .auditor-header {
            background-color: #d9d9d9;
            font-weight: bold;
            text-align: center;
        }

        .encabezado-gris {
        background-color: #d9d9d9;
        font-weight: bold;
        text-align: center;
    }

    

    </style>
</head>
<body>
    <header>
        <table>
            <tr>
                <td style="width: 25%; text-align: left;">
                    <img src="{{ public_path('images/logo3.png') }}" alt="Logo UASLP">
                </td>
                <td style="width: 50%; text-align: center;">
                    <h1>INFORME DE AUDITORÍA INTERNA</h1>
                </td>
                <td style="width: 25%; text-align: right;">
                    <img src="{{ public_path('images/logo4.jpg') }}" alt="Logo SICAL">
                </td>
            </tr>
        </table>
    </header>

    <!-- ENCABEZADO DE INFORMACIÓN GENERAL -->
    <table class="pegadas">
        <tr class="table-header">
            <th>Dependencia Administrativa / Entidad Académica</th>
            <th>Proceso</th>
            <th>Líder de Proceso</th>
            <th>Fecha</th>
        </tr>
        <tr>
            <td>{{ $auditoria->registro->proceso->entidad->nombreEntidad ?? '---' }}</td>
            <td>{{ $auditoria->registro->proceso->nombreProceso ?? '---' }}</td>
            <td>{{ $auditoria->registro->proceso->usuario ? $auditoria->registro->proceso->usuario->nombre . ' ' . $auditoria->registro->proceso->usuario->apellidoPat . ' ' . $auditoria->registro->proceso->usuario->apellidoMat: '---' }}</td>
            <td>{{ \Carbon\Carbon::parse($auditoria->fecha)->format('d/m/Y') }}</td>
        </tr>
    </table>

    <!-- OBJETIVO -->
    <table class="pegadas">
        <tr class="table-header">
            <td>OBJETIVO DE LA AUDITORÍA</td>
        </tr>
        <tr>
            <td>{{ $auditoria->objetivoAud }}</td>
        </tr>
    </table>

    <!-- ALCANCE -->
    <table class="pegadas">
        <tr class="table-header">
            <td>ALCANCE DE AUDITORÍA</td>
        </tr>
        <tr>
            <td>{{ $auditoria->alcanceAud }}</td>
        </tr>
    </table>

    <!-- CRITERIOS -->
    <table class="pegadas">
        <tr class="table-header">
            <td colspan="2">CRITERIOS DE AUDITORÍA</td>
        </tr>
        @foreach($auditoria->criterios as $criterio)
        <tr>
            <td colspan="2">{{ $criterio->criterio }}</td>
        </tr>
        @endforeach
    </table>

    <!-- EQUIPO AUDITOR Y PERSONAL AUDITADO -->
    <table class="pegadas">
        <tr class="table-header">
            <td colspan="2">EQUIPO AUDITOR</td>
            <td colspan="2">PERSONAL AUDITADO</td>
        </tr>
        <tr class="auditor-header">
            <td>ROL ASIGNADO</td>
            <td>AUDITOR SICAL</td>
            <td>NOMBRE</td>
            <td>CARGO</td>
        </tr>
        @php
            // Calculamos cuántas filas necesitamos según la sección más larga
            $maxRows = max(count($auditoria->equipoAuditor), count($auditoria->personalAuditado));
        @endphp
        @for($i = 0; $i < $maxRows; $i++)
            <tr>
                {{-- Equipo Auditor --}}
                <td>{{ $auditoria->equipoAuditor[$i]->rolAsignado ?? '' }}</td>
                <td>{{ $auditoria->equipoAuditor[$i]->nombreAuditor ?? '' }}</td>
                {{-- Personal Auditado --}}
                <td>{{ $auditoria->personalAuditado[$i]->nombre ?? '' }}</td>
                <td>{{ $auditoria->personalAuditado[$i]->cargo ?? '' }}</td>
            </tr>
        @endfor
    </table>

    <!-- VERIFICACIÓN DE RUTA DE AUDITORÍA -->
    <table>
        <!-- Título principal encima de los encabezados -->
        <tr class="table-header">
            <td colspan="7">VERIFICACIÓN DE RUTA DE AUDITORÍA</td>
        </tr>
        <!-- Fila de encabezados -->
        <tr class="table-header">
            <td>CRITERIO</td>
            <td>REQ. ASOCIADO</td>
            <td>OBSERVACIONES</td>
            <td>EVIDENCIA OBJETIVA</td>
            <td colspan="3">TIPO DE HALLAZGO</td>
        </tr>
        <!-- Sub-encabezado gris para los tipos de hallazgo -->
        <tr class="table-subheader">
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>NC</td>
            <td>PM</td>
            <td>NINGUNO</td>
        </tr>
        @foreach($auditoria->verificacionRuta as $verificacion)
        <tr>
            <td>{{ $verificacion->criterio }}</td>
            <td>{{ $verificacion->reqAsociado }}</td>
            <td>{{ $verificacion->observaciones }}</td>
            <td>{{ $verificacion->evidencia }}</td>
            <td>@if($verificacion->tipoHallazgo == 'NC') X @endif</td>
            <td>@if($verificacion->tipoHallazgo == 'PM') X @endif</td>
            <td>@if($verificacion->tipoHallazgo == 'NINGUNO') X @endif</td>
        </tr>
        @endforeach
    </table>

    <!-- FORTALEZAS Y DEBILIDADES -->
    <table>
        <tr class="table-header">
            <td>FORTALEZAS IDENTIFICADAS</td>
        </tr>
        <tr>
            <td>{{ $auditoria->fortalezas ?? '' }}</td>
        </tr>
    </table>
    <table>
        <tr class="table-header">
            <td>DEBILIDADES IDENTIFICADAS</td>
        </tr>
        <tr>
            <td>{{ $auditoria->debilidades ?? '' }}</td>
        </tr>
    </table>

    <!-- NO CONFORMIDADES DETECTADAS -->
    <table>
        <tr class="table-header">
            <td colspan="4">NO CONFORMIDADES DETECTADAS</td>
        </tr>
        <tr class="encabezado-gris">
            <td>No.</td>
            <td>REQ. ISO 9001:2015</td>
            <td>DESCRIPCIÓN DEL HALLAZGO</td>
            <td>EVIDENCIA OBJETIVA</td>
        </tr>
        @foreach ($auditoria->puntosMejora as $punto)
            <tr>
                <td>N/A</td>
                <td>N/A</td>
                <td>N/A</td>
                <td>N/A</td>
            </tr>
        @endforeach
    </table>

    <!-- PUNTOS DE MEJORA DETECTADOS -->
    <table>
        <tr class="table-header">
            <td colspan="4">PUNTOS DE MEJORA DETECTADOS</td>
        </tr>
        <tr class="encabezado-gris">
            <td>No.</td>
            <td>REQ. ISO 9001:2015</td>
            <td>DESCRIPCIÓN DEL PUNTO DE MEJORA IDENTIFICADO</td>
            <td>EVIDENCIA OBJETIVA</td>
        </tr>
        @foreach($auditoria->puntosMejora as $pm)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $pm->reqISO ?? 'N/A' }}</td>
                <td>{{ $pm->descripcion }}</td>
                <td>{{ $pm->evidencia }}</td>
            </tr>
        @endforeach
    </table>

    <!-- CONCLUSIONES -->
    <table>
        <tr class="table-header">
            <td colspan="2">CONCLUSIONES GENERALES DE LA AUDITORÍA</td>
        </tr>
        @foreach($auditoria->conclusiones as $conclusion)
            <!-- Fila con el nombre de la conclusión (subcolumna gris) -->
            <tr class="table-subheader">
                <td colspan="2">{{ $conclusion->nombre }}</td>
            </tr>
            <!-- Fila con la descripción de la conclusión -->
            <tr>
                <td colspan="2">{{ $conclusion->descripcionConclusion }}</td>
            </tr>
        @endforeach
    </table>

    <!-- PLAZOS Y CONSIDERACIONES -->
    <table>
        <tr class="table-header">
            <td style="width: 10%;">No.</td>
            <td style="width: 90%;">PLAZOS Y CONSIDERACIONES</td>
        </tr>
        @foreach($auditoria->plazos as $plazo)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $plazo->descripcion }}</td>
        </tr>
        @endforeach
    </table>

    <!-- FILA SUPERIOR: ELABORA y REVISA -->
    <div style="width: 100%; margin-top: 20px; font-family: Arial, sans-serif;">

        <!-- ELABORA -->
        <div style="display: inline-block; width: 49%; vertical-align: top; text-align: center; box-sizing: border-box;">
            <div style="font-weight: bold; text-transform: uppercase; color: #000; margin-bottom: 5px;">
                ELABORA
            </div>
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; box-sizing: border-box;">
                <tr>
                    <td style="width: 50%; text-align: center; font-weight: bold; color: #0B3D91; background-color: #d9d9d9; padding: 3px; font-size: 10px;">
                        20/05/2024
                    </td>
                    <td style="width: 50%; background-color: #a0c44c; text-align: center; padding: 3px; font-size: 10px; white-space: nowrap;">
                        INFORME CONCLUIDO Y REVISADO
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center; padding: 5px; height: 18px; font-size: 10px;">
                        {{ $auditoria->auditorLider }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center; font-weight: bold; padding: 5px; height: 18px; font-size: 9px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        LÍDER DEL EQUIPO AUDITOR O AUDITOR INTERNO ÚNICO ASIGNADO
                    </td>
                </tr>
            </table>
        </div>

        <!-- REVISA -->
        <div style="display: inline-block; width: 49%; vertical-align: top; text-align: center; box-sizing: border-box;">
            <div style="font-weight: bold; text-transform: uppercase; color: #000; margin-bottom: 5px;">
                REVISA
            </div>
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; box-sizing: border-box;">
                <tr>
                    <td style="width: 50%; text-align: center; font-weight: bold; color: #0B3D91; background-color: #d9d9d9; padding: 3px; font-size: 10px;"></td>
                    <td style="width: 50%; background-color: #50a44c; text-align: center; padding: 3px; font-size: 10px; white-space: nowrap;">
                        APROBACION DIGC
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center; padding: 5px; height: 18px; font-size: 10px;"></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center; font-weight: bold; padding: 5px; height: 18px; font-size: 10px;"></td>
                </tr>
            </table>
        </div>

    </div>

    <!-- FILA INFERIOR: ACEPTACIÓN DEL AUDITADO -->
    <div style="width: 100%; margin-top: 20px; text-align: center;">
        <div style="font-weight: bold; text-transform: uppercase; color: #000; margin-bottom: 5px;">
            ACEPTACIÓN DEL AUDITADO
        </div>
        <table style="width: 48%; margin: 0 auto; border: 1px solid #000; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; text-align: center; font-weight: bold; color: #0B3D91; background-color: #d9d9d9; padding: 3px; font-size: 10px;"></td>
                <td style="width: 50%; background-color: #50a44c; text-align: center; padding: 3px; font-size: 10px; white-space: nowrap;">
                    ACEPTO DE CONFORMIDAD
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; padding: 5px; height: 18px; font-size: 10px;"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; font-weight: bold; padding: 5px; height: 18px; font-size: 10px;"></td>
            </tr>
        </table>
    </div>

</body>
</html>
