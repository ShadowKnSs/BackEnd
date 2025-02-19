<?php

namespace App\Http\Controllers\Api;


use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\MacroProceso;

class MacroProcesoController extends Controller
{
    public function index()
    {
        $macroprocesos = MacroProceso::all();
        return response()->json(['macroprocesos' => $macroprocesos], 200);
    }
}
