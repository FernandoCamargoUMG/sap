<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

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
     * Almacenar un nuevo documento
     */
    public function store(Request $request)
    {
        try {
            // Decodificar el tipo de entidad que viene codificado del frontend
            $decodedType = urldecode(urldecode($request->documentable_type));
            
            // Validar los datos de entrada
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:pdf|max:10240', // 10MB máximo
                'nombre_documento' => 'required|string',
                'descripcion' => 'nullable|string',
                'documentable_type' => 'required|string',
                'documentable_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Datos de entrada inválidos',
                    'details' => $validator->errors()
                ], 422);
            }

            $file = $request->file('file');
            
            // Obtener tipo de entidad sin namespace para carpeta
            $entityType = last(explode('\\', $decodedType));
            
            // Generar nombre único para el archivo
            $fileName = time() . '_' . Str::random(10) . '.pdf';
            
            // Definir la ruta completa (sin 'public/' para la BD)
            $filePath = "documentos/{$entityType}/{$fileName}";
            
            // Almacenar el archivo en el disco público
            $path = $file->storeAs("documentos/{$entityType}", $fileName, 'public');
            
            if (!$path) {
                return response()->json(['error' => 'Error al almacenar el archivo'], 500);
            }

            // Crear registro en la base de datos
            $documento = Documento::create([
                'nombre_archivo' => $request->nombre_documento, // Campo correcto según migración
                'descripcion' => $request->descripcion,
                'ruta_archivo' => $filePath,
                'tipo_archivo' => $file->getClientOriginalExtension(),
                'tamanio' => $file->getSize(), // Campo correcto según migración
                'documentable_type' => $decodedType,
                'documentable_id' => $request->documentable_id,
                'usuario_id' => Auth::id() ?? 1, // Campo correcto según migración
                'estado' => 1
            ]);

            // Registrar en bitácora
            Bitacora::create([
                'usuario_id' => Auth::id() ?? 1,
                'tabla_afectada' => 'documentos', // Campo correcto según migración
                'registro_id' => $documento->id, // ID del documento creado
                'accion' => 'creado', // Usar valores del enum definido
                'detalle' => "Documento '{$request->nombre_documento}' subido para " . 
                           $entityType . " ID: {$request->documentable_id}"
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento subido exitosamente',
                'documento' => $documento->load('usuario')
            ], 201);

        } catch (Exception $e) {
            Log::error('Error al subir documento:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'error' => 'Error interno del servidor',
                'message' => $e->getMessage()
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

        // Usar el disco público para verificar y descargar
        $rutaArchivo = $documento->ruta_archivo;

        if (!Storage::disk('public')->exists($rutaArchivo)) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo no encontrado en el servidor: ' . $rutaArchivo
            ], 404);
        }

        return Storage::disk('public')->download($rutaArchivo, $documento->nombre_archivo);
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
        // Decodificar el tipo de entidad que viene codificado del frontend
        $decodedType = urldecode(urldecode($documentableType));
        
        $documentos = Documento::with('usuario')
            ->where('documentable_type', $decodedType)
            ->where('documentable_id', $documentableId)
            ->where('estado', 1)
            ->orderBy('created_at', 'desc')
            ->get();

        // Agregar URL de descarga a cada documento
        $documentos->map(function ($documento) {
            if ($documento->ruta_archivo) {
                // Si ya tiene 'public/', no agregarlo de nuevo
                $rutaUrl = str_starts_with($documento->ruta_archivo, 'public/') 
                    ? $documento->ruta_archivo 
                    : 'public/' . $documento->ruta_archivo;
                $documento->url_descarga = Storage::url($rutaUrl);
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
