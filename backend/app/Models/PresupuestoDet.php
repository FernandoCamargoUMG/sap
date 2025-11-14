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
        'monto_ejecutado',
        'descripcion',
        'documento_id',
        'estado'
    ];

    protected $casts = [
        'monto_asignado' => 'decimal:2',
        'monto_ejecutado' => 'decimal:2',
        'estado' => 'integer'
    ];

    protected $appends = [
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
     * Scope para detalles activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Ejecutar monto del presupuesto (cuando se hace un gasto)
     */
    public function ejecutarMonto($monto, $descripcion = '')
    {
        if ($this->getSaldoPorEjecutar() >= $monto) {
            $this->monto_ejecutado += $monto;
            if ($descripcion) {
                $this->descripcion = $this->descripcion 
                    ? $this->descripcion . "\n" . $descripcion 
                    : $descripcion;
            }
            $this->save();
            return true;
        }
        return false;
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

    /**
     * Al crear, actualizar o eliminar, actualizar el saldo del renglón
     */
    protected static function booted()
    {
        static::created(function ($detalle) {
            if ($detalle->renglon) {
                // Reducir saldo disponible del renglón cuando se asigna presupuesto
                $nuevoSaldo = $detalle->renglon->saldo_actual - $detalle->monto_asignado;
                $detalle->renglon->actualizarSaldo($nuevoSaldo);
            }
        });

        static::updated(function ($detalle) {
            if ($detalle->isDirty('monto_asignado') && $detalle->renglon) {
                // Calcular diferencia en monto asignado
                $diferencia = $detalle->monto_asignado - $detalle->getOriginal('monto_asignado');
                $nuevoSaldo = $detalle->renglon->saldo_actual - $diferencia;
                $detalle->renglon->actualizarSaldo($nuevoSaldo);
            }
        });

        static::deleted(function ($detalle) {
            if ($detalle->renglon) {
                // Restaurar saldo al renglón cuando se elimina la asignación
                $nuevoSaldo = $detalle->renglon->saldo_actual + $detalle->monto_asignado;
                $detalle->renglon->actualizarSaldo($nuevoSaldo);
            }
        });
    }
}
