<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Intra extends Model
{
    use SoftDeletes;

    protected $table = 'intras';

    protected $fillable = [
        'renglon_origen',
        'renglon_destino',
        'monto',
        'fecha',
        'justificacion',
        'usuario_id',
        'estado'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'datetime',
        'estado' => 'integer'
    ];

    /**
     * Relación con renglón origen
     */
    public function renglonOrigen()
    {
        return $this->belongsTo(Renglon::class, 'renglon_origen');
    }

    /**
     * Relación con renglón destino
     */
    public function renglonDestino()
    {
        return $this->belongsTo(Renglon::class, 'renglon_destino');
    }

    /**
     * Relación con usuario que realizó la transferencia
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación polimórfica con documentos
     */
    public function documentos()
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    /**
     * Scope para intras activas
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Obtener el presupuesto activo para un renglón en el año actual
     */
    public static function getPresupuestoActivoPorRenglon($renglonId, $anio = null)
    {
        $anio = $anio ?? date('Y');
        
        return PresupuestoDet::with(['presupuestoCab', 'renglon'])
            ->whereHas('presupuestoCab', function($query) use ($anio) {
                $query->where('anio', $anio)
                      ->where('estado', 1);
            })
            ->where('renglon_id', $renglonId)
            ->where('estado', 1)
            ->first();
    }
}
