<?php

namespace App\Http\Controllers;

use App\Models\pagos;
use Illuminate\Http\Request;

class PagosController extends Controller
{
    public function historialPorCedula($cedula)
    {
        $pagos = pagos::where('cedula_paciente', $cedula)
            ->orderBy('fecha_pago', 'desc')
            ->get();

        if ($pagos->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron pagos para esta cédula.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Historial de pagos encontrado.',
            'data' => $pagos
        ], 200);
    }
}
