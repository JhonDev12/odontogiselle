<?php

namespace App\Http\Controllers;

use App\Models\Historial;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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


public function store(Request $request, $cedula)
{
    try {
        $request->validate([
            'monto'        => 'required|numeric|min:0',
            'fecha_pago'   => 'required|date',
            'detalle'      => 'nullable|string|max:255',
            'metodo_pago'  => 'nullable|string|max:50'
        ]);

        // Buscar historial más reciente
        $historial = Historial::where('cedula_paciente', $cedula)
            ->latest('created_at')
            ->first();

     
        if (!$historial) {
            $historial = Historial::create([
                'cedula_paciente'         => $cedula,
                'nombre_paciente'         => 'Nombre no disponible',
                'telefono_paciente'       => '0000000000',
                'email_paciente'          => null,
                'fecha_cita'              => now()->toDateString(),
                'hora_cita'               => now()->format('H:i:s'),
                'estado_cita'             => 'pendiente',
                'procedimiento'           => null,
                'observaciones'           => null,
                'estado_procedimiento'    => null,
                'motivo_consulta'         => null,
                'antecedentes_personales' => null,
                'antecedentes_familiares' => null,
                'antecedentes_quirurgicos'=> null,
                'medicacion_actual'       => null,
                'alergias'                => null,
                'fuma'                    => false,
                'consume_alcohol'         => false,
                'bruxismo'                => false,
                'higiene_oral'            => null,
                'examen_clinico'          => null,
                'diagnostico'             => null,
                'plan_tratamiento'        => null,
            ]);
        }

        // Verificar si ya existe un pago con esa cédula y ese historial
        $pagoExistente = Pago::where('cedula_paciente', $cedula)
            ->where('historial_id', $historial->id)
            ->first();

        if ($pagoExistente) {
            // Actualiza el pago existente
            $pagoExistente->update([
                'monto'        => $request->monto,
                'fecha_pago'   => $request->fecha_pago,
                'detalle'      => $request->detalle,
                'metodo_pago'  => $request->metodo_pago,
            ]);

            return response()->json([
                'message' => 'Pago actualizado correctamente.',
                'data'    => $pagoExistente
            ], 200);
        }

        // Si no existe, crear nuevo pago
        $nuevoPago = Pago::create([
            'cedula_paciente' => $cedula,
            'historial_id'    => $historial->id,
            'monto'           => $request->monto,
            'fecha_pago'      => $request->fecha_pago,
            'detalle'         => $request->detalle,
            'metodo_pago'     => $request->metodo_pago,
        ]);

        return response()->json([
            'message' => 'Pago creado correctamente.',
            'data'    => $nuevoPago
        ], 201);

    } catch (\Exception $e) {
        Log::error('Error al crear/actualizar pago: ' . $e->getMessage());

        return response()->json([
            'message' => 'Ocurrió un error al registrar el pago.',
            'error'   => $e->getMessage()
        ], 500);
    }
}


}