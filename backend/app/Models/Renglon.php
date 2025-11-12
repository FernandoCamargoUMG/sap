<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Renglon extends Model
{
    use SoftDeletes;

    protected $table = 'renglones';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'monto_asignado',
        'monto_comprometido',
        'monto_ejecutado',
        'saldo_disponible',
        'estado'
    ];

    protected $casts = [
        'monto_asignado' => 'decimal:2',
        'monto_comprometido' => 'decimal:2',
        'monto_ejecutado' => 'decimal:2',
        'saldo_disponible' => 'decimal:2',
        'estado' => 'integer'
    ];

    /**
     * Relación con presupuesto detalle
     */
    public function presupuestosDetalle()
    {
        return $this->hasMany(PresupuestoDet::class, 'renglon_id');
    }

    /**
     * Relación con movimientos detalle
     */
    public function movimientosDetalle()
    {
        return $this->hasMany(MovimientoDet::class, 'renglon_id');
    }

    /**
     * Relación con facturas detalle
     */
    public function facturasDetalle()
    {
        return $this->hasMany(FacturaDet::class, 'renglon_id');
    }

    /**
     * Scope para renglones activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para renglones con saldo disponible
     */
    public function scopeConSaldo($query)
    {
        return $query->where('saldo_disponible', '>', 0);
    }

    /**
     * Calcular y actualizar saldo disponible
     */
    public function actualizarSaldo()
    {
        $this->saldo_disponible = $this->monto_asignado - $this->monto_comprometido - $this->monto_ejecutado;
        $this->save();
    }

    /**
     * Verificar si hay saldo suficiente
     */
    public function tieneSaldo($monto)
    {
        return $this->saldo_disponible >= $monto;
    }
}
