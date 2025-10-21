<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Reporte de Proceso</title>

    <style>
        /* ---------- Página / tipografía ---------- */
        @page {
            margin: 28mm 18mm 20mm 18mm;
        }

        /* top right bottom left */
        * {
            box-sizing: border-box;
        }

        html,
        body {
            font-family: Arial, sans-serif;
            color: #0f172a;
        }

        body {
            font-size: 12px;
        }

        /* ---------- Paleta ---------- */
        /* Primario = azul institucional */
        .c-primary {
            color: #0e75cb;
        }

        .bg-primary {
            background: #0e75cb;
            color: #fff;
        }

        .bg-soft {
            background: #eef6fe;
        }

        .border-soft {
            border-color: #d9e6f2;
        }

        /* ---------- Header / Footer fijos ---------- */
        header {
            position: fixed;
            top: -22mm;
            left: 0;
            right: 0;
            height: 22mm;
        }

        footer {
            position: fixed;
            bottom: -14mm;
            left: 0;
            right: 0;
            height: 14mm;
            font-size: 10px;
            color: #64748b;
        }

        .page-number:after {
            content: counter(page);
        }

        .page-count:after {
            content: counter(pages);
        }

        /* ---------- Utilidades ---------- */
        .mt-0 {
            margin-top: 0
        }

        .mt-6 {
            margin-top: 6px
        }

        .mt-10 {
            margin-top: 10px
        }

        .mt-16 {
            margin-top: 16px
        }

        .mt-20 {
            margin-top: 20px
        }

        .mt-28 {
            margin-top: 28px
        }

        .mt-32 {
            margin-top: 32px
        }

        .mt-40 {
            margin-top: 40px
        }

        .mb-0 {
            margin-bottom: 0
        }

        .mb-6 {
            margin-bottom: 6px
        }

        .mb-10 {
            margin-bottom: 10px
        }

        .mb-16 {
            margin-bottom: 16px
        }

        .mb-20 {
            margin-bottom: 20px
        }

        .mb-28 {
            margin-bottom: 28px
        }

        .mb-32 {
            margin-bottom: 32px
        }

        .mb-40 {
            margin-bottom: 40px
        }

        .py-6 {
            padding-top: 6px;
            padding-bottom: 6px
        }

        .py-8 {
            padding-top: 8px;
            padding-bottom: 8px
        }

        .py-10 {
            padding-top: 10px;
            padding-bottom: 10px
        }

        .px-10 {
            padding-left: 10px;
            padding-right: 10px
        }

        .text-center {
            text-align: center
        }

        .text-right {
            text-align: right
        }

        .text-muted {
            color: #64748b
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .chip {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            background: #eef6fe;
            color: #0e75cb;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #d9e6f2;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 11px;
        }

        .badge-success {
            background: #22c55e;
            color: #fff;
        }

        .badge-danger {
            background: #ef4444;
            color: #fff;
        }

        .badge-neutral {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* ---------- Secciones tipo "card" ---------- */
        .section-card {
            margin-top: 24px;
            border: 1px solid #d9e6f2;
            border-left: 6px solid #0e75cb;
            padding: 14px 14px 10px 14px;
            border-radius: 6px;
            background: #fff;
        }

        .section-title {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #0e75cb;
            letter-spacing: .2px;
        }

        .section-subtitle {
            margin: 6px 0 8px;
            font-weight: bold;
            color: #0f172a;
        }

        /* ---------- Tablas ---------- */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            padding: 6px 8px;
            border: 1px solid #d9e6f2;
            word-break: break-word;
        }

        thead th {
            font-weight: bold;
        }

        .table {
            font-size: 11px;
        }

        .thead-primary th {
            background: #0e75cb;
            color: #fff;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .no-border th,
        .no-border td {
            border: none;
        }

        /* ---------- Imágenes ---------- */
        .img-contained {
            max-width: 100%;
            max-height: 520px;
        }

        /* ---------- Cortes de página ---------- */
        .page-break {
            page-break-before: always;
        }

        .avoid-break {
            page-break-inside: avoid;
        }

        /* ---------- Encabezado del título ---------- */
        .doc-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            color: #0e75cb;
            margin: 6mm 0 2mm;
        }

        .small {
            font-size: 10px;
        }

        .section-empty {
            margin-top: 12px;
            border: 1px dashed #cbd5e1;
            border-left: 6px solid #94a3b8;
            padding: 10px 12px;
            border-radius: 6px;
            background: #f8fafc;
            color: #64748b;
            font-style: italic;
        }

        .section-empty .section-title {
            color: #475569;
            margin-bottom: 6px;
        }
    </style>
</head>
@php
    // Controla si quieres mostrar tarjetas "vacías" o simplemente omitir secciones
    $mostrarVacias = $mostrarVacias ?? false;

    // Helper para strings tipo "No disponible" o vacíos
    $hasVal = function ($v) {
        return isset($v) && $v !== '' && $v !== 'No disponible';
    };
@endphp
<body>

    {{-- Header fijo --}}
    <header>
        <table class="no-border" style="width:100%;">
            <tr>
                <td style="width:25%; text-align:left;">
                    <img src="{{ public_path('images/logo3.png') }}" alt="Logo 3" width="150">
                </td>
                <td style="width:50%; text-align:center;">
                    <div style="font-weight:bold; font-size:14px; color:#0e75cb;">Sistema de Gestión de la Calidad</div>
                    <div class="small text-muted">Reporte generado el {{ date('d/m/Y') }}</div>
                </td>
                <td style="width:25%; text-align:right;">
                    <img src="{{ public_path('images/logo4.jpg') }}" alt="Logo 4" width="150">
                </td>
            </tr>
        </table>
    </header>

    {{-- Footer fijo con numeración --}}
    <footer>
        <table class="no-border" style="width:100%;">
            <tr>
                <td class="small">Entidad: <strong>{{ $entidad }}</strong></td>
<td class="small text-right">
  Página <span class="page-number"></span> de <span class="page-count"></span>
</td>
            </tr>
        </table>
    </footer>

    {{-- Título --}}
    <h1 class="doc-title">Reporte del Proceso</h1>

    {{-- Aviso de parcial --}}
    @if(isset($reporteParcial) && $reporteParcial)
        <p class="text-center" style="color:#b91c1c; font-weight:bold; margin-top:2mm;">
            Atención: Este reporte contiene datos parciales.
        </p>
    @endif

    {{-- Resumen / Metadata --}}
    <div class="section-card avoid-break">
        <h2 class="section-title">Resumen del Proceso</h2>

        <table class="table">
            <thead class="thead-primary">
                <tr>
                    <th>Entidad / Dependencia</th>
                    <th>Nombre del Proceso</th>
                    <th>Líder del Proceso</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $entidad }}</td>
                    <td>{{ $nombreProceso }}</td>
                    <td>{{ $liderProceso }}</td>
                    <td class="text-center">
                        @if ($estado === 'Activo')
                            <span class="badge badge-success">Activo</span>
                        @elseif ($estado === 'Inactivo')
                            <span class="badge badge-danger">Inactivo</span>
                        @else
                            <span class="badge badge-neutral">{{ $estado }}</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="grid-3 mt-10">
            <div><span class="chip">Norma</span> &nbsp; <strong>{{ $norma }}</strong></div>
            <div><span class="chip">Año Certificación</span> &nbsp; <strong>{{ $anioCertificacion }}</strong></div>
            <div><span class="chip">Fecha de Emisión</span> &nbsp; <strong>{{ date('d/m/Y') }}</strong></div>
        </div>

        <div class="mt-10"><strong>Objetivo:</strong> {{ $objetivo }}</div>
        <div class="mt-6"><strong>Alcance:</strong> {{ $alcance }}</div>
    </div>

    {{-- Mapa de Proceso (si hay datos) --}}
    @if(
            ($documentos && $documentos !== 'No disponible') ||
            ($puestosInvolucrados && $puestosInvolucrados !== 'No disponible') ||
            ($fuente && $fuente !== 'No disponible') ||
            ($material && $material !== 'No disponible') ||
            ($requisitos && $requisitos !== 'No disponible') ||
            ($salidas && $salidas !== 'No disponible') ||
            ($receptores && $receptores !== 'No disponible')
        )
            <div class="section-card">
                <h2 class="section-title">Mapa de Proceso</h2>

                <div class="grid-2">
                    <div><strong>Documentos relacionados:</strong> {{ $documentos ?? 'No disponible' }}</div>
                    <div><strong>Puestos involucrados:</strong> {{ $puestosInvolucrados ?? 'No disponible' }}</div>
                </div>

                <table class="table mt-10">
                    <thead class="thead-primary">
                        <tr>
                            <th style="width:28%;">Fuente de entrada</th>
                            <th style="width:36%;">Material y/o Información</th>
                            <th style="width:36%;">Requisito de entrada</th>
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

                <table class="table mt-10">
                    <thead class="thead-primary">
                        <tr>
                            <th style="width:50%;">Salidas</th>
                            <th style="width:50%;">Receptores de salida / Cliente</th>
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

    {{-- Diagrama de Flujo --}}
    @php
    $srcFlujo = null;

    if (!empty($diagramaFlujo)) {
        // 1) data URL (base64) -> úsalo directo
        if (is_string($diagramaFlujo) && str_starts_with($diagramaFlujo, 'data:image/')) {
            $srcFlujo = $diagramaFlujo;
        } else {
            // 2) Intentar ruta local en public/storage
            $pathFromUrl = parse_url($diagramaFlujo, PHP_URL_PATH) ?? '';
            // normaliza: /storage/...  -> storage/...
            $relative = ltrim(str_replace('/storage/', 'storage/', $pathFromUrl), '/');
            $localPath = public_path($relative);

            if (is_file($localPath)) {
                $srcFlujo = $localPath; // DomPDF acepta ruta absoluta local
            } elseif (filter_var($diagramaFlujo, FILTER_VALIDATE_URL)) {
                // 3) URL absoluta: requiere remote_enabled = true
                $srcFlujo = $diagramaFlujo;
            }
        }
    }

    // Valida extensión imagen para evitar PDFs/ZIPs
    $extOk = false;
    if ($srcFlujo) {
        $ext = strtolower(pathinfo(is_string($srcFlujo) ? $srcFlujo : '', PATHINFO_EXTENSION));
        $extOk = in_array($ext, ['png','jpg','jpeg','gif']) || str_starts_with($srcFlujo, 'data:image/');
    }
@endphp

@if($srcFlujo && $extOk)
  <div class="section-card text-center">
    <h2 class="section-title">Diagrama de Flujo</h2>
    <img src="{{ $srcFlujo }}" alt="Diagrama de Flujo" class="img-contained" />
  </div>
@elseif($mostrarVacias)
  <div class="section-empty avoid-break">
    <h2 class="section-title">Diagrama de Flujo</h2>
    <div>No hay diagrama de flujo disponible.</div>
  </div>
@endif
v>
    @endif

    {{-- Plan de Control (tabla + gráfica) --}}
    @if($planControl && count($planControl) > 0)
        <div class="section-card avoid-break">
            <h2 class="section-title">Plan de Control</h2>
            <table class="table">
                <thead class="thead-primary">
                    <tr>
                        <th>Actividad</th>
                        <th>Procedimiento</th>
                        <th>Características a verificar</th>
                        <th>Criterio de aceptación</th>
                        <th>Frecuencia</th>
                        <th>Identificación de la salida</th>
                        <th>Registro de la salida</th>
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

    @if(!empty($graficaPlanControl))
        <div class="section-card text-center">
            <h3 class="section-subtitle">Gráfica de Plan de Control</h3>
            <img src="{{ $graficaPlanControl }}" class="img-contained" alt="Gráfica Plan de Control">
        </div>
    @endif


    {{-- Gestión de Riesgos (matrices) --}}
    @if(!empty($riesgos) && count($riesgos) > 0)
        <div class="section-card page-break">
            <h2 class="section-title">Gestión de Riesgos</h2>

            <div class="section-subtitle">1) Identificación</div>
            <table class="table">
                <thead class="thead-primary">
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
                            <td style="width:40px; text-align:center;">{{ $index + 1 }}</td>
                            <td>{{ $r->fuente }}</td>
                            <td>{{ $r->tipoRiesgo }}</td>
                            <td>{{ $r->descripcion }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="section-subtitle mt-16">2) Análisis</div>
            <table class="table">
                <thead class="thead-primary">
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

            <div class="section-subtitle mt-16">3) Tratamiento</div>
            <table class="table">
                <thead class="thead-primary">
                    <tr>
                        <th>Actividades</th>
                        <th>Acciones de mejora</th>
                        <th>Responsable</th>
                        <th>Fecha implementación</th>
                        <th>Fecha evaluación</th>
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

            <div class="section-subtitle mt-16">4) Evaluación de la Efectividad</div>
            <table class="table">
                <thead class="thead-primary">
                    <tr>
                        <th>Reeval. Severidad</th>
                        <th>Reeval. Ocurrencia</th>
                        <th>NRP</th>
                        <th>Efectividad</th>
                        <th>Análisis</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($riesgos as $r)
                        @php
                            $efectivo = $r->valorNRP >= $r->reevaluacionNRP;
                            $label = $efectivo ? 'Efectivo' : 'No efectivo';
                        @endphp
                        <tr>
                            <td>{{ $r->reevaluacionSeveridad }}</td>
                            <td>{{ $r->reevaluacionOcurrencia }}</td>
                            <td>{{ $r->reevaluacionNRP }}</td>
                            <td class="text-center">
                                <span class="badge {{ $efectivo ? 'badge-success' : 'badge-danger' }}">{{ $label }}</span>
                            </td>
                            <td>{{ $r->analisisEfectividad }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($graficaRiesgos))
        <div class="section-card text-center">
            <h3 class="section-subtitle">Gráfica de Riesgos</h3>
            <img src="{{ $graficaRiesgos }}" class="img-contained" alt="Gráfica de Riesgos">
        </div>
    @endif

    {{-- Análisis de Datos – Plan de Control (indicadores) --}}
    @if(!empty($planControlIndicadores) && count($planControlIndicadores) > 0)
        <div class="section-card avoid-break">
            <h2 class="section-title">Análisis de Datos – Conformidad del producto/servicio (9.1.3 a)</h2>
            <table class="table">
                <thead class="thead-primary">
                    <tr>
                        <th>No</th>
                        <th>Descripción de Indicador</th>
                        <th>Meta</th>
                        <th>Ene–Jun</th>
                        <th>Jul–Dic</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalMeta = 0;
                        $totalSem1 = 0;
                        $totalSem2 = 0;
                    $count = count($planControlIndicadores); @endphp
                    @foreach ($planControlIndicadores as $i => $indicador)
                        @php
                            $totalMeta += $indicador->meta ?? 0;
                            $totalSem1 += $indicador->resultadoSemestral1 ?? 0;
                            $totalSem2 += $indicador->resultadoSemestral2 ?? 0;
                        @endphp
                        <tr>
                            <td style="width:40px; text-align:center;">{{ $i + 1 }}</td>
                            <td>{{ $indicador->nombreIndicador }}</td>
                            <td class="text-center">{{ $indicador->meta }}</td>
                            <td class="text-center">{{ $indicador->resultadoSemestral1 }}</td>
                            <td class="text-center">{{ $indicador->resultadoSemestral2 }}</td>
                        </tr>
                    @endforeach
                    <tr style="background:#f1f5f9; font-weight:bold;">
                        <td colspan="2">Promedio</td>
                        <td class="text-center">{{ number_format($totalMeta / max($count, 1), 2) }}</td>
                        <td class="text-center">{{ number_format($totalSem1 / max($count, 1), 2) }}</td>
                        <td class="text-center">{{ number_format($totalSem2 / max($count, 1), 2) }}</td>
                    </tr>
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

    {{-- Satisfacción del Cliente (encuesta + retro) --}}
    @php
        $encuestas = collect($indicadoresSatisfaccion ?? [])->where('origen', 'Encuesta')->values();
        $retroalimentaciones = collect($indicadoresSatisfaccion ?? [])->where('origen', 'Retroalimentacion')->values();
        $firstEncuesta = $encuestas->first();
        $noEncuestas = data_get($firstEncuesta, 'noEncuestas', 0);
        $totalFelicitaciones = $retroalimentaciones->sum('felicitaciones') ?: 0;
        $totalSugerencias = $retroalimentaciones->sum('sugerencias') ?: 0;
        $totalQuejas = $retroalimentaciones->sum('quejas') ?: 0;
        $totalRetro = $totalFelicitaciones + $totalSugerencias + $totalQuejas;
        $sumRowTotals = $retroalimentaciones->sum('total') ?: 0;
        $interpretacionGeneral = data_get($firstEncuesta, 'interpretacion', 'No hay interpretación');
        $necesidadGeneral = data_get($firstEncuesta, 'necesidad', 'No hay necesidad');
      @endphp

    @if($encuestas->count() > 0 || $retroalimentaciones->count() > 0)
        <div class="section-card avoid-break">
            <h2 class="section-title">Satisfacción del Cliente (9.1.3 b)</h2>

            @if($encuestas->count() > 0)
                <div class="section-subtitle">Encuesta de Satisfacción</div>
                <table class="table">
                    <thead class="thead-primary">
                        <tr>
                            <th>No</th>
                            <th>Descripción del indicador</th>
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
                            @php
                                $noEnc = (int) data_get($item, 'noEncuestas', 0);
                                $reg = (float) data_get($item, 'regular', 0);
                                $malo = (float) data_get($item, 'malo', 0);
                                $eb = (float) data_get($item, 'porcentajeEB', 0);
                                $meta = data_get($item, 'meta', null);
                              @endphp
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>{{ $item['nombreIndicador'] ?? 'Encuesta' }}</td>
                                <td class="text-center">{{ $noEnc }}</td>
                                <td class="text-center">{{ $eb }}%</td>
                                <td class="text-center">{{ $noEnc > 0 ? round($reg * 100 / $noEnc, 2) : '-' }}%</td>
                                <td class="text-center">{{ $noEnc > 0 ? round($malo * 100 / $noEnc, 2) : '-' }}%</td>
                                <td class="text-center">{{ isset($meta) ? $meta : '-' }}%</td>
                                <td class="text-center">{{ $eb }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if($retroalimentaciones->count() > 0)
                <div class="section-subtitle mt-16">Retroalimentación</div>
                <table class="table">
                    <thead class="thead-primary">
                        <tr>
                            <th>No</th>
                            <th>Descripción del Indicador</th>
                            <th>F</th>
                            <th>S</th>
                            <th>Q</th>
                            <th>Total</th>
                            <th colspan="2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($retroalimentaciones as $idx => $item)
                            <tr>
                                <td class="text-center">{{ $idx + 1 }}</td>
                                <td>{{ $item['nombreIndicador'] }}</td>
                                <td class="text-center">{{ $item['felicitaciones'] }}</td>
                                <td class="text-center">{{ $item['sugerencias'] }}</td>
                                <td class="text-center">{{ $item['quejas'] }}</td>
                                <td class="text-center">{{ $item['total'] }}</td>
                                <td colspan="2"></td>
                            </tr>
                        @endforeach
                        <tr style="font-weight:bold; background:#f1f5f9;">
                            <td colspan="2">Total retroalimentación</td>
                            <td class="text-center">{{ $totalFelicitaciones }}</td>
                            <td class="text-center">{{ $totalSugerencias }}</td>
                            <td class="text-center">{{ $totalQuejas }}</td>
                            <td class="text-center">{{ $totalRetro }}</td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="2">Suma totales retroalimentación</td>
                            <td class="text-center">{{ $sumRowTotals }}</td>
                            <td colspan="5"></td>
                        </tr>
                        <tr>
                            <td colspan="4"><strong>Interpretación:</strong> {{ $interpretacionGeneral }}</td>
                            <td colspan="4"><strong>Necesidad:</strong> {{ $necesidadGeneral }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    @php $hayGrafEncuesta = !empty($graficaEncuesta); @endphp

    @if($hayGrafEncuesta)
        <div class="section-card text-center">
            <h3 class="section-subtitle">Gráfica de Encuesta</h3>
            <img src="{{ $graficaEncuesta }}" class="img-contained" alt="Gráfica Encuesta">
        </div>
    @elseif($mostrarVacias)
        <div class="section-empty avoid-break">
            <div>No hay gráfica de encuesta.</div>
        </div>
    @endif

    @if(!empty($graficaRetroalimentacion))
        <div class="section-card text-center">
            <h3 class="section-subtitle">Gráfica de Retroalimentación</h3>
            <img src="{{ $graficaRetroalimentacion }}" class="img-contained" alt="Gráfica Retroalimentación">
        </div>
    @endif

    {{-- Desempeño del Proceso (Mapa de Proceso) --}}
    @if(!empty($mapaProcesoIndicadores) && count($mapaProcesoIndicadores) > 0)
        <div class="section-card avoid-break">
            <h2 class="section-title">Análisis de Datos – Desempeño del Proceso</h2>
            <table class="table">
                <thead class="thead-primary">
                    <tr>
                        <th>No</th>
                        <th>Descripción de los Indicadores</th>
                        <th>Meta</th>
                        <th>Ene–Jun</th>
                        <th>Jul–Dic</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalMeta = 0;
                        $totalSem1 = 0;
                        $totalSem2 = 0;
                    $count = count($mapaProcesoIndicadores); @endphp
                    @foreach ($mapaProcesoIndicadores as $index => $item)
                        @php
                            $totalMeta += $item->meta ?? 0;
                            $totalSem1 += $item->resultadoSemestral1 ?? 0;
                            $totalSem2 += $item->resultadoSemestral2 ?? 0;
                        @endphp
                        <tr>
                            <td style="width:40px; text-align:center;">{{ $index + 1 }}</td>
                            <td>{{ $item->nombreIndicador }}</td>
                            <td class="text-center">{{ $item->meta }}</td>
                            <td class="text-center">{{ $item->resultadoSemestral1 }}</td>
                            <td class="text-center">{{ $item->resultadoSemestral2 }}</td>
                        </tr>
                    @endforeach
                    <tr style="background:#f1f5f9; font-weight:bold;">
                        <td colspan="2">Promedio</td>
                        <td class="text-center">{{ number_format($totalMeta / max($count, 1), 2) }}</td>
                        <td class="text-center">{{ number_format($totalSem1 / max($count, 1), 2) }}</td>
                        <td class="text-center">{{ number_format($totalSem2 / max($count, 1), 2) }}</td>
                    </tr>
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

    @if(!empty($graficaMP))
        <div class="section-card text-center">
            <h3 class="section-subtitle">Gráfica de Mapa de Proceso</h3>
            <img src="{{ $graficaMP }}" class="img-contained" alt="Gráfica Mapa de Proceso">
        </div>
    @endif

    {{-- Eficacia de Riesgos y Oportunidades (tabla + gráfica) --}}
    @if(isset($eficaciaRiesgos) && !empty($eficaciaRiesgos) && count($eficaciaRiesgos) > 0)
        <div class="section-card avoid-break">
            <h2 class="section-title">Análisis de Datos – Eficacia de los Riesgos y Oportunidades</h2>
            <table class="table">
                <thead class="thead-primary">
                    <tr>
                        <th>No</th>
                        <th>Nombre del Indicador</th>
                        <th>Meta</th>
                        <th>Resultado Anual</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($eficaciaRiesgos as $index => $indi)
                        <tr>
                            <td style="width:40px; text-align:center;">{{ $index + 1 }}</td>
                            <td>{{ $indi->nombreIndicador }}</td>
                            <td class="text-center">{{ $indi->meta ?? 'No asignada' }}</td>
                            <td class="text-center">{{ $indi->resultadoAnual ?? '-' }}</td>
                        </tr>
                    @endforeach
                    <tr style="background:#f1f5f9;">
                        <td colspan="2"><strong>Interpretación del comportamiento</strong></td>
                        <td colspan="2">{{ $eficaciaRiesgos[0]->interpretacion ?? 'No disponible' }}</td>
                    </tr>
                    <tr style="background:#f1f5f9;">
                        <td colspan="2"><strong>Necesidad de mejora en el SGC</strong></td>
                        <td colspan="2">{{ $eficaciaRiesgos[0]->necesidad ?? 'No disponible' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

   @if(!empty($graficaRiesgos))
        <div class="section-card text-center">
            <h3 class="section-subtitle">Gráfica de Eficacia</h3>
            <img src="{{ $graficaRiesgos }}" class="img-contained" alt="Gráfica de Eficacia">
        </div>
    @endif

    {{-- Evaluación de Proveedores --}}
    @if(isset($evaluacionProveedores) && !empty($evaluacionProveedores['indicadores']) && count($evaluacionProveedores['indicadores']) > 0)
        <div class="section-card page-break">
            <h2 class="section-title">Análisis de Datos – Desempeño de Proveedores Externos</h2>
            <table class="table">
                <thead class="thead-primary">
                    <tr>
                        <th>No</th>
                        <th>Nombre del Indicador</th>
                        <th>Meta</th>
                        <th>Ene–Jun</th>
                        <th>Jul–Dic</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($evaluacionProveedores['indicadores'] as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $item['categoria'] }}</td>
                            <td class="text-center">{{ $item['meta'] ?? 'No disponible' }}%</td>
                            <td class="text-center">{{ $item['resultado1'] ?? '-' }}%</td>
                            <td class="text-center">{{ $item['resultado2'] ?? '-' }}%</td>
                        </tr>
                    @endforeach
                    <tr style="background:#f1f5f9;">
                        <td colspan="2"><strong>Interpretación:</strong></td>
                        <td colspan="3">{{ $evaluacionProveedores['interpretacion'] ?? 'No disponible' }}</td>
                    </tr>
                    <tr style="background:#f1f5f9;">
                        <td colspan="2"><strong>Necesidad de mejora:</strong></td>
                        <td colspan="3">{{ $evaluacionProveedores['necesidad'] ?? 'No disponible' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if(!empty($graficaEvaluacion))
        <div class="section-card text-center">
            <h3 class="section-subtitle">Gráfica de Evaluación de Proveedores</h3>
            <img src="{{ $graficaEvaluacion }}" class="img-contained" alt="Gráfica de Evaluación de Proveedores">
        </div>
    @endif

    {{-- Plan Correctivo --}}
    @if($planCorrectivo)
        <div class="section-card">
            <h2 class="section-title">Plan Correctivo</h2>

            <table class="table">
                <tbody>
                    <tr>
                        <th style="width:20%;">Coordinador</th>
                        <td style="width:30%;">{{ $planCorrectivo->coordinadorPlan }}</td>
                        <th style="width:20%;">Código</th>
                        <td style="width:30%;">{{ $planCorrectivo->codigo }}</td>
                    </tr>
                    <tr>
                        <th>Fecha</th>
                        <td>{{ $planCorrectivo->fechaInicio }}</td>
                        <th></th>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-10"><strong>Origen de la no conformidad:</strong> {{ $planCorrectivo->origenConformidad }}</div>
            <div class="mt-6"><strong>Equipo de mejora:</strong> {{ $planCorrectivo->equipoMejora }}</div>

            @if(isset($actividadesPlan) && $actividadesPlan->where('idPlanCorrectivo', $planCorrectivo->idPlanCorrectivo)->count() > 0)
                <div class="section-subtitle mt-16">Actividades de reacción</div>
                <table class="table">
                    <thead class="thead-primary">
                        <tr>
                            <th>Actividad</th>
                            <th>Responsable</th>
                            <th>Fecha programada</th>
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
            @endif

            <div class="mt-10"><strong>Revisión y análisis:</strong> {{ $planCorrectivo->revisionAnalisis }}</div>
            <div class="mt-6"><strong>Determinación de causa raíz:</strong> {{ $planCorrectivo->causaRaiz }}</div>
        </div>
    @endif

    {{-- Plan de Trabajo --}}
    @if(isset($planTrabajoData) && $planTrabajoData['planTrabajo'])
        <div class="section-card page-break">
            <h2 class="section-title">Plan de Trabajo</h2>

            <table class="table">
                <tbody>
                    <tr>
                        <th style="width:22%;">Fecha de elaboración</th>
                        <td style="width:28%;">{{ $planTrabajoData['planTrabajo']->fechaElaboracion ?? 'No especificado' }}
                        </td>
                        <th style="width:22%;">Objetivo</th>
                        <td style="width:28%;">{{ $planTrabajoData['planTrabajo']->objetivo ?? 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <th>Fecha de revisión</th>
                        <td>{{ $planTrabajoData['planTrabajo']->fechaRevision ?? 'No especificado' }}</td>
                        <th>Revisado por</th>
                        <td>{{ $planTrabajoData['planTrabajo']->revisadoPor ?? 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <th>Responsable</th>
                        <td>{{ $planTrabajoData['planTrabajo']->responsable ?? 'No especificado' }}</td>
                        <th>Estado</th>
                        <td>{{ $planTrabajoData['planTrabajo']->estado ?? 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <th>Fuente</th>
                        <td>{{ $planTrabajoData['planTrabajo']->fuente ?? 'No especificado' }}</td>
                        <th>Entregable</th>
                        <td>{{ $planTrabajoData['planTrabajo']->entregable ?? 'No especificado' }}</td>
                    </tr>
                </tbody>
            </table>

            @if(isset($planTrabajoData['fuentes']) && count($planTrabajoData['fuentes']) > 0)
                <div class="section-subtitle mt-16">Fuentes documentales</div>
                <table class="table">
                    <thead class="thead-primary">
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
                                <td class="text-center">{{ $fuente->noActividad ?? '-' }}</td>
                                <td>{{ $fuente->responsable ?? '-' }}</td>
                                <td class="text-center">{{ $fuente->fechaInicio ?? '-' }}</td>
                                <td class="text-center">{{ $fuente->fechaTermino ?? '-' }}</td>
                                <td class="text-center">{{ $fuente->estado ?? '-' }}</td>
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

    {{-- Proyecto de Mejora --}}
    @if(isset($proyectoMejoraData) && $proyectoMejoraData['proyectoMejora'])
        <div class="section-card page-break">
            <h2 class="section-title">Proyecto de Mejora</h2>

            <table class="table">
                <tbody>
                    <tr>
                        <th style="width:20%;">Fecha</th>
                        <td style="width:30%;">{{ $proyectoMejoraData['proyectoMejora']->fecha }}</td>
                        <th style="width:20%;">No. Mejora</th>
                        <td style="width:30%;">{{ $proyectoMejoraData['proyectoMejora']->noMejora }}</td>
                    </tr>
                    <tr>
                        <th>Descripción de la mejora</th>
                        <td colspan="3">{{ $proyectoMejoraData['proyectoMejora']->descripcionMejora }}</td>
                    </tr>
                    <tr>
                        <th>Áreas de impacto / Personal beneficiado</th>
                        <td colspan="3">{{ $proyectoMejoraData['proyectoMejora']->areaImpacto }}</td>
                    </tr>
                    <tr>
                        <th>Situación actual</th>
                        <td colspan="3">{{ $proyectoMejoraData['proyectoMejora']->situacionActual }}</td>
                    </tr>
                    <tr>
                        <th>Aprobación (Nombre)</th>
                        <td>{{ $proyectoMejoraData['proyectoMejora']->aprobacionNombre }}</td>
                        <th>Aprobación (Puesto)</th>
                        <td>{{ $proyectoMejoraData['proyectoMejora']->aprobacionPuesto }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="section-subtitle mt-16">Objetivos / Beneficios</div>
            @if(count($proyectoMejoraData['objetivos']) > 0)
                <ul class="mt-6">
                    @foreach($proyectoMejoraData['objetivos'] as $objetivo)
                        <li>{{ $objetivo->descripcionObj }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">No se registraron objetivos.</p>
            @endif

            <div class="section-subtitle mt-16">Responsables involucrados</div>
            @if(count($proyectoMejoraData['responsables']) > 0)
                <ul class="mt-6">
                    @foreach($proyectoMejoraData['responsables'] as $responsable)
                        <li>{{ $responsable->nombre }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">No hay responsables registrados.</p>
            @endif

            <div class="section-subtitle mt-16">Indicadores de Éxito</div>
            @if(count($proyectoMejoraData['indicadoresExito']) > 0)
                <ul class="mt-6">
                    @foreach($proyectoMejoraData['indicadoresExito'] as $indicador)
                        <li>{{ $indicador->nombreInd }} — Meta: {{ $indicador->meta }}</li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">No se definieron indicadores.</p>
            @endif

            <div class="section-subtitle mt-16">Recursos</div>
            @if(count($proyectoMejoraData['recursos']) > 0)
                <table class="table">
                    <thead class="thead-primary">
                        <tr>
                            <th>Tiempo</th>
                            <th>Recursos materiales y humanos</th>
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
                <p class="text-muted">No se registraron recursos.</p>
            @endif

            <div class="section-subtitle mt-16">Actividades</div>
            @if(count($proyectoMejoraData['actividadesPM']) > 0)
                <table class="table">
                    <thead class="thead-primary">
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
                <p class="text-muted">No se registraron actividades.</p>
            @endif
        </div>
    @endif


    {{-- Auditorías --}}
    @if($auditorias && count($auditorias) > 0)
        <div class="section-card avoid-break">
            <h2 class="section-title">Auditorías del Proceso</h2>
            <table class="table">
                <thead class="thead-primary">
                    <tr>
                        <th>Fecha programada</th>
                        <th>Hora programada</th>
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

    {{-- Seguimiento --}}
    @if($seguimientos && count($seguimientos) > 0)
        <div class="section-card page-break">
            <h2 class="section-title">Seguimiento</h2>
            @foreach ($seguimientos as $seguimiento)
                <div class="mt-10">
                    <div class="section-subtitle">Minuta</div>

                    @if($asistentes->where('idSeguimiento', $seguimiento->idSeguimiento)->count() > 0)
                        <div class="mb-6"><strong>Asistentes</strong></div>
                        <table class="table">
                            <thead class="thead-primary">
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

                    <div class="grid-3 mt-10">
                        <div><span class="chip">Lugar</span> &nbsp; {{ $seguimiento->lugar }}</div>
                        <div><span class="chip">Fecha</span> &nbsp; {{ $seguimiento->fecha }}</div>
                        <div><span class="chip">Duración</span> &nbsp; {{ $seguimiento->duracion }}</div>
                    </div>

                    @if($actividadesSeg->where('idSeguimiento', $seguimiento->idSeguimiento)->count() > 0)
                        <div class="section-subtitle mt-16">Actividades</div>
                        <table class="table">
                            <thead class="thead-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Actividad realizada</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($actividadesSeg->where('idSeguimiento', $seguimiento->idSeguimiento) as $actividad)
                                    <tr>
                                        <td style="width:40px; text-align:center;">{{ $loop->iteration }}</td>
                                        <td>{{ $actividad->descripcion }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if($compromisosSeg->where('idSeguimiento', $seguimiento->idSeguimiento)->count() > 0)
                        <div class="section-subtitle mt-16">Compromisos</div>
                        <table class="table">
                            <thead class="thead-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Compromiso</th>
                                    <th>Responsable</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($compromisosSeg->where('idSeguimiento', $seguimiento->idSeguimiento) as $compromiso)
                                    <tr>
                                        <td style="width:40px; text-align:center;">{{ $loop->iteration }}</td>
                                        <td>{{ $compromiso->descripcion }}</td>
                                        <td>{{ $compromiso->responsables }}</td>
                                        <td>{{ $compromiso->fecha }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <div style="height:8px;"></div>
                </div>
            @endforeach
        </div>
    @endif

</body>

</html>