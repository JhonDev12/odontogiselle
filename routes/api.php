<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\PagosController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return response()->json([
        'message' => 'La API está funcionando correctamente'
    ]);
});

    Route::controller(CitaController::class)->group(function () {
        Route::get('citas', 'index');
        Route::post('create', 'store');
        Route::post('update/{id}', 'update');
        Route::post('delete/{id}', 'destroy');
    });


    Route::controller(HistorialController::class)->group(function () {
        Route::get('historial/{cedula}', 'historialPorCedula');
        Route::post('llenar-campos/{cedula}', 'llenarCamposPorCedula');
    });

    Route::controller(PagosController::class)->group(function () {
        Route::get('pagos/{cedula}', 'historialPagos');
        Route::get('pagos', 'index');
        Route::post('nuevos/{cedula}', 'store');
        Route::post('eliminar/pagos/{id}', 'destroy');
    });


    Route::controller(PersonaController::class)->group(function () {
        Route::get('personas', 'index');
        Route::post('crear/persona', 'store');
        Route::post('actualizar/{id}', 'update');
        Route::post('eliminar/{id}', 'destroy');
    });

    Route::controller(RolController::class)->group(function () {
        Route::get('roles', 'index');
        Route::post('crear/rol', 'store');
        Route::post('actualizar/rol/{id}', 'update');
        Route::post('eliminar/rol/{id}', 'destroy');
    });

    Route::controller(UserController::class)->group(function () {
        Route::get('usuarios', 'index');
        Route::post('actualizar/rol/usuario/{id}', 'actualizarRol');
    });
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});
Route::post('login', [AuthController::class, 'login']);
