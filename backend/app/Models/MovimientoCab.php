<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimientoCab extends Model
{
    use SoftDeletes;

    protected $table = 'movimiento_cab';

    protected $fillable = [
        'tipo_movimiento',
        'fecha',
        'descripcion',
        'usuario_id',
        'documento_id',
        'presupuesto_cab_id',
        'numero_documento',
        'proveedor',
        'estado'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'estado' => 'integer'
    ];

    /**
     * Relación con usuario que creó el movimiento
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación con presupuesto (si es ejecución presupuestaria)
     */
    public function presupuestoCab()
    {
        return $this->belongsTo(PresupuestoCab::class, 'presupuesto_cab_id');
    }

    /**
     * Relación con detalles del movimiento
     */
    public function detalles()
    {
        return $this->hasMany(MovimientoDet::class, 'movimiento_id');
    }

    /**
     * Relación polimórfica con documentos
     */
    public function documentos()
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    /**
     * Scope para movimientos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope por tipo de movimiento
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_movimiento', $tipo);
    }

    /**
     * Scope para ejecuciones presupuestarias
     */
    public function scopeEjecucionesPresupuestarias($query)
    {
        return $query->where('tipo_movimiento', 'ejecucion_presupuestaria');
    }

    /**
     * Calcular monto total desde los detalles
     */
    public function calcularMontoTotal()
    {
        return $this->detalles()->sum('monto');
    }
}
