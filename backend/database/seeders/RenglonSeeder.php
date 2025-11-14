<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Renglon;

class RenglonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear renglones de ejemplo (nueva arquitectura - solo clasificadores)
        $renglones = [
            [
                'codigo' => '111',
                'nombre' => 'Sueldos y Salarios',
                'descripcion' => 'Remuneraciones al personal de la institución',
                'grupo' => 'Personal',
                'estado' => 1
            ],
            [
                'codigo' => '122', 
                'nombre' => 'Servicios Técnicos',
                'descripcion' => 'Contratación de servicios técnicos y profesionales',
                'grupo' => 'Servicios',
                'estado' => 1
            ],
            [
                'codigo' => '211',
                'nombre' => 'Combustibles',
                'descripcion' => 'Compra de combustibles y lubricantes',
                'grupo' => 'Materiales',
                'estado' => 1
            ],
            [
                'codigo' => '231',
                'nombre' => 'Útiles de Oficina',
                'descripcion' => 'Materiales y suministros de oficina',
                'grupo' => 'Materiales',
                'estado' => 1
            ],
            [
                'codigo' => '322',
                'nombre' => 'Equipo de Cómputo',
                'descripcion' => 'Adquisición de equipo informático',
                'grupo' => 'Inversión',
                'estado' => 1
            ]
        ];

        foreach ($renglones as $renglonData) {
            Renglon::create($renglonData);
        }
        
        echo "✅ Renglones creados exitosamente (nueva arquitectura - solo clasificadores)\n";
        echo "   - Total renglones: " . count($renglones) . "\n";
    }
}