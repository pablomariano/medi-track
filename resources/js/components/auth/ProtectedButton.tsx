import React from 'react';
import { Button } from '@/components/ui/button';
import { useAuth } from '@/hooks/use-auth';
import { CanAccess } from './CanAccess';
import { cn } from '@/lib/utils';

interface ProtectedButtonProps extends React.ComponentProps<typeof Button> {
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
    fallbackVariant?: 'outline' | 'ghost' | 'secondary';
}

export function ProtectedButton({
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
    fallbackVariant = 'outline',
    className,
    children,
    ...props
}: ProtectedButtonProps) {
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

    // If access granted, render normal button
    if (hasAccess) {
        return (
            <Button className={className} {...props}>
                {children}
            </Button>
        );
    }

    // If disabling when denied, render disabled button
    if (disableWhenDenied) {
        return (
            <Button
                className={cn(className, 'opacity-50 cursor-not-allowed')}
                variant={fallbackVariant}
                disabled
                title={tooltip || 'No tienes permisos para esta acción'}
                {...props}
            >
                {children}
            </Button>
        );
    }

    // Default: render as normal button (should not happen)
    return (
        <Button className={className} {...props}>
            {children}
        </Button>
    );
}

// Convenience components for common actions
interface ActionButtonProps extends Omit<ProtectedButtonProps, 'resource' | 'action'> {
    resource: string;
}

export function CreateButton({ resource, children = 'Crear', ...props }: ActionButtonProps) {
    return (
        <ProtectedButton resource={resource} action="create" {...props}>
            {children}
        </ProtectedButton>
    );
}

export function EditButton({ resource, children = 'Editar', ...props }: ActionButtonProps) {
    return (
        <ProtectedButton resource={resource} action="edit" {...props}>
            {children}
        </ProtectedButton>
    );
}

export function DeleteButton({ resource, children = 'Eliminar', ...props }: ActionButtonProps) {
    return (
        <ProtectedButton 
            resource={resource} 
            action="delete" 
            variant="destructive"
            fallbackVariant="outline"
            {...props}
        >
            {children}
        </ProtectedButton>
    );
}

export function ViewButton({ resource, children = 'Ver', ...props }: ActionButtonProps) {
    return (
        <ProtectedButton resource={resource} action="index" {...props}>
            {children}
        </ProtectedButton>
    );
} 