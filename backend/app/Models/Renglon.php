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
        'estado'
    ];

    protected $casts = [
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
     * Obtener total presupuestado para un año específico
     */
    public function getTotalPresupuestado($anio = null)
    {
        $query = $this->presupuestosDetalle();
        
        if ($anio) {
            $query->whereHas('presupuestoCab', function($q) use ($anio) {
                $q->where('anio', $anio);
            });
        }
        
        return $query->sum('monto_asignado');
    }

    /**
     * Obtener total ejecutado para un año específico
     */
    public function getTotalEjecutado($anio = null)
    {
        $query = $this->movimientosDetalle();
        
        if ($anio) {
            $query->whereHas('movimiento', function($q) use ($anio) {
                $q->whereYear('fecha', $anio);
            });
        }
        
        return $query->sum('monto');
    }
}
