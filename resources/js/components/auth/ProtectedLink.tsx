import React from 'react';
import { Link } from '@inertiajs/react';
import { useAuth } from '@/hooks/use-auth';
import { cn } from '@/lib/utils';

interface ProtectedLinkProps extends React.ComponentProps<typeof Link> {
    permission?: string;
    role?: string;
    anyPermissions?: string[];
    anyRoles?: string[];
    requireAdmin?: boolean;
    resource?: string;
    action?: string;
    hideWhenDenied?: boolean;
    disableWhenDenied?: boolean;
    tooltip?: string;
}

export function ProtectedLink({
    permission,
    role,
    anyPermissions,
    anyRoles,
    requireAdmin = false,
    resource,
    action = 'index',
    hideWhenDenied = false,
    disableWhenDenied = true,
    tooltip,
    className,
    children,
    ...props
}: ProtectedLinkProps) {
    const auth = useAuth();

    // Check if user has access
    const hasAccess = (() => {
        if (requireAdmin) return auth.isAdmin();
        if (role) return auth.hasRole(role);
        if (anyRoles) return auth.hasAnyRole(anyRoles);
        if (permission) return auth.hasPermission(permission);
        if (anyPermissions) return auth.hasAnyPermission(anyPermissions);
        if (resource) return auth.canAccess(resource, action);
        return true; // No restrictions
    })();

    // If hiding when denied and no access, don't render
    if (hideWhenDenied && !hasAccess) {
        return null;
    }

    // If access granted, render normal link
    if (hasAccess) {
        return (
            <Link className={className} {...props}>
                {children}
            </Link>
        );
    }

    // If disabling when denied, render as span with disabled styling
    if (disableWhenDenied) {
        return (
            <span
                className={cn(
                    className,
                    'opacity-50 cursor-not-allowed text-muted-foreground'
                )}
                title={tooltip || 'No tienes permisos para acceder a esta sección'}
            >
                {children}
            </span>
        );
    }

    // Default: render as normal link (should not happen)
    return (
        <Link className={className} {...props}>
            {children}
        </Link>
    );
}

// Hook para verificar si una ruta debe estar activa
export function useCanNavigate() {
    const auth = useAuth();

    const canNavigate = (
        permission?: string,
        role?: string,
        anyPermissions?: string[],
        anyRoles?: string[],
        requireAdmin?: boolean,
        resource?: string,
        action: string = 'index'
    ): boolean => {
        if (requireAdmin) return auth.isAdmin();
        if (role) return auth.hasRole(role);
        if (anyRoles) return auth.hasAnyRole(anyRoles);
        if (permission) return auth.hasPermission(permission);
        if (anyPermissions) return auth.hasAnyPermission(anyPermissions);
        if (resource) return auth.canAccess(resource, action);
        return true;
    };

    return { canNavigate };
} 