<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Bitacora;
use App\Http\Requests\UsuarioRequest;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    /**
     * Listar todos los usuarios activos
     */
    public function index()
    {
        $usuarios = Usuario::with('rol')
            ->where('estado', 1)
            ->whereNull('deleted_at')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $usuarios
        ], 200);
    }

    /**
     * Crear nuevo usuario
     */
    public function store(UsuarioRequest $request)
    {
        $usuario = Usuario::create($request->validated());

        // Registrar en bitácora (solo si hay sesión activa)
        if (session('usuario_id')) {
            Bitacora::registrar(
                'usuarios',
                $usuario->id,
                'creado',
                session('usuario_id'),
                "Usuario {$usuario->nombre} creado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => $usuario->load('rol')
        ], 201);
    }

    /**
     * Mostrar un usuario específico
     */
    public function show($id)
    {
        $usuario = Usuario::with('rol')->find($id);

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $usuario
        ], 200);
    }

    /**
     * Actualizar usuario
     */
    public function update(UsuarioRequest $request, $id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $data = $request->validated();
        
        // Si no se envía contraseña, no actualizarla
        if (empty($data['contraseña'])) {
            unset($data['contraseña']);
        }

        $usuario->update($data);

        // Registrar en bitácora (solo si hay sesión activa)
        if (session('usuario_id')) {
            Bitacora::registrar(
                'usuarios',
                $usuario->id,
                'modificado',
                session('usuario_id'),
                "Usuario {$usuario->nombre} actualizado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente',
            'data' => $usuario->load('rol')
        ], 200);
    }

    /**
     * Soft delete - Eliminar usuario (lógico)
     */
    public function destroy($id)
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $usuario->estado = 0;
        $usuario->save();
        $usuario->delete(); // Soft delete

        // Registrar en bitácora (solo si hay sesión activa)
        if (session('usuario_id')) {
            Bitacora::registrar(
                'usuarios',
                $usuario->id,
                'eliminado',
                session('usuario_id'),
                "Usuario {$usuario->nombre} eliminado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado exitosamente'
        ], 200);
    }

    /**
     * Restaurar usuario eliminado
     */
    public function restore($id)
    {
        $usuario = Usuario::withTrashed()->find($id);

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        $usuario->restore();
        $usuario->estado = 1;
        $usuario->save();

        // Registrar en bitácora (solo si hay sesión activa)
        if (session('usuario_id')) {
            Bitacora::registrar(
                'usuarios',
                $usuario->id,
                'restaurado',
                session('usuario_id'),
                "Usuario {$usuario->nombre} restaurado"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario restaurado exitosamente',
            'data' => $usuario->load('rol')
        ], 200);
    }

    /**
     * Listar usuarios eliminados (soft deleted)
     */
    public function deleted()
    {
        $usuarios = Usuario::onlyTrashed()
            ->with('rol')
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $usuarios
        ], 200);
    }
}
