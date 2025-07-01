<?php

use App\Http\Controllers\CitaController;
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

