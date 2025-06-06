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
  SidebarMenuSub,
  SidebarMenuSubItem,
  SidebarMenuSubButton,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarGroupContent
} from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/react';
import { 
  Home, 
  Users, 
  Settings, 
  UserPlus, 
  User, 
  Stethoscope, 
  Heart, 
  UserX, 
  Shield, 
  Key, 
  UserCheck,
  Pill,
  FlaskConical,
  Ruler,
  RouteIcon,
  Tablets,
  ClipboardList,
  Syringe,
  ChevronRight,
  LucideIcon
} from 'lucide-react';

interface NavigationItem {
  title: string;
  href: string;
  icon: LucideIcon;
  isActive?: boolean;
}

interface NavigationGroup {
  title: string;
  items: NavigationItem[];
  icon?: LucideIcon;
  collapsible?: boolean;
}

export function AppSidebar() {
  const page = usePage();
  const currentUrl = page.url;

  // Función para verificar si un item está activo
  const isItemActive = (href: string) => {
    return currentUrl.startsWith(href);
  };

  // Función para verificar si un grupo tiene algún item activo
  const isGroupActive = (items: NavigationItem[]) => {
    return items.some(item => isItemActive(item.href));
  };

  const navigationGroups: NavigationGroup[] = [
    // Dashboard principal
    {
      title: "Principal",
      items: [
        {
          title: 'Dashboard',
          href: '/dashboard',
          icon: Home,
        }
      ]
    },
    
    // Sistema de Medicamentos
    {
      title: "Medicamentos",
      icon: Pill,
      collapsible: true,
      items: [
        {
          title: 'Principios Activos',
          href: '/medicamentos/principios-activos',
          icon: FlaskConical,
        },
        {
          title: 'Unidades de Medida',
          href: '/medicamentos/unidades-medida',
          icon: Ruler,
        },
        {
          title: 'Formas Farmacéuticas',
          href: '/medicamentos/formas-farmaceuticas',
          icon: Tablets,
        },
        {
          title: 'Vías de Administración',
          href: '/medicamentos/vias-administracion',
          icon: RouteIcon,
        },
        {
          title: 'Medicamentos',
          href: '/medicamentos',
          icon: Pill,
        },
        {
          title: 'Tratamientos',
          href: '/tratamientos',
          icon: ClipboardList,
        },
        {
          title: 'Administraciones',
          href: '/administraciones',
          icon: Syringe,
        }
      ]
    },

    // Gestión de Usuarios
    {
      title: "Usuarios",
      icon: Users,
      collapsible: true,
      items: [
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
          title: 'Usuarios del Sistema',
          href: '/usuarios',
          icon: Users,
        }
      ]
    },

    // Configuración y Administración
    {
      title: "Configuración",
      icon: Settings,
      collapsible: true,
      items: [
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
        {
          title: 'Configuración',
          href: '/settings',
          icon: Settings,
        }
      ]
    }
  ];

  const [openGroups, setOpenGroups] = React.useState<string[]>(() => {
    // Abrir automáticamente el grupo que contiene la página actual
    const activeGroups = navigationGroups
      .filter(group => isGroupActive(group.items))
      .map(group => group.title);
    return activeGroups;
  });

  const toggleGroup = (groupTitle: string) => {
    setOpenGroups(prev => 
      prev.includes(groupTitle) 
        ? prev.filter(g => g !== groupTitle)
        : [...prev, groupTitle]
    );
  };

  return (
    <Sidebar variant="inset" collapsible="icon">
      <SidebarHeader className="px-2">
        <SidebarTrigger />
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" asChild>
              <Link href="/dashboard" prefetch className="flex items-center">
                <span className="text-base font-semibold">MediTrack</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      <SidebarContent>
        {/* Botón de crear usuario - siempre visible */}
        <SidebarGroup>
          <SidebarGroupContent>
            <SidebarMenu>
              <SidebarMenuItem>
                <SidebarMenuButton asChild>
                  <Link href="/usuarios/select-type" className="flex items-center gap-2">
                    <UserPlus className="h-4 w-4" />
                    <span>Crear Usuario</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>

        {/* Grupos de navegación */}
        {navigationGroups.map((group) => (
          <SidebarGroup key={group.title}>
            {group.collapsible ? (
              <>
                <SidebarGroupLabel asChild>
                  <button
                    onClick={() => toggleGroup(group.title)}
                    className="flex items-center justify-between w-full text-left hover:bg-sidebar-accent hover:text-sidebar-accent-foreground rounded-md px-2 py-1"
                  >
                    <div className="flex items-center gap-2">
                      {group.icon && React.createElement(group.icon, { className: "h-4 w-4" })}
                      <span>{group.title}</span>
                    </div>
                    <ChevronRight 
                      className={`h-4 w-4 transition-transform ${
                        openGroups.includes(group.title) ? 'rotate-90' : ''
                      }`} 
                    />
                  </button>
                </SidebarGroupLabel>
                {openGroups.includes(group.title) && (
                  <SidebarGroupContent>
                    <SidebarMenu>
                      {group.items.map((item) => (
                        <SidebarMenuItem key={item.title}>
                          <SidebarMenuButton asChild isActive={isItemActive(item.href)}>
                            <Link href={item.href} className="flex items-center gap-2">
                              {React.createElement(item.icon, { className: "h-4 w-4" })}
                              <span>{item.title}</span>
                            </Link>
                          </SidebarMenuButton>
                        </SidebarMenuItem>
                      ))}
                    </SidebarMenu>
                  </SidebarGroupContent>
                )}
              </>
            ) : (
              <>
                {group.title !== "Principal" && <SidebarGroupLabel>{group.title}</SidebarGroupLabel>}
                <SidebarGroupContent>
                  <SidebarMenu>
                    {group.items.map((item) => (
                      <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton asChild isActive={isItemActive(item.href)}>
                          <Link href={item.href} className="flex items-center gap-2">
                            {React.createElement(item.icon, { className: "h-4 w-4" })}
                            <span>{item.title}</span>
                          </Link>
                        </SidebarMenuButton>
                      </SidebarMenuItem>
                    ))}
                  </SidebarMenu>
                </SidebarGroupContent>
              </>
            )}
          </SidebarGroup>
        ))}
      </SidebarContent>

      <SidebarFooter>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton asChild tooltip="Profile">
              <Link href="/profile" className="flex items-center gap-2">
                <Users className="h-4 w-4" />
                <span>Profile</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
        <NavUser />
      </SidebarFooter>
    </Sidebar>
  );
}
