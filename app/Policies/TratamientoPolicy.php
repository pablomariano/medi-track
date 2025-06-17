<?php

namespace App\Policies;

use App\Models\Tratamiento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TratamientoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin siempre puede ver todos
        if ($user->isAdmin()) {
            return true;
        }

        // Médicos y cuidadores pueden ver tratamientos (filtrados por lógica de negocio)
        return $user->hasAnyRole(['medico', 'cuidador']) && 
               $user->hasPermission('pacientes.index');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tratamiento $tratamiento): bool
    {
        // Admin siempre puede ver
        if ($user->isAdmin()) {
            return true;
        }

        // Médicos solo pueden ver tratamientos que han creado
        if ($user->hasRole('medico')) {
            return $tratamiento->medico_usuario_id === $user->id;
        }

        // Cuidadores solo pueden ver tratamientos de pacientes asignados
        if ($user->hasRole('cuidador')) {
            return $this->isCuidadorAssignedToPaciente($user, $tratamiento->paciente_id);
        }

        // Apoderados pueden ver tratamientos de pacientes a cargo
        if ($user->hasRole('apoderado')) {
            return $this->isApoderadoAssignedToPaciente($user, $tratamiento->paciente_id);
        }

        // Pacientes pueden ver sus propios tratamientos
        if ($user->hasRole('paciente')) {
            return $tratamiento->paciente->usuario_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo médicos y admin pueden crear tratamientos
        return $user->hasRole('medico') || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tratamiento $tratamiento): bool
    {
        // Admin siempre puede editar
        if ($user->isAdmin()) {
            return true;
        }

        // Solo el médico responsable puede editar el tratamiento
        if ($user->hasRole('medico')) {
            return $tratamiento->medico_usuario_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tratamiento $tratamiento): bool
    {
        // Admin siempre puede eliminar
        if ($user->isAdmin()) {
            return true;
        }

        // Solo el médico responsable puede eliminar el tratamiento
        if ($user->hasRole('medico')) {
            return $tratamiento->medico_usuario_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tratamiento $tratamiento): bool
    {
        return $this->delete($user, $tratamiento);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tratamiento $tratamiento): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can change treatment status.
     */
    public function changeStatus(User $user, Tratamiento $tratamiento): bool
    {
        // Admin siempre puede cambiar estado
        if ($user->isAdmin()) {
            return true;
        }

        // Solo el médico responsable puede cambiar el estado
        if ($user->hasRole('medico')) {
            return $tratamiento->medico_usuario_id === $user->id;
        }

        return false;
    }

    /**
     * Verificar si el cuidador está asignado al paciente del tratamiento
     */
    private function isCuidadorAssignedToPaciente(User $user, int $pacienteId): bool
    {
        return \DB::table('paciente_cuidadores')
            ->where('paciente_id', $pacienteId)
            ->where('cuidador_usuario_id', $user->id)
            ->where('activo', true)
            ->exists();
    }

    /**
     * Verificar si el apoderado está asignado al paciente del tratamiento
     */
    private function isApoderadoAssignedToPaciente(User $user, int $pacienteId): bool
    {
        return \DB::table('paciente_apoderados')
            ->where('paciente_id', $pacienteId)
            ->where('apoderado_usuario_id', $user->id)
            ->exists();
    }
}
