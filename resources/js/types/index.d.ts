import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    role?: Role;
    permissions?: Permission[];
    can_permissions?: string[];
    [key: string]: unknown; // This allows for additional properties...
}

export interface Role {
    id: number;
    nombre: string;
    descripcion?: string;
    activo: boolean;
    created_at: string;
    updated_at: string;
}

export interface Permission {
    id: number;
    nombre: string;
    descripcion?: string;
    categoria?: string;
    activo: boolean;
    created_at: string;
    updated_at: string;
}

export interface AuthContextType {
    user: User | null;
    hasRole: (roleName: string) => boolean;
    hasPermission: (permissionName: string) => boolean;
    hasAnyRole: (roleNames: string[]) => boolean;
    hasAnyPermission: (permissionNames: string[]) => boolean;
    isAdmin: () => boolean;
    canAccess: (resource: string, action?: string) => boolean;
}

export interface ProtectedComponentProps {
    permission?: string;
    role?: string;
    anyPermissions?: string[];
    anyRoles?: string[];
    requireAdmin?: boolean;
    fallback?: React.ReactNode;
    children: React.ReactNode;
}

/**
 * Este archivo contiene las definiciones de tipos TypeScript para el sistema de autenticación y autorización.
 * 
 * Principales interfaces:
 * 
 * - User: Define la estructura de un usuario con sus propiedades básicas y relaciones
 *   como rol y permisos.
 * 
 * - Role: Define la estructura de un rol en el sistema, incluyendo su nombre,
 *   descripción y estado activo.
 * 
 * - Permission: Define la estructura de un permiso individual con propiedades como
 *   nombre, descripción y categoría.
 * 
 * - AuthContextType: Define el contexto de autenticación con métodos helper para
 *   verificar roles y permisos del usuario actual.
 * 
 * - ProtectedComponentProps: Define las props para componentes protegidos que requieren
 *   ciertos roles o permisos para ser renderizados.
 * 
 * Este archivo es fundamental para el tipado estático en el frontend de la aplicación,
 * asegurando consistencia en el manejo de usuarios, roles y permisos.
 */
