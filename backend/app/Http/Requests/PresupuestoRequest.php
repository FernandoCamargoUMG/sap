<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PresupuestoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'anio' => 'required|integer|min:2000|max:2100',
            'descripcion' => 'required|string|max:500',
            'fecha_aprobacion' => 'required|date',
            'monto_total' => 'nullable|numeric|min:0',
            'usuario_id' => 'required|exists:usuarios,id',
            'estado' => 'required|integer|in:0,1',
            
            // Validación para detalles (array)
            'detalles' => 'nullable|array',
            'detalles.*.renglon_id' => 'required|exists:renglones,id',
            'detalles.*.monto' => 'required|numeric|min:0',
            'detalles.*.observaciones' => 'nullable|string',
            'detalles.*.estado' => 'required|integer|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'anio.required' => 'El año es obligatorio',
            'descripcion.required' => 'La descripción es obligatoria',
            'fecha_aprobacion.required' => 'La fecha de aprobación es obligatoria',
            'usuario_id.required' => 'El usuario es obligatorio',
            'usuario_id.exists' => 'El usuario no existe',
            'estado.required' => 'El estado es obligatorio',
            
            'detalles.*.renglon_id.required' => 'El renglón es obligatorio en cada detalle',
            'detalles.*.renglon_id.exists' => 'El renglón especificado no existe',
            'detalles.*.monto.required' => 'El monto es obligatorio en cada detalle',
            'detalles.*.monto.min' => 'El monto debe ser mayor o igual a 0'
        ];
    }
}
