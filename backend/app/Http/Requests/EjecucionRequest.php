<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EjecucionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'presupuesto_det_id' => 'required|integer|exists:presupuesto_det,id',
            'monto' => 'required|numeric|min:0.01',
            'descripcion' => 'nullable|string|max:500'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'presupuesto_det_id.required' => 'El ID del detalle de presupuesto es obligatorio',
            'presupuesto_det_id.exists' => 'El detalle de presupuesto no existe',
            'monto.required' => 'El monto es obligatorio',
            'monto.numeric' => 'El monto debe ser un número válido',
            'monto.min' => 'El monto debe ser mayor a 0',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres'
        ];
    }
}