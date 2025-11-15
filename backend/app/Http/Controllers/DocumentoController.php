<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    /**
     * Listar todos los documentos
     */
    public function index(Request $request)
    {
        $query = Documento::with('usuario')
            ->where('estado', 1);

        // Filtrar por tipo de entidad si se proporciona
        if ($request->has('documentable_type')) {
            $query->where('documentable_type', $request->documentable_type);
        }

        // Filtrar por ID de entidad si se proporciona
        if ($request->has('documentable_id')) {
            $query->where('documentable_id', $request->documentable_id);
        }

        $documentos = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $documentos
        ], 200);
    }

    /**
     * Subir nuevo documento
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'archivo' => 'required|file|mimes:pdf|max:10240', // 10MB máximo
                'documentable_type' => 'required|string|max:50',
                'documentable_id' => 'required|integer',
                'descripcion' => 'nullable|string|max:500',
                'usuario_id' => 'required|integer'
            ]);

            $archivo = $request->file('archivo');
            
            // Generar nombre único para el archivo
            $nombreOriginal = $archivo->getClientOriginalName();
            $extension = $archivo->getClientOriginalExtension();
            $nombreUnico = uniqid() . '_' . time() . '.' . $extension;
            
            // Definir la carpeta según el tipo de documento
            $carpeta = $this->getCarpetaPorTipo($validated['documentable_type']);
            $rutaCompleta = $carpeta . '/' . $nombreUnico;
            
            // Subir archivo
            $rutaArchivo = $archivo->storeAs('public/' . $rutaCompleta);
            
            // Crear registro en base de datos
            $documento = Documento::create([
                'documentable_type' => $validated['documentable_type'],
                'documentable_id' => $validated['documentable_id'],
                'usuario_id' => $validated['usuario_id'],
                'nombre_archivo' => $nombreOriginal,
                'ruta_archivo' => $rutaCompleta,
                'tipo_archivo' => $extension,
                'tamanio' => $archivo->getSize(),
                'descripcion' => $validated['descripcion'] ?? null,
                'estado' => 1
            ]);

            // Registrar en bitácora
            Bitacora::registrar(
                'documentos',
                $documento->id,
                'creado',
                $validated['usuario_id'],
                "Documento {$documento->nombre_archivo} subido para {$documento->documentable_type}:{$documento->documentable_id}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Documento subido exitosamente',
                'data' => [
                    'documento' => $documento->load('usuario'),
                    'url_descarga' => Storage::url($rutaArchivo)
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un documento específico
     */
    public function show($id)
    {
        $documento = Documento::with('usuario')->find($id);

        if (!$documento) {
            return response()->json([
                'success' => false,
                'message' => 'Documento no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $documento
        ], 200);
    }

    /**
     * Descargar documento
     */
    public function download($id)
    {
        $documento = Documento::find($id);

        if (!$documento) {
            return response()->json([
                'success' => false,
                'message' => 'Documento no encontrado'
            ], 404);
        }

        // Verificar si el archivo existe
        if (!Storage::exists($documento->ruta_archivo)) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo no encontrado en el servidor'
            ], 404);
        }

        return Storage::download($documento->ruta_archivo, $documento->nombre_archivo);
    }

    /**
     * Actualizar información del documento
     */
    public function update(Request $request, $id)
    {
        $documento = Documento::find($id);

        if (!$documento) {
            return response()->json([
                'success' => false,
                'message' => 'Documento no encontrado'
            ], 404);
        }

        $validated = $request->validate([
            'nombre_archivo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'estado' => 'nullable|integer|in:0,1'
        ]);

        $documento->update($validated);

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'documentos',
                $documento->id,
                'modificado',
                session('usuario_id'),
                "Documento {$documento->nombre_archivo} actualizado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Documento actualizado exitosamente',
            'data' => $documento->load('usuario')
        ], 200);
    }

    /**
     * Eliminar documento (soft delete)
     */
    public function destroy($id)
    {
        $documento = Documento::find($id);

        if (!$documento) {
            return response()->json([
                'success' => false,
                'message' => 'Documento no encontrado'
            ], 404);
        }

        $documento->estado = 0;
        $documento->save();
        $documento->delete();

        // Registrar en bitácora
        if (session('usuario_id')) {
            Bitacora::registrar(
                'documentos',
                $documento->id,
                'eliminado',
                session('usuario_id'),
                "Documento {$documento->nombre_archivo} eliminado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Documento eliminado exitosamente'
        ], 200);
    }

    /**
     * Obtener documentos de una entidad específica
     */
    public function byEntity($documentableType, $documentableId)
    {
        $documentos = Documento::with('usuario')
            ->where('documentable_type', $documentableType)
            ->where('documentable_id', $documentableId)
            ->where('estado', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        // Agregar URL de descarga a cada documento
        $documentos->map(function ($documento) {
            if ($documento->ruta_archivo) {
                $documento->url_descarga = Storage::url('public/' . $documento->ruta_archivo);
            }
            return $documento;
        });

        return response()->json([
            'success' => true,
            'data' => $documentos
        ], 200);
    }

    /**
     * Obtener carpeta por tipo de entidad
     */
    private function getCarpetaPorTipo($tipo)
    {
        switch ($tipo) {
            case 'App\\Models\\PresupuestoCab':
                return 'documentos/presupuestos';
            case 'App\\Models\\MovimientoCab':
                return 'documentos/movimientos';
            case 'App\\Models\\FacturaCab':
                return 'documentos/facturas';
            case 'App\\Models\\Intra':
                return 'documentos/intras';
            case 'App\\Models\\Cur':
                return 'documentos/cur';
            default:
                return 'documentos/otros';
        }
    }
}
