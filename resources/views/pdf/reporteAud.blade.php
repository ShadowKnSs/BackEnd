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
    <table>
        <!-- Fila de encabezados -->
        <tr class="table-header">
            <th>Dependencia Administrativa / Entidad Académica</th>
            <th>Proceso</th>
            <th>Líder de Proceso</th>
            <th>Fecha</th>
        </tr>
        <!-- Fila de valores -->
        <tr>
            <td>{{ $auditoria->registro->proceso->entidad->nombreEntidad ?? '---' }}</td>
            <td>{{ $auditoria->registro->proceso->nombreProceso ?? '---' }}</td>
            <td>{{ $auditoria->auditorLider ?? '---' }}</td>
            <td>{{ \Carbon\Carbon::parse($auditoria->fecha)->format('d/m/Y') }}</td>
        </tr>
    </table>


    <!-- OBJETIVO -->
    <table>
        <tr class="table-header">
            <td>OBJETIVO DE LA AUDITORÍA</td>
        </tr>
        <tr>
            <td>{{ $auditoria->objetivoAud }}</td>
        </tr>
    </table>

    <!-- ALCANCE -->
    <table>
        <tr class="table-header">
            <td>ALCANCE DE AUDITORÍA</td>
        </tr>
        <tr>
            <td>{{ $auditoria->alcanceAud }}</td>
        </tr>
    </table>

    <!-- CRITERIOS -->
    <table>
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
    <table>
        <tr class="table-header">
            <td colspan="2">EQUIPO AUDITOR</td>
            <td colspan="2">PERSONAL AUDITADO</td>
        </tr>
        <tr>
            <th>ROL ASIGNADO</th>
            <th>AUDITOR SICAL</th>
            <th>NOMBRE</th>
            <th>CARGO</th>
        </tr>
        @foreach($auditoria->equipoAuditor as $item)
        <tr>
            <td>{{ $item->rolAsignado }}</td>
            <td>{{ $item->nombreAuditor }}</td>
            <td>{{ $item->nombreAuditado ?? '' }}</td>
            <td>{{ $item->cargoAuditado ?? '' }}</td>
        </tr>
        @endforeach

        <!-- Subheader para OBSERVADORES -->
        <tr class="table-subheader">
            <td colspan="4">PERSONAL DIGC (OBSERVADORES)</td>
        </tr>
        @foreach($auditoria->personalAuditado as $item)
        <tr>
            <td colspan="2"></td>
            <td>{{ $item->nombre }}</td>
            <td>{{ $item->cargo }}</td>
        </tr>
        @endforeach
    </table>

</body>
</html>
