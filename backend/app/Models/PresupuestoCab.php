<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresupuestoCab extends Model
{
    use SoftDeletes;

    protected $table = 'presupuesto_cab';

    protected $fillable = [
        'anio',
        'descripcion',
        'fecha_aprobacion',
        'monto_total',
        'usuario_id',
        'estado'
    ];

    protected $casts = [
        'fecha_aprobacion' => 'date',
        'monto_total' => 'decimal:2',
        'estado' => 'integer',
        'anio' => 'integer'
    ];

    /**
     * Relación con usuario que creó el presupuesto
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación con detalles del presupuesto
     */
    public function detalles()
    {
        return $this->hasMany(PresupuestoDet::class, 'presupuesto_cab_id');
    }

    /**
     * Relación polimórfica con documentos
     */
    public function documentos()
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    /**
     * Scope para presupuestos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para presupuestos por año
     */
    public function scopePorAnio($query, $anio)
    {
        return $query->where('anio', $anio);
    }

    /**
     * Calcular monto total desde los detalles
     */
    public function calcularMontoTotal()
    {
        $this->monto_total = $this->detalles()->sum('monto');
        $this->save();
    }
}
