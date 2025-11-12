<?php

namespace App\Http\Controllers;

use App\Models\Cur;
use App\Models\Renglon;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurController extends Controller
{
    /**
     * Listar todos los compromisos (CUR)
     */
    public function index()
    {
        $curs = Cur::with(['renglon', 'usuario'])
            ->where('estado', 1)
            ->orderBy('fecha_compromiso', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $curs
        ], 200);
    }

    /**
     * Crear nuevo compromiso
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'renglon_id' => 'required|exists:renglones,id',
            'numero_cur' => 'required|string|max:50|unique:cur,numero_cur',
            'descripcion' => 'required|string|max:500',
            'monto' => 'required|numeric|min:0.01',
            'fecha_compromiso' => 'nullable|date',
            'estado' => 'nullable|integer|in:0,1'
        ]);

        DB::beginTransaction();
        
        try {
            // Validar saldo disponible en renglón
            $renglon = Renglon::find($validated['renglon_id']);

            if ($renglon->saldo_disponible < $validated['monto']) {
                throw new \Exception("Saldo insuficiente en renglón {$renglon->codigo}. Disponible: {$renglon->saldo_disponible}");
            }

            // Crear compromiso
            $cur = Cur::create([
                'renglon_id' => $validated['renglon_id'],
                'usuario_id' => session('usuario_id'),
                'numero_cur' => $validated['numero_cur'],
                'descripcion' => $validated['descripcion'],
                'monto' => $validated['monto'],
                'fecha_compromiso' => $validated['fecha_compromiso'] ?? now(),
                'estado' => $validated['estado'] ?? 1
            ]);

            // Afectar saldo (comprometer recursos)
            $renglon->saldo_disponible -= $validated['monto'];
            $renglon->save();

            // Registrar en bitácora
            if (session('usuario_id')) {
                Bitacora::registrar(
                    'cur',
                    $cur->id,
                    'creado',
                    session('usuario_id'),
                    "CUR {$cur->numero_cur} creado por monto {$cur->monto} en renglón {$renglon->codigo}"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compromiso (CUR) creado exitosamente',
                'data' => $cur->load(['renglon', 'usuario'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear compromiso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un compromiso específico
     */
    public function show($id)
    {
        $cur = Cur::with(['renglon', 'usuario'])->find($id);

        if (!$cur) {
            return response()->json([
                'success' => false,
                'message' => 'Compromiso no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cur
        ], 200);
    }

    /**
     * Anular compromiso y liberar recursos
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $cur = Cur::with('renglon')->find($id);

            if (!$cur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compromiso no encontrado'
                ], 404);
            }

            // Liberar saldo comprometido
            $renglon = $cur->renglon;
            $renglon->saldo_disponible += $cur->monto;
            $renglon->save();

            // Anular compromiso
            $cur->estado = 0;
            $cur->save();

            // Registrar en bitácora
            if (session('usuario_id')) {
                Bitacora::registrar(
                    'cur',
                    $cur->id,
                    'anulado',
                    session('usuario_id'),
                    "CUR {$cur->numero_cur} anulado, saldo liberado"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compromiso anulado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al anular compromiso: ' . $e->getMessage()
            ], 500);
        }
    }
}
