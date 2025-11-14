<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Renglon;
use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;

class RenglonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear renglones de ejemplo
        $renglones = [
            [
                'codigo' => '001-001',
                'nombre' => 'Sueldos y Salarios Personal Permanente',
                'descripcion' => 'Remuneraciones al personal permanente',
                'monto_inicial' => 100000.00,
                'saldo_actual' => 100000.00,
                'estado' => 1
            ],
            [
                'codigo' => '001-002', 
                'nombre' => 'Sueldos y Salarios Personal Temporal',
                'descripcion' => 'Remuneraciones al personal temporal',
                'monto_inicial' => 50000.00,
                'saldo_actual' => 50000.00,
                'estado' => 1
            ],
            [
                'codigo' => '002-001',
                'nombre' => 'Servicios Profesionales',
                'descripcion' => 'Contratación de servicios profesionales externos',
                'monto_inicial' => 75000.00,
                'saldo_actual' => 75000.00,
                'estado' => 1
            ],
            [
                'codigo' => '003-001',
                'nombre' => 'Materiales y Suministros de Oficina',
                'descripcion' => 'Compra de materiales de oficina',
                'monto_inicial' => 25000.00,
                'saldo_actual' => 25000.00,
                'estado' => 1
            ],
            [
                'codigo' => '004-001',
                'nombre' => 'Equipo de Cómputo',
                'descripcion' => 'Adquisición de equipo informático',
                'monto_inicial' => 80000.00,
                'saldo_actual' => 80000.00,
                'estado' => 1
            ]
        ];

        foreach ($renglones as $renglonData) {
            Renglon::create($renglonData);
        }

        // Crear un presupuesto de ejemplo
        $presupuesto = PresupuestoCab::create([
            'anio' => 2025,
            'mes' => 11,
            'descripcion' => 'Presupuesto Noviembre 2025',
            'creado_por' => 1, // Asumiendo que existe un usuario con ID 1
            'estado' => 'aprobado'
        ]);

        // Crear algunos detalles de presupuesto para probar los saldos
        $renglon1 = Renglon::where('codigo', '001-001')->first();
        $renglon2 = Renglon::where('codigo', '002-001')->first();

        if ($renglon1) {
            PresupuestoDet::create([
                'presupuesto_id' => $presupuesto->id,
                'renglon_id' => $renglon1->id,
                'monto_asignado' => 30000.00,
                'monto_ejecutado' => 15000.00
            ]);
        }

        if ($renglon2) {
            PresupuestoDet::create([
                'presupuesto_id' => $presupuesto->id,
                'renglon_id' => $renglon2->id,
                'monto_asignado' => 20000.00,
                'monto_ejecutado' => 5000.00
            ]);
        }
    }
}