<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Auditoría</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }
        h1 {
            text-align: center;
            color: #004A98;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #cccccc;
            padding: 8px;
            text-align: left;
        }
        .section-title {
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 5px;
        }
    </style>
</head>
<body>
    <h1>Auditoría del {{ \Carbon\Carbon::parse($auditoria->fecha)->format('d/m/Y') }}</h1>

    <p><strong>Entidad:</strong> Nombre de la Entidad</p>
    <p><strong>Proceso:</strong> Nombre del Proceso</p>
    <p><strong>Líder:</strong> {{ $auditoria->auditorLider ?? '---' }}</p>

    <p><strong>Objetivo:</strong> {{ $auditoria->objetivoAud }}</p>
    <p><strong>Alcance:</strong> {{ $auditoria->alcanceAud }}</p>

    <div class="section-title">Criterios</div>
    <ul>
        @foreach($auditoria->criterios as $criterio)
            <li>{{ $criterio->criterio }}</li>
        @endforeach
    </ul>

    <div class="section-title">Equipo Auditor</div>
    <table>
        <thead>
            <tr>
                <th>Rol</th>
                <th>Nombre</th>
            </tr>
        </thead>
        <tbody>
            @foreach($auditoria->equipoAuditor as $item)
                <tr>
                    <td>{{ $item->rolAsignado }}</td>
                    <td>{{ $item->nombreAuditor }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Personal Auditado</div>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Cargo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($auditoria->personalAuditado as $item)
                <tr>
                    <td>{{ $item->nombre }}</td>
                    <td>{{ $item->cargo }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Verificación de Ruta</div>
    <table>
        <thead>
            <tr>
                <th>Criterio</th>
                <th>Req. Asociado</th>
                <th>Observaciones</th>
                <th>Evidencia</th>
                <th>Tipo de Hallazgo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($auditoria->verificacionRuta as $item)
                <tr>
                    <td>{{ $item->criterio }}</td>
                    <td>{{ $item->reqAsociado }}</td>
                    <td>{{ $item->observaciones }}</td>
                    <td>{{ $item->evidencia }}</td>
                    <td>{{ $item->tipoHallazgo }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Fortalezas</div>
    <p>{{ $auditoria->fortalezas }}</p>

    <div class="section-title">Debilidades</div>
    <p>{{ $auditoria->debilidades }}</p>

    <div class="section-title">Puntos de Mejora</div>
    <table>
        <thead>
            <tr>
                <th>Requisito ISO</th>
                <th>Descripción</th>
                <th>Evidencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($auditoria->puntosMejora as $item)
                <tr>
                    <td>{{ $item->reqISO }}</td>
                    <td>{{ $item->descripcion }}</td>
                    <td>{{ $item->evidencia }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Conclusiones Generales</div>
    @foreach($auditoria->conclusiones as $c)
        <p><strong>{{ $c->nombre }}:</strong> {{ $c->descripcionConclusion }}</p>
    @endforeach

    <div class="section-title">Plazos y Consideraciones</div>
    <ul>
        @foreach($auditoria->plazos as $p)
            <li>{{ $p->descripcion }}</li>
        @endforeach
    </ul>
</body>
</html>
