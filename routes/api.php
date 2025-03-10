<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MacroProcesoController;
use App\Http\Controllers\Api\EntidadDependenciaController;
use App\Http\Controllers\Api\ProcessController;
use App\Http\Controllers\Api\LiderController;
use App\Http\Controllers\Api\IndicadorConsolidadoController;
use App\Http\Controllers\Api\IndicadorResultadoController;
use App\Http\Controllers\Api\RetroalimentacionController;
use App\Http\Controllers\Api\EncuestaController;
use App\Http\Controllers\Api\EvaluaProveedoresController;
use App\Http\Controllers\Api\NoticiasController;
use App\Http\Controllers\Api\EventosAvisosController;



Route::get('macroprocesos', [MacroProcesoController::class, 'index']);
Route::get('entidades', [EntidadDependenciaController::class, 'index']);
Route::get('lideres', [LiderController::class, 'index']); 
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


// Retroalimentación
Route::prefix('evalua-proveedores')->group(function () {
    Route::post('{idIndicadorConsolidado}/resultados', [RetroalimentacionController::class, 'store']);
    Route::get('{idIndicadorConsolidado}/resultados', [RetroalimentacionController::class, 'show']);
});

Route::apiResource('noticias', NoticiasController::class);
Route::apiResource('eventos-avisos', EventosAvisosController::class);

//Ruta para obtener resultados de los resultados de plan de control
Route::get('/plan-control', [IndicadorResultadoController::class, 'getResultadosPlanControl']);

