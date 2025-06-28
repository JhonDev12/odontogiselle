<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;

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
        'nombre_paciente'=>'required|string|max:255',
        'cedula_paciente' => 'required|string|max:12',
        'email_paciente' => 'nullable|email|max:255',
        'telefono_paciente' => 'required|string|max:15',
        'fecha_hora_cita' => 'required|date',
        'motivo_cita' => 'nullable|string|max:255',
        'estado' => 'nullable|string|in:pendiente,confirmada,cancelada',
        'observaciones' => 'nullable|string|max:500',
    ]);
      $cita = Cita::create($request->all());

    if (!$cita) {
        return response()->json(['message' => 'Error al crear la cita'], 500);
    }
    return response()->json([
        'message' => 'Cita creada exitosamente',
        'data' => $cita
    ], 201);
 }
    public function show($id)
    {
        // Logic to display a specific appointment
    }

    public function update(Request $request, $id)
    {
        // Logic to update a specific appointment
    }

    public function destroy($id)
    {
        // Logic to delete a specific appointment
    }
}
