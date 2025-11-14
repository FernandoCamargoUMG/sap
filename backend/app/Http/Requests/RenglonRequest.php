<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RenglonRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $renglonId = $this->route('id');

        return [
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('renglones', 'codigo')->ignore($renglonId)->whereNull('deleted_at')
            ],
            'nombre' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'grupo' => 'nullable|string|max:50',
            'monto_inicial' => 'required|numeric|min:0',
            'estado' => 'required|integer|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'codigo.required' => 'El código del renglón es obligatorio',
            'codigo.unique' => 'El código del renglón ya está registrado',
            'nombre.required' => 'El nombre del renglón es obligatorio',
            'monto_inicial.required' => 'El monto inicial es obligatorio',
            'monto_inicial.min' => 'El monto inicial debe ser mayor o igual a 0',
            'estado.required' => 'El estado es obligatorio',
            'estado.in' => 'El estado debe ser 0 o 1'
        ];
    }
}
