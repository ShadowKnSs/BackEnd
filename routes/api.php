<?php
use Illuminate\Support\Facades\Route;
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

// Controlador de Plan Correctivo
use App\Http\Controllers\Api\PlanCorrectivoController;



Route::get('macroprocesos', [MacroProcesoController::class, 'index']);
Route::get('entidades', [EntidadDependenciaController::class, 'index']);
Route::get('/entidades/{id}', [EntidadDependenciaController::class, 'show']);
Route::get('lideres', [LiderController::class, 'index']); 
#Route::post('procesos', [ProcessController::class, 'store']);
#Route::get('/procesos', [ProcesoController::class, 'obtenerProcesosPorEntidad']);
Route::post('/procesos', [ProcessController::class, 'store']);

Route::get('/procesos/entidad/{idEntidad}', [ProcessController::class, 'obtenerProcesosPorEntidad']);
Route::post('/registros', [RegistrosController::class, 'store']); // Ruta para crear un nuevo registro
Route::get('/registros/{idProceso}', [RegistrosController::class, 'index']); // Ruta para obtener registros por idProceso
Route::post('minutas', [MinutaController::class, 'store']); // crear minuta


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

