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

    protected $appends = [
        'monto_asignado',
        'monto_ejecutado', 
        'saldo_disponible',
        'saldo_por_ejecutar'
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
     * Actualizar saldo actual
     */
    public function actualizarSaldo($nuevoSaldo)
    {
        $this->saldo_actual = max(0, $nuevoSaldo); // No permitir saldos negativos
        $this->save();
    }

    /**
     * Verificar si hay saldo suficiente
     */
    public function tieneSaldo($monto)
    {
        return $this->saldo_actual >= $monto;
    }

    /**
     * Obtener total asignado en presupuestos
     */
    public function getMontoAsignadoAttribute()
    {
        return $this->presupuestosDetalle()->sum('monto_asignado');
    }

    /**
     * Obtener total ejecutado
     */
    public function getMontoEjecutadoAttribute()
    {
        return $this->presupuestosDetalle()->sum('monto_ejecutado');
    }

    /**
     * Obtener saldo disponible (calculado)
     */
    public function getSaldoDisponibleAttribute()
    {
        return $this->monto_inicial - $this->monto_asignado;
    }

    /**
     * Obtener saldo por ejecutar (asignado pero no ejecutado)
     */
    public function getSaldoPorEjecutarAttribute()
    {
        return $this->monto_asignado - $this->monto_ejecutado;
    }
}
