<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Cambiar según lógica de autorización
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $usuarioId = $this->route('usuario'); // Para update

        $rules = [
            'nombre' => 'required|string|max:100',
            'correo' => [
                'required',
                'email',
                'max:100',
                Rule::unique('usuarios', 'correo')->ignore($usuarioId)->whereNull('deleted_at')
            ],
            'rol_id' => 'required|exists:roles,id',
            'estado' => 'sometimes|boolean',
        ];

        // Contraseña solo requerida en creación
        if ($this->isMethod('post')) {
            $rules['contraseña'] = 'required|string|min:6|max:255';
        } else {
            $rules['contraseña'] = 'sometimes|nullable|string|min:6|max:255';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'correo.required' => 'El correo es obligatorio',
            'correo.email' => 'El correo debe ser válido',
            'correo.unique' => 'El correo ya está registrado',
            'contraseña.required' => 'La contraseña es obligatoria',
            'contraseña.min' => 'La contraseña debe tener al menos 6 caracteres',
            'rol_id.required' => 'El rol es obligatorio',
            'rol_id.exists' => 'El rol seleccionado no existe',
        ];
    }
}
