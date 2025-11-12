<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'numero_cur' => 'required|string|max:50',
            'renglon_id' => 'required|exists:renglones,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha_compromiso' => 'required|date',
            'descripcion' => 'required|string',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'usuario_id' => 'required|exists:usuarios,id',
            'estado' => 'required|integer|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'numero_cur.required' => 'El número de CUR es obligatorio',
            'renglon_id.required' => 'El renglón es obligatorio',
            'monto.required' => 'El monto es obligatorio',
            'monto.min' => 'El monto debe ser mayor a 0',
            'fecha_compromiso.required' => 'La fecha de compromiso es obligatoria',
            'descripcion.required' => 'La descripción es obligatoria',
            'usuario_id.required' => 'El usuario es obligatorio'
        ];
    }
}
