<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FacturaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'numero_factura' => 'required|string|max:50',
            'serie' => 'required|string|max:20',
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha_factura' => 'required|date',
            'fecha_recepcion' => 'required|date',
            'monto_total' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
            'usuario_id' => 'required|exists:usuarios,id',
            'estado' => 'required|integer|in:0,1',
            
            // Validación para detalles
            'detalles' => 'nullable|array',
            'detalles.*.renglon_id' => 'required|exists:renglones,id',
            'detalles.*.descripcion' => 'required|string|max:500',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.monto' => 'nullable|numeric|min:0',
            'detalles.*.estado' => 'required|integer|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'numero_factura.required' => 'El número de factura es obligatorio',
            'serie.required' => 'La serie es obligatoria',
            'proveedor_id.required' => 'El proveedor es obligatorio',
            'proveedor_id.exists' => 'El proveedor no existe',
            'fecha_factura.required' => 'La fecha de factura es obligatoria',
            'fecha_recepcion.required' => 'La fecha de recepción es obligatoria',
            'usuario_id.required' => 'El usuario es obligatorio',
            
            'detalles.*.renglon_id.required' => 'El renglón es obligatorio en cada detalle',
            'detalles.*.descripcion.required' => 'La descripción es obligatoria en cada detalle',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria en cada detalle',
            'detalles.*.precio_unitario.required' => 'El precio unitario es obligatorio'
        ];
    }
}
