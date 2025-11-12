<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cur extends Model
{
    use SoftDeletes;

    protected $table = 'cur';

    protected $fillable = [
        'numero_cur',
        'renglon_id',
        'monto',
        'fecha_compromiso',
        'descripcion',
        'proveedor_id',
        'usuario_id',
        'estado'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_compromiso' => 'date',
        'estado' => 'integer'
    ];

    /**
     * Relación con renglón presupuestario
     */
    public function renglon()
    {
        return $this->belongsTo(Renglon::class, 'renglon_id');
    }

    /**
     * Relación con proveedor
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    /**
     * Relación con usuario que registró el CUR
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
     * Scope para CURs activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Al crear, comprometer el monto en el renglón
     */
    protected static function booted()
    {
        static::created(function ($cur) {
            $cur->renglon->monto_comprometido += $cur->monto;
            $cur->renglon->actualizarSaldo();
        });

        static::updated(function ($cur) {
            if ($cur->isDirty('monto')) {
                $diferencia = $cur->monto - $cur->getOriginal('monto');
                $cur->renglon->monto_comprometido += $diferencia;
                $cur->renglon->actualizarSaldo();
            }
        });

        static::deleted(function ($cur) {
            $cur->renglon->monto_comprometido -= $cur->monto;
            $cur->renglon->actualizarSaldo();
        });
    }
}
