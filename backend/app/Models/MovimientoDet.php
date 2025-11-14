<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimientoDet extends Model
{
    use SoftDeletes;

    protected $table = 'movimiento_det';

    protected $fillable = [
        'movimiento_id',
        'renglon_id',
        'presupuesto_det_id',
        'monto',
        'descripcion_detalle',
        'estado'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'estado' => 'integer'
    ];

    /**
     * Relación con encabezado del movimiento
     */
    public function movimiento()
    {
        return $this->belongsTo(MovimientoCab::class, 'movimiento_id');
    }

    /**
     * Relación con renglón presupuestario
     */
    public function renglon()
    {
        return $this->belongsTo(Renglon::class, 'renglon_id');
    }

    /**
     * Relación con detalle del presupuesto ejecutado
     */
    public function presupuestoDet()
    {
        return $this->belongsTo(PresupuestoDet::class, 'presupuesto_det_id');
    }

    /**
     * Scope para detalles activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }


}
