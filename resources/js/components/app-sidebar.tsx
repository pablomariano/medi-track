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
import { Link, usePage } from '@inertiajs/react';
import { Home, Calendar, Users, FileText, Settings, PlusCircle, Pill, Shield, Key, UserCheck, Stethoscope, Heart, UserX, User, LucideIcon, UserPlus, Activity, Clock, BarChart3, UserCog, AlertTriangle, Eye, Code, Settings2 } from 'lucide-react';

interface NavigationItem {
  title: string;
  href: string;
  icon: LucideIcon;
}

const dashboardItems: NavigationItem[] = [
  {
    title: 'Dashboard Principal',
    href: '/dashboard',
    icon: Home,
  },
  {
    title: 'Panel Administración',
    href: '/admin-dashboard',
    icon: Settings2,
  },
  {
    title: 'Adherencia Temporal',
    href: '/dashboard/adherencia-temporal',
    icon: Clock,
  },
];

const medicamentosItems: NavigationItem[] = [
  {
    title: 'Medicamentos',
    href: '/medicamentos',
    icon: Pill,
  },
  {
    title: 'Tratamientos',
    href: '/tratamientos',
    icon: Activity,
  },
  {
    title: 'Pendientes',
    href: '/administraciones/pendientes',
    icon: Clock,
  },
  {
    title: 'Historial',
    href: '/administraciones/historial',
    icon: FileText,
  },
];

const usuariosItems: NavigationItem[] = [
  {
    title: 'Pacientes',
    href: '/pacientes',
    icon: User,
  },
  {
    title: 'Personal Médico',
    href: '/personal-medico',
    icon: Stethoscope,
  },
  {
    title: 'Cuidadores',
    href: '/cuidadores',
    icon: Heart,
  },
  {
    title: 'Apoderados',
    href: '/apoderados',
    icon: UserX,
  },
  {
    title: 'Usuarios',
    href: '/usuarios',
    icon: Users,
  },
  {
    title: 'Asignaciones Cuidadores',
    href: '/asignaciones-cuidadores',
    icon: UserCog,
  },
  {
    title: 'Historial Asignaciones',
    href: '/asignaciones-cuidadores/historial',
    icon: FileText,
  },
];

const configuracionItems: NavigationItem[] = [
  {
    title: 'Roles',
    href: '/roles',
    icon: Shield,
  },
  {
    title: 'Permisos',
    href: '/permisos',
    icon: Key,
  },
  {
    title: 'Géneros',
    href: '/generos',
    icon: UserCheck,
  },
];

const auditoriaItems: NavigationItem[] = [
  {
    title: 'Logs de Auditoría',
    href: '/audit',
    icon: Eye,
  },
  {
    title: 'Dashboard Auditoría',
    href: '/audit/dashboard',
    icon: AlertTriangle,
  },
];

export function AppSidebar() {
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

  return (
    <Sidebar variant="inset" collapsible="icon">
      <SidebarHeader className="px-2">
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild isActive={isActiveRoute('/dashboard')}>
              <Link href="/dashboard" prefetch className="flex items-center">
                <span className="text-base font-semibold">Meditrack</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>
      <SidebarContent>
        <ScrollArea className="h-full">
          <SidebarMenu>
            <SidebarMenuItem>
              <SidebarMenuButton asChild isActive={isActiveRoute('/usuarios/select-type')}>
                <Link href="/usuarios/select-type" className="flex items-center gap-2">
                  <UserPlus className="h-4 w-4" />
                  <span>Crear Usuario</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
            
            {/* Dashboard */}
            <div className="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
              Dashboard
            </div>
            {dashboardItems.map((item: NavigationItem) => (
              <SidebarMenuItem key={item.title}>
                <SidebarMenuButton asChild tooltip={item.title} isActive={isActiveRoute(item.href)}>
                  <Link href={item.href} className="flex items-center gap-2">
                    {React.createElement(item.icon, { className: "h-4 w-4" })}
                    <span>{item.title}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            ))}

            {/* Sistema de Medicamentos */}
            <div className="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
              Medicamentos
            </div>
            {medicamentosItems.map((item: NavigationItem) => (
              <SidebarMenuItem key={item.title}>
                <SidebarMenuButton asChild tooltip={item.title} isActive={isActiveRoute(item.href)}>
                  <Link href={item.href} className="flex items-center gap-2">
                    {React.createElement(item.icon, { className: "h-4 w-4" })}
                    <span>{item.title}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            ))}

            {/* Gestión de Usuarios */}
            <div className="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
              Usuarios
            </div>
            {usuariosItems.map((item: NavigationItem) => (
              <SidebarMenuItem key={item.title}>
                <SidebarMenuButton asChild tooltip={item.title} isActive={isActiveRoute(item.href)}>
                  <Link href={item.href} className="flex items-center gap-2">
                    {React.createElement(item.icon, { className: "h-4 w-4" })}
                    <span>{item.title}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            ))}

            {/* Auditoría */}
            <div className="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
              Auditoría
            </div>
            {auditoriaItems.map((item: NavigationItem) => (
              <SidebarMenuItem key={item.title}>
                <SidebarMenuButton asChild tooltip={item.title} isActive={isActiveRoute(item.href)}>
                  <Link href={item.href} className="flex items-center gap-2">
                    {React.createElement(item.icon, { className: "h-4 w-4" })}
                    <span>{item.title}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            ))}

            {/* Configuración */}
            <div className="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">
              Configuración
            </div>
            {configuracionItems.map((item: NavigationItem) => (
              <SidebarMenuItem key={item.title}>
                <SidebarMenuButton asChild tooltip={item.title} isActive={isActiveRoute(item.href)}>
                  <Link href={item.href} className="flex items-center gap-2">
                    {React.createElement(item.icon, { className: "h-4 w-4" })}
                    <span>{item.title}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            ))}
          </SidebarMenu>
        </ScrollArea>
      </SidebarContent>

      <SidebarFooter>
        <SidebarMenu>
          <SidebarMenuItem>
            {/* <SidebarMenuButton asChild tooltip="Profile">
              <Link href="/profile" className="flex items-center gap-2">
                <Users className="h-4 w-4" />
                <span>Profile</span>
              </Link>
            </SidebarMenuButton> */}
          </SidebarMenuItem>
        </SidebarMenu>
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
}
