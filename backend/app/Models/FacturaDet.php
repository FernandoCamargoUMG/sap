<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacturaDet extends Model
{
    use SoftDeletes;

    protected $table = 'factura_det';

    protected $fillable = [
        'factura_id',
        'renglon_id',
        'item',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'estado'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'estado' => 'integer'
    ];

    /**
     * Relación con encabezado de factura
     */
    public function factura()
    {
        return $this->belongsTo(FacturaCab::class, 'factura_id');
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
            $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
        });

        static::created(function ($detalle) {
            // Actualizar total de la factura
            $detalle->factura->calcularTotal();
        });

        static::updating(function ($detalle) {
            $detalle->subtotal = $detalle->cantidad * $detalle->precio_unitario;
        });

        static::updated(function ($detalle) {
            // Actualizar total de la factura
            $detalle->factura->calcularTotal();
        });

        static::deleted(function ($detalle) {
            // Actualizar total de la factura
            $detalle->factura->calcularTotal();
        });
    }
}
