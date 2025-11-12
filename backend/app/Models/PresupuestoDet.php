<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresupuestoDet extends Model
{
    use SoftDeletes;

    protected $table = 'presupuesto_det';

    protected $fillable = [
        'presupuesto_cab_id',
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
     * Relación con encabezado del presupuesto
     */
    public function presupuestoCab()
    {
        return $this->belongsTo(PresupuestoCab::class, 'presupuesto_cab_id');
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
     * Al crear o actualizar, actualizar el monto del renglón
     */
    protected static function booted()
    {
        static::created(function ($detalle) {
            $detalle->renglon->monto_asignado += $detalle->monto;
            $detalle->renglon->actualizarSaldo();
        });

        static::updated(function ($detalle) {
            if ($detalle->isDirty('monto')) {
                $diferencia = $detalle->monto - $detalle->getOriginal('monto');
                $detalle->renglon->monto_asignado += $diferencia;
                $detalle->renglon->actualizarSaldo();
            }
        });

        static::deleted(function ($detalle) {
            $detalle->renglon->monto_asignado -= $detalle->monto;
            $detalle->renglon->actualizarSaldo();
        });
    }
}
