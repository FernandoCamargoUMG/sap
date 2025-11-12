<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacturaDet extends Model
{
    use SoftDeletes;

    protected $table = 'factura_det';

    protected $fillable = [
        'factura_cab_id',
        'renglon_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'monto',
        'estado'
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'monto' => 'decimal:2',
        'estado' => 'integer'
    ];

    /**
     * Relación con encabezado de factura
     */
    public function facturaCab()
    {
        return $this->belongsTo(FacturaCab::class, 'factura_cab_id');
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
     * Al crear o actualizar, calcular monto y afectar renglón
     */
    protected static function booted()
    {
        static::creating(function ($detalle) {
            $detalle->monto = $detalle->cantidad * $detalle->precio_unitario;
        });

        static::created(function ($detalle) {
            $detalle->renglon->monto_ejecutado += $detalle->monto;
            $detalle->renglon->actualizarSaldo();
        });

        static::updating(function ($detalle) {
            $detalle->monto = $detalle->cantidad * $detalle->precio_unitario;
        });

        static::updated(function ($detalle) {
            if ($detalle->isDirty('monto')) {
                $diferencia = $detalle->monto - $detalle->getOriginal('monto');
                $detalle->renglon->monto_ejecutado += $diferencia;
                $detalle->renglon->actualizarSaldo();
            }
        });

        static::deleted(function ($detalle) {
            $detalle->renglon->monto_ejecutado -= $detalle->monto;
            $detalle->renglon->actualizarSaldo();
        });
    }
}
