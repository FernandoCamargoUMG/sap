<?php

namespace App\Http\Controllers;

use App\Models\Intra;
use App\Models\Renglon;
use App\Models\PresupuestoDet;
use App\Models\Bitacora;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IntraController extends Controller
{
    /**
     * Listar todas las transferencias (intras)
     */
    public function index()
    {
        $intras = Intra::with(['renglonOrigen', 'renglonDestino', 'usuario', 'documentos' => function ($query) {
                $query->where('estado', 1)->with('usuario')->orderBy('created_at', 'desc');
            }])
            ->where('estado', 1)
            ->orderBy('fecha', 'desc')
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
            'renglon_origen' => 'required|exists:renglones,id',
            'renglon_destino' => 'required|exists:renglones,id|different:renglon_origen',
            'monto' => 'required|numeric|min:0.01',
            'justificacion' => 'required|string|max:500',
            'fecha' => 'nullable|date',
            'anio' => 'nullable|integer|min:2020|max:2030'
        ]);

        DB::beginTransaction();
        
        try {
            $anio = $validated['anio'] ?? date('Y');
            
            // Obtener presupuestos activos para los renglones en el año especificado
            $presupuestoOrigen = $this->getPresupuestoActivoPorRenglon($validated['renglon_origen'], $anio);
            $presupuestoDestino = $this->getPresupuestoActivoPorRenglon($validated['renglon_destino'], $anio);

            if (!$presupuestoOrigen) {
                throw new \Exception("No existe presupuesto activo para el renglón origen en el año {$anio}");
            }

            if (!$presupuestoDestino) {
                throw new \Exception("No existe presupuesto activo para el renglón destino en el año {$anio}");
            }

            // Validar que el renglón origen tenga suficiente saldo disponible
            $saldoDisponibleOrigen = $presupuestoOrigen->saldo_por_ejecutar;
            
            if ($saldoDisponibleOrigen < $validated['monto']) {
                throw new \Exception("Saldo insuficiente en renglón origen {$presupuestoOrigen->renglon->codigo}. Disponible: Q" . number_format($saldoDisponibleOrigen, 2));
            }

            // Crear transferencia
            $intra = Intra::create([
                'renglon_origen' => $validated['renglon_origen'],
                'renglon_destino' => $validated['renglon_destino'],
                'usuario_id' => Auth::id() ?? 1,
                'monto' => $validated['monto'],
                'justificacion' => $validated['justificacion'],
                'fecha' => $validated['fecha'] ?? now(),
                'estado' => 1
            ]);

            // Transferir presupuesto: restar del origen y sumar al destino
            $presupuestoOrigen->monto_asignado -= $validated['monto'];
            $presupuestoOrigen->save();

            $presupuestoDestino->monto_asignado += $validated['monto'];
            $presupuestoDestino->save();

            // Registrar en bitácora
            if (Auth::id()) {
                Bitacora::registrar(
                    'intras',
                    $intra->id,
                    'creado',
                    Auth::id(),
                    "Transferencia de Q" . number_format($validated['monto'], 2) . " desde renglón {$presupuestoOrigen->renglon->codigo} hacia {$presupuestoDestino->renglon->codigo}. Justificación: {$validated['justificacion']}"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transferencia presupuestaria creada exitosamente',
                'data' => $intra->load(['renglonOrigen', 'renglonDestino', 'usuario']),
                'detalle' => [
                    'renglon_origen' => [
                        'codigo' => $presupuestoOrigen->renglon->codigo,
                        'nombre' => $presupuestoOrigen->renglon->nombre,
                        'presupuesto_anterior' => $presupuestoOrigen->monto_asignado + $validated['monto'],
                        'presupuesto_actual' => $presupuestoOrigen->monto_asignado
                    ],
                    'renglon_destino' => [
                        'codigo' => $presupuestoDestino->renglon->codigo,
                        'nombre' => $presupuestoDestino->renglon->nombre,
                        'presupuesto_anterior' => $presupuestoDestino->monto_asignado - $validated['monto'],
                        'presupuesto_actual' => $presupuestoDestino->monto_asignado
                    ]
                ]
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
     * Obtener presupuesto activo para un renglón en un año específico
     */
    private function getPresupuestoActivoPorRenglon($renglonId, $anio)
    {
        return PresupuestoDet::with(['presupuestoCab', 'renglon'])
            ->whereHas('presupuestoCab', function($query) use ($anio) {
                $query->where('anio', $anio)
                      ->where('estado', 1);
            })
            ->where('renglon_id', $renglonId)
            ->where('estado', 1)
            ->first();
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
     * Anular transferencia y reversar presupuestos
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

            if ($intra->estado == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'La transferencia ya está anulada'
                ], 400);
            }

            // Obtener el año de la transferencia para buscar los presupuestos correctos
            $anio = date('Y', strtotime($intra->fecha));
            
            // Obtener presupuestos activos para reversar la transferencia
            $presupuestoOrigen = $this->getPresupuestoActivoPorRenglon($intra->renglon_origen, $anio);
            $presupuestoDestino = $this->getPresupuestoActivoPorRenglon($intra->renglon_destino, $anio);

            if (!$presupuestoOrigen || !$presupuestoDestino) {
                throw new \Exception("No se pueden encontrar los presupuestos para reversar la transferencia");
            }

            // Reversar transferencia: devolver al origen y quitar del destino
            $presupuestoOrigen->monto_asignado += $intra->monto;
            $presupuestoOrigen->save();

            $presupuestoDestino->monto_asignado -= $intra->monto;
            $presupuestoDestino->save();

            // Anular transferencia
            $intra->estado = 0;
            $intra->save();

            // Registrar en bitácora
            if (Auth::id()) {
                Bitacora::registrar(
                    'intras',
                    $intra->id,
                    'anulado',
                    Auth::id(),
                    "Transferencia anulada, presupuestos reversados. Q" . number_format($intra->monto, 2) . " devuelto a renglón {$intra->renglonOrigen->codigo}"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transferencia anulada y presupuestos reversados exitosamente',
                'detalle' => [
                    'monto_revertido' => $intra->monto,
                    'renglon_origen_restaurado' => $intra->renglonOrigen->codigo,
                    'renglon_destino_reducido' => $intra->renglonDestino->codigo
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al anular transferencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener renglones con presupuesto disponible para transferencias
     */
    public function getRenglonesDisponibles(Request $request)
    {
        $anio = $request->get('anio', date('Y'));
        
        try {
            $renglones = Renglon::with(['presupuestosDetalle' => function($query) use ($anio) {
                $query->whereHas('presupuestoCab', function($q) use ($anio) {
                    $q->where('anio', $anio)->where('estado', 1);
                })->where('estado', 1);
            }])
            ->where('estado', 1)
            ->get()
            ->map(function($renglon) use ($anio) {
                $presupuesto = $renglon->presupuestosDetalle->first();
                
                return [
                    'id' => $renglon->id,
                    'codigo' => $renglon->codigo,
                    'nombre' => $renglon->nombre,
                    'grupo' => $renglon->grupo,
                    'presupuesto_asignado' => $presupuesto ? $presupuesto->monto_asignado : 0,
                    'monto_ejecutado' => $presupuesto ? $presupuesto->monto_ejecutado : 0,
                    'saldo_disponible' => $presupuesto ? $presupuesto->saldo_por_ejecutar : 0,
                    'porcentaje_ejecucion' => $presupuesto ? $presupuesto->porcentaje_ejecucion : 0,
                    'tiene_presupuesto' => $presupuesto ? true : false
                ];
            })
            ->filter(function($item) {
                return $item['tiene_presupuesto']; // Solo renglones con presupuesto
            })
            ->values();

            return response()->json([
                'success' => true,
                'data' => $renglones,
                'anio' => $anio
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener renglones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subir documento a una transferencia
     */
    public function uploadDocument(Request $request, $id)
    {
        $request->validate([
            'documento' => 'required|file|mimes:pdf|max:10240' // 10MB máximo
        ]);

        DB::beginTransaction();
        
        try {
            $intra = Intra::findOrFail($id);
            
            // Subir documento
            $archivo = $request->file('documento');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('intras', $nombreArchivo, 'public');

            $documento = Documento::create([
                'documentable_type' => Intra::class,
                'documentable_id' => $intra->id,
                'nombre_archivo' => $nombreArchivo,
                'ruta_archivo' => $rutaArchivo,
                'tipo_archivo' => $archivo->getClientOriginalExtension(),
                'tamanio' => $archivo->getSize(),
                'descripcion' => 'Documento de transferencia presupuestaria',
                'usuario_id' => Auth::id() ?? 1,
                'estado' => 1
            ]);

            // Registrar en bitácora
            if (Auth::id()) {
                Bitacora::registrar(
                    'intras',
                    $intra->id,
                    'documento_subido',
                    Auth::id(),
                    "Documento subido para transferencia #{$intra->id}"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documento subido correctamente',
                'data' => $documento->load('usuario')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al subir documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar documento de una transferencia
     */
    public function downloadDocument($documentoId)
    {
        try {
            $documento = Documento::where('documentable_type', Intra::class)
                ->where('id', $documentoId)
                ->where('estado', 1)
                ->firstOrFail();
            
            $rutaArchivo = $documento->ruta_archivo;
            
            if (!Storage::disk('public')->exists($rutaArchivo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ], 404);
            }

            return response()->download(storage_path('app/public/' . $rutaArchivo), $documento->nombre_archivo);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar documento de una transferencia
     */
    public function deleteDocument($documentoId)
    {
        DB::beginTransaction();
        
        try {
            $documento = Documento::where('documentable_type', Intra::class)
                ->where('id', $documentoId)
                ->where('estado', 1)
                ->firstOrFail();

            $intra = $documento->documentable;

            // Eliminar archivo físico
            if (Storage::disk('public')->exists($documento->ruta_archivo)) {
                Storage::disk('public')->delete($documento->ruta_archivo);
            }

            // Marcar documento como eliminado
            $documento->estado = 0;
            $documento->save();

            // Registrar en bitácora
            if (Auth::id()) {
                Bitacora::registrar(
                    'intras',
                    $intra->id,
                    'documento_eliminado',
                    Auth::id(),
                    "Documento eliminado de transferencia #{$intra->id}"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado correctamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar documento: ' . $e->getMessage()
            ], 500);
        }
    }
}
