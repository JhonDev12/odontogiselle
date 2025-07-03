<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Historial;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
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
        if($request->estado === 'cancelada') {
            return response()->json([
                'message' => 'Error al crear la cita. No se puede crear una cita con estado "cancelada". Por favor, elige otro estado.'
            ], 400);

        }
        try {

            $cita = Cita::create($request->all());

            // Registro en historial médico
            Historial::create([
                'cedula_paciente'       => $cita->cedula_paciente,
                'nombre_paciente'       => $cita->nombre_paciente,
                'telefono_paciente'     => $cita->telefono_paciente,
                'email_paciente'        => $cita->email_paciente,
                'fecha_cita'            => $cita->fecha_hora_cita ? Carbon::parse($cita->fecha_hora_cita)->toDateString() : now()->toDateString(),
                'hora_cita'             => $cita->fecha_hora_cita ? Carbon::parse($cita->fecha_hora_cita)->format('H:i:s') : now()->format('H:i:s'),
                'estado_cita'           => $cita->estado,
                'procedimiento'         => null,
                'observaciones'         => $cita->observaciones,
                'estado_procedimiento'  => null,
                'motivo_consulta'       => null,
                'antecedentes_personales' => null,
                'antecedentes_familiares' => null,
                'antecedentes_quirurgicos' => null,
                'medicacion_actual'     => null,
                'alergias'              => null,
                'fuma'                  => false,
                'consume_alcohol'       => false,
                'bruxismo'              => false,
                'higiene_oral'          => null,
                'examen_clinico'        => null,
                'diagnostico'           => null,
                'plan_tratamiento'      => null,
            ]);

            // Preparar número para WhatsApp
            $numeroDestino = preg_replace('/\D/', '', $request->telefono_paciente);
            $numeroDestino = '57' . ltrim($numeroDestino, '0');

            if (strlen($numeroDestino) < 10 || strlen($numeroDestino) > 15) {
                return response()->json([
                    'message' => 'Número de teléfono inválido.'
                ], 400);
            }

            // Enviar mensaje
            $mensaje = "Hola *{$request->nombre_paciente}*, tu cita ha sido agendada exitosamente para el día *{$fecha}* a las *{$hora}*. ¡Te esperamos!";

            $whatsappResponse = Http::post('http://127.0.0.1:3000/enviar-mensaje', [
                'numero'  => $numeroDestino,
                'mensaje' => $mensaje
            ]);

            if ($whatsappResponse->successful()) {
                return response()->json([
                    'message' => 'Cita creada y mensaje enviado por WhatsApp.',
                    'data'    => $cita
                ], 201);
            } else {
                return response()->json([
                    'message' => 'Cita creada, pero no se pudo enviar el mensaje por WhatsApp.',
                    'data'    => $cita,
                    'error'   => $whatsappResponse->json()
                ], 207);
            }
        } catch (\Exception $e) {
            Log::error('Error al guardar cita o historial o enviar WhatsApp: ' . $e->getMessage());
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
            'cancelada_en'       => 'nullable|date',
        ]);

        $cita = Cita::find($id);
        if (!$cita) {
            return response()->json(['message' => 'Cita no encontrada'], 404);
        }

        $esCancelada = $request->estado === 'cancelada' && $cita->estado !== 'cancelada';
        $esReactivo = in_array($request->estado, ['pendiente', 'confirmada']) && $cita->estado === 'cancelada';
        $esReprogramada = $request->filled('fecha_hora_cita') && $request->estado !== 'cancelada';

        try {
            if ($esCancelada) {
                $request->merge([
                    'fecha_hora_cita' => null,
                    'cancelada_en'    => Carbon::now()->toDateTimeString()
                ]);
            }

            if ($esReactivo || $esReprogramada) {
                $nuevaFecha = Carbon::parse($request->fecha_hora_cita);
                $fecha = $nuevaFecha->toDateString();
                $hora  = $nuevaFecha->format('H:i:s');

                if ($nuevaFecha->lessThan(Carbon::now())) {
                    return response()->json(['message' => 'No se puede asignar una fecha pasada.'], 400);
                }

                $request->merge(['fecha_hora_cita' => $nuevaFecha->toDateTimeString(), 'cancelada_en' => null]);

                if ($request->filled('cedula_paciente')) {
                    $existeOtraCita = Cita::where('cedula_paciente', $request->cedula_paciente)
                        ->whereIn('estado', ['pendiente', 'confirmada'])
                        ->whereDate('fecha_hora_cita', $fecha)
                        ->where('id', '!=', $cita->id)
                        ->exists();

                    if ($existeOtraCita) {
                        return response()->json(['message' => 'Ya existe otra cita para esa cédula ese día.'], 409);
                    }

                    $horarioOcupado = Cita::whereDate('fecha_hora_cita', $fecha)
                        ->whereTime('fecha_hora_cita', $hora)
                        ->where('estado', '!=', 'cancelada')
                        ->where('id', '!=', $cita->id)
                        ->exists();

                    if ($horarioOcupado) {
                        return response()->json(['message' => 'Ese horario ya está ocupado.'], 409);
                    }
                }
            }

            // Guardar la cita
            $cita->fill($request->all());
            $cita->save();

            // Guardar en el historial médico
            Historial::create([
                'cedula_paciente'       => $cita->cedula_paciente,
                'nombre_paciente'       => $cita->nombre_paciente,
                'telefono_paciente'     => $cita->telefono_paciente,
                'email_paciente'        => $cita->email_paciente,
                'fecha_cita'            => $cita->fecha_hora_cita ? Carbon::parse($cita->fecha_hora_cita)->toDateString() : now()->toDateString(),
                'hora_cita'             => $cita->fecha_hora_cita ? Carbon::parse($cita->fecha_hora_cita)->format('H:i:s') : now()->format('H:i:s'),
                'estado_cita'           => $cita->estado,
                'procedimiento'         => null,
                'observaciones'         => $cita->observaciones,
                'estado_procedimiento'  => null,
                'motivo_consulta'       => null,
                'antecedentes_personales' => null,
                'antecedentes_familiares' => null,
                'antecedentes_quirurgicos' => null,
                'medicacion_actual'     => null,
                'alergias'              => null,
                'fuma'                  => false,
                'consume_alcohol'       => false,
                'bruxismo'              => false,
                'higiene_oral'          => null,
                'examen_clinico'        => null,
                'diagnostico'           => null,
                'plan_tratamiento'      => null,
            ]);


            // Enviar mensaje por WhatsApp
            $numeroDestino = preg_replace('/\D/', '', $cita->telefono_paciente);
            $numeroDestino = '57' . ltrim($numeroDestino, '0');

            $fechaMensaje = $cita->fecha_hora_cita ? Carbon::parse($cita->fecha_hora_cita)->toDateString() : '';
            $horaMensaje  = $cita->fecha_hora_cita ? Carbon::parse($cita->fecha_hora_cita)->format('H:i') : '';

            $mensaje = match ($cita->estado) {
                'cancelada'   => "Hola *{$cita->nombre_paciente}*, tu cita ha sido *cancelada*. Si deseas reprogramarla, contáctanos nuevamente.",
                'confirmada'  => "Hola *{$cita->nombre_paciente}*, tu cita ha sido *confirmada* para el *{$fechaMensaje}* a las *{$horaMensaje}*. ¡Te esperamos! 🦷",
                'pendiente'   => "Hola *{$cita->nombre_paciente}*, tu cita está *pendiente* para el *{$fechaMensaje}* a las *{$horaMensaje}*. ¡Te esperamos! 🦷",
                default       => "Hola *{$cita->nombre_paciente}*, tu cita fue actualizada."
            };

            $whatsappResponse = Http::post('http://127.0.0.1:3000/enviar-mensaje', [
                'numero'  => $numeroDestino,
                'mensaje' => $mensaje
            ]);

            return response()->json([
                'message' => 'Cita actualizada y registrada en historial.',
                'data'    => $cita->fresh(),
                'whatsapp' => $whatsappResponse->successful()
                    ? 'Mensaje enviado'
                    : 'Error al enviar mensaje'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar cita: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al actualizar la cita.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
