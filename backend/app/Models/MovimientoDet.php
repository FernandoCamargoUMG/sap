<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimientoDet extends Model
{
    use SoftDeletes;

    protected $table = 'movimiento_det';

    protected $fillable = [
        'movimiento_cab_id',
        'renglon_id',
        'monto',
        'observaciones',
        'estado'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'estado' => 'integer'
    ];

    /**
     * Relación con encabezado del movimiento
     */
    public function movimientoCab()
    {
        return $this->belongsTo(MovimientoCab::class, 'movimiento_cab_id');
    }

    /**
     * Relación con renglón presupuestario
     */
    public function renglon()
    {
        return $this->belongsTo(Renglon::class, 'renglon_id');
    }

    /**
     * Scope para detalles activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Al crear o actualizar, afectar compromisos del renglón
     */
    protected static function booted()
    {
        static::created(function ($detalle) {
            $detalle->renglon->monto_comprometido += $detalle->monto;
            $detalle->renglon->actualizarSaldo();
        });

        static::updated(function ($detalle) {
            if ($detalle->isDirty('monto')) {
                $diferencia = $detalle->monto - $detalle->getOriginal('monto');
                $detalle->renglon->monto_comprometido += $diferencia;
                $detalle->renglon->actualizarSaldo();
            }
        });

        static::deleted(function ($detalle) {
            $detalle->renglon->monto_comprometido -= $detalle->monto;
            $detalle->renglon->actualizarSaldo();
        });
    }
}
