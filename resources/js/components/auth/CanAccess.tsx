import React from 'react';
import { useAuth } from '@/hooks/use-auth';
import { ProtectedComponentProps } from '@/types';

interface CanAccessProps extends ProtectedComponentProps {
    resource?: string;
    action?: string;
    hideWhenDenied?: boolean;
}

export function CanAccess({
    permission,
    role,
    anyPermissions,
    anyRoles,
    requireAdmin = false,
    resource,
    action = 'index',
    fallback = null,
    children,
}: CanAccessProps) {
    const auth = useAuth();

    // Check admin requirement first
    if (requireAdmin && !auth.isAdmin()) {
        return <>{fallback}</>;
    }

    // Check specific role
    if (role && !auth.hasRole(role)) {
        return <>{fallback}</>;
    }

    // Check any of multiple roles
    if (anyRoles && !auth.hasAnyRole(anyRoles)) {
        return <>{fallback}</>;
    }

    // Check specific permission
    if (permission && !auth.hasPermission(permission)) {
        return <>{fallback}</>;
    }

    // Check any of multiple permissions
    if (anyPermissions && !auth.hasAnyPermission(anyPermissions)) {
        return <>{fallback}</>;
    }

    // Check resource-based access
    if (resource && !auth.canAccess(resource, action)) {
        return <>{fallback}</>;
    }

    // If no restrictions are specified or all checks pass, render children
    return <>{children}</>;
}

// Convenience components for common use cases
export function AdminOnly({ children, fallback = null }: { children: React.ReactNode; fallback?: React.ReactNode }) {
    return (
        <CanAccess requireAdmin fallback={fallback}>
            {children}
        </CanAccess>
    );
}

export function MedicalOnly({ children, fallback = null }: { children: React.ReactNode; fallback?: React.ReactNode }) {
    return (
        <CanAccess anyRoles={['admin', 'medico']} fallback={fallback}>
            {children}
        </CanAccess>
    );
}

export function CaregiverAccess({ children, fallback = null }: { children: React.ReactNode; fallback?: React.ReactNode }) {
    return (
        <CanAccess anyRoles={['admin', 'medico', 'cuidador']} fallback={fallback}>
            {children}
        </CanAccess>
    );
} 