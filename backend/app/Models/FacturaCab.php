<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacturaCab extends Model
{
    use SoftDeletes;

    protected $table = 'factura_cab';

    protected $fillable = [
        'proveedor_id',
        'folio',
        'fecha',
        'total',
        'documento_id',
        'tipo',
        'estado'
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'total' => 'decimal:2',
        'estado' => 'integer'
    ];

    /**
     * Accessor para fecha - asegurar formato correcto
     */
    public function getFechaAttribute($value)
    {
        if (!$value) return null;
        return \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    /**
     * Mutator para fecha - normalizar entrada
     */
    public function setFechaAttribute($value)
    {
        if ($value) {
            $this->attributes['fecha'] = \Carbon\Carbon::parse($value)->format('Y-m-d');
        }
    }

    /**
     * Relación con proveedor
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    /**
     * Relación con documento
     */
    public function documento()
    {
        return $this->belongsTo(Documento::class, 'documento_id');
    }

    /**
     * Relación con detalles de la factura
     */
    public function detalles()
    {
        return $this->hasMany(FacturaDet::class, 'factura_id');
    }

    /**
     * Relación polimórfica con documentos
     */
    public function documentos()
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    /**
     * Scope para facturas activas
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para buscar por folio
     */
    public function scopePorFolio($query, $folio)
    {
        return $query->where('folio', $folio);
    }

    /**
     * Scope por tipo de factura
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Calcular total desde los detalles
     */
    public function calcularTotal()
    {
        $this->total = $this->detalles()->sum('subtotal');
        $this->save();
        return $this->total;
    }
}
