<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    protected $guarded = [];

   public function user()
{
    return $this->hasOne(User::class, 'persona_id');
}
    public function rol()
    {
     return $this->hasOne(Rol::class, 'persona_id');
}
}