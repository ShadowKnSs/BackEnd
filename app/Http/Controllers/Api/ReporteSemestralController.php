<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\ReporteSemestral;
use Illuminate\Support\Str;


class ReporteSemestralController extends Controller
{
    /*public function generarYRegistrar(Request $request)
    {
        Log::info('Se recibió la petición para generar PDF', $request->all());
        // 1. Validar los datos obligatorios
        $request->validate([
            'anio' => 'required|integer',
            'periodo' => 'required|string|max:50',
            'fortalezas' => 'nullable|string',
            'debilidades' => 'nullable|string',
            'conclusion' => 'required|string',
            'listas' => 'nullable|array',   // por si mandas listas
            'imagenes' => 'nullable|array',
        ]);

        // 2. Verificar que no exista reporte con mismo año y periodo
        $existeReporte = ReporteSemestral::where('anio', $request->anio)
            ->where('periodo', $request->periodo)
            ->exists();

        if ($existeReporte) {
            return response()->json(['error' => 'Ya existe un reporte para este año y periodo'], 409);
        }
        Log::info('Validación pasada, preparando datos...');
        // 3. Extraer datos
        $imagenes = $request->input('imagenes', []);
        $datosRiesgos = $request->input('listas.datosRiesgos', []);
        $datosIndicadores = $request->input('listas.datosIndicadores', []);
        $datosAccionesMejora = $request->input('listas.datosAccionesMejora', []);
        $datosAuditorias = $request->input('listas.datosAuditorias', []);
        $datosSeguimiento = $request->input('listas.datosSeguimiento', []);
        $fortalezas = $request->input('fortalezas');
        $debilidades = $request->input('debilidades');
        $conclusion = $request->input('conclusion');
        $anio = $request->input('anio');
        $periodo = $request->input('periodo');

        // 4. Generar el PDF
        $pdf = PDF::loadView('pdf.reporte', compact(
            'imagenes',
            'datosRiesgos',
            'datosIndicadores',
            'datosAccionesMejora',
            'datosAuditorias',
            'datosSeguimiento',
            'fortalezas',
            'debilidades',
            'conclusion',
            'anio',
            'periodo'
        ));

        // 5. Guardar PDF en storage/app/public/reportes/
        $fileName = 'reporte_' . $anio . '_' . $periodo . '_' . Str::random(8) . '.pdf';
        Storage::disk('public')->makeDirectory('reportes');
        $pdf->save(storage_path('app/public/reportes/' . $fileName));

        $ruta = 'reportes/' . $fileName;

        // 6. Registrar en BD
        $reporte = ReporteSemestral::create([
            'anio' => $anio,
            'periodo' => $periodo,
            'fortalezas' => $fortalezas,
            'debilidades' => $debilidades,
            'conclusion' => $conclusion,
            'fechaGeneracion' => now()->format('Y-m-d H:i:s'),
            'ubicacion' => $ruta
        ]);
        Log::info('Guardando PDF en storage...');

        // 7. Responder con el registro creado y la ruta del archivo
        return response()->json([
            'message' => 'Reporte generado y registrado con éxito',
            'reporte' => $reporte,
            'url' => asset('storage/' . $ruta) // para poder descargar directo desde el front
        ], 201);
    }*/
        public function generarYRegistrar(Request $request)
{
    try {
        // 1️⃣ Validar datos obligatorios
        $request->validate([
            'anio' => 'required|integer',
            'periodo' => 'required|string|max:50',
            'fortalezas' => 'nullable|string',
            'debilidades' => 'nullable|string',
            'conclusion' => 'required|string',
            'listas' => 'nullable|array',
            'imagenes' => 'nullable|array',
        ]);

        Log::info('Validación pasada', $request->all());

        // 2️⃣ Verificar reporte duplicado
        $existeReporte = ReporteSemestral::where('anio', $request->anio)
            ->where('periodo', $request->periodo)
            ->exists();

        if ($existeReporte) {
            Log::warning("Ya existe un reporte para año {$request->anio} y periodo {$request->periodo}");
            return response()->json(['error' => 'Ya existe un reporte para este año y periodo'], 409);
        }

        Log::info('No existe reporte duplicado, preparando datos...');

        // 3️⃣ Preparar datos
        $imagenes = $request->input('imagenes', []);
        $listas = $request->input('listas', []);

        $datosRiesgos = $listas['datosRiesgos'] ?? [];
        $datosIndicadores = $listas['datosIndicadores'] ?? [];
        $datosAccionesMejora = $listas['datosAccionesMejora'] ?? [];
        $datosAuditorias = $listas['datosAuditorias'] ?? [];
        $datosSeguimiento = $listas['datosSeguimiento'] ?? [];

        $fortalezas = $request->input('fortalezas');
        $debilidades = $request->input('debilidades');
        $conclusion = $request->input('conclusion');
        $anio = $request->input('anio');
        $periodo = $request->input('periodo');

        // 4️⃣ Generar PDF
        try {
            $pdf = PDF::loadView('pdf.reporte', compact(
                'imagenes',
                'datosRiesgos',
                'datosIndicadores',
                'datosAccionesMejora',
                'datosAuditorias',
                'datosSeguimiento',
                'fortalezas',
                'debilidades',
                'conclusion',
                'anio',
                'periodo'
            ));
            Log::info('PDF generado correctamente');
        } catch (\Exception $e) {
            Log::error('Error generando PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Error al generar el PDF', 'detalle' => $e->getMessage()], 500);
        }

        // 5️⃣ Guardar PDF en storage
        $fileName = 'reporte_' . $anio . '_' . $periodo . '_' . Str::random(8) . '.pdf';
        Storage::disk('public')->makeDirectory('reportes');

        try {
            $pdf->save(storage_path('app/public/reportes/' . $fileName));
            Log::info('PDF guardado en storage: reportes/' . $fileName);
        } catch (\Exception $e) {
            Log::error('Error guardando PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Error al guardar el PDF', 'detalle' => $e->getMessage()], 500);
        }

        $ruta = 'reportes/' . $fileName;

        // 6️⃣ Registrar en BD
        try {
            $reporte = ReporteSemestral::create([
                'anio' => $anio,
                'periodo' => $periodo,
                'fortalezas' => $fortalezas,
                'debilidades' => $debilidades,
                'conclusion' => $conclusion,
                'fechaGeneracion' => now()->format('Y-m-d H:i:s'),
                'ubicacion' => $ruta
            ]);

            Log::info('Reporte registrado en BD con IDReporteSemestral: ' . $reporte->idReporteSemestral);
            Log::info('Datos del registro:', (array) $reporte->toArray());
        } catch (\Exception $e) {
            Log::error('Error registrando en BD: ' . $e->getMessage());
            return response()->json(['error' => 'Error al registrar en BD', 'detalle' => $e->getMessage()], 500);
        }

        // 7️⃣ Responder con URL del PDF
        return response()->json([
    'message' => 'Reporte generado y registrado con éxito',
    'reporte' => $reporte,
    'url' => url('/api/descargar-reporte/' . $reporte->idReporteSemestral)
], 201);



    } catch (\Exception $e) {
        Log::error('Error inesperado: ' . $e->getMessage());
        return response()->json(['error' => 'Error inesperado en el servidor', 'detalle' => $e->getMessage()], 500);
    }
}

}
