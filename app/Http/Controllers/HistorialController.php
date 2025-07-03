<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
 public function llenarCamposPorCedula(Request $request, $cedula)
{
   
    $historial = Historial::where('cedula_paciente', $cedula)
        ->orderBy('created_at', 'desc')
        ->first();

   
    if (!$historial) {
        return response()->json([
            'message' => 'No se encontró historial clínico con esa cédula.'
        ], 404);
    }

   
    $request->validate([
        'procedimiento' => 'nullable|string|max:800',
        'observaciones' => 'nullable|string|max:500',
        'estado_procedimiento' => 'nullable|in:realizado,en progreso,pendiente',
        'motivo_consulta' => 'nullable|string|max:500',
        'antecedentes_personales' => 'nullable|string|max:500',
        'antecedentes_familiares' => 'nullable|string|max:500',
        'antecedentes_quirurgicos' => 'nullable|string|max:500',
        'medicacion_actual' => 'nullable|string|max:500',
        'alergias' => 'nullable|string|max:500',
        'fuma' => 'nullable|boolean',
        'consume_alcohol' => 'nullable|boolean',
        'bruxismo' => 'nullable|boolean',
        'higiene_oral' => 'nullable|string|max:500',
        'examen_clinico' => 'nullable|string|max:500',
        'diagnostico' => 'nullable|string|max:500',
        'plan_tratamiento' => 'nullable|string|max:500',
    ]);

    
    $historial->update($request->only([
        'procedimiento',
        'observaciones',
        'estado_procedimiento',
        'motivo_consulta',
        'antecedentes_personales',
        'antecedentes_familiares',
        'antecedentes_quirurgicos',
        'medicacion_actual',
        'alergias',
        'fuma',
        'consume_alcohol',
        'bruxismo',
        'higiene_oral',
        'examen_clinico',
        'diagnostico',
        'plan_tratamiento'
    ]));

    return response()->json([
        'message' => 'Último historial actualizado correctamente.',
        'data' => $historial
    ], 200);
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