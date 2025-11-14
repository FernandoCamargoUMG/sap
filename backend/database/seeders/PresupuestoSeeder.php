<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Renglon;
use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use Illuminate\Support\Facades\DB;

class PresupuestoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            // Limpiar datos existentes de forma segura
            PresupuestoDet::query()->delete();
            PresupuestoCab::query()->delete();
            
            // Crear presupuesto para Noviembre 2025
            $presupuesto = PresupuestoCab::create([
                'anio' => 2025,
                'mes' => 11,
                'descripcion' => 'Presupuesto Noviembre 2025 - Guatemala',
                'creado_por' => 1,
                'fecha_creacion' => now(),
                'estado' => 1
            ]);

            // Obtener los renglones existentes (usando los que ya tenemos)
            $renglones = Renglon::where('estado', 1)->get();
            
            if ($renglones->count() > 0) {
                // Crear detalles de presupuesto basados en los renglones existentes
                foreach ($renglones as $index => $renglon) {
                    // Asignar diferentes porcentajes del monto inicial
                    $porcentajeAsignado = match($index) {
                        0 => 0.8, // 80% del primer renglón
                        1 => 0.6, // 60% del segundo renglón
                        2 => 0.7, // 70% del tercer renglón
                        default => 0.5 // 50% para otros renglones
                    };
                    
                    $montoAsignado = $renglon->monto_inicial * $porcentajeAsignado;
                    
                    // Simular algo de ejecución (entre 0% y 30%)
                    $porcentajeEjecutado = rand(0, 30) / 100;
                    $montoEjecutado = $montoAsignado * $porcentajeEjecutado;
                    
                    PresupuestoDet::create([
                        'presupuesto_id' => $presupuesto->id,
                        'renglon_id' => $renglon->id,
                        'monto_asignado' => round($montoAsignado, 2),
                        'monto_ejecutado' => round($montoEjecutado, 2),
                        'descripcion' => 'Asignación mensual ' . $renglon->nombre,
                        'estado' => 1
                    ]);
                }
                
                echo "✅ Presupuesto creado exitosamente:\n";
                echo "   - ID: {$presupuesto->id}\n";
                echo "   - Periodo: Noviembre 2025\n";
                echo "   - Detalles creados: " . $renglones->count() . "\n";
                
            } else {
                echo "❌ No se encontraron renglones en la base de datos.\n";
                echo "   Ejecuta primero: php artisan db:seed --class=RenglonSeeder\n";
            }
            
            // Crear presupuesto adicional para Diciembre 2025
            $presupuestoDic = PresupuestoCab::create([
                'anio' => 2025,
                'mes' => 12,
                'descripcion' => 'Presupuesto Diciembre 2025 - Guatemala',
                'creado_por' => 1,
                'fecha_creacion' => now(),
                'estado' => 1
            ]);

            // Agregar detalles para diciembre con diferentes asignaciones
            foreach ($renglones as $index => $renglon) {
                $porcentajeAsignado = match($index) {
                    0 => 0.9, // 90% del primer renglón
                    1 => 0.7, // 70% del segundo renglón
                    2 => 0.8, // 80% del tercer renglón
                    default => 0.6 // 60% para otros renglones
                };
                
                $montoAsignado = $renglon->monto_inicial * $porcentajeAsignado;
                
                // Menos ejecución en diciembre (nuevo mes)
                $porcentajeEjecutado = rand(0, 15) / 100;
                $montoEjecutado = $montoAsignado * $porcentajeEjecutado;
                
                PresupuestoDet::create([
                    'presupuesto_id' => $presupuestoDic->id,
                    'renglon_id' => $renglon->id,
                    'monto_asignado' => round($montoAsignado, 2),
                    'monto_ejecutado' => round($montoEjecutado, 2),
                    'descripcion' => 'Asignación mensual ' . $renglon->nombre,
                    'estado' => 1
                ]);
            }
            
            echo "✅ Presupuesto adicional creado:\n";
            echo "   - ID: {$presupuestoDic->id}\n";
            echo "   - Periodo: Diciembre 2025\n";
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollback();
            echo "❌ Error al crear presupuestos: " . $e->getMessage() . "\n";
        }
    }
}