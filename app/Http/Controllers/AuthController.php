<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        $token = $user->createToken('token-api')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'user'    => $user,
            'token'   => $token,
            'rol'     => $user->rol_id
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete(); 

        return response()->json([
            'message' => 'Sesión cerrada correctamente.'
        ]);
    }

}
