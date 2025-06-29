<?php

use App\Http\Controllers\CitaController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return response()->json([
        'message' => 'La API está funcionando correctamente'
    ]);
});


Route::get('citas', [CitaController::class, 'index']);

Route::post('create', [CitaController::class, 'store']);

Route::post('update/{id}', [CitaController::class, 'update']);

