<?php

namespace App\Http\Controllers;

use App\Models\Intra;
use App\Models\Renglon;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntraController extends Controller
{
    /**
     * Listar todas las transferencias (intras)
     */
    public function index()
    {
        $intras = Intra::with(['renglonOrigen', 'renglonDestino', 'usuario'])
            ->where('estado', 1)
            ->orderBy('fecha_transferencia', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $intras
        ], 200);
    }

    /**
     * Crear nueva transferencia entre renglones
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'renglon_origen_id' => 'required|exists:renglones,id',
            'renglon_destino_id' => 'required|exists:renglones,id|different:renglon_origen_id',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:500',
            'fecha_transferencia' => 'nullable|date',
            'estado' => 'nullable|integer|in:0,1'
        ]);

        DB::beginTransaction();
        
        try {
            // Validar saldo disponible en renglón origen
            $renglonOrigen = Renglon::find($validated['renglon_origen_id']);
            $renglonDestino = Renglon::find($validated['renglon_destino_id']);

            if ($renglonOrigen->saldo_disponible < $validated['monto']) {
                throw new \Exception("Saldo insuficiente en renglón origen {$renglonOrigen->codigo}. Disponible: {$renglonOrigen->saldo_disponible}");
            }

            // Crear transferencia
            $intra = Intra::create([
                'renglon_origen_id' => $validated['renglon_origen_id'],
                'renglon_destino_id' => $validated['renglon_destino_id'],
                'usuario_id' => session('usuario_id'),
                'monto' => $validated['monto'],
                'descripcion' => $validated['descripcion'] ?? null,
                'fecha_transferencia' => $validated['fecha_transferencia'] ?? now(),
                'estado' => $validated['estado'] ?? 1
            ]);

            // Afectar saldos
            $renglonOrigen->presupuesto_vigente -= $validated['monto'];
            $renglonOrigen->saldo_disponible -= $validated['monto'];
            $renglonOrigen->save();

            $renglonDestino->presupuesto_vigente += $validated['monto'];
            $renglonDestino->saldo_disponible += $validated['monto'];
            $renglonDestino->save();

            // Registrar en bitácora
            if (session('usuario_id')) {
                Bitacora::registrar(
                    'intras',
                    $intra->id,
                    'creado',
                    session('usuario_id'),
                    "Transferencia de {$validated['monto']} desde {$renglonOrigen->codigo} hacia {$renglonDestino->codigo}"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transferencia creada exitosamente',
                'data' => $intra->load(['renglonOrigen', 'renglonDestino', 'usuario'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear transferencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una transferencia específica
     */
    public function show($id)
    {
        $intra = Intra::with(['renglonOrigen', 'renglonDestino', 'usuario'])->find($id);

        if (!$intra) {
            return response()->json([
                'success' => false,
                'message' => 'Transferencia no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $intra
        ], 200);
    }

    /**
     * Anular transferencia y reversar saldos
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $intra = Intra::with(['renglonOrigen', 'renglonDestino'])->find($id);

            if (!$intra) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transferencia no encontrada'
                ], 404);
            }

            // Reversar saldos
            $renglonOrigen = $intra->renglonOrigen;
            $renglonDestino = $intra->renglonDestino;

            $renglonOrigen->presupuesto_vigente += $intra->monto;
            $renglonOrigen->saldo_disponible += $intra->monto;
            $renglonOrigen->save();

            $renglonDestino->presupuesto_vigente -= $intra->monto;
            $renglonDestino->saldo_disponible -= $intra->monto;
            $renglonDestino->save();

            // Anular transferencia
            $intra->estado = 0;
            $intra->save();

            // Registrar en bitácora
            if (session('usuario_id')) {
                Bitacora::registrar(
                    'intras',
                    $intra->id,
                    'anulado',
                    session('usuario_id'),
                    "Transferencia anulada, saldos reversados"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transferencia anulada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al anular transferencia: ' . $e->getMessage()
            ], 500);
        }
    }
}
