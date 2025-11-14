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
            'mes' => 'required|integer|min:1|max:12',
            'descripcion' => 'nullable|string|max:255',
            'usuario_id' => 'nullable|exists:usuarios,id',
            'estado' => 'nullable|integer|in:0,1',
            
            // Validación para detalles (array)
            'detalles' => 'nullable|array',
            'detalles.*.renglon_id' => 'required|exists:renglones,id',
            'detalles.*.monto_asignado' => 'required|numeric|min:0',
            'detalles.*.descripcion' => 'nullable|string',
            'detalles.*.estado' => 'nullable|integer|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'anio.required' => 'El año es obligatorio',
            'mes.required' => 'El mes es obligatorio',
            'mes.min' => 'El mes debe estar entre 1 y 12',
            'mes.max' => 'El mes debe estar entre 1 y 12',
            'usuario_id.exists' => 'El usuario no existe',
            
            'detalles.*.renglon_id.required' => 'El renglón es obligatorio en cada detalle',
            'detalles.*.renglon_id.exists' => 'El renglón especificado no existe',
            'detalles.*.monto_asignado.required' => 'El monto asignado es obligatorio en cada detalle',
            'detalles.*.monto_asignado.min' => 'El monto asignado debe ser mayor o igual a 0'
        ];
    }
}
