<?php

namespace App\Policies;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PacientePolicy
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

        // Verificar permisos básicos
        return $user->hasPermission('pacientes.index');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Paciente $paciente): bool
    {
        // Admin siempre puede ver
        if ($user->isAdmin()) {
            return true;
        }

        // Médicos pueden ver todos los pacientes
        if ($user->hasRole('medico')) {
            return $user->hasPermission('pacientes.index');
        }

        // Cuidadores solo pueden ver pacientes asignados
        if ($user->hasRole('cuidador')) {
            return $this->isCuidadorAssigned($user, $paciente);
        }

        // Apoderados solo pueden ver pacientes a cargo
        if ($user->hasRole('apoderado')) {
            return $this->isApoderadoAssigned($user, $paciente);
        }

        // Pacientes solo pueden ver sus propios datos
        if ($user->hasRole('paciente')) {
            return $paciente->usuario_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('pacientes.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Paciente $paciente): bool
    {
        // Admin siempre puede editar
        if ($user->isAdmin()) {
            return true;
        }

        // Médicos pueden editar todos los pacientes
        if ($user->hasRole('medico') && $user->hasPermission('pacientes.edit')) {
            return true;
        }

        // Cuidadores pueden editar solo pacientes asignados
        if ($user->hasRole('cuidador') && $user->hasPermission('pacientes.edit')) {
            return $this->isCuidadorAssigned($user, $paciente);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Paciente $paciente): bool
    {
        // Solo admin y médicos pueden eliminar pacientes
        return $user->hasPermission('pacientes.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Paciente $paciente): bool
    {
        return $user->hasPermission('pacientes.delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Paciente $paciente): bool
    {
        return $user->isAdmin();
    }

    /**
     * Verificar si el cuidador está asignado al paciente
     */
    private function isCuidadorAssigned(User $user, Paciente $paciente): bool
    {
        return \DB::table('paciente_cuidadores')
            ->where('paciente_id', $paciente->id)
            ->where('cuidador_usuario_id', $user->id)
            ->where('activo', true)
            ->exists();
    }

    /**
     * Verificar si el apoderado está asignado al paciente
     */
    private function isApoderadoAssigned(User $user, Paciente $paciente): bool
    {
        return \DB::table('paciente_apoderados')
            ->where('paciente_id', $paciente->id)
            ->where('apoderado_usuario_id', $user->id)
            ->exists();
    }
}
