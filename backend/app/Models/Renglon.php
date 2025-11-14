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
        'grupo',
        'monto_inicial',
        'saldo_actual',
        'estado'
    ];

    protected $casts = [
        'monto_inicial' => 'decimal:2',
        'saldo_actual' => 'decimal:2',
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
        return $query->where('saldo_actual', '>', 0);
    }

    /**
     * Actualizar saldo actual (se calculará desde presupuestos_det)
     * Este método se usará cuando se afecte el saldo desde otros módulos
     */
    public function actualizarSaldo($nuevoSaldo)
    {
        $this->saldo_actual = $nuevoSaldo;
        $this->save();
    }

    /**
     * Verificar si hay saldo suficiente
     */
    public function tieneSaldo($monto)
    {
        return $this->saldo_actual >= $monto;
    }
}
