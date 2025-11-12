<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacturaCab extends Model
{
    use SoftDeletes;

    protected $table = 'factura_cab';

    protected $fillable = [
        'numero_factura',
        'serie',
        'proveedor_id',
        'fecha_factura',
        'fecha_recepcion',
        'monto_total',
        'observaciones',
        'usuario_id',
        'estado'
    ];

    protected $casts = [
        'fecha_factura' => 'date',
        'fecha_recepcion' => 'date',
        'monto_total' => 'decimal:2',
        'estado' => 'integer'
    ];

    /**
     * Relación con proveedor
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    /**
     * Relación con usuario que registró la factura
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Relación con detalles de la factura
     */
    public function detalles()
    {
        return $this->hasMany(FacturaDet::class, 'factura_cab_id');
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
     * Scope para buscar por número de factura
     */
    public function scopePorNumero($query, $numero)
    {
        return $query->where('numero_factura', $numero);
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
