<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'admin',
                'descripcion' => 'Administrador del sistema con acceso total',
                'activo' => true
            ],
            [
                'nombre' => 'medico',
                'descripcion' => 'Personal médico - gestión de pacientes y diagnósticos',
                'activo' => true
            ],
            [
                'nombre' => 'cuidador',
                'descripcion' => 'Personal de cuidado y asistencia a pacientes',
                'activo' => true
            ],
            [
                'nombre' => 'apoderado',
                'descripcion' => 'Familiares o responsables de pacientes',
                'activo' => true
            ],
            [
                'nombre' => 'paciente',
                'descripcion' => 'Personas que reciben atención médica',
                'activo' => true
            ]
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['nombre' => $roleData['nombre']],
                $roleData
            );
        }
    }
} 