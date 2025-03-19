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
// Rutas principales de Indicadores Consolidados
Route::apiResource('indicadoresconsolidados', IndicadorConsolidadoController::class);

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

//Route::apiResource('cronogramas', CronogramaController::class);
Route::get('entidad-nombres', [EntidadDependenciaController::class, 'getNombres']);
Route::get('procesos-nombres', [ProcessController::class, 'getNombres']);
Route::get('cronograma', [CronogramaController::class, 'index']);
Route::post('cronograma', [CronogramaController::class, 'store']);
Route::put('cronograma/{id}', [CronogramaController::class, 'update']);


//*********************************************************/
//                  Para Manual Operativo
//*********************************************************/
Route::apiResource('controlcambios', ControlCambioController::class);
Route::apiResource('mapaproceso', MapaProcesoController::class);
Route::apiResource('indmapaproceso', IndMapaProcesoController::class);
Route::apiResource('actividadcontrol', ActividadControlController::class);



// 1) GET datos-generales
Route::get('gestionriesgos/{idRegistro}/datos-generales', [GestionRiesgoController::class, 'getDatosGenerales']);

// 2) GET /api/gestionriesgos/{idRegistro} => showByRegistro
Route::get('gestionriesgos/{idRegistro}', [GestionRiesgoController::class, 'showByRegistro']);

// 3) POST /api/gestionriesgos => store
Route::post('gestionriesgos', [GestionRiesgoController::class, 'store']);

// 4) PUT /api/gestionriesgos/{idGesRies} => update
Route::put('gestionriesgos/{idGesRies}', [GestionRiesgoController::class, 'update']);
Route::post('gestionriesgos/{idGesRies}/riesgos', [RiesgoController::class, 'store']);


// Listar riesgos de una gestión
Route::get('gestionriesgos/{idGesRies}/riesgos', [RiesgoController::class, 'index']);

// Crear un nuevo riesgo
Route::post('gestionriesgos/{idGesRies}/riesgos', [RiesgoController::class, 'store']);

// Mostrar un riesgo específico
Route::get('gestionriesgos/{idGesRies}/riesgos/{idRiesgo}', [RiesgoController::class, 'show']);

// Actualizar un riesgo
Route::put('gestionriesgos/{idGesRies}/riesgos/{idRiesgo}', [RiesgoController::class, 'update']);

// Eliminar un riesgo
Route::delete('gestionriesgos/{idGesRies}/riesgos/{idRiesgo}', [RiesgoController::class, 'destroy']);


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




