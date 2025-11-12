<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProveedorRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $proveedorId = $this->route('id');

        return [
            'nit' => [
                'required',
                'string',
                'max:20',
                Rule::unique('proveedores', 'nit')->ignore($proveedorId)->whereNull('deleted_at')
            ],
            'nombre' => 'required|string|max:200',
            'direccion' => 'nullable|string|max:500',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100',
            'contacto' => 'nullable|string|max:100',
            'estado' => 'required|integer|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'nit.required' => 'El NIT es obligatorio',
            'nit.unique' => 'El NIT ya está registrado',
            'nombre.required' => 'El nombre del proveedor es obligatorio',
            'correo.email' => 'El correo debe ser válido',
            'estado.required' => 'El estado es obligatorio',
            'estado.in' => 'El estado debe ser 0 o 1'
        ];
    }
}
