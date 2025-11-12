<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IntraRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'numero_intra' => 'required|string|max:50',
            'renglon_origen_id' => 'required|exists:renglones,id|different:renglon_destino_id',
            'renglon_destino_id' => 'required|exists:renglones,id',
            'monto' => 'required|numeric|min:0.01',
            'fecha_transferencia' => 'required|date',
            'justificacion' => 'required|string',
            'usuario_id' => 'required|exists:usuarios,id',
            'estado' => 'required|integer|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'numero_intra.required' => 'El número de intra es obligatorio',
            'renglon_origen_id.required' => 'El renglón origen es obligatorio',
            'renglon_origen_id.different' => 'El renglón origen debe ser diferente al destino',
            'renglon_destino_id.required' => 'El renglón destino es obligatorio',
            'monto.required' => 'El monto es obligatorio',
            'monto.min' => 'El monto debe ser mayor a 0',
            'fecha_transferencia.required' => 'La fecha es obligatoria',
            'justificacion.required' => 'La justificación es obligatoria',
            'usuario_id.required' => 'El usuario es obligatorio'
        ];
    }
}
