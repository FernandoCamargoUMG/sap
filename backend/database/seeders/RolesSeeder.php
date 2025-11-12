<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles por defecto
        Rol::create(['nombre' => 'Admin', 'descripcion' => 'Administrador del sistema', 'estado' => 1]);
        Rol::create(['nombre' => 'Contador', 'descripcion' => 'Encargado de la contabilidad', 'estado' => 1]);
        Rol::create(['nombre' => 'Auditor', 'descripcion' => 'Responsable de auditorías', 'estado' => 1]);
    }
}
