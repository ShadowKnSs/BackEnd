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
use App\Http\Controllers\Api\CronogramaController;


use App\Http\Controllers\Api\ControlCambioController;
use App\Http\Controllers\Api\MapaProcesoController;
use App\Http\Controllers\Api\IndMapaProcesoController;
use App\Http\Controllers\Api\ActividadControlController;
use App\Http\Controllers\Api\AuditoriaInternaController;
use App\Http\Controllers\Api\ReporteAuditoriaController;
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


//Reporte
use App\Http\Controllers\Api\ReporteProcesoController;

use App\Http\Controllers\Api\BuscadorSemController;
use App\Http\Controllers\Api\BuscadorAudiController;
use App\Http\Controllers\Api\BuscadorProcController;

use App\Http\Controllers\Api\FormatosController;
use App\Http\Controllers\Api\GraficaController;



use App\Http\Controllers\Api\TokenTemporalController;

use App\Http\Controllers\Api\ReporteSemestralController;
use App\Http\Controllers\Api\NotificacionController;
use App\Http\Controllers\NotificacionTestController;

use Barryvdh\DomPDF\Facade\Pdf;


//Login
use App\Http\Controllers\Api\AuthController;

//*********************************************************/
//                  Login
//*********************************************************/
Route::post('/login', [AuthController::class, 'login']);


Route::get('macroprocesos', [MacroProcesoController::class, 'index']);
//*********************************************************/
//                  Entidades/Dependencias
//*********************************************************/
Route::post('/entidades-por-usuario', [EntidadDependenciaController::class, 'entidadesPorUsuario']); //Agregada por JRH 05/04/2025
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

//Route::apiResource('cronogramas', CronogramaController::class);
Route::get('entidad-nombres', [EntidadDependenciaController::class, 'getNombres']);
Route::get('procesos-nombres', [ProcessController::class, 'getNombres']);

Route::post('cronograma/filtrar', [CronogramaController::class, 'index']);
Route::post('cronograma', [CronogramaController::class, 'store']);
Route::put('cronograma/{id}', [CronogramaController::class, 'update']);


//*********************************************************/
//                  Para Manual Operativo
//*********************************************************/
Route::get('actividadcontrol/{idProceso}', [ActividadControlController::class, 'index']);
Route::get('mapaproceso/{idProceso}', [MapaProcesoController::class, 'index']);
Route::apiResource('controlcambios', ControlCambioController::class);
Route::apiResource('mapaproceso', MapaProcesoController::class);
Route::apiResource('indmapaproceso', IndMapaProcesoController::class);
Route::apiResource('actividadcontrol', ActividadControlController::class);



//Para Auditoria Interna
Route::apiResource('auditorias', AuditoriaInternaController::class);
Route::apiResource('reportesauditoria', ReporteAuditoriaController::class)->only([ 'index', 'store', 'destroy' ]);
Route::get('/reporte-pdf/{id}', [ReporteAuditoriaController::class, 'descargarPDF']);

Route::post('/mapa-proceso/{idProceso}/subir-diagrama', [MapaProcesoController::class, 'subirDiagramaFlujo']);




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
Route::put('analisisDatos/{idRegistro}/necesidad-interpretacion', [FormAnalisisDatosController::class, 'updateNecesidadInterpretacion']);

//Ruta para obtener resultados de los resultados de plan de control
Route::get('/plan-control/{idProceso}', [IndicadorResultadoController::class, 'getResultadosPlanControl']);
Route::get('/mapa-proceso', [IndicadorResultadoController::class, 'getResultadosIndMapaProceso']);
Route::get('/gestion-riesgos/{idRegistro}', [IndicadorResultadoController::class, 'getResultadosRiesgos']);

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

Route::get('/formatos', [FormatosController::class, 'index']);
Route::post('/formatos', [FormatosController::class, 'store']);



//*********************************************************/
//                  Para Reporte de Procesos
//*********************************************************/

Route::post('/reportes-proceso', [ReporteProcesoController::class, 'store']);
Route::get('/reportes-proceso', [ReporteProcesoController::class, 'index']);
Route::delete('/reportes-proceso/{idReporteProceso}', [ReporteProcesoController::class, 'destroy']);
Route::get('/generar-reporte/{idProceso}/{anio}', [ReporteProcesoController::class, 'generarReporte']);
Route::get('/datos-reporte/{idProceso}/{anio}', [ReporteProcesoController::class, 'obtenerDatosReporte']);
Route::get('/mapa-proceso/{idProceso}', [ReporteProcesoController::class, 'obtenerMapaProceso']);
Route::get('/auditoria/{idProceso}', [ReporteProcesoController::class, 'obtenerAuditoria']);
Route::get('/seguimiento/{idProceso}/{anio}', [ReporteProcesoController::class, 'obtenerSeguimiento']);
Route::get('/proyecto-mejora/{idProceso}/{anio}', [ReporteProcesoController::class, 'obtenerPM']);
Route::get('/plan-correctivo/{idProceso}/{anio}', [ReporteProcesoController::class, 'obtenerPlanCorrectivo']);
Route::get('/gestion-riesgos/{idProceso}/{anio}', [ReporteProcesoController::class, 'obtenerRiesgosPorProcesoYAnio']);
//Graficas para el reporte
Route::post('/graficas/guardar', [GraficaController::class, 'guardar']);

Route::get('/indicadores/actividad-control/{idProceso}/{anio}', [IndicadorConsolidadoController::class, 'actividadControl']);
Route::get('/indicadores/satisfaccion-cliente/{idProceso}/{anio}', [ReporteProcesoController::class, 'indicadoresSatisfaccionCliente']);
Route::get('/indicadores/mapa-proceso/{idProceso}/{anio}', [ReporteProcesoController::class, 'indicadoresMapaProceso']);
Route::get('/indicadores/eficacia-riesgos/{idProceso}/{anio}', [ReporteProcesoController::class, 'eficaciaRiesgos']);
Route::get('/indicadores/evaluacion-proveedores/{idProceso}/{anio}', [ReporteProcesoController::class, 'evaluacionProveedores']);


Route::get('/vista-reporte', function () {
    return view('proceso');
});

Route::post('/generar-token', [TokenTemporalController::class, 'generar']);
Route::post('/validar-token', [TokenTemporalController::class, 'validar']);
Route::get('/usuarios-temporales', [TokenTemporalController::class, 'index']);
Route::delete('/usuarios-temporales/{id}', [TokenTemporalController::class, 'destroy']);

Route::get('/buscar-por-anio', [BuscadorSemController::class, 'buscarPorAnio']);

Route::get('/buscar-auditorias', [BuscadorAudiController::class, 'buscarPorAnio']);

Route::get('/procesos-buscar', [BuscadorProcController::class, 'buscarPorAnio']);

Route::post('/formatos', [FormatosController::class, 'store']);
Route::get('/formatos', [FormatosController::class, 'index']);

Route::post('/generar-pdf', [ReporteSemestralController::class, 'generarPDF']); // generar archivo pdf reporte semestral
Route::get('/get-riesgos-sem', [dataSemController::class, 'obtenerData']); //obtener lista data semestral
Route::get('/get-seguimiento-sem', [SeguimientoSemController::class, 'obtenerDatosSeguimiento']); //obtener la lista seguimiento semestral
Route::get('/get-auditorias-sem', [AuditoriaSemController::class, 'obtenerDatosAuditorias']); //obtener la lista auditorias semestra
Route::get('/get-acciones-sem', [ActMejoraSemController::class, 'obtenerDatosAccionesMejora']);//obtener la lista de Act mejora semestral
Route::get('/get-indicador-sem', [IndicadorSemController::class, 'obtenerDatosIndicadores']);//obtener l alista indicadores semestral
Route::post('/reporte-semestral', [SaveReportSemController::class, 'store']); //registrar la generacion de un reporte semestral
Route::get('/reportes-semestrales', [SaveReportSemController::class, 'obtenerReportesSemestrales']); //obtener todos los reportes semestrales generados
Route::get('/verificar-reporte', [SaveReportSemController::class, 'verificarReporteExistente']);

Route::get('cronograma', [CronogramaController::class, 'index']);
Route::post('cronograma', [CronogramaController::class, 'store']);
//Route::put('cronograma/{id}', [CronogramaController::class, 'update']);

// Actualizar una auditoría 
Route::put('/cronograma/{id}', [CronogramaController::class, 'update']);

// Eliminar una auditoría 
Route::delete('/cronograma/{id}', [CronogramaController::class, 'destroy']);

Route::get('/notificaciones/{idUsuario}', [NotificacionController::class, 'getNotificaciones']);
Route::post('/notificaciones/marcar-leidas/{idUsuarios}/{notificationId}', [NotificacionController::class, 'marcarComoLeidas']);
Route::get('/notificaciones/count/{idUsuario}', [NotificacionController::class, 'contarNotificacionesNoLeidas']);


//Route::get('/emitir-notificacion/{idUsuario}', [NotificacionTestController::class, 'enviarNotificacion']);
