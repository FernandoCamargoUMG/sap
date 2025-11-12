<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    use HasFactory;

    protected $table = 'bitacora';

    protected $fillable = [
        'tabla_afectada',
        'registro_id',
        'accion',
        'usuario_id',
        'fecha_accion',
        'detalle',
    ];

    protected $casts = [
        'fecha_accion' => 'datetime',
    ];

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Método estático para registrar acciones
    public static function registrar($tabla, $registroId, $accion, $usuarioId, $detalle = null)
    {
        return self::create([
            'tabla_afectada' => $tabla,
            'registro_id' => $registroId,
            'accion' => $accion,
            'usuario_id' => $usuarioId,
            'fecha_accion' => now(),
            'detalle' => $detalle,
        ]);
    }
}
