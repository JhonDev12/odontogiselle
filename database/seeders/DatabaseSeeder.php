<?php

namespace Database\Seeders;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
      // Crear rol
        $rol = Rol::firstOrCreate([
            'nombre' => 'Administrador'
        ], [
            'descripcion' => 'Rol con todos los permisos',
            'activo' => true
        ]);

        // Crear persona
        $persona = Persona::firstOrCreate([
            'numero_documento' => '1234567890',
        ], [
            'nombres' => 'Admin',
            'apellidos' => 'Demo',
            'tipo_documento' => 'CC',
            'fecha_nacimiento' => '1990-01-01',
            'telefono' => '3001234567',
            'direccion' => 'Calle falsa 123',
            'email' => 'admin@demo.com',
        ]);

        // Crear usuario (sin 'name')
        User::create([
             'name' => 'Administrador',
            'email' => 'admin@demo.com',
            'password' => Hash::make('1234567890'), // la cédula como contraseña
            'persona_id' => $persona->id,
            'rol_id' => $rol->id,
        ]);
    }
}
