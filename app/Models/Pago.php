<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $guarded = []; // Permite asignación masiva de todos los campos excepto los especificados
 

   public function historial()
    {
        return $this->belongsTo(Historial::class); // Más limpio
    }
}
