<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActaBajaCuantia extends Model
{
    use SoftDeletes;

    protected $table = 'actas_baja_cuantia';

    protected $fillable = [
        'numero_acta',
        'fecha_acta',
        'proveedor_id',
        'descripcion_compra',
        'monto_total',
        'detalle',
        'contenido_acta',
        'responsable',
        'cargo_responsable',
        'usuario_id',
        'estado'
    ];

    protected $casts = [
        'fecha_acta' => 'date:Y-m-d',
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
     * Relación con usuario que registra
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
     * Scope para actas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope por año
     */
    public function scopePorAnio($query, $anio)
    {
        return $query->whereYear('fecha_acta', $anio);
    }

    /**
     * Scope por proveedor
     */
    public function scopePorProveedor($query, $proveedorId)
    {
        return $query->where('proveedor_id', $proveedorId);
    }

    /**
     * Obtener el número formateado del acta
     */
    public function getNumeroFormateadoAttribute()
    {
        return "ACTA-" . str_pad($this->numero_acta, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener el monto formateado
     */
    public function getMontoFormateadoAttribute()
    {
        return 'Q' . number_format($this->monto_total, 2, '.', ',');
    }
}