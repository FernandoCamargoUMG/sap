<?php

namespace App\Http\Controllers;

use App\Models\Cur;
use App\Models\Renglon;
use App\Models\Bitacora;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CurController extends Controller
{
    /**
     * Listar todos los compromisos (CUR)
     */
    public function index()
    {
        $curs = Cur::with(['renglon', 'proveedor', 'usuario', 'documento', 'documentos' => function ($query) {
                $query->where('estado', 1)->with('usuario')->orderBy('created_at', 'desc');
            }])
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
            'proveedor_id' => 'required|exists:proveedores,id',
            'numero_cur' => 'required|string|max:50|unique:cur,numero_cur',
            'descripcion' => 'required|string|max:500',
            'monto' => 'required|numeric|min:0.01',
            'fecha_compromiso' => 'nullable|date',
            'estado' => 'nullable|integer|in:0,1',
            'documento' => 'nullable|file|mimes:pdf|max:10240' // 10MB máximo
        ]);

        DB::beginTransaction();
        
        try {
            // Subir documento si existe (igual que facturas)
            $documento_id = null;
            if ($request->hasFile('documento')) {
                $archivo = $request->file('documento');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $rutaArchivo = $archivo->storeAs('cur', $nombreArchivo, 'public');

                $documento = Documento::create([
                    'documentable_type' => Cur::class,
                    'documentable_id' => 0, // Se actualizará después
                    'nombre_archivo' => $nombreArchivo,
                    'ruta_archivo' => $rutaArchivo,
                    'tipo_archivo' => $archivo->getClientOriginalExtension(),
                    'tamanio' => $archivo->getSize(),
                    'descripcion' => 'CUR PDF',
                    'usuario_id' => Auth::id() ?? 1,
                    'estado' => 1
                ]);

                $documento_id = $documento->id;
            }

            // Obtener información del renglón solo para el registro de bitácora
            $renglon = Renglon::find($validated['renglon_id']);

            // Crear compromiso sin validar saldo
            $cur = Cur::create([
                'renglon_id' => $validated['renglon_id'],
                'proveedor_id' => $validated['proveedor_id'],
                'usuario_id' => Auth::id() ?? 1,
                'numero_cur' => $validated['numero_cur'],
                'descripcion' => $validated['descripcion'],
                'monto' => $validated['monto'],
                'fecha_compromiso' => $validated['fecha_compromiso'] ?? now(),
                'estado' => $validated['estado'] ?? 1,
                'documento_id' => $documento_id
            ]);

            // Actualizar el documento con el ID del CUR (igual que facturas)
            if ($documento_id) {
                $documento->documentable_id = $cur->id;
                $documento->save();
            }

            // Registrar en bitácora
            if (Auth::id()) {
                Bitacora::registrar(
                    'cur',
                    $cur->id,
                    'creado',
                    Auth::id(),
                    "CUR {$cur->numero_cur} creado por monto {$cur->monto} en renglón {$renglon->codigo}"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compromiso (CUR) creado exitosamente',
                'data' => $cur->load(['renglon', 'proveedor', 'usuario', 'documento'])
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
        $cur = Cur::with(['renglon', 'proveedor', 'usuario', 'documento', 'documentos' => function ($query) {
                $query->where('estado', 1)->with('usuario')->orderBy('created_at', 'desc');
            }])->find($id);

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
     * Descargar documento del CUR (igual que facturas)
     */
    public function descargarDocumento($id)
    {
        $cur = Cur::with('documento')->find($id);

        if (!$cur || !$cur->documento) {
            return response()->json([
                'success' => false,
                'message' => 'Documento no encontrado'
            ], 404);
        }

        $rutaArchivo = $cur->documento->ruta_archivo;

        if (!Storage::disk('public')->exists($rutaArchivo)) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo no encontrado en el servidor'
            ], 404);
        }

        return response()->download(storage_path('app/public/' . $rutaArchivo), $cur->documento->nombre_archivo);
    }

    /**
     * Subir o actualizar documento de un CUR
     */
    public function uploadDocument(Request $request, $id)
    {
        $request->validate([
            'documento' => 'required|file|mimes:pdf|max:10240' // 10MB máximo
        ]);

        DB::beginTransaction();
        
        try {
            $cur = Cur::findOrFail($id);
            
            // Si ya tiene un documento, eliminarlo
            if ($cur->documento_id && $cur->documento) {
                // Eliminar archivo físico
                if (Storage::disk('public')->exists($cur->documento->ruta_archivo)) {
                    Storage::disk('public')->delete($cur->documento->ruta_archivo);
                }
                // Eliminar registro de documento
                $cur->documento->delete();
            }

            // Subir nuevo documento
            $archivo = $request->file('documento');
            $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
            $rutaArchivo = $archivo->storeAs('cur', $nombreArchivo, 'public');

            $documento = Documento::create([
                'documentable_type' => Cur::class,
                'documentable_id' => $cur->id,
                'nombre_archivo' => $nombreArchivo,
                'ruta_archivo' => $rutaArchivo,
                'tipo_archivo' => $archivo->getClientOriginalExtension(),
                'tamanio' => $archivo->getSize(),
                'descripcion' => 'CUR PDF',
                'usuario_id' => Auth::id() ?? 1,
                'estado' => 1
            ]);

            // Actualizar el CUR con el nuevo documento
            $cur->documento_id = $documento->id;
            $cur->save();

            // Registrar en bitácora
            if (Auth::id()) {
                Bitacora::registrar(
                    'cur',
                    $cur->id,
                    'documento_actualizado',
                    Auth::id(),
                    "Documento actualizado para CUR {$cur->numero_cur}"
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documento subido correctamente',
                'data' => $cur->load(['renglon', 'proveedor', 'usuario', 'documento'])
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
     * Eliminar documento de un CUR
     */
    public function deleteDocument($id)
    {
        DB::beginTransaction();
        
        try {
            $cur = Cur::with('documento')->findOrFail($id);
            
            if (!$cur->documento_id || !$cur->documento) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay documento para eliminar'
                ], 404);
            }

            // Eliminar archivo físico
            if (Storage::disk('public')->exists($cur->documento->ruta_archivo)) {
                Storage::disk('public')->delete($cur->documento->ruta_archivo);
            }

            // Eliminar registro de documento
            $cur->documento->delete();

            // Actualizar el CUR
            $cur->documento_id = null;
            $cur->save();

            // Registrar en bitácora
            if (Auth::id()) {
                Bitacora::registrar(
                    'cur',
                    $cur->id,
                    'documento_eliminado',
                    Auth::id(),
                    "Documento eliminado del CUR {$cur->numero_cur}"
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
     * Agregar documentos a un CUR existente
     */
    public function addDocuments(Request $request, $id)
    {
        $request->validate([
            'documentos' => 'required|array',
            'documentos.*' => 'file|mimes:pdf|max:10240'
        ]);

        DB::beginTransaction();
        
        try {
            $cur = Cur::findOrFail($id);
            
            foreach ($request->file('documentos') as $archivo) {
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $rutaArchivo = $archivo->storeAs('cur', $nombreArchivo, 'public');

                Documento::create([
                    'documentable_type' => Cur::class,
                    'documentable_id' => $cur->id,
                    'nombre_archivo' => $nombreArchivo,
                    'ruta_archivo' => $rutaArchivo,
                    'tipo_archivo' => $archivo->getClientOriginalExtension(),
                    'tamanio' => $archivo->getSize(),
                    'descripcion' => 'Documento adicional CUR',
                    'usuario_id' => Auth::id() ?? 1,
                    'estado' => 1
                ]);
            }

            DB::commit();

            // Cargar el CUR con todos sus documentos
            $cur->load(['renglon', 'proveedor', 'usuario', 'documentos' => function ($query) {
                $query->where('estado', 1)->with('usuario')->orderBy('created_at', 'desc');
            }]);

            return response()->json([
                'success' => true,
                'message' => 'Documentos agregados correctamente',
                'data' => $cur
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar documentos: ' . $e->getMessage()
            ], 500);
        }
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

            // Anular compromiso
            $cur->estado = 0;
            $cur->save();

            // Registrar en bitácora
            if (Auth::id()) {
                Bitacora::registrar(
                    'cur',
                    $cur->id,
                    'anulado',
                    Auth::id(),
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
