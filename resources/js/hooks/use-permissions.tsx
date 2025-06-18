import { usePage } from '@inertiajs/react';
import { User } from '@/types';
import { useAuth } from './use-auth';

interface UsePermissionsReturn {
    // Pacientes
    canViewPacientes: boolean;
    canCreatePacientes: boolean;
    canEditPacientes: boolean;
    canDeletePacientes: boolean;
    
    // Tratamientos
    canViewTratamientos: boolean;
    canCreateTratamientos: boolean;
    canEditTratamientos: boolean;
    canDeleteTratamientos: boolean;
    
    // Medicamentos
    canViewMedicamentos: boolean;
    canCreateMedicamentos: boolean;
    canEditMedicamentos: boolean;
    canDeleteMedicamentos: boolean;
    
    // Usuarios
    canViewUsuarios: boolean;
    canCreateUsuarios: boolean;
    canEditUsuarios: boolean;
    canDeleteUsuarios: boolean;
    
    // Administración
    canViewRoles: boolean;
    canViewPermisos: boolean;
    canViewGeneros: boolean;
    
    // Gestión médica
    canManagePersonalMedico: boolean;
    canManageCuidadores: boolean;
    canManageApoderados: boolean;
    canViewAdministraciones: boolean;
    
    // Cronogramas
    canViewCronogramas: boolean;
    canViewResumenSemanal: boolean;
    
    // Función genérica
    canPerform: (resource: string, action: string) => boolean;
}

export function usePermissions(): UsePermissionsReturn {
    const auth = useAuth();

    // Helpers para verificar permisos específicos
    const canPerform = (resource: string, action: string): boolean => {
        return auth.canAccess(resource, action);
    };

    return {
        // Pacientes
        canViewPacientes: auth.canAccess('pacientes', 'index'),
        canCreatePacientes: auth.canAccess('pacientes', 'create'),
        canEditPacientes: auth.canAccess('pacientes', 'edit'),
        canDeletePacientes: auth.canAccess('pacientes', 'delete'),

        // Tratamientos  
        canViewTratamientos: auth.canAccess('tratamientos', 'index'),
        canCreateTratamientos: auth.hasAnyRole(['admin', 'medico']),
        canEditTratamientos: auth.hasAnyRole(['admin', 'medico']),
        canDeleteTratamientos: auth.hasAnyRole(['admin', 'medico']),

        // Medicamentos
        canViewMedicamentos: auth.canAccess('medicamentos', 'index'),
        canCreateMedicamentos: auth.canAccess('medicamentos', 'create'),
        canEditMedicamentos: auth.canAccess('medicamentos', 'edit'),
        canDeleteMedicamentos: auth.canAccess('medicamentos', 'delete'),

        // Usuarios
        canViewUsuarios: auth.hasPermission('usuarios.index'),
        canCreateUsuarios: auth.hasPermission('usuarios.create'),
        canEditUsuarios: auth.hasPermission('usuarios.edit'),
        canDeleteUsuarios: auth.hasPermission('usuarios.delete'),

        // Administración
        canViewRoles: auth.hasPermission('roles.index'),
        canViewPermisos: auth.hasPermission('permisos.index'),
        canViewGeneros: auth.hasPermission('generos.index'),

        // Gestión médica
        canManagePersonalMedico: auth.hasAnyRole(['admin', 'medico']),
        canManageCuidadores: auth.hasAnyRole(['admin', 'medico']),
        canManageApoderados: auth.hasAnyRole(['admin', 'medico']),
        canViewAdministraciones: auth.hasAnyRole(['admin', 'medico', 'cuidador']),

        // Cronogramas
        canViewCronogramas: auth.hasAnyRole(['admin', 'medico', 'cuidador']),
        canViewResumenSemanal: auth.hasAnyRole(['admin', 'medico', 'cuidador']),

        // Función genérica
        canPerform,
    };
}

// Hook específico para roles
export function useRoles() {
    const auth = useAuth();

    return {
        isAdmin: auth.isAdmin(),
        isMedico: auth.hasRole('medico'),
        isCuidador: auth.hasRole('cuidador'),
        isApoderado: auth.hasRole('apoderado'),
        isPaciente: auth.hasRole('paciente'),
        
        // Helpers para combinaciones comunes
        isMedicalStaff: auth.hasAnyRole(['admin', 'medico']),
        isCareStaff: auth.hasAnyRole(['admin', 'medico', 'cuidador']),
        isAuthorizedUser: auth.hasAnyRole(['admin', 'medico', 'cuidador', 'apoderado']),
        
        currentRole: auth.user?.role?.nombre || 'none',
        user: auth.user,
    };
} 