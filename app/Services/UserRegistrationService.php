<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\PersonalMedico;
use App\Models\Cuidador;
use App\Models\Apoderado;
use App\Models\Paciente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserRegistrationService
{
    /**
     * Crear un médico con su usuario y datos específicos
     */
    public function createMedico(array $userData, array $medicoData): User
    {
        return DB::transaction(function () use ($userData, $medicoData) {
            // Obtener o crear el rol de médico
            $rolMedico = Role::firstOrCreate(
                ['nombre' => 'medico'],
                [
                    'descripcion' => 'Personal médico - gestión de pacientes y diagnósticos',
                    'activo' => true
                ]
            );

            // Crear usuario con rol de médico
            $userData['rol_id'] = $rolMedico->id;
            $userData['password'] = Hash::make($userData['password']);
            
            // Generar campo name para compatibilidad
            $userData['name'] = trim(
                ($userData['nombre'] ?? '') . ' ' . 
                ($userData['apellido_paterno'] ?? '') . ' ' . 
                ($userData['apellido_materno'] ?? '')
            );
            
            $user = User::create($userData);

            // Crear registro de personal médico
            $medicoData['usuario_id'] = $user->id;
            PersonalMedico::create($medicoData);

            return $user;
        });
    }

    /**
     * Crear un cuidador con su usuario y datos específicos
     */
    public function createCuidador(array $userData, array $cuidadorData): User
    {
        return DB::transaction(function () use ($userData, $cuidadorData) {
            // Obtener o crear el rol de cuidador
            $rolCuidador = Role::firstOrCreate(
                ['nombre' => 'cuidador'],
                [
                    'descripcion' => 'Personal de cuidado y asistencia a pacientes',
                    'activo' => true
                ]
            );

            // Crear usuario con rol de cuidador
            $userData['rol_id'] = $rolCuidador->id;
            $userData['password'] = Hash::make($userData['password']);
            
            // Generar campo name para compatibilidad
            $userData['name'] = trim(
                ($userData['nombre'] ?? '') . ' ' . 
                ($userData['apellido_paterno'] ?? '') . ' ' . 
                ($userData['apellido_materno'] ?? '')
            );
            
            $user = User::create($userData);

            // Crear registro de cuidador
            $cuidadorData['usuario_id'] = $user->id;
            Cuidador::create($cuidadorData);

            return $user;
        });
    }

    /**
     * Crear un apoderado con su usuario y datos específicos
     */
    public function createApoderado(array $userData, array $apoderadoData): User
    {
        return DB::transaction(function () use ($userData, $apoderadoData) {
            // Obtener o crear el rol de apoderado
            $rolApoderado = Role::firstOrCreate(
                ['nombre' => 'apoderado'],
                [
                    'descripcion' => 'Familiares o responsables de pacientes',
                    'activo' => true
                ]
            );

            // Crear usuario con rol de apoderado
            $userData['rol_id'] = $rolApoderado->id;
            $userData['password'] = Hash::make($userData['password']);
            
            // Generar campo name para compatibilidad
            $userData['name'] = trim(
                ($userData['nombre'] ?? '') . ' ' . 
                ($userData['apellido_paterno'] ?? '') . ' ' . 
                ($userData['apellido_materno'] ?? '')
            );
            
            $user = User::create($userData);

            // Crear registro de apoderado
            $apoderadoData['usuario_id'] = $user->id;
            Apoderado::create($apoderadoData);

            return $user;
        });
    }

    /**
     * Crear un paciente (puede o no tener usuario asociado)
     */
    public function createPaciente(array $pacienteData, ?array $userData = null): Paciente
    {
        return DB::transaction(function () use ($pacienteData, $userData) {
            $userId = null;

            // Si se proporcionan datos de usuario, crear el usuario con rol de paciente
            if ($userData) {
                $rolPaciente = Role::firstOrCreate(
                    ['nombre' => 'paciente'],
                    [
                        'descripcion' => 'Paciente - acceso a su propia información médica',
                        'activo' => true
                    ]
                );

                $userData['rol_id'] = $rolPaciente->id;
                $userData['password'] = Hash::make($userData['password']);
                
                // Generar campo name para compatibilidad
                $userData['name'] = trim(
                    ($userData['nombre'] ?? '') . ' ' . 
                    ($userData['apellido_paterno'] ?? '') . ' ' . 
                    ($userData['apellido_materno'] ?? '')
                );
                
                $user = User::create($userData);
                $userId = $user->id;
            }

            // Crear registro de paciente
            $pacienteData['usuario_id'] = $userId;
            return Paciente::create($pacienteData);
        });
    }

    /**
     * Crear un administrador
     */
    public function createAdmin(array $userData): User
    {
        return DB::transaction(function () use ($userData) {
            // Obtener o crear el rol de admin
            $rolAdmin = Role::firstOrCreate(
                ['nombre' => 'admin'],
                [
                    'descripcion' => 'Administrador del sistema con acceso total',
                    'activo' => true
                ]
            );

            // Crear usuario con rol de admin
            $userData['rol_id'] = $rolAdmin->id;
            $userData['password'] = Hash::make($userData['password']);
            
            // Generar campo name para compatibilidad
            $userData['name'] = trim(
                ($userData['nombre'] ?? '') . ' ' . 
                ($userData['apellido_paterno'] ?? '') . ' ' . 
                ($userData['apellido_materno'] ?? '')
            );
            
            return User::create($userData);
        });
    }

    /**
     * Obtener tipos de usuario disponibles
     */
    public function getUserTypes(): array
    {
        return [
            'medico' => [
                'label' => 'Personal Médico',
                'description' => 'Médicos, especialistas y personal sanitario',
                'icon' => 'user-doctor'
            ],
            'cuidador' => [
                'label' => 'Cuidador',
                'description' => 'Personal de cuidado y asistencia',
                'icon' => 'heart-handshake'
            ],
            'apoderado' => [
                'label' => 'Apoderado',
                'description' => 'Familiares o responsables de pacientes',
                'icon' => 'users'
            ],
            'paciente' => [
                'label' => 'Paciente',
                'description' => 'Personas que reciben atención médica',
                'icon' => 'user'
            ],
            'admin' => [
                'label' => 'Administrador',
                'description' => 'Acceso completo al sistema',
                'icon' => 'shield'
            ]
        ];
    }
} 