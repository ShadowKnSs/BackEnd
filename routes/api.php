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
use App\Http\Controllers\Api\ControlCambioController;
use App\Http\Controllers\Api\MapaProcesoController;
use App\Http\Controllers\Api\IndMapaProcesoController;
use App\Http\Controllers\Api\ActividadControlController;
use App\Http\Controllers\Api\AuditoriaInternaController;

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

// Para retroalimentación:
Route::post('retroalimentacion/{idIndicador}/resultados', [RetroalimentacionController::class, 'store']);
Route::post('encuesta/{idIndicador}/resultados', [EncuestaController::class, 'store']);
Route::get('encuesta/{idIndicador}/resultados', [EncuestaController::class, 'show']);

Route::apiResource('noticias', NoticiasController::class);
Route::apiResource('eventos-avisos', EventosAvisosController::class);

// Para Manual Operativo
Route::apiResource('controlcambios', ControlCambioController::class);
Route::apiResource('mapaproceso', MapaProcesoController::class);
Route::apiResource('indmapaproceso', IndMapaProcesoController::class);
Route::apiResource('actividadcontrol', ActividadControlController::class);

//Para Auditoria Interna
Route::apiResource('auditorias', AuditoriaInternaController::class);
