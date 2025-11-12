<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    use SoftDeletes;

    protected $table = 'documentos';

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_archivo',
        'tamanio',
        'descripcion',
        'usuario_id',
        'estado'
    ];

    protected $casts = [
        'tamanio' => 'integer',
        'estado' => 'integer'
    ];

    /**
     * Relación polimórfica - obtener el modelo padre
     */
    public function documentable()
    {
        return $this->morphTo();
    }

    /**
     * Relación con usuario que subió el documento
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    /**
     * Scope para documentos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope por tipo de archivo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_archivo', $tipo);
    }

    /**
     * Obtener tamaño formateado
     */
    public function getTamanioFormateadoAttribute()
    {
        $bytes = $this->tamanio;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
