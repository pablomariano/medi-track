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
import { Link } from '@inertiajs/react';
import { Home, Calendar, Users, FileText, Settings, PlusCircle, Pill, LucideIcon } from 'lucide-react';

interface NavigationItem {
  title: string;
  href: string;
  icon: LucideIcon;
}

const mainNavItems: NavigationItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: Home,
  },
  {
    title: 'Appointments',
    href: '/appointments',
    icon: Calendar,
  },
  {
    title: 'Patients',
    href: '/patients',
    icon: Users,
  },
  {
    title: 'Medical Records',
    href: '/records',
    icon: FileText,
  },
  {
    title: 'Medicines',
    href: '/medicines',
    icon: Pill,
  },
  {
    title: 'Settings',
    href: '/settings',
    icon: Settings,
  },
];

export function AppSidebar() {
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
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton asChild>
              <Link href="/new-appointment" className="flex items-center gap-2">
                <PlusCircle className="h-4 w-4" />
                <span>New Appointment</span>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
          {mainNavItems.map((item) => (
            <SidebarMenuItem key={item.title}>
              <SidebarMenuButton asChild tooltip={item.title}>
                <Link href={item.href} className="flex items-center gap-2">
                  {React.createElement(item.icon, { className: "h-4 w-4" })}
                  <span>{item.title}</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          ))}
        </SidebarMenu>
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
