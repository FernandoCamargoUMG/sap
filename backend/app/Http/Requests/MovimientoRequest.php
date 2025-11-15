<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovimientoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'numero_movimiento' => 'required|string|max:50',
            'tipo_movimiento' => 'required|string|in:ejecucion_presupuestaria,ajuste,traslado',
            'fecha_movimiento' => 'required|date',
            'descripcion' => 'required|string|max:500',
            'monto_total' => 'nullable|numeric|min:0',
            'usuario_id' => 'required|exists:usuarios,id',
            'estado' => 'required|integer|in:0,1',
            
            // Validación para detalles
            'detalles' => 'nullable|array',
            'detalles.*.renglon_id' => 'required|exists:renglones,id',
            'detalles.*.monto' => 'required|numeric|min:0.01',
            'detalles.*.observaciones' => 'nullable|string',
            'detalles.*.estado' => 'required|integer|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'numero_movimiento.required' => 'El número de movimiento es obligatorio',
            'tipo_movimiento.required' => 'El tipo de movimiento es obligatorio',
            'fecha_movimiento.required' => 'La fecha es obligatoria',
            'descripcion.required' => 'La descripción es obligatoria',
            'usuario_id.required' => 'El usuario es obligatorio',
            
            'detalles.*.renglon_id.required' => 'El renglón es obligatorio en cada detalle',
            'detalles.*.monto.required' => 'El monto es obligatorio en cada detalle'
        ];
    }
}
