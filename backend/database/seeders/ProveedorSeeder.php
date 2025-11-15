<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Proveedor;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $proveedores = [
            [
                'nombre' => 'Distribuidora San Juan, S.A.',
                'nit' => '12345678-9',
                'direccion' => 'Zona 1, Ciudad de Guatemala',
                'telefono' => '2234-5678',
                'correo' => 'ventas@distribuidorasanjuan.com',
                'estado' => 1
            ],
            [
                'nombre' => 'Comercial López Hermanos',
                'nit' => '98765432-1',
                'direccion' => 'Zona 10, Ciudad de Guatemala',
                'telefono' => '2345-6789',
                'correo' => 'info@lopezhmanos.com',
                'estado' => 1
            ],
            [
                'nombre' => 'Suministros Industriales CFAG',
                'nit' => '11111111-1',
                'direccion' => 'Zona 12, Ciudad de Guatemala',
                'telefono' => '2456-7890',
                'correo' => 'suministros@cfag.edu.gt',
                'estado' => 1
            ],
            [
                'nombre' => 'Papelería y Útiles Escolares El Estudiante',
                'nit' => '22222222-2',
                'direccion' => 'Zona 4, Ciudad de Guatemala',
                'telefono' => '2567-8901',
                'correo' => 'ventas@elestudiante.com',
                'estado' => 1
            ],
            [
                'nombre' => 'Alimentos y Bebidas Guatemalteca, S.A.',
                'nit' => '33333333-3',
                'direccion' => 'Zona 7, Ciudad de Guatemala',
                'telefono' => '2678-9012',
                'correo' => 'pedidos@alimentosguatemala.com',
                'estado' => 1
            ],
            [
                'nombre' => 'Equipos de Oficina Modernos',
                'nit' => '44444444-4',
                'direccion' => 'Zona 9, Ciudad de Guatemala',
                'telefono' => '2789-0123',
                'correo' => 'equipos@oficinasmodernas.com',
                'estado' => 1
            ],
            [
                'nombre' => 'Ferretería El Martillo',
                'nit' => '55555555-5',
                'direccion' => 'Zona 18, Ciudad de Guatemala',
                'telefono' => '2890-1234',
                'correo' => 'ferreteria@elmartillo.com',
                'estado' => 1
            ],
            [
                'nombre' => 'Limpieza y Mantenimiento ProClean',
                'nit' => '66666666-6',
                'direccion' => 'Zona 11, Ciudad de Guatemala',
                'telefono' => '2901-2345',
                'correo' => 'servicios@proclean.com.gt',
                'estado' => 1
            ]
        ];

        foreach ($proveedores as $proveedor) {
            Proveedor::create($proveedor);
        }
    }
}