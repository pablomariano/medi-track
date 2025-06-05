<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Genero;
use App\Models\PersonalMedico;
use App\Models\Cuidador;
use App\Models\Apoderado;
use App\Models\Paciente;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Asegurar que existen los géneros básicos
        $this->createGeneros();
        
        // Crear usuarios de prueba con roles coherentes
        $this->createAdminUser();
        $this->createMedicoUser();
        $this->createCuidadorUser();
        $this->createApoderadoUser();
        $this->createPacienteUser();
        $this->createPacienteSinUsuario();
    }

    private function createGeneros(): void
    {
        $generos = [
            ['id' => 'M', 'nombre' => 'Masculino'],
            ['id' => 'F', 'nombre' => 'Femenino'],
            ['id' => 'O', 'nombre' => 'Otro'],
        ];

        foreach ($generos as $genero) {
            Genero::firstOrCreate(['id' => $genero['id']], $genero);
        }
    }

    private function createAdminUser(): void
    {
        $adminRole = Role::where('nombre', 'admin')->first();
        
        $admin = User::create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@meditrack.com',
            'password' => Hash::make('password'),
            'telefono' => '+56 9 1111 1111',
            'rol_id' => $adminRole->id,
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        echo "✅ Usuario admin creado: {$admin->email}\n";
    }

    private function createMedicoUser(): void
    {
        $medicoRole = Role::where('nombre', 'medico')->first();
        
        $medico = User::create([
            'name' => 'Dr. Juan Pérez',
            'email' => 'medico@meditrack.com',
            'password' => Hash::make('password'),
            'telefono' => '+56 9 2222 2222',
            'rol_id' => $medicoRole->id,
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        // Crear registro en personal_medico
        PersonalMedico::create([
            'usuario_id' => $medico->id,
            'especialidad' => 'Medicina Interna',
            'numero_colegiatura' => 'MED-12345',
            'institucion' => 'Hospital Central',
            'anos_experiencia' => 10,
        ]);

        echo "✅ Usuario médico creado: {$medico->email}\n";
    }

    private function createCuidadorUser(): void
    {
        $cuidadorRole = Role::where('nombre', 'cuidador')->first();
        
        $cuidador = User::create([
            'name' => 'María González',
            'email' => 'cuidador@meditrack.com',
            'password' => Hash::make('password'),
            'telefono' => '+56 9 3333 3333',
            'rol_id' => $cuidadorRole->id,
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        // Crear registro en cuidadores
        Cuidador::create([
            'usuario_id' => $cuidador->id,
            'certificaciones' => 'Certificado en primeros auxilios, Curso de cuidado de adultos mayores',
            'experiencia_anos' => 5,
            'disponibilidad_horaria' => 'Lunes a Viernes 8:00-18:00',
            'tarifa_hora' => 15000,
        ]);

        echo "✅ Usuario cuidador creado: {$cuidador->email}\n";
    }

    private function createApoderadoUser(): void
    {
        $apoderadoRole = Role::where('nombre', 'apoderado')->first();
        
        $apoderado = User::create([
            'name' => 'Carlos Silva',
            'email' => 'apoderado@meditrack.com',
            'password' => Hash::make('password'),
            'telefono' => '+56 9 4444 4444',
            'rol_id' => $apoderadoRole->id,
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        // Crear registro en apoderados
        Apoderado::create([
            'usuario_id' => $apoderado->id,
            'relacion_paciente' => 'padre',
            'es_contacto_emergencia' => true,
        ]);

        echo "✅ Usuario apoderado creado: {$apoderado->email}\n";
    }

    private function createPacienteUser(): void
    {
        $pacienteRole = Role::where('nombre', 'paciente')->first();
        
        $paciente = User::create([
            'name' => 'Ana López',
            'email' => 'paciente@meditrack.com',
            'password' => Hash::make('password'),
            'telefono' => '+56 9 5555 5555',
            'rol_id' => $pacienteRole->id,
            'activo' => true,
            'email_verified_at' => now(),
        ]);

        // Crear registro en pacientes (con usuario)
        Paciente::create([
            'usuario_id' => $paciente->id,
            'nombre' => 'Ana López',
            'fecha_nacimiento' => '1985-03-15',
            'genero_id' => 'F',
            'numero_documento' => '12.345.678-9',
            'tipo_documento' => 'rut',
            'tipo_sangre' => 'O+',
            'altura' => 165.5,
            'direccion' => 'Av. Principal 123, Santiago',
            'telefono_emergencia' => '+56 9 6666 6666',
            'observaciones_medicas' => 'Alérgica a la penicilina',
            'activo' => true,
        ]);

        echo "✅ Usuario paciente con cuenta creado: {$paciente->email}\n";
    }

    private function createPacienteSinUsuario(): void
    {
        // Crear paciente sin cuenta de usuario (menor de edad)
        Paciente::create([
            'usuario_id' => null,
            'nombre' => 'Pedro Silva Menor',
            'fecha_nacimiento' => '2010-08-22',
            'genero_id' => 'M',
            'numero_documento' => '25.987.654-3',
            'tipo_documento' => 'rut',
            'tipo_sangre' => 'A+',
            'altura' => 140.0,
            'direccion' => 'Calle Secundaria 456, Valparaíso',
            'telefono_emergencia' => '+56 9 4444 4444', // Mismo del apoderado
            'observaciones_medicas' => 'Asma leve, inhalador de rescate',
            'activo' => true,
        ]);

        echo "✅ Paciente sin cuenta de usuario creado: Pedro Silva Menor\n";
    }
} 