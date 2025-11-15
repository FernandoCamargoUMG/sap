<?php

namespace App\Http\Controllers;

use App\Models\FacturaCab;
use App\Models\FacturaDet;
use App\Models\Proveedor;
use App\Models\Renglon;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class FacturaController extends Controller
{
    /**
     * Listar todas las facturas
     */
    public function index(Request $request)
    {
        try {
            $query = FacturaCab::with(['proveedor', 'documento'])
                ->orderBy('created_at', 'desc');

            // Filtros
            if ($request->has('tipo') && $request->tipo !== '') {
                $query->where('tipo', $request->tipo);
            }

            if ($request->has('proveedor_id') && $request->proveedor_id !== '') {
                $query->where('proveedor_id', $request->proveedor_id);
            }

            if ($request->has('fecha_desde') && $request->fecha_desde !== '') {
                $query->whereDate('fecha', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta') && $request->fecha_hasta !== '') {
                $query->whereDate('fecha', '<=', $request->fecha_hasta);
            }

            if ($request->has('folio') && $request->folio !== '') {
                $query->where('folio', 'like', '%' . $request->folio . '%');
            }

            // Solo facturas activas por defecto
            $query->where('estado', 1);

            $facturas = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $facturas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las facturas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva factura
     */
    public function store(Request $request)
    {
        // Procesar detalles si vienen como JSON string
        $requestData = $request->all();
        if (isset($requestData['detalles']) && is_string($requestData['detalles'])) {
            $requestData['detalles'] = json_decode($requestData['detalles'], true);
        }

        $validator = Validator::make($requestData, [
            'proveedor_id' => 'required|exists:proveedores,id',
            'folio' => 'required|string|max:50|unique:factura_cab,folio',
            'fecha' => 'required|date',
            'tipo' => 'required|in:inventario,bodega,despensa',
            'detalles' => 'required|array|min:1',
            'detalles.*.renglon_id' => 'required|exists:renglones,id',
            'detalles.*.item' => 'required|string|max:100',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'documento' => 'nullable|file|mimes:pdf|max:10240' // 10MB máximo
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Subir documento si existe
            $documento_id = null;
            if ($request->hasFile('documento')) {
                $archivo = $request->file('documento');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $rutaArchivo = $archivo->storeAs('facturas', $nombreArchivo, 'public');

                $documento = Documento::create([
                    'documentable_type' => FacturaCab::class,
                    'documentable_id' => 0, // Se actualizará después
                    'nombre_archivo' => $nombreArchivo,
                    'ruta_archivo' => $rutaArchivo,
                    'tipo_archivo' => $archivo->getClientOriginalExtension(),
                    'tamanio' => $archivo->getSize(),
                    'descripcion' => 'Factura PDF',
                    'usuario_id' => Auth::id() ?? 1, // ID del usuario autenticado
                    'estado' => 1
                ]);

                $documento_id = $documento->id;
            }

            // Crear factura
            $factura = FacturaCab::create([
                'proveedor_id' => $requestData['proveedor_id'],
                'folio' => $requestData['folio'],
                'fecha' => $requestData['fecha'],
                'total' => 0, // Se calculará automáticamente
                'documento_id' => $documento_id,
                'tipo' => $requestData['tipo'],
                'estado' => 1
            ]);

            // Actualizar el documento con el ID de la factura
            if ($documento_id) {
                $documento->documentable_id = $factura->id;
                $documento->save();
            }

            // Crear detalles
            foreach ($requestData['detalles'] as $detalle) {
                FacturaDet::create([
                    'factura_id' => $factura->id,
                    'renglon_id' => $detalle['renglon_id'],
                    'item' => $detalle['item'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'estado' => 1
                ]);
            }

            // Recargar la factura con sus relaciones
            $factura->load(['proveedor', 'documento', 'detalles.renglon']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Factura creada exitosamente',
                'data' => $factura
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // Eliminar archivo si se subió
            if (isset($rutaArchivo)) {
                Storage::disk('public')->delete($rutaArchivo);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una factura específica
     */
    public function show($id)
    {
        try {
            $factura = FacturaCab::with([
                'proveedor',
                'documento',
                'detalles.renglon'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $factura
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una factura
     */
    public function update(Request $request, $id)
    {
        // Procesar detalles si vienen como JSON string
        $requestData = $request->all();
        if (isset($requestData['detalles']) && is_string($requestData['detalles'])) {
            $requestData['detalles'] = json_decode($requestData['detalles'], true);
        }

        $validator = Validator::make($requestData, [
            'proveedor_id' => 'required|exists:proveedores,id',
            'folio' => 'required|string|max:50|unique:factura_cab,folio,' . $id,
            'fecha' => 'required|date',
            'tipo' => 'required|in:inventario,bodega,despensa',
            'detalles' => 'required|array|min:1',
            'detalles.*.renglon_id' => 'required|exists:renglones,id',
            'detalles.*.item' => 'required|string|max:100',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'documento' => 'nullable|file|mimes:pdf|max:10240',
            'eliminar_documento' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $factura = FacturaCab::findOrFail($id);

            // Manejo del documento
            $documento_id = $factura->documento_id;
            
            // Si se solicita eliminar el documento actual
            if ($request->input('eliminar_documento') === true || $request->input('eliminar_documento') === 'true') {
                if ($factura->documento) {
                    Storage::disk('public')->delete($factura->documento->ruta_archivo);
                    $factura->documento->delete();
                }
                $documento_id = null;
            }
            
            // Si se sube un nuevo documento
            if ($request->hasFile('documento')) {
                // Eliminar documento anterior si existe
                if ($factura->documento) {
                    Storage::disk('public')->delete($factura->documento->ruta_archivo);
                    $factura->documento->delete();
                }

                $archivo = $request->file('documento');
                $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                $rutaArchivo = $archivo->storeAs('facturas', $nombreArchivo, 'public');

                $documento = Documento::create([
                    'documentable_type' => FacturaCab::class,
                    'documentable_id' => $factura->id,
                    'nombre_archivo' => $nombreArchivo,
                    'ruta_archivo' => $rutaArchivo,
                    'tipo_archivo' => $archivo->getClientOriginalExtension(),
                    'tamanio' => $archivo->getSize(),
                    'descripcion' => 'Factura PDF',
                    'usuario_id' => Auth::id() ?? 1, // ID del usuario autenticado
                    'estado' => 1
                ]);

                $documento_id = $documento->id;
            }

            // Actualizar factura
            $factura->update([
                'proveedor_id' => $requestData['proveedor_id'],
                'folio' => $requestData['folio'],
                'fecha' => $requestData['fecha'],
                'tipo' => $requestData['tipo'],
                'documento_id' => $documento_id
            ]);

            // Eliminar detalles existentes
            $factura->detalles()->delete();

            // Crear nuevos detalles
            foreach ($requestData['detalles'] as $detalle) {
                FacturaDet::create([
                    'factura_id' => $factura->id,
                    'renglon_id' => $detalle['renglon_id'],
                    'item' => $detalle['item'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'estado' => 1
                ]);
            }

            // Recargar la factura con sus relaciones
            $factura->load(['proveedor', 'documento', 'detalles.renglon']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Factura actualizada exitosamente',
                'data' => $factura
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una factura (soft delete)
     */
    public function destroy($id)
    {
        try {
            $factura = FacturaCab::findOrFail($id);
            
            // Cambiar estado en lugar de eliminar
            $factura->update(['estado' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Factura eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener proveedores activos
     */
    public function getProveedores()
    {
        try {
            $proveedores = Proveedor::where('estado', 1)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'nit']);

            return response()->json([
                'success' => true,
                'data' => $proveedores
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proveedores: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener renglones activos
     */
    public function getRenglones()
    {
        try {
            $renglones = Renglon::where('estado', 1)
                ->orderBy('codigo')
                ->get(['id', 'codigo', 'nombre']);

            return response()->json([
                'success' => true,
                'data' => $renglones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener renglones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar documento de la factura
     */
    public function descargarDocumento($id)
    {
        try {
            $factura = FacturaCab::with('documento')->findOrFail($id);

            if (!$factura->documento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta factura no tiene documento adjunto'
                ], 404);
            }

            $rutaCompleta = storage_path('app/public/' . $factura->documento->ruta_archivo);

            if (!file_exists($rutaCompleta)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no existe en el servidor'
                ], 404);
            }

            return response()->download($rutaCompleta, $factura->documento->nombre_archivo);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar documento de la factura
     */
    public function eliminarDocumento($id)
    {
        try {
            $factura = FacturaCab::with('documento')->findOrFail($id);

            if (!$factura->documento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta factura no tiene documento adjunto'
                ], 404);
            }

            // Eliminar archivo físico
            Storage::disk('public')->delete($factura->documento->ruta_archivo);

            // Eliminar registro del documento
            $factura->documento->delete();

            // Actualizar factura para quitar la referencia al documento
            $factura->update(['documento_id' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener facturas con detalles para reportes
     */
    public function facturasParaReporte(Request $request)
    {
        try {
            $query = FacturaCab::with(['proveedor', 'detalles.renglon'])
                ->orderBy('fecha', 'desc');

            // Filtros de fecha
            if ($request->has('fecha_inicio') && $request->fecha_inicio !== '') {
                $query->whereDate('fecha', '>=', $request->fecha_inicio);
            }

            if ($request->has('fecha_fin') && $request->fecha_fin !== '') {
                $query->whereDate('fecha', '<=', $request->fecha_fin);
            }

            // Filtros adicionales
            if ($request->has('tipo') && $request->tipo !== '') {
                $query->where('tipo', $request->tipo);
            }

            if ($request->has('proveedor_id') && $request->proveedor_id !== '') {
                $query->where('proveedor_id', $request->proveedor_id);
            }

            $facturas = $query->get();

            return response()->json([
                'success' => true,
                'data' => $facturas,
                'total_facturas' => $facturas->count(),
                'total_general' => $facturas->sum('total')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facturas para reporte: ' . $e->getMessage()
            ], 500);
        }
    }
}
