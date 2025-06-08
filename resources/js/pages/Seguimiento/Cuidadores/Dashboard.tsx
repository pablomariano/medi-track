import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Calendar, 
    Clock, 
    AlertTriangle, 
    CheckCircle, 
    User, 
    Pill,
    Activity,
    Bell
} from 'lucide-react';

interface Paciente {
    id: number;
    nombres: string;
    apellidos: string;
    edad: number;
    tratamientos_count: number;
}

interface AdministracionPendiente {
    id: number;
    medicamento: {
        nombre_comercial: string;
        concentracion: number;
        unidad: string;
    };
    paciente: {
        nombres: string;
        apellidos: string;
    };
    hora_programada: string;
    dosis: number;
    via_administracion: string;
    estado: string;
    prioridad: 'normal' | 'alta' | 'urgente';
}

interface Alerta {
    id: number;
    tipo: string;
    mensaje: string;
    paciente: {
        nombres: string;
        apellidos: string;
    };
    created_at: string;
    prioridad: 'baja' | 'media' | 'alta' | 'critica';
}

interface Stats {
    pacientes_asignados: number;
    administraciones_pendientes_hoy: number;
    administraciones_completadas_hoy: number;
    alertas_activas: number;
}

interface Props {
    pacientesAsignados: Paciente[];
    administracionesPendientes: AdministracionPendiente[];
    alertasActivas: Alerta[];
    stats: Stats;
}

export default function Dashboard({ 
    pacientesAsignados, 
    administracionesPendientes, 
    alertasActivas, 
    stats 
}: Props) {
    const [filtroAlertas, setFiltroAlertas] = useState<string>('todas');

    const getPrioridadColor = (prioridad: string) => {
        switch (prioridad) {
            case 'critica': return 'bg-red-100 text-red-800 border-red-200';
            case 'alta': return 'bg-orange-100 text-orange-800 border-orange-200';
            case 'urgente': return 'bg-red-100 text-red-800 border-red-200';
            case 'media': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            default: return 'bg-green-100 text-green-800 border-green-200';
        }
    };

    const alertasFiltradas = filtroAlertas === 'todas' 
        ? alertasActivas 
        : alertasActivas.filter(alerta => alerta.prioridad === filtroAlertas);

    return (
        <AppSidebarLayout>
            <Head title="Dashboard - Cuidador" />
            
            <div className="container mx-auto py-6 space-y-6">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">
                            Dashboard de Cuidador
                        </h1>
                        <p className="text-gray-600 mt-1">
                            Gestión diaria de administraciones y seguimiento de pacientes
                        </p>
                    </div>
                    <div className="flex items-center space-x-2 mt-4 md:mt-0">
                        <Badge variant="outline" className="flex items-center space-x-1">
                            <Clock className="h-3 w-3" />
                            <span>{new Date().toLocaleDateString()}</span>
                        </Badge>
                    </div>
                </div>

                {/* Estadísticas principales */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Pacientes Asignados
                            </CardTitle>
                            <User className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pacientes_asignados}</div>
                            <p className="text-xs text-muted-foreground">
                                Bajo tu cuidado
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Pendientes Hoy
                            </CardTitle>
                            <Pill className="h-4 w-4 text-orange-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-orange-600">
                                {stats.administraciones_pendientes_hoy}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Administraciones por realizar
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Completadas Hoy
                            </CardTitle>
                            <CheckCircle className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">
                                {stats.administraciones_completadas_hoy}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Administraciones realizadas
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">
                                Alertas Activas
                            </CardTitle>
                            <Bell className="h-4 w-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">
                                {stats.alertas_activas}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Requieren atención
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* Administraciones Pendientes */}
                    <Card className="col-span-1">
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <Activity className="h-5 w-5" />
                                <span>Próximas Administraciones</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {administracionesPendientes.length === 0 ? (
                                    <div className="text-center py-8 text-gray-500">
                                        <Pill className="h-12 w-12 mx-auto mb-4 text-gray-300" />
                                        <p>No hay administraciones pendientes</p>
                                    </div>
                                ) : (
                                    administracionesPendientes.map((admin) => (
                                        <div key={admin.id} className="border rounded-lg p-4 space-y-2">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center space-x-2">
                                                    <Badge 
                                                        className={getPrioridadColor(admin.prioridad)}
                                                    >
                                                        {admin.prioridad}
                                                    </Badge>
                                                    <span className="font-medium">
                                                        {admin.hora_programada}
                                                    </span>
                                                </div>
                                                <Button size="sm" variant="outline">
                                                    Administrar
                                                </Button>
                                            </div>
                                            
                                            <div className="text-sm">
                                                <p className="font-medium">
                                                    {admin.paciente.nombres} {admin.paciente.apellidos}
                                                </p>
                                                <p className="text-gray-600">
                                                    {admin.medicamento.nombre_comercial} - {admin.dosis} {admin.medicamento.unidad}
                                                </p>
                                                <p className="text-gray-500">
                                                    Vía: {admin.via_administracion}
                                                </p>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Alertas Activas */}
                    <Card className="col-span-1">
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="flex items-center space-x-2">
                                    <AlertTriangle className="h-5 w-5" />
                                    <span>Alertas Activas</span>
                                </CardTitle>
                                <select 
                                    value={filtroAlertas}
                                    onChange={(e) => setFiltroAlertas(e.target.value)}
                                    className="text-sm border rounded px-2 py-1"
                                >
                                    <option value="todas">Todas</option>
                                    <option value="critica">Críticas</option>
                                    <option value="alta">Alta</option>
                                    <option value="media">Media</option>
                                    <option value="baja">Baja</option>
                                </select>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-3">
                                {alertasFiltradas.length === 0 ? (
                                    <div className="text-center py-8 text-gray-500">
                                        <Bell className="h-12 w-12 mx-auto mb-4 text-gray-300" />
                                        <p>No hay alertas activas</p>
                                    </div>
                                ) : (
                                    alertasFiltradas.map((alerta) => (
                                        <div key={alerta.id} className="border rounded-lg p-3 space-y-2">
                                            <div className="flex items-start justify-between">
                                                <Badge 
                                                    className={getPrioridadColor(alerta.prioridad)}
                                                >
                                                    {alerta.prioridad}
                                                </Badge>
                                                <span className="text-xs text-gray-500">
                                                    {new Date(alerta.created_at).toLocaleTimeString()}
                                                </span>
                                            </div>
                                            
                                            <div className="text-sm">
                                                <p className="font-medium">
                                                    {alerta.paciente.nombres} {alerta.paciente.apellidos}
                                                </p>
                                                <p className="text-gray-600">
                                                    {alerta.mensaje}
                                                </p>
                                            </div>
                                            
                                            <div className="flex space-x-2">
                                                <Button size="sm" variant="outline">
                                                    Ver Detalles
                                                </Button>
                                                <Button size="sm" variant="ghost">
                                                    Marcar Leída
                                                </Button>
                                            </div>
                                        </div>
                                    ))
                                )}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Lista de Pacientes */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <User className="h-5 w-5" />
                            <span>Pacientes Asignados</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {pacientesAsignados.map((paciente) => (
                                <div key={paciente.id} className="border rounded-lg p-4 space-y-3">
                                    <div className="flex items-center justify-between">
                                        <h3 className="font-medium">
                                            {paciente.nombres} {paciente.apellidos}
                                        </h3>
                                        <Badge variant="secondary">
                                            {paciente.edad} años
                                        </Badge>
                                    </div>
                                    
                                    <div className="text-sm text-gray-600">
                                        <p>{paciente.tratamientos_count} tratamiento(s) activo(s)</p>
                                    </div>
                                    
                                    <Button 
                                        size="sm" 
                                        className="w-full"
                                        onClick={() => window.location.href = `/seguimiento/cuidador/paciente/${paciente.id}`}
                                    >
                                        Ver Detalles
                                    </Button>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 