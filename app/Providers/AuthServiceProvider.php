<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Paciente;
use App\Models\Tratamiento;
use App\Models\Medicine;
use App\Policies\PacientePolicy;
use App\Policies\TratamientoPolicy;
use App\Policies\MedicinePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Paciente::class => PacientePolicy::class,
        Tratamiento::class => TratamientoPolicy::class,
        Medicine::class => MedicinePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Registrar las policies
        $this->registerPolicies();

        // Gates adicionales para funcionalidades específicas
        $this->registerGates();
    }

    /**
     * Registrar gates adicionales para el sistema
     */
    private function registerGates(): void
    {
        // Gate para administración general
        Gate::define('admin-access', function ($user) {
            return $user->isAdmin();
        });

        // Gate para acceso médico
        Gate::define('medical-access', function ($user) {
            return $user->hasAnyRole(['admin', 'medico']);
        });

        // Gate para gestión de cuidadores
        Gate::define('caregiver-management', function ($user) {
            return $user->hasAnyRole(['admin', 'medico']) && 
                   $user->hasPermission('cuidadores.index');
        });

        // Gate para administraciones de medicamentos
        Gate::define('medicine-administration', function ($user) {
            return $user->hasAnyRole(['medico', 'cuidador']) && 
                   $user->hasPermission('pacientes.index');
        });

        // Gate para reportes médicos
        Gate::define('medical-reports', function ($user) {
            return $user->hasAnyRole(['admin', 'medico']);
        });

        // Gate para gestión de usuarios
        Gate::define('user-management', function ($user) {
            return $user->hasPermission('usuarios.index');
        });

        // Gate para configuración del sistema
        Gate::define('system-configuration', function ($user) {
            return $user->hasAnyRole(['admin']) && 
                   $user->hasAnyPermission(['roles.index', 'permisos.index']);
        });

        // Gate para cronogramas
        Gate::define('schedule-access', function ($user) {
            return $user->hasAnyRole(['admin', 'medico', 'cuidador']) && 
                   $user->hasPermission('pacientes.index');
        });

        // Gate para asignaciones específicas de pacientes
        Gate::define('patient-assignment', function ($user, $pacienteId = null) {
            if ($user->isAdmin()) {
                return true;
            }

            if ($user->hasRole('medico')) {
                return true; // Los médicos pueden ver todos sus pacientes
            }

            if ($user->hasRole('cuidador') && $pacienteId) {
                return \DB::table('paciente_cuidadores')
                    ->where('paciente_id', $pacienteId)
                    ->where('cuidador_usuario_id', $user->id)
                    ->where('activo', true)
                    ->exists();
            }

            if ($user->hasRole('apoderado') && $pacienteId) {
                return \DB::table('paciente_apoderados')
                    ->where('paciente_id', $pacienteId)
                    ->where('apoderado_usuario_id', $user->id)
                    ->exists();
            }

            return false;
        });

        // Gate para tratamientos específicos
        Gate::define('treatment-access', function ($user, $tratamientoId = null) {
            if ($user->isAdmin()) {
                return true;
            }

            if (!$tratamientoId) {
                return false;
            }

            $tratamiento = \App\Models\Tratamiento::find($tratamientoId);
            if (!$tratamiento) {
                return false;
            }

            // Médico responsable
            if ($user->hasRole('medico')) {
                return $tratamiento->medico_usuario_id === $user->id;
            }

            // Cuidador asignado al paciente
            if ($user->hasRole('cuidador')) {
                return \DB::table('paciente_cuidadores')
                    ->where('paciente_id', $tratamiento->paciente_id)
                    ->where('cuidador_usuario_id', $user->id)
                    ->where('activo', true)
                    ->exists();
            }

            return false;
        });
    }
}
