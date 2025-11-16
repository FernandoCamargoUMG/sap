<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActaBajaCuantiaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $actaId = $this->route('id');
        
        return [
            'numero_acta' => [
                'required',
                'string',
                'max:50',
                $actaId ? 'unique:actas_baja_cuantia,numero_acta,' . $actaId : 'unique:actas_baja_cuantia,numero_acta'
            ],
            'fecha_acta' => 'required|date|before_or_equal:today',
            'proveedor_id' => 'required|exists:proveedores,id',
            'descripcion_compra' => 'required|string|max:1000',
            'monto_total' => 'required|numeric|min:0.01|max:999999999.99',
            'detalle' => 'required|string|max:2000',
            'contenido_acta' => 'required|string|max:3000',
            'responsable' => 'required|string|max:150',
            'cargo_responsable' => 'required|string|max:150',
            'estado' => 'nullable|integer|in:0,1',
            'documento' => 'nullable|file|mimes:pdf|max:10240' // 10MB máximo
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'numero_acta.required' => 'El número de acta es obligatorio.',
            'numero_acta.unique' => 'Este número de acta ya existe.',
            'fecha_acta.required' => 'La fecha del acta es obligatoria.',
            'fecha_acta.before_or_equal' => 'La fecha del acta no puede ser futura.',
            'proveedor_id.required' => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe.',
            'descripcion_compra.required' => 'La descripción de la compra es obligatoria.',
            'monto_total.required' => 'El monto total es obligatorio.',
            'monto_total.min' => 'El monto total debe ser mayor a cero.',
            'detalle.required' => 'El detalle de lo comprado es obligatorio.',
            'contenido_acta.required' => 'El contenido del acta es obligatorio.',
            'responsable.required' => 'El nombre del responsable es obligatorio.',
            'cargo_responsable.required' => 'El cargo del responsable es obligatorio.',
            'documento.mimes' => 'El documento debe ser un archivo PDF.',
            'documento.max' => 'El documento no debe exceder los 10MB.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'numero_acta' => 'número de acta',
            'fecha_acta' => 'fecha del acta',
            'proveedor_id' => 'proveedor',
            'descripcion_compra' => 'descripción de la compra',
            'monto_total' => 'monto total',
            'detalle' => 'detalle',
            'contenido_acta' => 'contenido del acta',
            'responsable' => 'responsable',
            'cargo_responsable' => 'cargo del responsable',
            'documento' => 'documento PDF'
        ];
    }
}
