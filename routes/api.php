<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\MacroProcesoController;
use App\Http\Controllers\Api\EntidadDependenciaController;
use App\Http\Controllers\Api\ProcessController;
use App\Http\Controllers\Api\LiderController;
use App\Http\Controllers\Api\RegistrosController;
use App\Http\Controllers\Api\MinutaController;

use App\Http\Controllers\Api\IndicadorConsolidadoController;
use App\Http\Controllers\Api\IndicadorResultadoController;
use App\Http\Controllers\Api\RetroalimentacionController;
use App\Http\Controllers\Api\EncuestaController;
use App\Http\Controllers\Api\EvaluaProveedoresController;
use App\Http\Controllers\Api\NoticiasController;
use App\Http\Controllers\Api\EventosAvisosController;
use App\Http\Controllers\Api\CronogramaController;


use App\Http\Controllers\Api\ControlCambioController;
use App\Http\Controllers\Api\MapaProcesoController;
use App\Http\Controllers\Api\IndMapaProcesoController;
use App\Http\Controllers\Api\ActividadControlController;
use App\Http\Controllers\Api\GestionRiesgoController;
use App\Http\Controllers\Api\RiesgoController;
use App\Http\Controllers\Api\FormAnalisisDatosController;


use App\Http\Controllers\Api\ActividadMejoraController;
// Controlador de Plan Correctivo
use App\Http\Controllers\Api\PlanCorrectivoController;
// Controlador de Plan Trabajo
use App\Http\Controllers\Api\PlanTrabajoController;
use App\Http\Controllers\Api\FuentePtController;
use App\Http\Controllers\Api\ProyectoMejoraController;
use App\Http\Controllers\Api\ReporteSemestralController;
use Barryvdh\DomPDF\Facade\Pdf;


Route::get('macroprocesos', [MacroProcesoController::class, 'index']);
Route::get('entidades', [EntidadDependenciaController::class, 'index']);
Route::get('/entidades/{id}', [EntidadDependenciaController::class, 'show']);
Route::get('lideres', [LiderController::class, 'index']); 
#Route::post('procesos', [ProcessController::class, 'store']);
#Route::get('/procesos', [ProcesoController::class, 'obtenerProcesosPorEntidad']);
Route::post('/procesos', [ProcessController::class, 'store']);

Route::get('/procesos/entidad/{idEntidad}', [ProcessController::class, 'obtenerProcesosPorEntidad']);
/*Route::post('/registros', [RegistrosController::class, 'store']); // Ruta para crear un nuevo registro
Route::get('/registros/{idProceso}', [RegistrosController::class, 'index']); // Ruta para obtener registros por idProceso*/

Route::post('minutasAdd', [MinutaController::class, 'store']); // crear minuta
Route::get('/minutas/registro/{idRegistro}', [MinutaController::class, 'getMinutasByRegistro']); //obtener todad las minutas de un proceso en un año 
Route::put('/minutas/{id}', [MinutaController::class, 'update']); //actualizar una minuta
Route::delete('/minutasDelete/{id}', [MinutaController::class, 'destroy']);

Route::post('/registros', [RegistrosController::class, 'store']); //crear carpeta
Route::put('/registros{id}', [RegistrosController::class, 'update']);//actualizar update
Route::post('/registros/filtrar', [RegistrosController::class, 'obtenerRegistrosPorProcesoYApartado']); // obtener carpetas por proceso de un apartado


// Route::get('procesos', action: [ProcessController::class, 'index']); 
// Route::post('procesos', [ProcessController::class, 'store']);
Route::apiResource('procesos', controller: ProcessController::class);
Route::apiResource('indicadoresconsolidados', IndicadorConsolidadoController::class);
Route::post('evalua-proveedores/{idIndicador}/resultados', [EvaluaProveedoresController::class, 'store']);
Route::get('evalua-proveedores/{idIndicador}/resultados', [EvaluaProveedoresController::class, 'show']);
// Para registrar resultados:
Route::post('indicadoresconsolidados/{idIndicadorConsolidado}/resultados', [IndicadorResultadoController::class, 'store']);
Route::get('indicadoresconsolidados/{idIndicadorConsolidado}/resultados', [IndicadorResultadoController::class, 'show']);


// Retroalimentación

Route::get('/indicadores/retroalimentacion', [IndicadorConsolidadoController::class, 'indexRetroalimentacion']);
Route::prefix('retroalimentacion')->group(function () {
    Route::post('{idIndicadorConsolidado}/resultados', [RetroalimentacionController::class, 'store']);
    Route::get('{idIndicadorConsolidado}/resultados', [RetroalimentacionController::class, 'show']);

    //Encuestas
});Route::prefix('encuesta')->group(function () {
    // Guardar (POST) resultados de la encuesta con idIndicadorConsolidado
    Route::post('{idIndicadorConsolidado}/resultados', [EncuestaController::class, 'store']);
    // Obtener (GET) resultados de la encuesta con idIndicadorConsolidado
    Route::get('{idIndicadorConsolidado}/resultados', [EncuestaController::class, 'show']);
});


// Evaluacion de Proeveedores
Route::prefix('evalua-proveedores')->group(function () {
    Route::post('{idIndicadorConsolidado}/resultados', [EvaluaProveedoresController::class, 'store']);
    Route::get('{idIndicadorConsolidado}/resultados', [EvaluaProveedoresController::class, 'show']);
});

Route::apiResource('noticias', NoticiasController::class);
Route::apiResource('eventos-avisos', EventosAvisosController::class);

//Route::apiResource('cronogramas', CronogramaController::class);
Route::get('entidad-nombres', [EntidadDependenciaController::class, 'getNombres']);
Route::get('procesos-nombres', [ProcessController::class, 'getNombres']);
Route::get('cronograma', [CronogramaController::class, 'index']);
Route::post('cronograma', [CronogramaController::class, 'store']);
Route::put('cronograma/{id}', [CronogramaController::class, 'update']);










// Para Manual Operativo
Route::apiResource('controlcambios', ControlCambioController::class);
Route::apiResource('mapaproceso', MapaProcesoController::class);
Route::apiResource('indmapaproceso', IndMapaProcesoController::class);
Route::apiResource('actividadcontrol', ActividadControlController::class);
Route::get('gestionriesgos/{idGesRies}/riesgos', [GestionRiesgoController::class, 'getRiesgosByGesRies']);

Route::post('gestionriesgos/{idGesRies}/riesgos', [GestionRiesgoController::class, 'store']);

Route::put('gestionriesgos/{idGesRies}/riesgos/{idRiesgo}', [GestionRiesgoController::class, 'update']);

Route::delete('gestionriesgos/{idGesRies}/riesgos/{idRiesgo}', [GestionRiesgoController::class, 'delete']);

Route::get('analisisDatos/{idformAnalisisDatos}/analisis', [FormAnalisisDatosController::class, 'show']);
Route::put('analisisDatos/{idformAnalisisDatos}/necesidad-interpretacion', [FormAnalisisDatosController::class, 'updateNecesidadInterpretacion']);

//Ruta para obtener resultados de los resultados de plan de control
Route::get('/plan-control', [IndicadorResultadoController::class, 'getResultadosPlanControl']);
Route::get('/mapa-proceso', [IndicadorResultadoController::class, 'getResultadosIndMapaProceso']);
Route::get('/gestion-riesgos', [IndicadorResultadoController::class, 'getResutadosRiesgos']);

//Ruta para los planes correctivos
Route::get('/plan-correctivos', [PlanCorrectivoController::class, 'index']);
//Ruta para obtener la informacion de un plan
Route::get('/plan-correctivo/{id}', [PlanCorrectivoController::class,'show']);
//Ruta para crear un nuevo plan
Route::post('/plan-correctivos', [PlanCorrectivoController::class,'store']);
//Ruta para actualizar un plan
Route::put('/plan-correctivo/{id}', [PlanCorrectivoController::class,'update']);
//Ruta para eliminar un plan
Route::delete('/plan-correctivo/{id}', [PlanCorrectivoController::class,'destroy']);

//Rutas para el manejo de las actividades
Route::post('/actividades', [PlanCorrectivoController::class,'createActividad']);
Route::put('/actividades/{idActividadPlan}', [PlanCorrectivoController::class,'updateActividad']);
Route::delete('/actividades/{idActividadPlan}', [PlanCorrectivoController::class,'deleteActividad']);

Route::get('/plan-correctivos/registro/{idRegistro}', [PlanCorrectivoController::class, 'getByRegistro']);

Route::apiResource('plantrabajo', PlanTrabajoController::class);
Route::apiResource('actividadmejora', ActividadMejoraController::class);
Route::apiResource('fuentept', FuentePtController::class);
Route::get('/plantrabajo/registro/{idRegistro}', [PlanTrabajoController::class, 'getByRegistro']);
Route::post('/proyecto-mejora', [ProyectoMejoraController::class, 'store']);



/*Route::post('/generar-pdf', function (Request $request) {
    $data = $request->all(); // Ahora sí obtiene los datos correctamente

    $pdf = Pdf::loadView('pdf.reporte', compact('data'));
// Genera el PDF con la vista
    return $pdf->download('reporte-semestral.pdf'); // Descarga el PDF
});*/

/*Route::post('/generar-pdf', function (Request $request) {
    $conclusion = $request->input('conclusion');
    $imageBase64 = $request->input('image'); // Recibe la imagen como base64

    return Pdf::loadView('pdf.reporte', compact('conclusion', 'imageBase64'))
        ->download('reporte-semestral.pdf');
});*/

/*Route::post('/generar-pdf', function (Request $request) {
    dd($request->all()); // Muestra lo que recibe el backend y detiene la ejecución
});*/
/*Route::post('/generar-pdf', function (Request $request) {
    try {
        dd($request->all()); // Verifica qué está llegando desde el front
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});*/
Route::post('/generar-pdf', function (Request $request) {
    try {
        Log::info('Solicitud recibida:', $request->all());

        $data = $request->all();
        Log::info('Datos procesados:', $data);

        $pdf = Pdf::loadView('pdf.reporte', compact('data'));
        Log::info('PDF generado correctamente');

        return $pdf->download('reporte-semestral.pdf');
    } catch (\Exception $e) {
        Log::error('Error al generar el PDF: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

