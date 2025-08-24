<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Proyecto de Mejora</title>
    <style>
        :root {
            --azul-oscuro: #1976D2;
            --azul-claro: #68A2C9;
            --verde-agua: #BBD8D7;
            --verde-claro: #DFECDF;
            --verde-pastel: #E3EBDA;
            --gris-claro: #DEDFD1;
            --gris-oscuro:rgb(0, 0, 0);
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 40px;
            color: var(--gris-oscuro);
            background-color: #fff;
        }

        h1 {
            text-align: center;
            color: var(--azul-oscuro);
            border-bottom: 2px solid var(--azul-claro);
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        h2 {
            margin-top: 30px;
            color: var(--azul-oscuro);
            border-bottom: 1px solid var(--azul-claro);
            padding-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: var(--verde-agua);
            color: var(--azul-oscuro);
            border: 1px solid var(--gris-claro);
        }

        th,
        td {
            border: 1px solid var(--gris-claro);
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        ul {
            padding-left: 20px;
        }

        .no-border-table td {
            border: none !important;
            padding: 4px 8px;
            background-color: transparent;
        }

        .signature-line div {
            border-top: 2px solid var(--azul-claro);
            width: 80%;
            margin: auto;
            padding-top: 4px;
        }

        .firma-label {
            color: var(--azul-claro);
            font-weight: bold;
        }
    </style>


</head>

<body>
    <h1>Proyecto de Mejora</h1>

    <h2>1. Datos Generales</h2>
    <table class="no-border-table" style="width:100%; margin-top: 10px;">
        <tr>
            <td style="width:50%;"><strong>División:</strong> {{ $proyecto->division ?? 'No proporcionado' }}</td>
            <td><strong>Departamento:</strong> {{ $proyecto->departamento ?? 'No proporcionado' }}</td>
        </tr>
        <tr>
            <td><strong>Responsable:</strong> {{ $proyecto->responsable ?? 'No proporcionado' }}</td>
            <td><strong>Fecha:</strong> {{ $proyecto->fecha ?? 'No proporcionado' }}</td>
        </tr>
        <tr>
            <td><strong>No. de Mejora:</strong> {{ $proyecto->noMejora ?? 'No proporcionado' }}</td>
            <td></td>
        </tr>
    </table>



    <h2>2. Descripción de la Mejora</h2>
    <p><strong>Descripción:</strong> {{ $proyecto->descripcionMejora ?? 'No proporcionado' }}</p>

    <h2>3. Área de Impacto</h2>
    <p>{{ $proyecto->areaImpacto ?? 'No proporcionado' }}</p>

    <h2>4. Personal Beneficiado</h2>
    <p>{{ $proyecto->personalBeneficiado ?? 'No proporcionado' }}</p>

    <h2>5. Situación Actual</h2>
    <p>{{ $proyecto->situacionActual ?? 'No proporcionado' }}</p>

    <h2>6. Objetivos</h2>
    @if($proyecto->objetivos->isEmpty())
        <p>No proporcionado</p>
    @else
        <ol>
            @foreach($proyecto->objetivos as $obj)
                <li>{{ $obj->descripcionObj ?? 'No proporcionado' }}</li>
            @endforeach
        </ol>
    @endif

    <h2>7. Responsables</h2>
    @if($proyecto->responsablesInv->isEmpty())
        <p>No proporcionado</p>
    @else
        <ol>
            @foreach($proyecto->responsablesInv as $r)
                <li>{{ $r->nombre ?? 'No proporcionado' }}</li>
            @endforeach
        </ol>
    @endif

    <h2>8. Indicadores de Éxito</h2>
    @if($proyecto->indicadoresExito->isEmpty())
        <p>No proporcionado</p>
    @else
        <ol>
            @foreach($proyecto->indicadoresExito as $ind)
                <li>{{ $ind->nombreInd ?? 'No proporcionado' }} — Meta: {{ $ind->meta ?? 'No definida' }}</li>
            @endforeach
        </ol>
    @endif

    <h2>9. Recursos</h2>
    @if($proyecto->recursos->isEmpty())
        <p>No proporcionado</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Tiempo Estimado</th>
                    <th>Recursos Materiales y Humanos</th>
                    <th>Costo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proyecto->recursos as $rec)
                    <tr>
                        <td>{{ $rec->tiempoEstimado ?? 'No proporcionado' }}</td>
                        <td>{{ $rec->recursosMatHum ?? 'No proporcionado' }}</td>
                        <td>${{ number_format($rec->costo ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>10. Plan de Trabajo</h2>
    @if($proyecto->actividades->isEmpty())
        <p>No proporcionado</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Actividad</th>
                    <th>Responsable</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proyecto->actividades as $act)
                    <tr>
                        <td>{{ $act->descripcionAct ?? 'No proporcionado' }}</td>
                        <td>{{ $act->responsable ?? 'No proporcionado' }}</td>
                        <td>{{ $act->fecha ?? 'No proporcionado' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>11. Aprobación</h2>
    <table style="width:100%; border: none; margin-top: 60px;">
        <tr>
            <td style="width:50%; vertical-align: bottom;">
                <p><strong>{{ $proyecto->aprobacionNombre ?? 'No proporcionado' }}</strong></p>
                <p>{{ $proyecto->aprobacionPuesto ?? 'No proporcionado' }}</p>
            </td>
            <td style="text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px solid #000; width: 80%; margin: auto; padding-top: 4px;">Firma</div>
            </td>
        </tr>
    </table>


</body>

</html>