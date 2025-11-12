<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login de usuario
     * @param Request $request (correo, contraseña)
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validar datos de entrada
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email',
            'contraseña' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar usuario por correo
        $usuario = Usuario::where('correo', $request->correo)
            ->where('estado', 1)
            ->whereNull('deleted_at')
            ->first();

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        // Verificar contraseña (MD5)
        if (md5($request->contraseña) !== $usuario->contraseña) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        // Guardar datos en sesión
        session([
            'usuario_id' => $usuario->id,
            'usuario_nombre' => $usuario->nombre,
            'usuario_correo' => $usuario->correo,
            'usuario_rol_id' => $usuario->rol_id,
            'usuario_rol' => $usuario->rol->nombre ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'data' => [
                'usuario' => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre,
                    'correo' => $usuario->correo,
                    'rol' => $usuario->rol->nombre ?? null,
                    'rol_id' => $usuario->rol_id,
                ]
            ]
        ], 200);
    }

    /**
     * Obtener información del usuario autenticado
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        if (!session('usuario_id')) {
            return response()->json([
                'success' => false,
                'message' => 'No hay sesión activa'
            ], 401);
        }

        $usuario = Usuario::find(session('usuario_id'));

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'correo' => $usuario->correo,
                'rol' => $usuario->rol->nombre ?? null,
                'rol_id' => $usuario->rol_id,
                'estado' => $usuario->estado,
            ]
        ], 200);
    }

    /**
     * Logout (cerrar sesión)
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        session()->flush();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ], 200);
    }
}
