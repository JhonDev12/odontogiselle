<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;

class PagosController extends Controller
{


    public function index (Request  $pagos)
    {
        $pagos = Pago::all();
        return response()->json([
            'message' => 'Lista de pagos obtenida correctamente.',
            'data' => $pagos
        ], 200);



    }


   public function historialPagos($cedula)
{
    try {
        $pagos = Pago::whereHas('historial', function ($query) use ($cedula) {
                $query->where('cedula_paciente', $cedula);
            })
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

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error interno al consultar los pagos.',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
