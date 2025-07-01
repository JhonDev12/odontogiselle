<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // arriba del archivo
use Illuminate\Support\Facades\Http;
use Exception;

class CitaController extends Controller
{
 public function index()
 {
   $citas = Cita::all();

   if ($citas->isEmpty()) {
       return response()->json(['message' => 'No hay citas registradas'], 404);
   }
   return response()->json($citas);
 }

 public function create()
 {
     // Logic to show form for creating a new appointment
 }




public function store(Request $request)
{
    
    $request->validate([
        'nombre_paciente'    => 'required|string|max:255',
        'cedula_paciente'    => 'required|string|max:12',
        'email_paciente'     => 'nullable|email|max:255',
        'telefono_paciente'  => 'required|string|max:15',
        'fecha_hora_cita'    => 'required|date',
        'motivo_cita'        => 'nullable|string|max:255',
        'estado'             => 'nullable|string|in:pendiente,confirmada,cancelada',
        'observaciones'      => 'nullable|string|max:500',
        'cancelada_en'       => 'nullable|date',
    ]);

    $fechaCompleta = Carbon::parse($request->fecha_hora_cita);
    $fecha = $fechaCompleta->toDateString();
    $hora  = $fechaCompleta->format('H:i:s');


    if ($fechaCompleta->lessThan(now())) {
        return response()->json([
            'message' => 'No se puede agendar una cita en una fecha y hora pasada.'
        ], 400);
    }

 
    $existeCitaParaCedula = Cita::where('cedula_paciente', $request->cedula_paciente)
        ->whereIn('estado', ['pendiente', 'confirmada'])
        ->whereDate('fecha_hora_cita', $fecha)
        ->exists();

    if ($existeCitaParaCedula) {
        return response()->json([
            'message' => 'Ya existe una cita pendiente o confirmada para esta cédula en ese mismo día.'
        ], 409);
    }

    
    $existeCitaEnMismoHorario = Cita::whereDate('fecha_hora_cita', $fecha)
        ->whereTime('fecha_hora_cita', $hora)
        ->where('estado', '!=', 'cancelada')
        ->exists();

    if ($existeCitaEnMismoHorario) {
        return response()->json([
            'message' => 'Ese horario ya está ocupado. Elige otro diferente.'
        ], 409);
    }

    try {
     
        $cita = Cita::create($request->all());

      
        $numeroDestino = preg_replace('/\D/', '', $request->telefono_paciente);
        $numeroDestino = '57' . ltrim($numeroDestino, '0');

        if (strlen($numeroDestino) < 10 || strlen($numeroDestino) > 15) {
            return response()->json([
                'message' => 'Número de teléfono inválido.'
            ], 400);
        }
        $token = 'EAAJMZAVsqrdIBO5P7OFvfwGvhp3MX7Dz684PaiG7iDEZBUZBgSXr8hfdyqVlv6dFbMCAeBa7KNQO5LMSyU6VNz1GDiSHULwFuqg3w2EBmJ4yfuDg4rcIrfTqwC21n4vjFadoqmstGLHx2WuAaFc3uUNi1qaTcfA2ZB6np6MZCYe75xqpVJBEEKiZCZAEiMb6H4SrBDnczQMu3n8qMGTDDf5DF1sJeqKmtn7ZBFBOzNMlhQZDZD';
        $telefonoId = '681093465091787'; // ID del número de prueba (remitente)
        $url = "https://graph.facebook.com/v18.0/{$telefonoId}/messages";

        $response = Http::withToken($token)->post($url, [
            'messaging_product' => 'whatsapp',
            'to' => $numeroDestino,
            'type' => 'template',
            'template' => [
                'name' => 'hello_world', 
                'language' => ['code' => 'en_US'],
            ],
        ]);

        if ($response->successful()) {
            return response()->json([
                'message' => 'Cita creada y mensaje enviado por WhatsApp.',
                'data' => $cita
            ], 201);
        } else {
            Log::error('WhatsApp API Error: ' . $response->body());
            return response()->json([
                'message' => 'Cita creada, pero no se pudo enviar el mensaje por WhatsApp.',
                'data' => $cita,
                'error' => $response->json()
            ], 207);
        }

    } catch (\Exception $e) {
        Log::error('Error al guardar cita o enviar WhatsApp: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error al crear la cita.',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function show($id)
    {
        // Logic to display a specific appointment
    }



public function update(Request $request, $id)
{

    $request->validate([
        'nombre_paciente'    => 'sometimes|required|string|max:255',
        'cedula_paciente'    => 'sometimes|required|string|max:12',
        'email_paciente'     => 'nullable|email|max:255',
        'telefono_paciente'  => 'sometimes|required|string|max:15',
        'fecha_hora_cita'    => 'sometimes|nullable|date',
        'motivo_cita'        => 'nullable|string|max:255',
        'estado'             => 'nullable|string|in:pendiente,confirmada,cancelada',
        'observaciones'      => 'nullable|string|max:500',
        'cancelada_en' => 'nullable|date',

    ]);

 
    $cita = Cita::find($id);

    if (!$cita) {
        return response()->json(['message' => 'Cita no encontrada'], 404);
    }

 if (
    $request->has('estado') &&
    $request->estado === 'cancelada' &&
    $cita->estado !== 'cancelada'
) {
    try {
        $request->merge([
            'fecha_hora_cita' => null,
            'cancelada_en'    =>Carbon::now()->toDateTimeString()
        ]);

        $cita->fill($request->all());
        $cita->save();

        return response()->json([
            'message' => 'Cita cancelada exitosamente',
            'data'    => $cita->fresh()
        ]);
    } catch (Exception $error) {
        Log::error('Error al cancelar cita: ' . $error->getMessage());

        return response()->json([
            'message' => 'Error interno al cancelar la cita',
            'error' => $error->getMessage()
        ], 500);
    }
}
  
    if ($request->filled('fecha_hora_cita') && $request->estado !== 'cancelada') {
        $nuevaFecha = Carbon::parse($request->fecha_hora_cita);
        $fecha = $nuevaFecha->toDateString();
        $hora  = $nuevaFecha->format('H:i:s');

       
        if ($nuevaFecha->lessThan(Carbon::now())) {
            return response()->json([
                'message' => 'No se puede reprogramar una cita a una fecha u hora pasada.'
            ], 400);
        }

        // Observar que no aya una misma cita al mismo dia 
        if ($request->filled('cedula_paciente')) {
            $existeOtraCita = Cita::where('cedula_paciente', $request->cedula_paciente)
                ->where('estado', 'pendiente','confirmada')
                ->whereDate('fecha_hora_cita', $fecha)
                ->where('id', '!=', $cita->id)
                ->exists();

            if ($existeOtraCita) {
                return response()->json([
                    'message' => 'Ya existe otra cita pendiente para esta cédula en ese mismo día.'
                ], 409);
            }
        }

        // vaqlidad que no aya una cita a la missma hora
        $horarioOcupado = Cita::whereDate('fecha_hora_cita', $fecha)
            ->whereTime('fecha_hora_cita', $hora)
            ->where('estado', '!=', 'cancelada')
            ->where('id', '!=', $cita->id)
            ->exists();

        if ($horarioOcupado) {
            return response()->json([
                'message' => 'Ese horario ya está ocupado. Elige otro diferente.'
            ], 409);
        }

        // Formatear correctamente la nueva fecha/hora
        $request->merge([
            'fecha_hora_cita' => $nuevaFecha->toDateTimeString()
        ]);
    }

 
    $cita->update($request->all());

    return response()->json([
        'message' => 'Cita actualizada exitosamente',
        'data'    => $cita
    ]);
}





    public function destroy($id)
    {
            $cita = Cita::find($id);

              if (!$cita) {
        return response()->json([
            'message' => 'Cita no encontrada'
        ], 404); 
    }
      $cita->delete();

        return response()->json([
            'message' => 'Cita eliminada exitosamente'
        ], 200); 
    }
}
