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
    </style>
</head>

<body>

    <!-- Encabezado del reporte -->
    <div style="text-align: center; margin-bottom: 20px;">
        <img src="{{ public_path('logo.png') }}" alt="Logo" width="80">
        <h2>Sistema de Gestión de Calidad</h2>
        <p>Reporte generado el {{ date('d/m/Y') }}</p>
    </div>

    <!-- Título del Reporte -->
    <h1 class="title">Reporte del Proceso</h1>

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

    <!-- Mapa de Proceso -->
    <div style="margin-bottom: 30px;">
        <h2 style="padding-bottom: 4px, color: #0e75cb;">Mapa de Proceso</h2>

        <!-- Documentos Relacionados -->
        <p><strong>Documentos Relacionados:</strong> {{ $documentos ?? 'No disponible' }}</p>

        <!-- Puestos Involucrados -->
        <p><strong>Puestos Involucrados:</strong> {{ $puestosInvolucrados ?? 'No disponible' }}</p>

        <!-- Tabla 1: Fuente de Entrada y Entradas -->
        <table width="100%" border="1" cellspacing="0" cellpadding="8"
            style="margin-top: 20px; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #0e75cb;color: white;">
                    <th style="text-align: center;">Fuente de Entrada</th>
                    <th colspan="2" style="text-align: center;">Entradas</th>
                </tr>
                <tr style="background-color: #0e75cb; color: white;">
                    <th></th>
                    <th style="text-align: center;">Material y/o Información</th>
                    <th style="text-align: center;">Requisito de Entrada</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">{{ $fuente ?? 'No disponible' }}</td>
                    <td style="text-align: center;">{{ $material ?? 'No disponible' }}</td>
                    <td style="text-align: center;">{{ $requisitos ?? 'No disponible' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Tabla 2: Salidas y Receptores -->
        <table width="100%" border="1" cellspacing="0" cellpadding="8"
            style="margin-top: 20px; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #0e75cb; color: white;">
                    <th style="text-align: center;">Salidas</th>
                    <th style="text-align: center;">Receptores de Salidas / Cliente</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">{{ $salidas ?? 'No disponible' }}</td>
                    <td style="text-align: center;">{{ $receptores ?? 'No disponible' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Diagrama de Flujo -->
    <div style="margin-top: 40px;">
        <h2 style="font-size: 20px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px;">
            Diagrama de Flujo
        </h2>

        @if (!empty($diagramaFlujo))
            <div style="margin-top: 15px; text-align: center;">
                <img src="{{ public_path(str_replace('/storage/', 'storage/', parse_url($diagramaFlujo, PHP_URL_PATH))) }}"
                    alt="Diagrama de Flujo" style="max-width: 100%; max-height: 600px;">
            </div>
        @else
            <p style="color: gray;">No se ha registrado un Diagrama de Flujo para este proceso.</p>
        @endif

    </div>

    <!-- Plan de Control -->
    <div style="margin-top: 40px;">
        <h2 style="font-size: 20px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px;">
            Plan de Control
        </h2>

        @if ($planControl && count($planControl) > 0)
            <table width="100%" border="1" cellspacing="0" cellpadding="6"
                style="border-collapse: collapse; margin-top: 10px; font-size: 12px;">
                <thead style="background-color: #0e75cb;">
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
        @else
            <p style="color: gray;">No hay actividades registradas para el plan de control de este proceso.</p>
        @endif
    </div>
    <!-- Auditorías -->
    <div style="margin-top: 40px;">
    <h2 style="font-size: 20px; font-weight: bold; border-bottom: 2px solid #000; padding-bottom: 5px;">
        Auditorías del Proceso
    </h2>

    @if ($auditorias && count($auditorias) > 0)
        <table width="100%" border="1" cellspacing="0" cellpadding="6"
            style="border-collapse: collapse; margin-top: 10px; font-size: 12px;">
            <thead style="background-color: #0e75cb; color: white;">
                <tr>
                    <th>Fecha Programada</th>
                    <th>Hora Programada</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Descripcion</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($auditorias as $auditoria)
                    <tr>
                        <td style="text-align: center;">{{ $auditoria->fechaProgramada }}</td>
                        <td style="text-align: center;">{{ $auditoria->horaProgramada }}</td>
                        <td style="text-align: center;">{{ $auditoria->tipoAuditoria }}</td>
                        <td style="text-align: center;">{{ $auditoria->estado }}</td>
                        <td style="text-align: center;">{{ $auditoria->descripcion }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: gray;">No hay auditorías registradas para este proceso.</p>
    @endif
    </div>
</body>

</html>