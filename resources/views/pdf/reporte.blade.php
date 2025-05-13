<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Semestral</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }

        h1 {
            color: #004A98;
            font-size: 28px;
            text-align: center;
        }

        .section {
            margin-bottom: 20px;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #FFFFFF;
        }

        table,
        th,
        td {
            border: 1px solid #004A98;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }

        th {
            background-color: #004A98;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #F4F4F4;
        }

        .estado-en-proceso {
            background-color: #F9B800;
            color: white;
        }

        .estado-cerrado {
            background-color: #4CAF50;
            color: white;
        }

        p {
            text-align: justify;
        }
    </style>
</head>

<!--<htmlpageheader name="page-header">
    <table width="100%">
        <tr>
            <td><img src="{{ public_path('images/logo1.png') }}" style="height: 50px;"></td>
            <td style="text-align: right;"><img src="{{ public_path('images/logo2.jpg') }}" style="height: 40px;"></td>
        </tr>
    </table>
</htmlpageheader>-->

<body>

    <!--<div style="display: flex; justify-content: space-between; align-items: center;">
    <img src="{{ public_path('images/logo1.png') }}" style="height: 70px;">
    <img src="{{ public_path('images/logo2.jpg') }}" style="height: 70px;">
</div>-->

    <h1>Reporte Semestral {{ $periodo }} {{ $anio }}</h1>

    <!-- Gestión de Riesgos -->
    @if(!empty($datosRiesgos))
        <h2>Gestión de Riesgos</h2>
        <p>Gráfica de los riesgos tratados este semestre.</p>

        @if(isset($imagenes['riesgos']))
            <img src="{{ $imagenes['riesgos'] }}" alt="Riesgos">
        @else
            <p>No se proporcionó imagen de riesgos.</p>
        @endif

        <table>
            <thead>
                <tr>
                    <th>Riesgo</th>
                    <th>Proceso</th>
                    <th>Entidad</th>
                    <th>Fuente</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datosRiesgos as $index => $item)
                    <tr>
                        <td>Riesgo {{ $index + 1 }}</td>
                        <td>{{ $item['NombreProceso'] }}</td>
                        <td>{{ $item['Entidad'] }}</td>
                        <td>{{ $item['fuente'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif


    <!-- Indicadores -->
    @if(!empty($datosIndicadores))
        <div class="section">
            <h2>Indicadores</h2>
            <p>Resultado de los indicadores evaluados este semestre, con resultados numéricos del 1 al 100.</p>

            @if(isset($imagenes['indicadores']))
                <img src="{{ $imagenes['indicadores'] }}" alt="Indicadores">
            @else
                <p>No se proporcionó imagen de indicadores.</p>
            @endif

            <table>
                <thead>
                    <tr>
                        <th>Indicador</th>
                        <th>Proceso</th>
                        <th>Entidad</th>
                        <th>Nombre Indicador</th>
                        <th>Origen</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datosIndicadores as $index => $item)
                        <tr>
                            <td>Indicador {{ $index + 1 }}</td>
                            <td>{{ $item['NombreProceso'] ?? '—' }}</td>
                            <td>{{ $item['Entidad'] ?? '—' }}</td>
                            <td>{{ $item['nombreIndicador'] ?? '—' }}</td>
                            <td>{{ $item['origenIndicador'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

            <h3>Cumplimiento e Incumplimiento Semestral</h3>
            <p>De acuerdo con los resultados de los indicadores de este semestre, estos son los porcentajes de cumplimiento
                e incumplimiento del DIGC.</p>

            @if(isset($imagenes['indicadoresP']))
                <img src="{{ $imagenes['indicadoresP'] }}" alt="Indicadores Pastel">
            @else
                <p>No se proporcionó imagen de indicadores pastel.</p>
            @endif
        </div>
    @endif


    <!-- Acciones de Mejora -->
    @if(!empty($datosAccionesMejora))
        <div class="section">
            <h2>Acciones de Mejora</h2>
            <p>Planes de trabajo evaluados este semestre.</p>

            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Proceso</th>
                        <th>Entidad</th>
                        <th>Fuente</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datosAccionesMejora as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['NombreProceso'] }}</td>
                            <td>{{ $item['Entidad'] }}</td>
                            <td>{{ $item['fuente'] }}</td>
                            <td>{{ $item['responsable'] }}</td>
                            <td class="{{ $item['estado'] == 'En proceso' ? 'estado-en-proceso' : 'estado-cerrado' }}">
                                {{ $item['estado'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif


    <!-- Auditorías Internas -->
    @if(!empty($datosAuditorias))
        <div class="section">
            <h2>Auditorías Internas</h2>
            <p>Auditorías internas realizadas este semestre.</p>

            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Proceso</th>
                        <th>Entidad</th>
                        <th>Auditor Líder</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datosAuditorias as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['NombreProceso'] }}</td>
                            <td>{{ $item['Entidad'] }}</td>
                            <td>{{ $item['AuditorLider'] }}</td>
                            <td>{{ $item['fecha'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if(!empty($fortalezas))
                <h3>Fortalezas Identificadas</h3>
                <p>{{ $fortalezas }}</p>
            @endif

            @if(!empty($debilidades))
                <h3>Debilidades Identificadas</h3>
                <p>{{ $debilidades }}</p>
            @endif
        </div>
    @endif

    <!-- Seguimiento -->
    @if(!empty($datosSeguimiento))
        <div class="section">
            <h2>Seguimiento</h2>
            <p>Reuniones de seguimiento realizadas este semestre.</p>
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Proceso</th>
                        <th>Entidad</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datosSeguimiento as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['NombreProceso'] }}</td>
                            <td>{{ $item['Entidad'] }}</td>
                            <td>{{ $item['fecha'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif


    <!-- Conclusión -->
    <div class="section">
        <h2>Conclusión</h2>
        <p>{{ $conclusion }}</p>
    </div>

</body>

</html>