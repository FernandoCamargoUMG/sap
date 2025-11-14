<?php

namespace App\Http\Controllers;

use App\Models\MovimientoCab;
use App\Models\MovimientoDet;
use App\Models\Renglon;
use App\Models\Bitacora;
use App\Http\Requests\MovimientoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimientoController extends Controller
{
    /**
     * Listar todos los movimientos activos
     */
    public function index()
    {
        $movimientos = MovimientoCab::with(['usuario', 'detalles.renglon'])
            ->activos()
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $movimientos
        ], 200);
    }

    /**
     * Crear nuevo movimiento con afectación de saldos
     */
    public function store(MovimientoRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $data = $request->validated();
            
            // Crear encabezado de movimiento
            $movimientoCab = MovimientoCab::create([
                'usuario_id' => session('usuario_id'),
                'tipo' => $data['tipo_movimiento'],
                'fecha' => $data['fecha_movimiento'] ?? now(),
                'descripcion' => $data['descripcion'] ?? null,
                'estado' => $data['estado'] ?? 1
            ]);

            // Crear detalles y afectar saldos
            if (isset($data['detalles']) && is_array($data['detalles'])) {
                foreach ($data['detalles'] as $detalle) {
                    // Obtener el renglón
                    $renglon = Renglon::find($detalle['renglon_id']);
                    
                    if (!$renglon) {
                        throw new \Exception("Renglón ID {$detalle['renglon_id']} no encontrado");
                    }

                    // Validar saldo disponible según tipo de movimiento
                    if (in_array($data['tipo_movimiento'], ['egreso', 'compromiso', 'devengado'])) {
                        if ($renglon->saldo_disponible < $detalle['monto']) {
                            throw new \Exception("Saldo insuficiente en renglón {$renglon->codigo}. Disponible: {$renglon->saldo_disponible}, Requerido: {$detalle['monto']}");
                        }
                    }

                    // Crear detalle del movimiento
                    MovimientoDet::create([
                        'movimiento_cab_id' => $movimientoCab->id,
                        'renglon_id' => $detalle['renglon_id'],
                        'descripcion' => $detalle['descripcion'],
                        'monto' => $detalle['monto'],
                        'estado' => $detalle['estado'] ?? 1
                    ]);

                    // Afectar saldo del renglón según tipo de movimiento
                    $this->afectarSaldoRenglon($renglon, $data['tipo_movimiento'], $detalle['monto']);
                }
            }

            // Registrar en bitácora
            if (session('usuario_id')) {
                Bitacora::registrar(
                    'movimiento_cab',
                    $movimientoCab->id,
                    'creado',
                    session('usuario_id'),
                    "Movimiento tipo {$movimientoCab->tipo} creado"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento creado exitosamente',
                'data' => $movimientoCab->load(['usuario', 'detalles.renglon'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un movimiento específico con sus detalles
     */
    public function show($id)
    {
        $movimiento = MovimientoCab::with(['usuario', 'detalles.renglon'])->find($id);

        if (!$movimiento) {
            return response()->json([
                'success' => false,
                'message' => 'Movimiento no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $movimiento
        ], 200);
    }

    /**
     * Actualizar movimiento (solo si no ha afectado saldos críticos)
     */
    public function update(MovimientoRequest $request, $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Los movimientos no pueden ser modificados una vez creados por integridad de saldos'
        ], 403);
    }

    /**
     * Anular movimiento y reversar saldos
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $movimientoCab = MovimientoCab::with('detalles')->find($id);

            if (!$movimientoCab) {
                return response()->json([
                    'success' => false,
                    'message' => 'Movimiento no encontrado'
                ], 404);
            }

            // Reversar saldos de cada detalle
            foreach ($movimientoCab->detalles as $detalle) {
                $renglon = Renglon::find($detalle->renglon_id);
                if ($renglon) {
                    $this->reversarSaldoRenglon($renglon, $movimientoCab->tipo, $detalle->monto);
                }
            }

            // Marcar como anulado
            $movimientoCab->estado = 0;
            $movimientoCab->save();
            $movimientoCab->delete();

            // Registrar en bitácora
            if (session('usuario_id')) {
                Bitacora::registrar(
                    'movimiento_cab',
                    $movimientoCab->id,
                    'anulado',
                    session('usuario_id'),
                    "Movimiento tipo {$movimientoCab->tipo} anulado, saldos reversados"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento anulado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al anular movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar movimientos anulados
     */
    public function deleted()
    {
        $movimientos = MovimientoCab::onlyTrashed()
            ->with(['usuario', 'detalles.renglon'])
            ->orderBy('fecha', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $movimientos
        ], 200);
    }

    /**
     * Afectar saldo del renglón según tipo de movimiento
     */
    private function afectarSaldoRenglon(Renglon $renglon, string $tipoMovimiento, float $monto)
    {
        switch ($tipoMovimiento) {
            case 'ampliacion':
            case 'incremento':
                $renglon->presupuesto_vigente += $monto;
                $renglon->saldo_disponible += $monto;
                break;
                
            case 'reduccion':
            case 'decremento':
                $renglon->presupuesto_vigente -= $monto;
                $renglon->saldo_disponible -= $monto;
                break;
                
            case 'compromiso':
            case 'reserva':
                $renglon->saldo_disponible -= $monto;
                break;
                
            case 'devengado':
            case 'egreso':
                $renglon->saldo_disponible -= $monto;
                break;
                
            case 'liberacion':
            case 'reintegro':
                $renglon->saldo_disponible += $monto;
                break;
        }
        
        $renglon->save();
    }

    /**
     * Reversar afectación de saldo del renglón
     */
    private function reversarSaldoRenglon(Renglon $renglon, string $tipoMovimiento, float $monto)
    {
        switch ($tipoMovimiento) {
            case 'ampliacion':
            case 'incremento':
                $renglon->presupuesto_vigente -= $monto;
                $renglon->saldo_disponible -= $monto;
                break;
                
            case 'reduccion':
            case 'decremento':
                $renglon->presupuesto_vigente += $monto;
                $renglon->saldo_disponible += $monto;
                break;
                
            case 'compromiso':
            case 'reserva':
            case 'devengado':
            case 'egreso':
                $renglon->saldo_disponible += $monto;
                break;
                
            case 'liberacion':
            case 'reintegro':
                $renglon->saldo_disponible -= $monto;
                break;
        }
        
        $renglon->save();
    }
}
