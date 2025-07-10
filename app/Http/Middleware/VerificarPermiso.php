<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class VerificarPermiso
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permiso
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next, $permiso)
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable|\App\Models\User|null $usuario */
        $usuario = Auth::user();


        if (
            !$usuario ||
            !$usuario->rol ||
            !$usuario->rol->permissions->contains('nombre', $permiso)
        ) {
            return response()->json(['message' => 'No tienes permiso para acceder a esta sección'], 403);
        }

        return $next($request);
    }
}
