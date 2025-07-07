<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index()
    {
        $usuarios = User::with('rol')->get();

        if ($usuarios->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron usuarios.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Lista de usuarios obtenida correctamente.',
            'data' => $usuarios
        ], 200);
    }


   public function actualizarRol(Request $request, $id)
{
    $request->validate([
        'rol_id' => 'required|exists:roles,id'
    ]);

    $usuario = User::find($id);

    if (!$usuario) {
        return response()->json([
            'message' => 'Usuario no encontrado.'
        ], 404);
    }

    $usuario->rol_id = $request->rol_id;
    $usuario->save();

    return response()->json([
        'message' => 'Rol del usuario actualizado correctamente.',
        'usuario' => $usuario
    ]);
}

}
