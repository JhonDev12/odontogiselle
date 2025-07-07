<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PersonaController extends Controller
{
    public function index()
    {
        $personas = Persona::all();

        if ($personas->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron personas.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Lista de personas obtenida correctamente.',
            'data' => $personas
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombres'          => 'required|string|max:100',
            'apellidos'        => 'required|string|max:100',
            'tipo_documento'   => 'required|string|max:5',
            'numero_documento' => 'required|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'telefono'         => 'nullable|string|max:20',
            'direccion'        => 'nullable|string|max:150',
            'email'            => 'required|email|max:100',
        ]);


        if (Persona::where('numero_documento', $request->numero_documento)->exists()) {
            return response()->json([
                'message' => 'La persona con esa cédula ya está registrada.',
            ], 409);
        }


        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'message' => 'El correo electrónico ya está en uso.',
            ], 409);
        }

        try {

            $persona = Persona::create([
                'nombres'          => $request->nombres,
                'apellidos'        => $request->apellidos,
                'tipo_documento'   => $request->tipo_documento,
                'numero_documento' => $request->numero_documento,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'telefono'         => $request->telefono,
                'direccion'        => $request->direccion,
                'email'            => $request->email,
            ]);


          $usuario = User::create([
    'name'        => $request->nombres . ' ' . $request->apellidos, // 👈 Esto es obligatorio
    'email'       => $request->email,
    'password'    => Hash::make($request->numero_documento),
    'persona_id'  => $persona->id,
    'rol_id'      => 1,
]);


            return response()->json([
                'message' => 'Persona y usuario creados exitosamente.',
                'persona' => $persona,
                'usuario' => $usuario
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear persona y usuario.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

public function update(Request $request, $id)
{
    $persona = Persona::with('user')->find($id); // 🔁 Se asegura de cargar el user relacionado

    if (!$persona) {
        return response()->json([
            'message' => 'Persona no encontrada.'
        ], 404);
    }

    $request->validate([
        'nombres'          => 'sometimes|string|max:100',
        'apellidos'        => 'sometimes|string|max:100',
        'tipo_documento'   => 'sometimes|string|max:5',
        'numero_documento' => 'sometimes|string|max:20|unique:personas,numero_documento,' . $id,
        'fecha_nacimiento' => 'nullable|date',
        'telefono'         => 'nullable|string|max:20',
        'direccion'        => 'nullable|string|max:150',
        'email'            => [
            'sometimes',
            'email',
            'max:100',
            Rule::unique('users', 'email')->ignore(optional($persona->user)->id),
        ],
    ]);

    try {
        // 🔁 Actualizar la persona
        $persona->update($request->only([
            'nombres', 'apellidos', 'tipo_documento',
            'numero_documento', 'fecha_nacimiento',
            'telefono', 'direccion', 'email'
        ]));

        // 🔁 Actualizar el usuario relacionado
        if ($persona->user) {
            $persona->user->update([
                'name'     => $request->nombres && $request->apellidos
                    ? $request->nombres . ' ' . $request->apellidos
                    : $persona->user->name,
                'email'    => $request->email ?? $persona->user->email,
                'password' => $request->filled('numero_documento')
                    ? Hash::make($request->numero_documento)
                    : $persona->user->password,
            ]);
        }

        return response()->json([
            'message' => 'Persona y usuario actualizados correctamente.',
            'data'    => $persona
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error al actualizar la persona y usuario.',
            'error'   => $e->getMessage()
        ], 500);
    }
}
    public function destroy($id)
    {
        $persona = Persona::find($id);

        if (!$persona) {
            return response()->json([
                'message' => 'Persona no encontrada.'
            ], 404);
        }


        $usuario = User::where('persona_id', $persona->id)->first();
        if ($usuario) {
            $usuario->delete();
        }

        $persona->delete();

        return response()->json([
            'message' => 'Persona y usuario eliminados correctamente.'
        ], 200);
    }
}
