<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proveedor extends Model
{
    use SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = [
        'nit',
        'nombre',
        'direccion',
        'telefono',
        'correo',
        'contacto',
        'estado'
    ];

    protected $casts = [
        'estado' => 'integer'
    ];

    /**
     * Relación con facturas
     */
    public function facturas()
    {
        return $this->hasMany(FacturaCab::class, 'proveedor_id');
    }

    /**
     * Scope para proveedores activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para buscar por NIT
     */
    public function scopePorNit($query, $nit)
    {
        return $query->where('nit', $nit);
    }
}
