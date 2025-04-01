<?php
use App\Http\Controllers\Api\ActMejoraSemController;
use App\Http\Controllers\Api\AuditoriaSemController;
use App\Http\Controllers\Api\dataSemController;
use App\Http\Controllers\Api\IndicadorSemController;
use App\Http\Controllers\Api\SaveReportSemController;
use App\Http\Controllers\Api\SeguimientoSemController;
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
<<<<<<<<< Temporary merge branch 1

// Controlador de Plan Correctivo
use App\Http\Controllers\Api\PlanCorrectivoController;


=========
use App\Http\Controllers\Api\ControlCambioController;
use App\Http\Controllers\Api\MapaProcesoController;
use App\Http\Controllers\Api\IndMapaProcesoController;
use App\Http\Controllers\Api\ActividadControlController;
use App\Http\Controllers\Api\AuditoriaInternaController;
use App\Http\Controllers\Api\ReporteAuditoriaController;
>>>>>>>>> Temporary merge branch 2

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

// ✅ Rutas con nombre explícito primero
Route::get('/registros/idRegistro', [RegistrosController::class, 'obtenerIdRegistro']);
Route::get('/registros/years/{idProceso}', [RegistrosController::class, 'obtenerAnios']);
Route::post('/registros/filtrar', [RegistrosController::class, 'obtenerRegistrosPorProcesoYApartado']);

// ✅ CRUD estándar después
Route::post('/registros', [RegistrosController::class, 'store']);
Route::put('/registros{id}', [RegistrosController::class, 'update']);
Route::get('/registros/{idRegistro}', [RegistrosController::class, 'show']);


// Route::get('procesos', action: [ProcessController::class, 'index']); 
// Route::post('procesos', [ProcessController::class, 'store']);
Route::apiResource('procesos', controller: ProcessController::class);
// Rutas principales de Indicadores Consolidados
Route::get('indicadoresconsolidados/{idProceso}', [IndicadorConsolidadoController::class, 'obtenerIndicadoresConsolidados']);

Route::apiResource('indicadoresconsolidados', IndicadorConsolidadoController::class);
Route::get('indicadoresconsolidados', [IndicadorConsolidadoController::class, 'index']);


// Registrar y obtener resultados por tipo de indicador
Route::prefix('indicadoresconsolidados')->group(function () {
    Route::post('{idIndicador}/resultados', [IndicadorResultadoController::class, 'store']);
    Route::get('{idIndicador}/resultados', [IndicadorResultadoController::class, 'show']);
});

// Rutas específicas para Retroalimentación
Route::prefix('retroalimentacion')->group(function () {
    Route::post('{idIndicador}/resultados', [RetroalimentacionController::class, 'store']);
    Route::get('{idIndicador}/resultados', [RetroalimentacionController::class, 'show']);
});

// Rutas específicas para Encuestas
Route::prefix('encuesta')->group(function () {
    Route::post('{idIndicador}/resultados', [EncuestaController::class, 'store']);
    Route::get('{idIndicador}/resultados', [EncuestaController::class, 'show']);
});

// Rutas específicas para Evaluación de Proveedores
Route::prefix('evalua-proveedores')->group(function () {
    Route::post('{idIndicador}/resultados', [EvaluaProveedoresController::class, 'store']);
    Route::get('{idIndicador}/resultados', [EvaluaProveedoresController::class, 'show']);
});

// Ruta para obtener solo indicadores de tipo retroalimentación
Route::get('/indicadores/retroalimentacion', [IndicadorConsolidadoController::class, 'indexRetroalimentacion']);


Route::apiResource('noticias', NoticiasController::class);
Route::apiResource('eventos-avisos', EventosAvisosController::class);

<<<<<<<<< Temporary merge branch 1
//Ruta para obtener resultados de los resultados de plan de control
Route::get('/plan-control', [IndicadorResultadoController::class, 'getResultadosPlanControl']);
Route::get('/mapa-proceso', [IndicadorResultadoController::class, 'getResultadosIndMapaProceso']);
Route::get('/gestion-riesgos', [IndicadorResultadoController::class, 'getResutadosRiesgos']);

//Ruta para los planes correctivos
Route::get('/plan-correctivos', [PlanCorrectivoController::class, 'index']);
//Ruta para obtener la informacion de un plan
Route::get('/plan-correctivo/{id}', [PlanCorrectivoController::class,'show']);
//Ruta para crear un nuevo plan
Route::post('/plan-correctivo', [PlanCorrectivoController::class,'store']);
//Ruta para actualizar un plan
Route::put('/plan-correctivo/{id}', [PlanCorrectivoController::class,'update']);
//Ruta para eliminar un plan
Route::delete('/plan-correctivo/{id}', [PlanCorrectivoController::class,'destroy']);

//Rutas para el manejo de las actividades
Route::post('/actividades', [PlanCorrectivoController::class,'createActividad']);
Route::put('/actividades/{idActividadPlan}', [PlanCorrectivoController::class,'updateActividad']);
Route::delete('/actividades/{idActividadPlan}', [PlanCorrectivoController::class,'deleteActividad']);

=========
// Para Manual Operativo
Route::apiResource('controlcambios', ControlCambioController::class);
Route::apiResource('mapaproceso', MapaProcesoController::class);
Route::apiResource('indmapaproceso', IndMapaProcesoController::class);
Route::apiResource('actividadcontrol', ActividadControlController::class);

//Para Auditoria Interna
Route::apiResource('auditorias', AuditoriaInternaController::class);
Route::apiResource('reportesauditoria', ReporteAuditoriaController::class)->only([ 'index', 'store', 'destroy' ]);
Route::get('/reporte-pdf/{id}', [ReporteAuditoriaController::class, 'descargarPDF']);
>>>>>>>>> Temporary merge branch 2
