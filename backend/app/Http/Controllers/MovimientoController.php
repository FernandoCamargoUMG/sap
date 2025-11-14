<?php

namespace App\Http\Controllers;

use App\Models\MovimientoCab;
use App\Models\MovimientoDet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimientoController extends Controller
{
    /**
     * Listar movimientos con filtros
     */
    public function index(Request $request)
    {
        try {
            $query = MovimientoCab::with(['detalles.renglon', 'presupuestoCab'])
                ->where('estado', 1)
                ->orderBy('fecha', 'desc');

            // Filtros básicos
            if ($request->has('anio')) {
                $query->whereYear('fecha', $request->anio);
            }

            if ($request->has('mes')) {
                $query->whereMonth('fecha', $request->mes);
            }

            $movimientos = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $movimientos
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar movimientos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un movimiento específico
     */
    public function show($id)
    {
        try {
            $movimiento = MovimientoCab::with(['detalles.renglon', 'presupuestoCab'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $movimiento
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Movimiento no encontrado'
            ], 404);
        }
    }
}
