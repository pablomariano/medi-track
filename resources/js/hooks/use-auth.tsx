import { usePage } from '@inertiajs/react';
import { AuthContextType, User } from '@/types';

export function useAuth(): AuthContextType {
    const { props } = usePage();
    const user = (props.auth as any)?.user as User | null;

    const hasRole = (roleName: string): boolean => {
        if (!user?.role) return false;
        return user.role.nombre === roleName;
    };

    const hasPermission = (permissionName: string): boolean => {
        if (!user) return false;
        
        // Check if user has explicit permissions array
        if (user.can_permissions && Array.isArray(user.can_permissions)) {
            return user.can_permissions.includes(permissionName);
        }
        
        // Fallback to permissions array if available
        if (user.permissions && Array.isArray(user.permissions)) {
            return user.permissions.some(permission => permission.nombre === permissionName);
        }
        
        return false;
    };

    const hasAnyRole = (roleNames: string[]): boolean => {
        if (!user?.role) return false;
        return roleNames.includes(user.role.nombre);
    };

    const hasAnyPermission = (permissionNames: string[]): boolean => {
        return permissionNames.some(permission => hasPermission(permission));
    };

    const isAdmin = (): boolean => {
        return hasRole('admin');
    };

    const canAccess = (resource: string, action: string = 'index'): boolean => {
        // Admin always has access
        if (isAdmin()) return true;

        // Build permission name from resource and action
        const permissionName = `${resource}.${action}`;
        return hasPermission(permissionName);
    };

    return {
        user,
        hasRole,
        hasPermission,
        hasAnyRole,
        hasAnyPermission,
        isAdmin,
        canAccess,
    };
} 