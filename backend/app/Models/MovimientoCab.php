<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimientoCab extends Model
{
    use SoftDeletes;

    protected $table = 'movimiento_cab';

    protected $fillable = [
        'numero_movimiento',
        'tipo_movimiento',
        'fecha_movimiento',
        'descripcion',
        'monto_total',
        'usuario_id',
        'estado'
    ];

    protected $casts = [
        'fecha_movimiento' => 'date',
        'monto_total' => 'decimal:2',
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
     * Relación con detalles del movimiento
     */
    public function detalles()
    {
        return $this->hasMany(MovimientoDet::class, 'movimiento_cab_id');
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
     * Calcular monto total desde los detalles
     */
    public function calcularMontoTotal()
    {
        $this->monto_total = $this->detalles()->sum('monto');
        $this->save();
    }
}
