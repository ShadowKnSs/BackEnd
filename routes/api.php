<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MacroProcesoController;
use App\Http\Controllers\Api\EntidadDependenciaController;
use App\Http\Controllers\Api\ProcessController;
use App\Http\Controllers\Api\LiderController;
use App\Http\Controllers\Api\RegistrosController;
use App\Http\Controllers\Api\MinutaController;


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






