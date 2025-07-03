<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Historial extends Model
{
  use HasFactory;

   protected $guarded = []; 


 public function pagos()
    {
        return $this->hasMany(pagos::class); // Relación 1 a muchos
    }
}
