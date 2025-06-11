<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permiso;
use Illuminate\Support\Facades\DB;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles básicos
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
                'descripcion' => 'Cuidador de pacientes - acceso limitado a pacientes asignados',
                'activo' => true
            ],
            [
                'nombre' => 'apoderado',
                'descripcion' => 'Apoderado/tutor - acceso a información de pacientes a cargo',
                'activo' => true
            ],
            [
                'nombre' => 'paciente',
                'descripcion' => 'Paciente - acceso a su propia información médica',
                'activo' => true
            ]
        ];

        foreach ($roles as $rol) {
            Role::firstOrCreate(['nombre' => $rol['nombre']], $rol);
        }

        // Crear permisos por módulos
        $permisos = [
            // Usuarios
            ['nombre' => 'usuarios.index', 'descripcion' => 'Ver lista de usuarios', 'modulo' => 'usuarios'],
            ['nombre' => 'usuarios.create', 'descripcion' => 'Crear usuarios', 'modulo' => 'usuarios'],
            ['nombre' => 'usuarios.edit', 'descripcion' => 'Editar usuarios', 'modulo' => 'usuarios'],
            ['nombre' => 'usuarios.delete', 'descripcion' => 'Eliminar usuarios', 'modulo' => 'usuarios'],
            
            // Roles
            ['nombre' => 'roles.index', 'descripcion' => 'Ver lista de roles', 'modulo' => 'roles'],
            ['nombre' => 'roles.create', 'descripcion' => 'Crear roles', 'modulo' => 'roles'],
            ['nombre' => 'roles.edit', 'descripcion' => 'Editar roles', 'modulo' => 'roles'],
            ['nombre' => 'roles.delete', 'descripcion' => 'Eliminar roles', 'modulo' => 'roles'],
            
            // Pacientes
            ['nombre' => 'pacientes.index', 'descripcion' => 'Ver lista de pacientes', 'modulo' => 'pacientes'],
            ['nombre' => 'pacientes.create', 'descripcion' => 'Crear pacientes', 'modulo' => 'pacientes'],
            ['nombre' => 'pacientes.edit', 'descripcion' => 'Editar pacientes', 'modulo' => 'pacientes'],
            ['nombre' => 'pacientes.delete', 'descripcion' => 'Eliminar pacientes', 'modulo' => 'pacientes'],
            ['nombre' => 'pacientes.own', 'descripcion' => 'Ver solo sus propios datos', 'modulo' => 'pacientes'],
            
            // Personal Médico
            ['nombre' => 'personal-medico.index', 'descripcion' => 'Ver personal médico', 'modulo' => 'personal-medico'],
            ['nombre' => 'personal-medico.create', 'descripcion' => 'Crear personal médico', 'modulo' => 'personal-medico'],
            ['nombre' => 'personal-medico.edit', 'descripcion' => 'Editar personal médico', 'modulo' => 'personal-medico'],
            ['nombre' => 'personal-medico.delete', 'descripcion' => 'Eliminar personal médico', 'modulo' => 'personal-medico'],
            
            // Cuidadores
            ['nombre' => 'cuidadores.index', 'descripcion' => 'Ver cuidadores', 'modulo' => 'cuidadores'],
            ['nombre' => 'cuidadores.create', 'descripcion' => 'Crear cuidadores', 'modulo' => 'cuidadores'],
            ['nombre' => 'cuidadores.edit', 'descripcion' => 'Editar cuidadores', 'modulo' => 'cuidadores'],
            ['nombre' => 'cuidadores.delete', 'descripcion' => 'Eliminar cuidadores', 'modulo' => 'cuidadores'],
            
            // Apoderados
            ['nombre' => 'apoderados.index', 'descripcion' => 'Ver apoderados', 'modulo' => 'apoderados'],
            ['nombre' => 'apoderados.create', 'descripcion' => 'Crear apoderados', 'modulo' => 'apoderados'],
            ['nombre' => 'apoderados.edit', 'descripcion' => 'Editar apoderados', 'modulo' => 'apoderados'],
            ['nombre' => 'apoderados.delete', 'descripcion' => 'Eliminar apoderados', 'modulo' => 'apoderados'],
            
            // Medicamentos
            ['nombre' => 'medicines.index', 'descripcion' => 'Ver medicamentos', 'modulo' => 'medicines'],
            ['nombre' => 'medicines.create', 'descripcion' => 'Crear medicamentos', 'modulo' => 'medicines'],
            ['nombre' => 'medicines.edit', 'descripcion' => 'Editar medicamentos', 'modulo' => 'medicines'],
            ['nombre' => 'medicines.delete', 'descripcion' => 'Eliminar medicamentos', 'modulo' => 'medicines'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::firstOrCreate(['nombre' => $permiso['nombre']], $permiso);
        }

        // Asignar permisos a roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        $admin = Role::where('nombre', 'admin')->first();
        $medico = Role::where('nombre', 'medico')->first();
        $cuidador = Role::where('nombre', 'cuidador')->first();
        $apoderado = Role::where('nombre', 'apoderado')->first();
        $paciente = Role::where('nombre', 'paciente')->first();

        // Admin: todos los permisos
        $todosLosPermisos = Permiso::all()->pluck('id');
        foreach ($todosLosPermisos as $permisoId) {
            DB::table('rol_permisos')->insertOrIgnore([
                'rol_id' => $admin->id,
                'permiso_id' => $permisoId
            ]);
        }

        // Médico: gestión de pacientes, medicamentos, ver personal
        $permisosMedico = [
            'pacientes.index', 'pacientes.create', 'pacientes.edit', 'pacientes.delete',
            'medicines.index', 'medicines.create', 'medicines.edit', 'medicines.delete',
            'personal-medico.index', 'cuidadores.index', 'apoderados.index'
        ];
        $this->assignPermissionsByName($medico->id, $permisosMedico);

        // Cuidador: ver pacientes asignados, medicamentos
        $permisosCuidador = [
            'pacientes.index', 'pacientes.edit',
            'medicines.index'
        ];
        $this->assignPermissionsByName($cuidador->id, $permisosCuidador);

        // Apoderado: ver pacientes a cargo
        $permisosApoderado = [
            'pacientes.index',
            'medicines.index'
        ];
        $this->assignPermissionsByName($apoderado->id, $permisosApoderado);

        // Paciente: solo sus propios datos
        $permisosPaciente = [
            'pacientes.own'
        ];
        $this->assignPermissionsByName($paciente->id, $permisosPaciente);
    }

    private function assignPermissionsByName(int $rolId, array $permisoNombres): void
    {
        foreach ($permisoNombres as $nombre) {
            $permiso = Permiso::where('nombre', $nombre)->first();
            if ($permiso) {
                DB::table('rol_permisos')->insertOrIgnore([
                    'rol_id' => $rolId,
                    'permiso_id' => $permiso->id
                ]);
            }
        }
    }
} 