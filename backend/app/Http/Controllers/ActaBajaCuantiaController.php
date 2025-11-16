<?php

namespace App\Http\Controllers;

use App\Models\ActaBajaCuantia;
use App\Models\Proveedor;
use App\Models\Bitacora;
use App\Models\Documento;
use App\Http\Requests\ActaBajaCuantiaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ActaBajaCuantiaController extends Controller
{
    /**
     * Listar todas las actas de baja cuantía
     */
    public function index()
    {
        try {
            $actas = ActaBajaCuantia::with([
                'proveedor', 
                'usuario', 
                'documentos' => function ($query) {
                    $query->where('estado', 1)->with('usuario')->orderBy('created_at', 'desc');
                }
            ])
            ->where('estado', 1)
            ->orderBy('fecha_acta', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $actas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las actas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva acta de baja cuantía
     */
    public function store(ActaBajaCuantiaRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $validated = $request->validated();
            
            // Crear el acta
            $acta = ActaBajaCuantia::create([
                'numero_acta' => $validated['numero_acta'],
                'fecha_acta' => $validated['fecha_acta'],
                'proveedor_id' => $validated['proveedor_id'],
                'descripcion_compra' => $validated['descripcion_compra'],
                'monto_total' => $validated['monto_total'],
                'detalle' => $validated['detalle'],
                'contenido_acta' => $validated['contenido_acta'],
                'responsable' => $validated['responsable'],
                'cargo_responsable' => $validated['cargo_responsable'],
                'usuario_id' => Auth::id() ?? 1,
                'estado' => $validated['estado'] ?? 1
            ]);

            // Subir documento si se proporciona
            if ($request->hasFile('documento')) {
                $this->subirDocumento($request->file('documento'), $acta);
            }

            // Registrar en bitácora
            if (Auth::id()) {
                Bitacora::registrar(
                    'actas_baja_cuantia',
                    $acta->id,
                    'creacion',
                    Auth::id(),
                    "Acta de baja cuantía creada: {$acta->numero_acta}"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acta de baja cuantía creada correctamente',
                'data' => $acta->load(['proveedor', 'usuario', 'documentos'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el acta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar acta específica
     */
    public function show($id)
    {
        try {
            $acta = ActaBajaCuantia::with([
                'proveedor', 
                'usuario', 
                'documentos' => function ($query) {
                    $query->where('estado', 1)->with('usuario')->orderBy('created_at', 'desc');
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $acta
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Acta no encontrada'
            ], 404);
        }
    }

    /**
     * Actualizar acta de baja cuantía
     */
    public function update(ActaBajaCuantiaRequest $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $acta = ActaBajaCuantia::findOrFail($id);
            $validated = $request->validated();
            
            $datosAnteriores = $acta->toArray();
            
            $acta->update([
                'numero_acta' => $validated['numero_acta'],
                'fecha_acta' => $validated['fecha_acta'],
                'proveedor_id' => $validated['proveedor_id'],
                'descripcion_compra' => $validated['descripcion_compra'],
                'monto_total' => $validated['monto_total'],
                'detalle' => $validated['detalle'],
                'contenido_acta' => $validated['contenido_acta'],
                'responsable' => $validated['responsable'],
                'cargo_responsable' => $validated['cargo_responsable'],
                'estado' => $validated['estado'] ?? $acta->estado
            ]);

            // Subir nuevo documento si se proporciona
            if ($request->hasFile('documento')) {
                $this->subirDocumento($request->file('documento'), $acta);
            }

            // Registrar en bitácora
            if (Auth::id()) {
                Bitacora::registrar(
                    'actas_baja_cuantia',
                    $acta->id,
                    'actualizacion',
                    Auth::id(),
                    "Acta de baja cuantía actualizada: {$acta->numero_acta}"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acta actualizada correctamente',
                'data' => $acta->load(['proveedor', 'usuario', 'documentos'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el acta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar acta (soft delete)
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        
        try {
            $acta = ActaBajaCuantia::findOrFail($id);
            
            $acta->update(['estado' => 0]);
            $acta->delete();

            // Registrar en bitácora
            if (Auth::id()) {
                Bitacora::registrar(
                    'actas_baja_cuantia',
                    $acta->id,
                    'eliminacion',
                    Auth::id(),
                    "Acta de baja cuantía eliminada: {$acta->numero_acta}"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acta eliminada correctamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el acta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subir documento a un acta
     */
    public function uploadDocument(Request $request, $id)
    {
        $request->validate([
            'documento' => 'required|file|mimes:pdf|max:10240' // 10MB máximo
        ]);

        DB::beginTransaction();
        
        try {
            $acta = ActaBajaCuantia::findOrFail($id);
            
            $documento = $this->subirDocumento($request->file('documento'), $acta);

            // Registrar en bitácora
            if (Auth::id()) {
                Bitacora::registrar(
                    'actas_baja_cuantia',
                    $acta->id,
                    'documento_subido',
                    Auth::id(),
                    "Documento subido para acta {$acta->numero_acta}"
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
     * Descargar documento
     */
    public function downloadDocument($documentoId)
    {
        try {
            $documento = Documento::where('documentable_type', ActaBajaCuantia::class)
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
     * Eliminar documento
     */
    public function deleteDocument($documentoId)
    {
        DB::beginTransaction();
        
        try {
            $documento = Documento::where('documentable_type', ActaBajaCuantia::class)
                ->where('id', $documentoId)
                ->where('estado', 1)
                ->firstOrFail();

            $acta = $documento->documentable;

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
                    'actas_baja_cuantia',
                    $acta->id,
                    'documento_eliminado',
                    Auth::id(),
                    "Documento eliminado del acta {$acta->numero_acta}"
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

    /**
     * Obtener proveedores para el formulario
     */
    public function getProveedores()
    {
        try {
            $proveedores = Proveedor::where('estado', 1)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'nit', 'direccion']);

            return response()->json([
                'success' => true,
                'data' => $proveedores
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proveedores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método privado para subir documentos
     */
    private function subirDocumento($archivo, $acta)
    {
        $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
        $rutaArchivo = $archivo->storeAs('actas_baja_cuantia', $nombreArchivo, 'public');

        return Documento::create([
            'documentable_type' => ActaBajaCuantia::class,
            'documentable_id' => $acta->id,
            'nombre_archivo' => $nombreArchivo,
            'ruta_archivo' => $rutaArchivo,
            'tipo_archivo' => $archivo->getClientOriginalExtension(),
            'tamanio' => $archivo->getSize(),
            'descripcion' => 'Documento de acta de baja cuantía',
            'usuario_id' => Auth::id() ?? 1,
            'estado' => 1
        ]);
    }
}
