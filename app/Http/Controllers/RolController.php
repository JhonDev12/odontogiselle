<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index(){
        $Roles = Rol::all();
        return response()->json([
            'message' => 'Lista de roles obtenida correctamente.',
            'data' => $Roles
        ], 200);
    }

    public function store(Request $request){
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'required|boolean',
        ]);

        $rol = Rol::create($request->all());

        return response()->json([
            'message' => 'Rol creado correctamente.',
            'data' => $rol
        ], 201);
    }


    public function update(Request $request, $id){
        $rol = Rol::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'activo' => 'required|boolean',
        ]);

        $rol->update($request->all());

        return response()->json([
            'message' => 'Rol actualizado correctamente.',
            'data' => $rol
        ], 200);
    }
    
 public function destroy($id)
{
    $rol = Rol::findOrFail($id);

    // Verifica si algún usuario tiene asignado este rol
    $usuariosConRol = User::where('rol_id', $id)->exists();

    if ($usuariosConRol) {
        return response()->json([
            'message' => 'No se puede eliminar el rol porque está asignado a uno o más usuarios.'
        ], 409); 
    }

    $rol->delete();

    return response()->json([
        'message' => 'Rol eliminado correctamente.'
    ], 200);
}

}
