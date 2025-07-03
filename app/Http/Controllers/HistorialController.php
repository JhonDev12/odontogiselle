<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function store(Request $request){

    }

    public function historialPorCedula($cedula)
{
    $historial = Historial::where('cedula_paciente', $cedula)
                    ->orderBy('fecha_cita', 'desc')
                    ->get(); 

    if ($historial->isEmpty()) {
        return response()->json([
            'message' => 'No se encontró historial para esta cédula.',
            'data' => []
        ], 404);
    }

    return response()->json([
        'message' => 'Historial completo encontrado.',
        'data' => $historial 
    ], 200);
}
}