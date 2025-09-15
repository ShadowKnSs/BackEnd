<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthInstitucionalController;


Route::get('/', function () {
    return view('welcome');
});



//Route::get('/login-institucional/callback', [AuthInstitucionalController::class, 'handleCallback']);}

use App\Models\ReporteSemestral;
use Illuminate\Support\Facades\Response;

Route::get('/descargar-reporte/{id}', function ($id) {
    $reporte = ReporteSemestral::findOrFail($id);
    $path = storage_path('app/public/' . $reporte->ubicacion);

    if (!file_exists($path)) {
        abort(404, 'El archivo no existe');
    }

    return response()->download($path, basename($path), [
        'Content-Type' => 'application/pdf'
    ]);
});
