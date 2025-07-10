<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
    {

       protected $table = 'roles'; 

    protected $guarded = [];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions()
{
    return $this->belongsToMany(Permission::class, 'permission_role');
}

}
