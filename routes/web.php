<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthInstitucionalController;


Route::get('/', function () {
    return view('welcome');
});



// Route::get('/login-institucional/callback', [AuthInstitucionalController::class, 'handleCallback']);
