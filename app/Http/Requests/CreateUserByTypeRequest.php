<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class CreateUserByTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Agregar lógica de autorización después
    }

    public function rules(): array
    {
        $rules = [
            // Datos base del usuario
            'user_data.name' => 'required|string|max:255',
            'user_data.email' => 'required|email|unique:users,email',
            'user_data.password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_data.telefono' => 'nullable|string|max:20',
            'user_data.activo' => 'boolean',
            'user_data.email_verificado' => 'boolean',
            
            // Tipo de usuario
            'tipo_usuario' => 'required|in:medico,cuidador,apoderado,paciente,admin'
        ];

        // Validaciones específicas según el tipo
        switch ($this->input('tipo_usuario')) {
            case 'medico':
                $rules = array_merge($rules, [
                    'specific_data.especialidad' => 'nullable|string|max:100',
                    'specific_data.numero_colegiatura' => 'nullable|string|max:50|unique:personal_medico,numero_colegiatura',
                    'specific_data.institucion' => 'nullable|string|max:100',
                    'specific_data.anos_experiencia' => 'nullable|integer|min:0'
                ]);
                break;

            case 'cuidador':
                $rules = array_merge($rules, [
                    'specific_data.certificaciones' => 'nullable|string',
                    'specific_data.experiencia_anos' => 'nullable|integer|min:0',
                    'specific_data.disponibilidad_horaria' => 'nullable|string|max:100',
                    'specific_data.tarifa_hora' => 'nullable|integer|min:0'
                ]);
                break;

            case 'apoderado':
                $rules = array_merge($rules, [
                    'specific_data.relacion_paciente' => 'nullable|string|max:50',
                    'specific_data.es_contacto_emergencia' => 'boolean'
                ]);
                break;

            case 'paciente':
                $rules = array_merge($rules, [
                    // Para pacientes, los datos de usuario son opcionales
                    'user_data.name' => 'nullable|string|max:255',
                    'user_data.email' => 'nullable|email|unique:users,email',
                    'user_data.password' => $this->filled('user_data.email') ? 
                        ['required', 'confirmed', Rules\Password::defaults()] : 'nullable',
                    
                    // Datos específicos del paciente
                    'specific_data.nombre' => 'required|string|max:100',
                    'specific_data.fecha_nacimiento' => 'nullable|date',
                    'specific_data.genero_id' => 'nullable|exists:generos,id',
                    'specific_data.numero_documento' => 'nullable|string|max:20|unique:pacientes,numero_documento',
                    'specific_data.tipo_documento' => 'nullable|string|max:10',
                    'specific_data.tipo_sangre' => 'nullable|string|max:10',
                    'specific_data.altura' => 'nullable|numeric|min:0|max:300',
                    'specific_data.direccion' => 'nullable|string',
                    'specific_data.telefono_emergencia' => 'nullable|string|max:20',
                    'specific_data.observaciones_medicas' => 'nullable|string',
                    'specific_data.activo' => 'boolean'
                ]);
                break;

            case 'admin':
                // Solo necesita datos base del usuario
                break;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'user_data.name.required' => 'El nombre es obligatorio.',
            'user_data.email.required' => 'El email es obligatorio.',
            'user_data.email.unique' => 'Este email ya está registrado.',
            'user_data.password.required' => 'La contraseña es obligatoria.',
            'user_data.password.confirmed' => 'Las contraseñas no coinciden.',
            'specific_data.numero_colegiatura.unique' => 'Este número de colegiatura ya está registrado.',
            'specific_data.numero_documento.unique' => 'Este número de documento ya está registrado.',
            'specific_data.nombre.required' => 'El nombre del paciente es obligatorio.',
            'tipo_usuario.required' => 'Debe seleccionar un tipo de usuario.',
            'tipo_usuario.in' => 'El tipo de usuario seleccionado no es válido.'
        ];
    }

    protected function prepareForValidation(): void
    {
        // Procesar datos antes de la validación
        if ($this->has('user_data.email_verificado')) {
            $this->merge([
                'user_data' => array_merge($this->input('user_data', []), [
                    'email_verified_at' => $this->input('user_data.email_verificado') ? now() : null
                ])
            ]);
        }
    }
} 