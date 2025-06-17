<?php

namespace App\Policies;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MedicinePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos los roles autenticados pueden ver medicamentos
        return $user->hasPermission('medicines.index');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Medicine $medicine): bool
    {
        // Todos los usuarios con permiso pueden ver medicamentos individuales
        return $user->hasPermission('medicines.index');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo admin y médicos pueden crear medicamentos
        return $user->hasPermission('medicines.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Medicine $medicine): bool
    {
        // Solo admin y médicos pueden editar medicamentos
        return $user->hasPermission('medicines.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Medicine $medicine): bool
    {
        // Solo admin y médicos pueden eliminar medicamentos
        return $user->hasPermission('medicines.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Medicine $medicine): bool
    {
        return $user->hasPermission('medicines.delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Medicine $medicine): bool
    {
        // Solo admin puede eliminar permanentemente
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage medicine inventory.
     */
    public function manageInventory(User $user): bool
    {
        // Solo admin y médicos pueden gestionar inventario
        return $user->hasAnyRole(['admin', 'medico']);
    }

    /**
     * Determine whether the user can view medicine reports.
     */
    public function viewReports(User $user): bool
    {
        // Admin, médicos y cuidadores pueden ver reportes
        return $user->hasAnyRole(['admin', 'medico', 'cuidador']);
    }
}
