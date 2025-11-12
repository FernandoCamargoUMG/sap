<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Intra extends Model
{
    use SoftDeletes;

    protected $table = 'intras';

    protected $fillable = [
        'numero_intra',
        'renglon_origen_id',
        'renglon_destino_id',
        'monto',
        'fecha_transferencia',
        'justificacion',
        'usuario_id',
        'estado'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_transferencia' => 'date',
        'estado' => 'integer'
    ];

    /**
     * Relación con renglón origen
     */
    public function renglonOrigen()
    {
        return $this->belongsTo(Renglon::class, 'renglon_origen_id');
    }

    /**
     * Relación con renglón destino
     */
    public function renglonDestino()
    {
        return $this->belongsTo(Renglon::class, 'renglon_destino_id');
    }

    /**
     * Relación con usuario que realizó la transferencia
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación polimórfica con documentos
     */
    public function documentos()
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    /**
     * Scope para intras activas
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Al crear, afectar los renglones
     */
    protected static function booted()
    {
        static::created(function ($intra) {
            // Restar del renglón origen
            $intra->renglonOrigen->monto_asignado -= $intra->monto;
            $intra->renglonOrigen->actualizarSaldo();

            // Sumar al renglón destino
            $intra->renglonDestino->monto_asignado += $intra->monto;
            $intra->renglonDestino->actualizarSaldo();
        });

        static::deleted(function ($intra) {
            // Revertir la transferencia
            $intra->renglonOrigen->monto_asignado += $intra->monto;
            $intra->renglonOrigen->actualizarSaldo();

            $intra->renglonDestino->monto_asignado -= $intra->monto;
            $intra->renglonDestino->actualizarSaldo();
        });
    }
}
