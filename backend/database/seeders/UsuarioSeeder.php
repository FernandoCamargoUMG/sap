<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Usuario::create([
            'nombre' => 'Administrador',
            'correo' => 'administrador@contabilidad.com',
            'contraseña' => 'admin123', // El mutator aplicará MD5 automáticamente
            'rol_id' => 1,
            'estado' => 1,
        ]);
    }
}
