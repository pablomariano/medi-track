import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { ArrowLeft, Plus, Users, Shield, Settings, BarChart3, Pill, FileText, Activity, UserCheck, Database, AlertTriangle, Stethoscope, Heart, UserX } from 'lucide-react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";

interface AdminStats {
    total_usuarios: number;
    usuarios_activos: number;
    roles_count: number;
    permisos_count: number;
    alertas_pendientes: number;
    medicamentos_count: number;
    pacientes_activos: number;
    tratamientos_activos: number;
}

interface Props {
    stats: AdminStats;
}

const adminSections = [
    {
        title: 'Gestión de Usuarios',
        description: 'Administrar usuarios del sistema, crear nuevos y gestionar accesos',
        icon: Users,
        href: '/usuarios/select-type',
        color: 'bg-blue-500/10',
        iconColor: 'text-blue-600',
        stats: 'total_usuarios'
    },
    {
        title: 'Roles y Permisos',
        description: 'Configurar roles del sistema y gestionar permisos de acceso',
        icon: Shield,
        href: '/roles',
        color: 'bg-green-500/10',
        iconColor: 'text-green-600',
        stats: 'roles_count'
    },
    {
        title: 'Personal Médico',
        description: 'Gestionar médicos, especialidades y perfiles profesionales',
        icon: Stethoscope,
        href: '/personal-medico',
        color: 'bg-purple-500/10',
        iconColor: 'text-purple-600',
        stats: null
    },
    {
        title: 'Cuidadores',
        description: 'Administrar cuidadores, certificaciones y disponibilidad',
        icon: Heart,
        href: '/cuidadores',
        color: 'bg-pink-500/10',
        iconColor: 'text-pink-600',
        stats: null
    },
    {
        title: 'Apoderados',
        description: 'Gestionar apoderados y contactos de emergencia',
        icon: UserX,
        href: '/apoderados',
        color: 'bg-orange-500/10',
        iconColor: 'text-orange-600',
        stats: null
    },
    {
        title: 'Medicamentos',
        description: 'Catálogo de medicamentos, dosis y presentaciones',
        icon: Pill,
        href: '/medicamentos',
        color: 'bg-cyan-500/10',
        iconColor: 'text-cyan-600',
        stats: 'medicamentos_count'
    },
    {
        title: 'Reportes y Auditoría',
        description: 'Revisar logs del sistema, actividades y cambios importantes',
        icon: FileText,
        href: '/audit',
        color: 'bg-indigo-500/10',
        iconColor: 'text-indigo-600',
        stats: null
    },
    {
        title: 'Configuración Sistema',
        description: 'Configuraciones generales, géneros y catálogos base',
        icon: Settings,
        href: '/generos',
        color: 'bg-gray-500/10',
        iconColor: 'text-gray-600',
        stats: null
    },
    {
        title: 'Estadísticas Generales',
        description: 'Métricas del sistema, usuarios activos y performance',
        icon: BarChart3,
        href: '/dashboard',
        color: 'bg-emerald-500/10',
        iconColor: 'text-emerald-600',
        stats: 'pacientes_activos'
    },
    {
        title: 'Monitoreo de Actividad',
        description: 'Seguimiento de tratamientos, adherencia y alertas',
        icon: Activity,
        href: '/administraciones/pendientes',
        color: 'bg-red-500/10',
        iconColor: 'text-red-600',
        stats: 'alertas_pendientes'
    },
    {
        title: 'Permisos Avanzados',
        description: 'Gestión detallada de permisos y módulos del sistema',
        icon: UserCheck,
        href: '/permisos',
        color: 'bg-yellow-500/10',
        iconColor: 'text-yellow-600',
        stats: 'permisos_count'
    },
    {
        title: 'Catálogo Componentes',
        description: 'Componentes del sistema para desarrollo y testing',
        icon: Database,
        href: '/component-catalog',
        color: 'bg-violet-500/10',
        iconColor: 'text-violet-600',
        stats: null
    }
];

export default function AdminDashboard({ stats }: Props) {
    return (
        <AppSidebarLayout>
            <div className="p-6 space-y-6">
                <div className="flex items-center gap-4">
                    <Link href={route('dashboard')}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Panel de Administración</h1>
                        <p className="text-muted-foreground">
                            Gestión completa del sistema MediTrack
                        </p>
                    </div>
                </div>

                {/* Estadísticas rápidas */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <Card className="p-4">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-blue-500/10 rounded-full">
                                <Users className="h-5 w-5 text-blue-600" />
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Total Usuarios</p>
                                <p className="text-2xl font-bold">{stats.total_usuarios}</p>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-4">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-emerald-500/10 rounded-full">
                                <Activity className="h-5 w-5 text-emerald-600" />
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Pacientes Activos</p>
                                <p className="text-2xl font-bold">{stats.pacientes_activos}</p>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-4">
                        <div className="flex items-center gap-3">
                            <div className="p-2 bg-cyan-500/10 rounded-full">
                                <Pill className="h-5 w-5 text-cyan-600" />
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Medicamentos</p>
                                <p className="text-2xl font-bold">{stats.medicamentos_count}</p>
                            </div>
                        </div>
                    </Card>

                    <Card className="p-4">
                        <div className="flex items-center gap-3">
                            <div className={`p-2 rounded-full ${stats.alertas_pendientes > 0 ? 'bg-red-500/10' : 'bg-gray-500/10'}`}>
                                <AlertTriangle className={`h-5 w-5 ${stats.alertas_pendientes > 0 ? 'text-red-600' : 'text-gray-600'}`} />
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Alertas Pendientes</p>
                                <p className="text-2xl font-bold">{stats.alertas_pendientes}</p>
                                {stats.alertas_pendientes > 0 && (
                                    <Badge className="text-xs bg-yellow-500/10 text-yellow-600 border-yellow-200">Requiere atención</Badge>
                                )}
                            </div>
                        </div>
                    </Card>
                </div>

                {/* Cards de funcionalidades administrativas */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-none">
                    {adminSections.map((section, index) => {
                        const IconComponent = section.icon;
                        const statValue = section.stats ? stats[section.stats as keyof AdminStats] : null;
                        
                        return (
                            <Link key={index} href={section.href}>
                                <Card className="cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-105 border-2 hover:border-primary/50">
                                    <CardHeader className="text-center pb-4">
                                        <div className={`mx-auto mb-4 p-3 ${section.color} rounded-full w-fit`}>
                                            <IconComponent className={`h-8 w-8 ${section.iconColor}`} />
                                        </div>
                                        <CardTitle className="text-lg">{section.title}</CardTitle>
                                        {statValue !== null && (
                                            <div className="flex items-center justify-center gap-2">
                                                <Badge variant="secondary" className="text-sm">
                                                    {statValue} elemento{statValue !== 1 ? 's' : ''}
                                                </Badge>
                                            </div>
                                        )}
                                    </CardHeader>
                                    <CardContent className="text-center">
                                        <CardDescription className="text-sm leading-relaxed">
                                            {section.description}
                                        </CardDescription>
                                        <div className="mt-4">
                                            <Button className="w-full" size="sm">
                                                <Plus className="h-4 w-4 mr-2" />
                                                Gestionar
                                            </Button>
                                        </div>
                                    </CardContent>
                                </Card>
                            </Link>
                        );
                    })}
                </div>

                {/* Sección de acciones rápidas */}
                <Card className="mt-8">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Settings className="h-5 w-5" />
                            Acciones Rápidas de Administración
                        </CardTitle>
                        <CardDescription>
                            Tareas comunes de administración del sistema
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <Link href={route('usuarios.select-type')}>
                                <Button variant="outline" className="w-full justify-start">
                                    <Plus className="h-4 w-4 mr-2" />
                                    Crear Usuario
                                </Button>
                            </Link>
                            
                            <Link href="/roles/create">
                                <Button variant="outline" className="w-full justify-start">
                                    <Shield className="h-4 w-4 mr-2" />
                                    Nuevo Rol
                                </Button>
                            </Link>
                            
                            <Link href="/audit">
                                <Button variant="outline" className="w-full justify-start">
                                    <FileText className="h-4 w-4 mr-2" />
                                    Ver Auditoría
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                {/* Información del sistema */}
                <Card className="bg-blue-50 border-blue-200">
                    <CardContent className="pt-6">
                        <div className="flex items-start gap-3">
                            <div className="p-2 bg-blue-100 rounded-full">
                                <Database className="h-5 w-5 text-blue-600" />
                            </div>
                            <div>
                                <h3 className="font-medium text-blue-900 mb-1">Panel de Administración MediTrack</h3>
                                <p className="text-sm text-blue-700">
                                    Desde este panel puedes gestionar todos los aspectos del sistema: usuarios, roles, permisos, 
                                    medicamentos y configuraciones. Utiliza las cards arriba para navegar a cada sección específica.
                                </p>
                                <div className="mt-2 text-xs text-blue-600">
                                    <strong>Nota:</strong> Solo los administradores tienen acceso completo a todas estas funcionalidades.
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 