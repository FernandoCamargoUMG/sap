<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'correo',
        'contraseña',
        'rol_id',
        'estado',
    ];

    protected $hidden = [
        'contraseña',
    ];

    protected $casts = [
        'estado' => 'integer',
    ];

    // Scope para obtener solo usuarios activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 1)->whereNull('deleted_at');
    }

    // Relación con rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    // Relación con bitácora
    public function bitacoras()
    {
        return $this->hasMany(Bitacora::class, 'usuario_id');
    }

    // Mutator para hashear la contraseña con MD5
    public function setContraseñaAttribute($value)
    {
        $this->attributes['contraseña'] = md5($value);
    }
}
