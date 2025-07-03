<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class pagos extends Model
{
    protected $guarded = []; // Permite asignación masiva de todos los campos excepto los especificados
    protected $table = 'pagos'; // Nombre de la tabla en la base de datos

   public function historial()
    {
        return $this->belongsTo(Historial::class); // Más limpio
    }
}
