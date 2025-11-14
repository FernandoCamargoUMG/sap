<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Renglon;
use App\Models\PresupuestoCab;
use App\Models\PresupuestoDet;
use App\Models\MovimientoCab;
use App\Models\MovimientoDet;
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
            MovimientoDet::query()->delete();
            MovimientoCab::query()->delete();
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

            // Obtener los renglones existentes (ahora son solo clasificadores)
            $renglones = Renglon::where('estado', 1)->get();
            
            if ($renglones->count() > 0) {
                // Crear detalles de presupuesto con asignaciones iniciales
                $asignaciones = [
                    120000.00, // Sueldos y Salarios
                    85000.00,  // Servicios Técnicos
                    45000.00,  // Combustibles
                    25000.00,  // Útiles de Oficina
                    95000.00   // Equipo de Cómputo
                ];
                
                foreach ($renglones as $index => $renglon) {
                    $montoAsignado = $asignaciones[$index] ?? 50000.00;
                    
                    $presupuestoDet = PresupuestoDet::create([
                        'presupuesto_id' => $presupuesto->id,
                        'renglon_id' => $renglon->id,
                        'monto_asignado' => $montoAsignado,
                        'descripcion' => 'Asignación presupuestaria ' . $renglon->nombre,
                        'estado' => 1
                    ]);
                    
                    // Crear algunos movimientos de ejemplo para simular ejecución
                    $this->crearMovimientos($presupuestoDet, $index);
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
            $asignacionesDic = [
                135000.00, // Sueldos y Salarios (incremento fin de año)
                70000.00,  // Servicios Técnicos
                40000.00,  // Combustibles
                30000.00,  // Útiles de Oficina
                80000.00   // Equipo de Cómputo
            ];
            
            foreach ($renglones as $index => $renglon) {
                $montoAsignado = $asignacionesDic[$index] ?? 45000.00;
                
                $presupuestoDet = PresupuestoDet::create([
                    'presupuesto_id' => $presupuestoDic->id,
                    'renglon_id' => $renglon->id,
                    'monto_asignado' => $montoAsignado,
                    'descripcion' => 'Asignación presupuestaria ' . $renglon->nombre,
                    'estado' => 1
                ]);
                
                // Menos movimientos en diciembre (mes más nuevo)
                if ($index < 3) {
                    $this->crearMovimientos($presupuestoDet, $index, 1);
                }
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
    
    /**
     * Crear movimientos de ejemplo para simular ejecución
     */
    private function crearMovimientos(PresupuestoDet $presupuestoDet, int $index, int $cantidad = 3): void
    {
        $tiposMovimiento = ['ejecucion_presupuestaria', 'ajuste', 'traslado'];
        $proveedores = ['Proveedor A', 'Proveedor B', 'Proveedor C', 'Interno'];
        
        for ($i = 0; $i < $cantidad; $i++) {
            // Crear cabecera de movimiento
            $movimientoCab = MovimientoCab::create([
                'tipo_movimiento' => $tiposMovimiento[$i % 3],
                'fecha' => now()->subDays(rand(5, 25)),
                'numero_documento' => 'DOC-' . str_pad(($index * 100) + $i + 1, 6, '0', STR_PAD_LEFT),
                'proveedor' => $proveedores[$i % 4],
                'descripcion' => 'Movimiento de prueba ' . ($i + 1),
                'presupuesto_cab_id' => $presupuestoDet->presupuesto_id,
                'usuario_id' => 1,
                'estado' => 1
            ]);
            
            // Calcular monto del detalle basado en porcentaje de la asignación
            $porcentajes = [0.15, 0.25, 0.10]; // 15%, 25%, 10%
            $montoDetalle = $presupuestoDet->monto_asignado * $porcentajes[$i % 3];
            
            // Crear detalle de movimiento
            MovimientoDet::create([
                'movimiento_id' => $movimientoCab->id,
                'renglon_id' => $presupuestoDet->renglon_id,
                'presupuesto_det_id' => $presupuestoDet->id,
                'monto' => $montoDetalle,
                'descripcion_detalle' => 'Detalle específico del gasto - ' . $presupuestoDet->renglon->nombre,
                'estado' => 1
            ]);
        }
    }
}