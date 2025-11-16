<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Renglon;
use App\Models\PresupuestoCab;
use App\Models\FacturaCab;
use App\Models\MovimientoCab;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Obtener estadísticas generales para el dashboard
     */
    public function getEstadisticas(): JsonResponse
    {
        try {
            // Contar usuarios activos
            $totalUsuarios = Usuario::where('estado', 1)->count();
            
            // Contar renglones presupuestarios
            $totalRenglones = Renglon::count();
            
            // Contar presupuestos
            $totalPresupuestos = PresupuestoCab::count();
            
            // Calcular total presupuestado - suma de monto_asignado de presupuesto_det
            $totalPresupuestado = DB::table('presupuesto_det')
                ->where('estado', 1)
                ->sum('monto_asignado') ?? 0;
            
            // Contar facturas (pueden ser 0 si no hay)
            $facturasDelMes = FacturaCab::count();
            
            // Calcular total ejecutado - suma de total de factura_cab
            $totalEjecutadoMes = FacturaCab::where('estado', 1)->sum('total') ?? 0;
            
            // Contar movimientos (pueden ser 0 si no hay)
            $movimientosDelMes = MovimientoCab::count();
            
            // Obtener saldo disponible total
            $saldoDisponible = $totalPresupuestado - $totalEjecutadoMes;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'usuarios' => [
                        'total' => $totalUsuarios,
                        'descripcion' => 'Activos en el sistema'
                    ],
                    'renglones' => [
                        'total' => $totalRenglones,
                        'descripcion' => 'Presupuestarios'
                    ],
                    'presupuestos' => [
                        'total' => $totalPresupuestos,
                        'monto_total' => number_format($totalPresupuestado, 2, '.', ','),
                        'descripcion' => 'En ejercicio fiscal ' . date('Y')
                    ],
                    'ejecucion' => [
                        'facturas_mes' => $facturasDelMes,
                        'monto_ejecutado' => number_format($totalEjecutadoMes, 2, '.', ','),
                        'saldo_disponible' => number_format($saldoDisponible, 2, '.', ','),
                        'descripcion' => 'Total del sistema'
                    ],
                    'movimientos' => [
                        'total_mes' => $movimientosDelMes,
                        'descripcion' => 'Total registrados'
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage(),
                'error' => $e->getFile() . ':' . $e->getLine(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Obtener resumen de actividad reciente
     */
    public function getActividadReciente(): JsonResponse
    {
        try {
            // Últimas 5 facturas
            $ultimasFacturas = FacturaCab::with(['proveedor'])
                                        ->whereNull('deleted_at')
                                        ->orderBy('created_at', 'desc')
                                        ->limit(5)
                                        ->get()
                                        ->map(function ($factura) {
                                            return [
                                                'tipo' => 'factura',
                                                'descripcion' => 'Factura ' . $factura->folio,
                                                'proveedor' => $factura->proveedor->nombre ?? null,
                                                'monto' => number_format($factura->total, 2),
                                                'fecha' => $factura->created_at->format('d/m/Y H:i'),
                                                'tipo_movimiento' => null
                                            ];
                                        });

            // Últimos 5 movimientos
            $ultimosMovimientos = MovimientoCab::whereNull('deleted_at')
                                              ->orderBy('created_at', 'desc')
                                              ->limit(5)
                                              ->get()
                                              ->map(function ($movimiento) {
                                                  return [
                                                      'tipo' => 'movimiento',
                                                      'descripcion' => $movimiento->descripcion ?? 'Movimiento ' . $movimiento->tipo_movimiento,
                                                      'tipo_movimiento' => ucfirst(str_replace('_', ' ', $movimiento->tipo_movimiento)),
                                                      'proveedor' => $movimiento->proveedor ?? null,
                                                      'fecha' => $movimiento->created_at->format('d/m/Y H:i'),
                                                      'monto' => null
                                                  ];
                                              });

            // Combinar y ordenar por fecha
            $actividades = collect()
                ->concat($ultimasFacturas)
                ->concat($ultimosMovimientos)
                ->sortByDesc('fecha')
                ->take(10)
                ->values();

            return response()->json([
                'success' => true,
                'data' => $actividades
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener actividad reciente: ' . $e->getMessage()
            ], 500);
        }
    }
}