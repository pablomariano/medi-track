import * as React from "react";
import { NavUser } from '@/components/nav-user';
import { 
  Sidebar, 
  SidebarContent, 
  SidebarHeader, 
  SidebarTrigger,
  SidebarMenu,
  SidebarMenuItem,
  SidebarMenuButton,
  SidebarFooter
} from '@/components/ui/sidebar';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ProtectedLink } from '@/components/auth/ProtectedLink';
import { useAuth } from '@/hooks/use-auth';
import { CanAccess } from '@/components/auth/CanAccess';
import { usePage } from '@inertiajs/react';
import { Home, Calendar, Users, FileText, Settings, PlusCircle, Pill, Shield, Key, UserCheck, Stethoscope, Heart, UserX, User, LucideIcon, UserPlus, Activity, Clock, BarChart3, UserCog, Code, Settings2, Mail } from 'lucide-react';

interface NavigationItem {
  title: string;
  href: string;
  icon: LucideIcon;
  permission?: string;
  role?: string;
  anyRoles?: string[];
  anyPermissions?: string[];
  requireAdmin?: boolean;
  resource?: string;
  action?: string;
}

interface NavigationSection {
  title: string;
  items: NavigationItem[];
  permission?: string;
  role?: string;
  anyRoles?: string[];
  requireAdmin?: boolean;
}

const navigationSections: NavigationSection[] = [
  {
    title: 'Dashboard',
    items: [
      {
        title: 'Dashboard Principal',
        href: '/dashboard',
        icon: Home,
      },
      {
        title: 'Panel Administración',
        href: '/admin-dashboard',
        icon: Settings2,
        requireAdmin: true,
      },
      {
        title: 'Dashboard Medicamentos',
        href: '/dashboard/medicamentos',
        icon: BarChart3,
        anyRoles: ['admin', 'medico', 'cuidador'],
      },
      {
        title: 'Adherencia Temporal',
        href: '/dashboard/adherencia-temporal',
        icon: Clock,
        anyRoles: ['admin', 'medico', 'cuidador'],
      },
    ],
  },
  {
    title: 'Mi Información',
    anyRoles: ['paciente'],
    items: [
      {
        title: 'Mi Perfil',
        href: '/mi-perfil',
        icon: User,
        anyRoles: ['paciente'],
      },
      {
        title: 'Mis Medicamentos',
        href: '/mis-medicamentos',
        icon: Pill,
        anyRoles: ['paciente'],
      },
      {
        title: 'Mis Tratamientos',
        href: '/mis-tratamientos',
        icon: Activity,
        anyRoles: ['paciente'],
      },
      {
        title: 'Mi Cronograma',
        href: '/mi-cronograma',
        icon: Calendar,
        anyRoles: ['paciente'],
      },
      {
        title: 'Preferencias de Email',
        href: '/settings/email-preferences',
        icon: Mail,
        anyRoles: ['paciente'],
      },
    ],
  },
  {
    title: 'Medicamentos',
    anyRoles: ['admin', 'medico', 'cuidador'],
    items: [
      {
        title: 'Medicamentos',
        href: '/medicamentos',
        icon: Pill,
        resource: 'medicamentos',
        action: 'index',
      },
      {
        title: 'Data Table',
        href: '/medicamentos-datatable',
        icon: BarChart3,
        resource: 'medicamentos',
        action: 'index',
      },
      {
        title: 'Tratamientos',
        href: '/tratamientos',
        icon: Activity,
        resource: 'tratamientos',
        action: 'index',
      },
      {
        title: 'Pendientes',
        href: '/administraciones/pendientes',
        icon: Clock,
        anyRoles: ['admin', 'medico', 'cuidador'],
      },
      {
        title: 'Historial',
        href: '/administraciones/historial',
        icon: FileText,
        anyRoles: ['admin', 'medico', 'cuidador'],
      },
    ],
  },
  {
    title: 'Gestión de Usuarios',
    anyRoles: ['admin', 'medico'],
    items: [
      {
        title: 'Pacientes',
        href: '/pacientes',
        icon: User,
        resource: 'pacientes',
        action: 'index',
      },
      {
        title: 'Personal Médico',
        href: '/personal-medico',
        icon: Stethoscope,
        anyRoles: ['admin', 'medico'],
      },
      {
        title: 'Cuidadores',
        href: '/cuidadores',
        icon: Heart,
        anyRoles: ['admin', 'medico'],
      },
      {
        title: 'Apoderados',
        href: '/apoderados',
        icon: UserX,
        anyRoles: ['admin', 'medico'],
      },
      {
        title: 'Usuarios',
        href: '/usuarios',
        icon: Users,
        permission: 'usuarios.index',
      },
      {
        title: 'Asignaciones Cuidadores',
        href: '/asignaciones-cuidadores',
        icon: UserCog,
        anyRoles: ['admin', 'medico'],
      },
      {
        title: 'Historial Asignaciones',
        href: '/asignaciones-cuidadores/historial',
        icon: FileText,
        anyRoles: ['admin', 'medico'],
      },
    ],
  },
  {
    title: 'Configuración',
    requireAdmin: true,
    items: [
      {
        title: 'Roles',
        href: '/roles',
        icon: Shield,
        permission: 'roles.index',
      },
      {
        title: 'Permisos',
        href: '/permisos',
        icon: Key,
        permission: 'permisos.index',
      },
      {
        title: 'Géneros',
        href: '/generos',
        icon: UserCheck,
        permission: 'generos.index',
      },
      {
        title: 'Medicines (Legacy)',
        href: '/medicines',
        icon: Pill,
        resource: 'medicines',
        action: 'index',
      },
      {
        title: 'Catálogo de Componentes',
        href: '/component-catalog',
        icon: Code,
        requireAdmin: true,
      },
      {
        title: 'Settings',
        href: '/settings',
        icon: Settings,
      },
    ],
  },
];

export function ProtectedSidebar() {
  const auth = useAuth();
  const page = usePage();
  
  // Función para verificar si una ruta está activa
  const isActiveRoute = (href: string): boolean => {
    // Comparación exacta para rutas específicas
    if (page.url === href) return true;
    
    // Para rutas que pueden tener sub-rutas (como /pacientes/1/edit)
    if (href !== '/' && href !== '/dashboard' && page.url.startsWith(href)) {
      return true;
    }
    
    return false;
  };

  const canAccessSection = (section: NavigationSection): boolean => {
    if (section.requireAdmin) return auth.isAdmin();
    if (section.role) return auth.hasRole(section.role);
    if (section.anyRoles) return auth.hasAnyRole(section.anyRoles);
    if (section.permission) return auth.hasPermission(section.permission);
    return true; // No restrictions
  };

  const canAccessItem = (item: NavigationItem): boolean => {
    if (item.requireAdmin) return auth.isAdmin();
    if (item.role) return auth.hasRole(item.role);
    if (item.anyRoles) return auth.hasAnyRole(item.anyRoles);
    if (item.permission) return auth.hasPermission(item.permission);
    if (item.anyPermissions) return auth.hasAnyPermission(item.anyPermissions);
    if (item.resource) return auth.canAccess(item.resource, item.action);
    return true; // No restrictions
  };

  return (
    <Sidebar variant="inset" collapsible="icon">
      <SidebarHeader className="px-2">
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild isActive={isActiveRoute('/dashboard')}>
              <ProtectedLink href="/dashboard" className="flex items-center">
                <span className="text-base font-semibold">MediTrack</span>
              </ProtectedLink>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>
      
      <SidebarContent>
        <ScrollArea className="h-full">
          <SidebarMenu>
            {/* Botón Crear Usuario - Solo para usuarios autorizados */}
            <CanAccess permission="usuarios.create" hideWhenDenied>
              <SidebarMenuItem>
                <SidebarMenuButton asChild isActive={isActiveRoute('/usuarios/select-type')}>
                  <ProtectedLink 
                    href="/usuarios/select-type" 
                    className="flex items-center gap-2"
                    permission="usuarios.create"
                    hideWhenDenied
                  >
                    <UserPlus className="h-4 w-4" />
                    <span>Crear Usuario</span>
                  </ProtectedLink>
                </SidebarMenuButton>
              </SidebarMenuItem>
            </CanAccess>
            
            {/* Secciones dinámicas según permisos */}
            {navigationSections.map((section) => {
              if (!canAccessSection(section)) return null;

              // Filtrar items visibles para esta sección
              const visibleItems = section.items.filter(canAccessItem);
              
              // No mostrar sección si no hay items visibles
              if (visibleItems.length === 0) return null;

              return (
                <React.Fragment key={section.title}>
                  <div className="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    {section.title}
                  </div>
                  {visibleItems.map((item: NavigationItem) => (
                    <SidebarMenuItem key={item.title}>
                      <SidebarMenuButton asChild tooltip={item.title} isActive={isActiveRoute(item.href)}>
                        <ProtectedLink 
                          href={item.href} 
                          className="flex items-center gap-2"
                          permission={item.permission}
                          role={item.role}
                          anyRoles={item.anyRoles}
                          anyPermissions={item.anyPermissions}
                          requireAdmin={item.requireAdmin}
                          resource={item.resource}
                          action={item.action}
                          hideWhenDenied
                        >
                          {React.createElement(item.icon, { className: "h-4 w-4" })}
                          <span>{item.title}</span>
                        </ProtectedLink>
                      </SidebarMenuButton>
                    </SidebarMenuItem>
                  ))}
                </React.Fragment>
              );
            })}
          </SidebarMenu>
        </ScrollArea>
      </SidebarContent>

      <SidebarFooter>
        <SidebarMenu>
          <SidebarMenuItem>
            {/* <SidebarMenuButton asChild tooltip="Profile" isActive={isActiveRoute('/profile')}>
              <ProtectedLink href="/profile" className="flex items-center gap-2">
                <Users className="h-4 w-4" />
                <span>Profile</span>
              </ProtectedLink>
            </SidebarMenuButton> */}
          </SidebarMenuItem>
        </SidebarMenu>
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
} 