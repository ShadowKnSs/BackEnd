<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Proceso</title>
    <style>
        table {
            word-break: break-word;
            table-layout: fixed;
        }

        th,
        td {
            word-wrap: break-word;
            word-break: break-word;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            padding: 0;
        }

        table {
            word-break: break-word;
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            word-wrap: break-word;
            padding: 8px;
            text-align: center;
        }

        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
            color: #0e75cb;
        }


        .section {
            margin-bottom: 15px;
            padding: 10px;
        }

        .bold {
            font-weight: bold;
        }

        .status {
            padding: 8px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            border-radius: 5px;
            font-size: 14px;
        }

        .status-activo {
            background-color: #4CAF50;
            color: white;
        }

        .status-inactivo {
            background-color: #F44336;
            color: white;
        }

        .encabezado {
            background-color: #0e75cb;
            font-weight: bold;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Header del reporte con logos -->
    <header>
        <table style="width: 100%; margin-bottom: 10px;">
            <tr>
                <td style="width: 25%; text-align: left;">
                    <img src="{{ public_path('images/logo3.png') }}" alt="Logo 3" width="160">
                </td>
                <td style="width: 50%; text-align: center;">
                    <h2 style="margin: 0;">Sistema de Gestión de Calidad</h2>
                </td>
                <td style="width: 25%; text-align: right;">
                    <img src="{{ public_path('images/logo4.jpg') }}" alt="Logo 4" width="160">
                </td>
            </tr>
        </table>
        <p style="text-align: center; margin: 0;">Reporte generado el {{ date('d/m/Y') }}</p>
    </header>

    <!-- Título del Reporte -->
    <h1 class="title">Reporte del Proceso</h1>
    {{-- Mostrar mensaje si el reporte contiene datos parciales --}}
    @if(isset($reporteParcial) && $reporteParcial)
        <p style="color: red; text-align: center; font-weight: bold;">
            Atención: Este reporte contiene datos parciales.
        </p>
    @endif

    <!-- Norma y Año de Certificación en la misma línea -->
    <div class="section">
        <span class="bold">Norma:</span> {{ $norma }} |
        <span class="bold">Año de Certificación:</span> {{ $anioCertificacion }}
    </div>

    <!-- Entidad y Nombre del Proceso en el mismo renglón -->
    <div class="section">
        <span class="bold">Entidad/Dependencia:</span> {{ $entidad }} |
        <span class="bold">Nombre del Proceso:</span> {{ $nombreProceso }}
    </div>

    <!-- Líder del Proceso -->
    <div class="section">
        <span class="bold">Líder del Proceso:</span> {{ $liderProceso }}
    </div>

    <!-- Objetivo -->
    <div class="section">
        <span class="bold">Objetivo:</span> {{ $objetivo }}
    </div>

    <!-- Alcance -->
    <div class="section">
        <span class="bold">Alcance:</span> {{ $alcance }}
    </div>

    <!-- Estado con color -->
    <div class="section">
        @if ($estado == 'Activo')
            <span class="status status-activo">Activo</span>
        @elseif ($estado == 'Inactivo')
            <span class="status status-inactivo">Inactivo</span>
        @else
            <span class="status">{{ $estado }}</span>
        @endif
    </div>

    @if(
            ($documentos && $documentos !== 'No disponible') ||
            ($puestosInvolucrados && $puestosInvolucrados !== 'No disponible') ||
            ($fuente && $fuente !== 'No disponible') ||
            ($material && $material !== 'No disponible') ||
            ($requisitos && $requisitos !== 'No disponible') ||
            ($salidas && $salidas !== 'No disponible') ||
            ($receptores && $receptores !== 'No disponible')
        )
        <!-- Mapa de Proceso -->
        <div style="margin-bottom: 30px;">
            <h2 class="title">Mapa de Proceso</h2>

            <p><strong>Documentos Relacionados:</strong> {{ $documentos ?? 'No disponible' }}</p>
            <p><strong>Puestos Involucrados:</strong> {{ $puestosInvolucrados ?? 'No disponible' }}</p>

            <!-- Tabla 1: Fuente de Entrada y Entradas -->
            <table border="1" cellspacing="0" cellpadding="8" style="margin-top: 20px;">
                <thead>
                    <tr class="encabezado">
                        <th>Fuente de Entrada</th>
                        <th colspan="2">Entradas</th>
                    </tr>
                    <tr class="encabezado">
                        <th></th>
                        <th>Material y/o Información</th>
                        <th>Requisito de Entrada</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $fuente ?? 'No disponible' }}</td>
                        <td>{{ $material ?? 'No disponible' }}</td>
                        <td>{{ $requisitos ?? 'No disponible' }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Tabla 2: Salidas y Receptores -->
            <table border="1" cellspacing="0" cellpadding="8" style="margin-top: 20px;">
                <thead>
                    <tr class="encabezado">
                        <th>Salidas</th>
                        <th>Receptores de Salidas / Cliente</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $salidas ?? 'No disponible' }}</td>
                        <td>{{ $receptores ?? 'No disponible' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
    <!-- Diagrama de flujo -->
    @php
        $rutaFlujo = !empty($diagramaFlujo) ? public_path(str_replace('/storage/', 'storage/', parse_url($diagramaFlujo, PHP_URL_PATH))) : null;
    @endphp

    @if(!empty($diagramaFlujo) && $rutaFlujo && file_exists($rutaFlujo))
        <div style="margin-top: 40px;">
            <h2 class="title">Diagrama de Flujo</h2>
            <div style="margin-top: 15px; text-align: center;">
                <img src="{{ $rutaFlujo }}" alt="Diagrama de Flujo" style="max-width: 100%; max-height: 600px;">
            </div>
        </div>
    @endif

    <!-- Plan de control  -->
    @if($planControl && count($planControl) > 0)
        <div style="margin-top: 40px;">
            <h2 class="title">Plan de Control</h2>
            <table border="1" cellspacing="0" cellpadding="6" style="font-size: 12px;">
                <thead class="encabezado">
                    <tr>
                        <th>Actividad</th>
                        <th>Procedimiento</th>
                        <th>Características a Verificar</th>
                        <th>Criterio de Aceptación</th>
                        <th>Frecuencia</th>
                        <th>Identificación de la Salida</th>
                        <th>Registro de la Salida</th>
                        <th>Tratamiento</th>
                        <th>Responsable</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($planControl as $actividad)
                        <tr>
                            <td>{{ $actividad->nombreActividad }}</td>
                            <td>{{ $actividad->procedimiento }}</td>
                            <td>{{ $actividad->caracteriticasVerificar }}</td>
                            <td>{{ $actividad->criterioAceptacion }}</td>
                            <td>{{ $actividad->frecuencia }}</td>
                            <td>{{ $actividad->identificacionSalida }}</td>
                            <td>{{ $actividad->registroSalida }}</td>
                            <td>{{ $actividad->tratamiento }}</td>
                            <td>{{ $actividad->responsable }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <!-- Auditorías -->
    @if($auditorias && count($auditorias) > 0)
        <div style="margin-top: 40px;">
            <h2 class="title">Auditorías del Proceso</h2>
            <table border="1" cellspacing="0" cellpadding="6" style="font-size: 12px;">
                <thead class="encabezado">
                    <tr>
                        <th>Fecha Programada</th>
                        <th>Hora Programada</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($auditorias as $auditoria)
                        <tr>
                            <td>{{ $auditoria->fechaProgramada }}</td>
                            <td>{{ $auditoria->horaProgramada }}</td>
                            <td>{{ $auditoria->tipoAuditoria }}</td>
                            <td>{{ $auditoria->estado }}</td>
                            <td>{{ $auditoria->descripcion }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Seguimientos -->
    @if($seguimientos && count($seguimientos) > 0)
        <div style="margin-top: 40px;">
            <h2 class="title">Seguimiento</h2>
            @foreach ($seguimientos as $seguimiento)
                <h3>Minuta</h3>

                @if($asistentes->where('idSeguimiento', $seguimiento->idSeguimiento)->count() > 0)
                    <h4>Asistentes:</h4>
                    <table border="1" cellspacing="0" cellpadding="6" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($asistentes->where('idSeguimiento', $seguimiento->idSeguimiento) as $asistente)
                                <tr>
                                    <td>{{ $asistente->nombre }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <p><strong>Lugar:</strong> {{ $seguimiento->lugar }}</p>
                <p><strong>Fecha:</strong> {{ $seguimiento->fecha }}</p>
                <p><strong>Duración:</strong> {{ $seguimiento->duracion }}</p>

                @if($actividadesSeg->where('idSeguimiento', $seguimiento->idSeguimiento)->count() > 0)
                    <h4>Actividades:</h4>
                    <table border="1" cellspacing="0" cellpadding="6" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Actividades Realizadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($actividadesSeg->where('idSeguimiento', $seguimiento->idSeguimiento) as $actividad)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $actividad->descripcion }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if($compromisosSeg->where('idSeguimiento', $seguimiento->idSeguimiento)->count() > 0)
                    <h4>Compromisos:</h4>
                    <table border="1" cellspacing="0" cellpadding="6" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Compromisos</th>
                                <th>Responsable</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($compromisosSeg->where('idSeguimiento', $seguimiento->idSeguimiento) as $compromiso)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $compromiso->descripcion }}</td>
                                    <td>{{ $compromiso->responsables }}</td>
                                    <td>{{ $compromiso->fecha }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <hr style="margin-top: 20px; margin-bottom: 20px;">
            @endforeach
        </div>
    @endif


    @if($proyectoMejora)
        <!-- Información básica del proyecto -->
        <table class="table table-bordered">
            <tr>
                <th>Fecha:</th>
                <td>{{ $proyectoMejora->fecha }}</td>
                <th>No. Mejora:</th>
                <td>{{ $proyectoMejora->noMejora }}</td>
            </tr>
            <tr>
                <th>Descripción de la mejora:</th>
                <td colspan="3">{{ $proyectoMejora->descripcionMejora }}</td>
            </tr>
        </table>

        <!-- Objetivos/Beneficios de la mejora -->
        <h4>Objetivos/Beneficios de la mejora:</h4>
        @if(isset($proyectoObjetivos) && count($proyectoObjetivos) > 0)
            <ul>
                @foreach($proyectoObjetivos as $obj)
                    <li>{{ $obj->descripcionObj }}</li>
                @endforeach
            </ul>
        @else
            <p>No se registraron objetivos.</p>
        @endif

        <!-- Áreas de impacto/Personal beneficiado -->
        <h4>Áreas de impacto/Personal beneficiado:</h4>
        <p>{{ $proyectoMejora->areaImpacto }}</p>
        <p><strong>Personal beneficiado:</strong> {{ $proyectoMejora->personalBeneficiado }}</p>

        <!-- Responsables involucrados -->
        <h4>Responsables involucrados:</h4>
        @if(isset($proyectoResponsables) && count($proyectoResponsables) > 0)
            <ul>
                @foreach($proyectoResponsables as $resp)
                    <li>{{ $resp->nombre }}</li>
                @endforeach
            </ul>
        @else
            <p>No se registraron responsables.</p>
        @endif

        <!-- Situación actual -->
        <h4>Situación actual:</h4>
        <p>{{ $proyectoMejora->situacionActual }}</p>

        <!-- Indicadores de Éxito -->
        <h4>Indicadores de Éxito:</h4>
        @if(isset($proyectoIndicadoresExito) && count($proyectoIndicadoresExito) > 0)
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nombre del Indicador</th>
                        <th>Meta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($proyectoIndicadoresExito as $indicador)
                        <tr>
                            <td>{{ $indicador->nombreInd }}</td>
                            <td>{{ $indicador->meta }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No se registraron indicadores de éxito.</p>
        @endif
    @endif

    <!-- Gestión de Riesgos -->
    @if(!empty($riesgos) && count($riesgos) > 0)
        <div style="margin-top: 40px;">
            <h2 class="title">Gestión de Riesgos</h2>

            <!-- Identificación de Riesgos -->
            <h3>1. Identificación</h3>
            <table border="1" cellspacing="0" cellpadding="6" style="font-size: 10px;">
                <thead class="encabezado">
                    <tr>
                        <th>No</th>
                        <th>Fuente</th>
                        <th>Tipo</th>
                        <th>Descripción de Riesgo/Oportunidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riesgos as $index => $r)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $r->fuente }}</td>
                            <td>{{ $r->tipoRiesgo }}</td>
                            <td>{{ $r->descripcion }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Análisis de Riesgos -->
            <h3>2. Análisis</h3>
            <table border="1" cellspacing="0" cellpadding="6" style="font-size: 10px;">
                <thead class="encabezado">
                    <tr>
                        <th>Consecuencias</th>
                        <th>Severidad</th>
                        <th>Ocurrencia</th>
                        <th>NRP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riesgos as $r)
                        <tr>
                            <td>{{ $r->consecuencias }}</td>
                            <td>{{ $r->valorSeveridad }}</td>
                            <td>{{ $r->valorOcurrencia }}</td>
                            <td>{{ $r->valorNRP }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Tratamiento de Riesgos -->
            <h3>3. Tratamiento</h3>
            <table border="1" cellspacing="0" cellpadding="6" style="font-size: 10px;">
                <thead class="encabezado">
                    <tr>
                        <th>Actividades</th>
                        <th>Acciones de Mejora</th>
                        <th>Responsable</th>
                        <th>Fecha Implementación</th>
                        <th>Fecha Evaluación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riesgos as $r)
                        <tr>
                            <td>{{ $r->actividades }}</td>
                            <td>{{ $r->accionMejora }}</td>
                            <td>{{ $r->responsable }}</td>
                            <td>{{ \Carbon\Carbon::parse($r->fechaImp)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($r->fechaEva)->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Evaluación de la Efectividad -->
            <h3 style="margin-top: 20px;">4. Evaluación de la Efectividad</h3>
            <table width="100%" border="1" cellspacing="0" cellpadding="6"
                style="font-size: 10px; border-collapse: collapse;">
                <thead class="encabezado">
                    <tr>
                        <th>Reevaluación Severidad</th>
                        <th>Reevaluación Ocurrencia</th>
                        <th>NRP</th>
                        <th style="text-align: center;">Efectividad</th>
                        <th>Análisis de la Efectividad del Tratamiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riesgos as $r)
                        @php
                            $efectivo = $r->valorNRP >= $r->reevaluacionNRP;
                            $color = $efectivo ? '#28a745' : '#dc3545';
                        @endphp
                        <tr>
                            <td>{{ $r->reevaluacionSeveridad }}</td>
                            <td>{{ $r->reevaluacionOcurrencia }}</td>
                            <td>{{ $r->reevaluacionNRP }}</td>
                            <td style="background-color: {{ $color }}; color: #fff; text-align: center; font-weight: bold;">
                            </td>
                            <td>{{ $r->analisisEfectividad }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($planControlIndicadores) && count($planControlIndicadores) > 0)
        <div style="margin-top: 40px;">
            <h2 class="title">Análisis de Datos</h2>
            <h3>9.1.3 a) conformidad del prodcuto o servicio</h3>

            <table width="100%" border="1" cellspacing="0" cellpadding="6"
                style="font-size: 11px; border-collapse: collapse;">
                <thead class="encabezado">
                    <tr>
                        <th>No</th>
                        <th>Descripción de Indicador</th>
                        <th>Meta</th>
                        <th>Ene-Jun</th>
                        <th>Jul-Dic</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalMeta = 0;
                        $totalSem1 = 0;
                        $totalSem2 = 0;
                        $count = count($planControlIndicadores);
                    @endphp

                    @foreach ($planControlIndicadores as $i => $indicador)
                        @php
                            $totalMeta += $indicador->meta ?? 0;
                            $totalSem1 += $indicador->resultadoSemestral1 ?? 0;
                            $totalSem2 += $indicador->resultadoSemestral2 ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $indicador->nombreIndicador }}</td>
                            <td align="center">{{ $indicador->meta }}</td>
                            <td align="center">{{ $indicador->resultadoSemestral1 }}</td>
                            <td align="center">{{ $indicador->resultadoSemestral2 }}</td>
                        </tr>
                    @endforeach

                    {{-- Fila de Promedios --}}
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td colspan="2">Promedio</td>
                        <td align="center">{{ number_format($totalMeta / $count, 2) }}</td>
                        <td align="center">{{ number_format($totalSem1 / $count, 2) }}</td>
                        <td align="center">{{ number_format($totalSem2 / $count, 2) }}</td>
                    </tr>

                    {{-- Fila de interpretación y necesidad --}}
                    <tr>
                        <td colspan="2"><strong>Interpretación</strong></td>
                        <td colspan="3">{{ $interpretacionPlanControl ?? 'No disponible' }}</td>
                    </tr>
                    <tr>
                        <td colspan="2"><strong>Necesidad de mejora</strong></td>
                        <td colspan="3">{{ $necesidadPlanControl ?? 'No disponible' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif


    <!-- Gráficas -->
    @if(file_exists($graficaPlanControl))
        <div style="margin-top: 40px; text-align: center;">
            <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 15px;">Gráfica de Plan de Control</h3>
            <img src="{{ $graficaPlanControl }}" style="width: 100%; max-height: 400px;" alt="Gráfica Plan de Control">
        </div>
    @endif

    @php
        $encuestas = collect($indicadoresSatisfaccion)->where('origen', 'Encuesta');
        $retroalimentaciones = collect($indicadoresSatisfaccion)->where('origen', 'Retroalimentacion');
        $noEncuestas = $encuestas->first()['noEncuestas'] ?? 0;
        $totalFelicitaciones = $retroalimentaciones->sum('felicitaciones');
        $totalSugerencias = $retroalimentaciones->sum('sugerencias');
        $totalQuejas = $retroalimentaciones->sum('quejas');
        $totalRetro = $totalFelicitaciones + $totalSugerencias + $totalQuejas;
        $sumRowTotals = $retroalimentaciones->sum('total');
        $interpretacionGeneral = $encuestas->first()['interpretacion'] ?? 'No hay interpretación';
        $necesidadGeneral = $encuestas->first()['necesidad'] ?? 'No hay necesidad';
    @endphp

    @if($encuestas->count() > 0 || $retroalimentaciones->count() > 0)
        <div style="margin-top: 40px;">
            <h2 class="title">9.1.3 b) Satisfacción del Cliente</h2>
            <table border="1" cellspacing="0" cellpadding="6"
                style="font-size: 11px; border-collapse: collapse; width: 100%;">
                <thead class="encabezado">
                    <tr>
                        <th colspan="8" class="text-center">Encuesta de Satisfacción</th>
                    </tr>
                    <tr>
                        <th>No</th>
                        <th>Descripción del Indicador</th>
                        <th>No. Encuestas</th>
                        <th>E+B (%)</th>
                        <th>R (%)</th>
                        <th>M (%)</th>
                        <th>Meta (%)</th>
                        <th>Anual (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($encuestas as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $item['nombreIndicador'] }}</td>
                            <td>{{ $item['noEncuestas'] }}</td>
                            <td>{{ $item['porcentajeEB'] }}%</td>
                            <td>{{ $noEncuestas > 0 ? round($item['regular'] * 100 / $noEncuestas, 2) : '-' }}%</td>
                            <td>{{ $noEncuestas > 0 ? round($item['malo'] * 100 / $noEncuestas, 2) : '-' }}%</td>
                            <td>{{ $item['meta'] ?? '-' }}%</td>
                            <td>{{ $item['porcentajeEB'] }}%</td>
                        </tr>
                    @endforeach
                    <tr class="encabezado">
                        <th colspan="8">Retroalimentación</th>
                    </tr>
                    <tr>
                        <th>No</th>
                        <th>Descripción del Indicador</th>
                        <th>F</th>
                        <th>S</th>
                        <th>Q</th>
                        <th>Total</th>
                        <th colspan="2"></th>
                    </tr>
                    @foreach($retroalimentaciones as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $item['nombreIndicador'] }}</td>
                            <td>{{ $item['felicitaciones'] }}</td>
                            <td>{{ $item['sugerencias'] }}</td>
                            <td>{{ $item['quejas'] }}</td>
                            <td>{{ $item['total'] }}</td>
                            <td colspan="2"></td>
                        </tr>
                    @endforeach
                    <tr style="font-weight: bold; background-color: #f0f0f0">
                        <td colspan="2">Total Retroalimentación</td>
                        <td>{{ $totalFelicitaciones }}</td>
                        <td>{{ $totalSugerencias }}</td>
                        <td>{{ $totalQuejas }}</td>
                        <td>{{ $totalRetro }}</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="2">Suma Totales Retroalimentación</td>
                        <td>{{ $sumRowTotals }}</td>
                        <td colspan="5"></td>
                    </tr>
                    <tr>
                        <td colspan="4"><strong>Interpretación:</strong> {{ $interpretacionGeneral }}</td>
                        <td colspan="4"><strong>Necesidad:</strong> {{ $necesidadGeneral }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif




    @if(file_exists($graficaEncuesta))
        <div style="margin-top: 40px; text-align: center;">
            <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 15px;">Gráfica de Encuesta</h3>
            <img src="{{ $graficaEncuesta }}" style="width: 100%; max-height: 400px;" alt="Gráfica Encuesta">
        </div>
    @endif


    @if(file_exists($graficaRetroalimentacion))
        <div style="margin-top: 40px; text-align: center;">
            <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 15px;">Gráfica de Retroalimentación</h3>
            <img src="{{ $graficaRetroalimentacion }}" style="width: 100%; max-height: 400px;"
                alt="Gráfica Retroalimentación">
        </div>
    @endif


    {{-- Tabla: Desempeño del Proceso --}}
    @if(!empty($mapaProcesoIndicadores) && count($mapaProcesoIndicadores) > 0)
        <div style="margin-top: 40px;">
            <h2 class="title">Análisis de Datos - Desempeño del Proceso</h2>

            <table width="100%" border="1" cellspacing="0" cellpadding="6"
                style="font-size: 11px; border-collapse: collapse;">
                <thead class="encabezado">
                    <tr>
                        <th>No</th>
                        <th>Descripción de los Indicadores</th>
                        <th>Meta</th>
                        <th>Ene-Jun</th>
                        <th>Jul-Dic</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalMeta = 0;
                        $totalSem1 = 0;
                        $totalSem2 = 0;
                        $count = count($mapaProcesoIndicadores);
                    @endphp

                    @foreach ($mapaProcesoIndicadores as $index => $item)
                        @php
                            $totalMeta += $item->meta ?? 0;
                            $totalSem1 += $item->resultadoSemestral1 ?? 0;
                            $totalSem2 += $item->resultadoSemestral2 ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->nombreIndicador }}</td>
                            <td align="center">{{ $item->meta }}</td>
                            <td align="center">{{ $item->resultadoSemestral1 }}</td>
                            <td align="center">{{ $item->resultadoSemestral2 }}</td>
                        </tr>
                    @endforeach

                    {{-- Fila de Promedios --}}
                    <tr style="background-color: #f0f0f0; font-weight: bold;">
                        <td colspan="2">Promedio</td>
                        <td align="center">{{ number_format($totalMeta / $count, 2) }}</td>
                        <td align="center">{{ number_format($totalSem1 / $count, 2) }}</td>
                        <td align="center">{{ number_format($totalSem2 / $count, 2) }}</td>
                    </tr>

                    {{-- Interpretación y Necesidad --}}
                    <tr>
                        <td colspan="2"><strong>Interpretación:</strong> {{ $interpretacionMapaProceso ?? 'No disponible' }}
                        </td>
                        <td colspan="3"><strong>Necesidad de mejora:</strong> {{ $necesidadMapaProceso ?? 'No disponible' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif


    @if(file_exists($graficaMP))
        <div style="margin-top: 40px; text-align: center;">
            <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 15px;">Gráfica de Mapa de Proceso</h3>
            <img src="{{ $graficaMP }}" style="width: 100%; max-height: 400px;" alt="Gráfica Mapa de Proceso">
        </div>
    @endif


    @if(!empty($eficaciaRiesgos) && count($eficaciaRiesgos) > 0)
        <div style="margin-top: 40px;">
            <h2 class="title">Análisis de Datos - Eficacia de los Riesgos y Oportunidades</h2>

            <table width="100%" border="1" cellspacing="0" cellpadding="6"
                style="font-size: 11px; border-collapse: collapse;">
                <thead class="encabezado">
                    <tr>
                        <th>No</th>
                        <th>Nombre del Indicador</th>
                        <th>Meta</th>
                        <th>Resultado Anual</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalMeta = 0;
                        $totalSem1 = 0;
                        $totalSem2 = 0;
                        $count = count($eficaciaRiesgos);
                    @endphp

                    @foreach ($eficaciaRiesgos as $index => $indi)
                        @php
                            $totalMeta += $indi->meta ?? 0;
                            $totalSem1 += $indi->resultadoSemestral1 ?? 0;
                            $totalSem2 += $indi->resultadoAnual ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $indi->nombreIndicador }}</td>
                            <td @if (is_null($indi->meta)) style="background-color: #ffe0e0; color: #b00020;" @endif>
                                {{ $indi->meta ?? 'No asignada' }}
                            </td>
                            <td style="text-align: center;">{{ $indi->resultadoAnual ?? '-' }}</td>
                        </tr>
                    @endforeach

                    {{-- Fila interpretación --}}
                    <tr style="background-color: #f0f0f0;">
                        <td colspan="2"><strong>Interpretación del comportamiento del proceso</strong></td>
                        <td colspan="2">{{ $eficaciaRiesgos[0]->interpretacion ?? 'No disponible' }}</td>
                    </tr>

                    {{-- Fila necesidad --}}
                    <tr style="background-color: #f0f0f0;">
                        <td colspan="2"><strong>Necesidad de mejora del proceso en el SGC</strong></td>
                        <td colspan="2">{{ $eficaciaRiesgos[0]->necesidad ?? 'No disponible' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif


    @if(file_exists($graficaRiesgos))
        <div style="margin-top: 40px; text-align: center;">
            <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 15px;">Gráfica de Riesgos</h3>
            <img src="{{ $graficaRiesgos }}" style="width: 100%; max-height: 400px;" alt="Gráfica de Riesgos">
        </div>
    @endif


    @if(isset($evaluacionProveedores) && !empty($evaluacionProveedores['indicadores']) && count($evaluacionProveedores['indicadores']) > 0)
        <div style="margin-top: 40px; page-break-before: always;">
            <h2 class="title">Análisis de Datos - Desempeño de Proveedores Externos</h2>

            <table width="100%" border="1" cellspacing="0" cellpadding="6"
                style="font-size: 11px; border-collapse: collapse;">
                <thead class="encabezado">
                    <tr>
                        <th>No</th>
                        <th>Nombre del Indicador</th>
                        <th>Meta</th>
                        <th>Ene-Jun</th>
                        <th>Jul-Dic</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($evaluacionProveedores['indicadores'] as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['categoria'] }}</td>
                            <td>{{ $item['meta'] ?? 'No disponible' }}%</td>
                            <td>{{ $item['resultado1'] ?? '-' }}%</td>
                            <td>{{ $item['resultado2'] ?? '-' }}%</td>
                        </tr>
                    @endforeach

                    {{-- Interpretación --}}
                    <tr style="background-color: #f0f0f0;">
                        <td colspan="2"><strong>Interpretación:</strong></td>
                        <td colspan="3">{{ $evaluacionProveedores['interpretacion'] ?? 'No disponible' }}</td>
                    </tr>

                    {{-- Necesidad --}}
                    <tr style="background-color: #f0f0f0;">
                        <td colspan="2"><strong>Necesidad de Mejora:</strong></td>
                        <td colspan="3">{{ $evaluacionProveedores['necesidad'] ?? 'No disponible' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if(file_exists($graficaEvaluacion))
        <div style="margin-top: 40px; text-align: center;">
            <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 15px;">Gráfica de Evaluación de Proveedores</h3>
            <img src="{{ $graficaEvaluacion }}" style="width: 100%; max-height: 400px;"
                alt="Gráfica de Evaluación de Proveedores">
        </div>
    @endif

    @if($planCorrectivo)
        <!-- Información básica del Plan Correctivo -->
        <table class="table table-bordered">
            <tr>
                <th>Coordinador:</th>
                <td>{{ $planCorrectivo->coordinadorPlan }}</td>
                <th>Código:</th>
                <td>{{ $planCorrectivo->codigo }}</td>
            </tr>
            <tr>
                <th>Fecha:</th>
                <td>{{ $planCorrectivo->fechaInicio }}</td>
            </tr>
        </table>

        <h4>Origen de la no conformidad:</h4>
        <p>{{ $planCorrectivo->origenConformidad }}</p>

        <h4>Equipo de mejora:</h4>
        <p>{{ $planCorrectivo->equipoMejora }}</p>

        <h4>Actividades de reacción:</h4>
        <table border="1" cellspacing="0" cellpadding="6" style="font-size: 12px;">
            <thead>
                <tr>
                    <th>Actividad</th>
                    <th>Responsable</th>
                    <th>Fecha Programada</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($actividadesPlan->where('idPlanCorrectivo', $planCorrectivo->idPlanCorrectivo) as $act)
                    <tr>
                        <td>{{ $act->descripcionAct }}</td>
                        <td>{{ $act->responsable }}</td>
                        <td>{{ $act->fechaProgramada }}</td>
                        <td>{{ $act->tipo }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h4>Revisión y análisis:</h4>
        <p>{{ $planCorrectivo->revisionAnalisis }}</p>

        <h4>Determinación de causa raíz:</h4>
        <p>{{ $planCorrectivo->causaRaiz }}</p>
    @endif

    @if(isset($planTrabajoData) && $planTrabajoData['planTrabajo'])
    <div style="margin-top: 40px; page-break-before: always;">
        <h2 class="title">Plan de Trabajo</h2>
        
        <!-- Información general -->
        <table width="100%" border="1" cellspacing="0" cellpadding="6" style="font-size: 11px; border-collapse: collapse; margin-bottom: 15px;">
            <tr>
                <td width="15%"><strong>Fecha de Elaboración:</strong></td>
                <td width="35%">{{ $planTrabajoData['planTrabajo']->fechaElaboracion ?? 'No especificado' }}</td>
                <td width="15%"><strong>Objetivo:</strong></td>
                <td width="35%">{{ $planTrabajoData['planTrabajo']->objetivo ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <td><strong>Fecha de Revisión:</strong></td>
                <td>{{ $planTrabajoData['planTrabajo']->fechaRevision ?? 'No especificado' }}</td>
                <td><strong>Revisado Por:</strong></td>
                <td>{{ $planTrabajoData['planTrabajo']->revisadoPor ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <td><strong>Responsable:</strong></td>
                <td>{{ $planTrabajoData['planTrabajo']->responsable ?? 'No especificado' }}</td>
                <td><strong>Estado:</strong></td>
                <td>{{ $planTrabajoData['planTrabajo']->estado ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <td><strong>Fuente:</strong></td>
                <td>{{ $planTrabajoData['planTrabajo']->fuente ?? 'No especificado' }}</td>
                <td><strong>Entregable:</strong></td>
                <td>{{ $planTrabajoData['planTrabajo']->entregable ?? 'No especificado' }}</td>
            </tr>
        </table>

        <!-- Fuentes documentales -->
        @if(isset($planTrabajoData['fuentes']) && count($planTrabajoData['fuentes']) > 0)
            <h4>Fuentes Documentales</h4>
            <table width="100%" border="1" cellspacing="0" cellpadding="6" style="font-size: 10px; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>No. Actividad</th>
                        <th>Responsable</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Término</th>
                        <th>Estado</th>
                        <th>Nombre Fuente</th>
                        <th>Elemento Entrada</th>
                        <th>Descripción</th>
                        <th>Entregable</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($planTrabajoData['fuentes'] as $fuente)
                        <tr>
                            <td>{{ $fuente->noActividad ?? '-' }}</td>
                            <td>{{ $fuente->responsable ?? '-' }}</td>
                            <td>{{ $fuente->fechaInicio ?? '-' }}</td>
                            <td>{{ $fuente->fechaTermino ?? '-' }}</td>
                            <td>{{ $fuente->estado ?? '-' }}</td>
                            <td>{{ $fuente->nombreFuente ?? '-' }}</td>
                            <td>{{ $fuente->elementoEntrada ?? '-' }}</td>
                            <td>{{ $fuente->descripcion ?? '-' }}</td>
                            <td>{{ $fuente->entregable ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endif

    @if(isset($proyectoMejoraData) && $proyectoMejoraData['proyectoMejora'])
        <div style="margin-top: 40px; page-break-before: always;">
            <h2 class="title">Proyecto de Mejora</h2>

            <!-- Información básica del proyecto -->
            <table width="100%" border="1" cellspacing="0" cellpadding="6"
                style="font-size: 11px; border-collapse: collapse; margin-bottom: 15px;">
                <tr>
                    <td width="15%"><strong>Fecha:</strong></td>
                    <td width="35%">{{ $proyectoMejoraData['proyectoMejora']->fecha }}</td>
                    <td width="15%"><strong>No. Mejora:</strong></td>
                    <td width="35%">{{ $proyectoMejoraData['proyectoMejora']->noMejora }}</td>
                </tr>
                <tr>
                    <td><strong>Descripción de la mejora:</strong></td>
                    <td colspan="3">{{ $proyectoMejoraData['proyectoMejora']->descripcionMejora }}</td>
                </tr>
                <tr>
                    <td><strong>Áreas de impacto/Personal beneficiado:</strong></td>
                    <td colspan="3">{{ $proyectoMejoraData['proyectoMejora']->areaImpacto }}</td>
                </tr>
                <tr>
                    <td><strong>Situación actual:</strong></td>
                    <td colspan="3">{{ $proyectoMejoraData['proyectoMejora']->situacionActual }}</td>
                </tr>
                <tr>
                    <td><strong>Aprobación Nombre:</strong></td>
                    <td>{{ $proyectoMejoraData['proyectoMejora']->aprobacionNombre }}</td>
                    <td><strong>Aprobación Puesto:</strong></td>
                    <td>{{ $proyectoMejoraData['proyectoMejora']->aprobacionPuesto }}</td>
                </tr>
            </table>

            <!-- Objetivos/Beneficios de la mejora -->
            <h3 style="margin-top: 20px;">Objetivos/Beneficio de la mejora:</h3>
            @if(count($proyectoMejoraData['objetivos']) > 0)
                <ul>
                    @foreach($proyectoMejoraData['objetivos'] as $objetivo)
                        <li>{{ $objetivo->descripcionObj }}</li>
                    @endforeach
                </ul>
            @else
                <p>No se registraron objetivos.</p>
            @endif

            <!-- Responsables involucrados -->
            <h3 style="margin-top: 20px;">Responsables involucrados:</h3>
            @if(count($proyectoMejoraData['responsables']) > 0)
                <ul>
                    @foreach($proyectoMejoraData['responsables'] as $responsable)
                        <li>{{ $responsable->nombre }}</li>
                    @endforeach
                </ul>
            @else
                <p>No hay responsables registrados.</p>
            @endif

            <!-- Indicadores de Éxito -->
            <h3 style="margin-top: 20px;">Indicadores de Éxito:</h3>
            @if(count($proyectoMejoraData['indicadoresExito']) > 0)
                <ul>
                    @foreach($proyectoMejoraData['indicadoresExito'] as $indicador)
                        <li>{{ $indicador->nombreInd }} - Meta: {{ $indicador->meta }}</li>
                    @endforeach
                </ul>
            @else
                <p>No se definieron indicadores.</p>
            @endif

            <!-- Recursos -->
            <h3 style="margin-top: 20px;">Recursos:</h3>
            @if(count($proyectoMejoraData['recursos']) > 0)
                <table width="100%" border="1" cellspacing="0" cellpadding="6"
                    style="font-size: 11px; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>Tiempo</th>
                            <th>Recursos Materiales y Humanos</th>
                            <th>Costo estimado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proyectoMejoraData['recursos'] as $recurso)
                            <tr>
                                <td>{{ $recurso->tiempoEstimado }}</td>
                                <td>{{ $recurso->recursosMatHum }}</td>
                                <td>{{ $recurso->costo }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No se registraron recursos.</p>
            @endif

            <!-- Actividades -->
            <h3 style="margin-top: 20px;">Actividades:</h3>
            @if(count($proyectoMejoraData['actividadesPM']) > 0)
                <table width="100%" border="1" cellspacing="0" cellpadding="6"
                    style="font-size: 11px; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th>Responsable</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($proyectoMejoraData['actividadesPM'] as $actividad)
                            <tr>
                                <td>{{ $actividad->descripcionAct }}</td>
                                <td>{{ $actividad->responsable }}</td>
                                <td>{{ $actividad->fecha }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No se registraron actividades.</p>
            @endif
        </div>
    @endif


</body>

</html>