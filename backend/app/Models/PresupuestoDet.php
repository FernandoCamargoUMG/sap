<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresupuestoDet extends Model
{
    use SoftDeletes;

    protected $table = 'presupuesto_det';

    protected $fillable = [
        'presupuesto_id',
        'renglon_id',
        'monto_asignado',
        'descripcion',
        'documento_id',
        'estado'
    ];

    protected $casts = [
        'monto_asignado' => 'decimal:2',
        'estado' => 'integer'
    ];

    protected $appends = [
        'monto_ejecutado',
        'saldo_por_ejecutar',
        'porcentaje_ejecucion'
    ];

    /**
     * Relación con encabezado del presupuesto
     */
    public function presupuestoCab()
    {
        return $this->belongsTo(PresupuestoCab::class, 'presupuesto_id');
    }

    /**
     * Relación con renglón presupuestario
     */
    public function renglon()
    {
        return $this->belongsTo(Renglon::class, 'renglon_id');
    }

    /**
     * Relación con movimientos (ejecuciones) de este presupuesto detalle
     */
    public function movimientos()
    {
        return $this->hasMany(MovimientoDet::class, 'presupuesto_det_id');
    }

    /**
     * Scope para detalles activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Obtener monto ejecutado (calculado desde movimientos)
     */
    public function getMontoEjecutadoAttribute()
    {
        return $this->movimientos()->sum('monto');
    }

    /**
     * Obtener saldo disponible para ejecutar
     */
    public function getSaldoPorEjecutarAttribute()
    {
        return $this->monto_asignado - $this->monto_ejecutado;
    }

    /**
     * Obtener porcentaje de ejecución
     */
    public function getPorcentajeEjecucionAttribute()
    {
        if ($this->monto_asignado > 0) {
            return ($this->monto_ejecutado / $this->monto_asignado) * 100;
        }
        return 0;
    }

    /**
     * Método helper para compatibilidad
     */
    public function getSaldoPorEjecutar()
    {
        return $this->saldo_por_ejecutar;
    }

    /**
     * Método helper para compatibilidad
     */
    public function getPorcentajeEjecucion()
    {
        return $this->porcentaje_ejecucion;
    }


}
