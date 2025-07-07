import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { useAuth } from '@/hooks/use-auth';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { 
    Heart, 
    Pill, 
    Calendar, 
    Clock, 
    TrendingUp, 
    AlertCircle,
    CheckCircle,
    ArrowRight,
    User,
    Activity,
    Plus
} from 'lucide-react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';

interface Medicamento {
    id: number;
    nombre: string;
    concentracion?: string;
    unidad_concentracion?: string;
}

interface ProximaAdministracion {
    id: number;
    fecha_hora_programada: string;
    medicamentoTratamiento: {
        medicamento: Medicamento;
    };
}

interface TratamientoActivo {
    id: number;
    nombre: string;
    medicamentos: Medicamento[];
    medico: {
        name: string;
    };
}

interface Paciente {
    id: number;
    nombre: string;
    genero?: {
        nombre: string;
    };
}

interface Estadisticas {
    tratamientos_activos: number;
    adherencia_7_dias: number;
    dosis_pendientes_hoy: number;
    proxima_dosis?: ProximaAdministracion;
}

interface Props {
    paciente: Paciente;
    tratamientos_activos: TratamientoActivo[];
    proximas_administraciones: ProximaAdministracion[];
    estadisticas: Estadisticas;
}

export default function DashboardPaciente({ 
    paciente, 
    tratamientos_activos, 
    proximas_administraciones, 
    estadisticas 
}: Props) {
    const auth = useAuth();

    // Función para formatear hora
    const formatearHora = (fechaHora: string) => {
        return new Date(fechaHora).toLocaleTimeString('es-CL', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    };

    // Función para formatear fecha
    const formatearFecha = (fechaHora: string) => {
        return new Date(fechaHora).toLocaleDateString('es-CL', {
            weekday: 'short',
            month: 'short',
            day: 'numeric'
        });
    };

    // Función para obtener saludo según la hora
    const obtenerSaludo = () => {
        const hora = new Date().getHours();
        if (hora < 12) return 'Buenos días';
        if (hora < 18) return 'Buenas tardes';
        return 'Buenas noches';
    };

    // Función para obtener color de adherencia
    const obtenerColorAdherencia = (porcentaje: number) => {
        if (porcentaje >= 80) return 'text-green-600';
        if (porcentaje >= 60) return 'text-yellow-600';
        return 'text-red-600';
    };

    // Función para obtener mensaje de adherencia
    const obtenerMensajeAdherencia = (porcentaje: number) => {
        if (porcentaje >= 90) return '¡Excelente cumplimiento!';
        if (porcentaje >= 80) return 'Buen cumplimiento';
        if (porcentaje >= 60) return 'Puede mejorar';
        return 'Necesita atención';
    };

    return (
        <AppSidebarLayout>
            <Head title="Mi Dashboard" />
            
            <div className="container mx-auto py-6 max-w-7xl">
                {/* Header de Bienvenida */}
                <div className="mb-8">
                    <div className="flex items-center justify-between">
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900">
                                {obtenerSaludo()}, {paciente.nombre}
                            </h1>
                            <p className="text-gray-600 mt-2">
                                Aquí tienes un resumen de tu tratamiento y medicamentos
                            </p>
                        </div>
                        <div className="flex items-center space-x-3">
                            <Link href={route('mi-cronograma.index')}>
                                <Button variant="outline">
                                    <Calendar className="h-4 w-4 mr-2" />
                                    Ver Cronograma
                                </Button>
                            </Link>
                            <Link href={route('mis-medicamentos.index')}>
                                <Button>
                                    <Pill className="h-4 w-4 mr-2" />
                                    Mis Medicamentos
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Métricas Principales */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    {/* Tratamientos Activos */}
                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <div className="text-2xl font-bold text-blue-600">
                                        {estadisticas.tratamientos_activos}
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        Tratamientos activos
                                    </p>
                                </div>
                                <Heart className="h-8 w-8 text-blue-600" />
                            </div>
                        </CardContent>
                    </Card>

                    {/* Adherencia */}
                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <div className={`text-2xl font-bold ${obtenerColorAdherencia(estadisticas.adherencia_7_dias)}`}>
                                        {estadisticas.adherencia_7_dias}%
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        Adherencia (7 días)
                                    </p>
                                </div>
                                <TrendingUp className={`h-8 w-8 ${obtenerColorAdherencia(estadisticas.adherencia_7_dias)}`} />
                            </div>
                            <div className="mt-3">
                                <Progress value={estadisticas.adherencia_7_dias} className="h-2" />
                                <p className="text-xs text-muted-foreground mt-1">
                                    {obtenerMensajeAdherencia(estadisticas.adherencia_7_dias)}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Dosis Pendientes Hoy */}
                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <div className="text-2xl font-bold text-yellow-600">
                                        {estadisticas.dosis_pendientes_hoy}
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        Dosis pendientes hoy
                                    </p>
                                </div>
                                <Clock className="h-8 w-8 text-yellow-600" />
                            </div>
                            {estadisticas.dosis_pendientes_hoy > 0 && (
                                <div className="mt-3">
                                    <Link href={route('mi-cronograma.index')}>
                                        <Button size="sm" variant="outline" className="w-full">
                                            Ver cronograma
                                        </Button>
                                    </Link>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Próxima Dosis */}
                    <Card>
                        <CardContent className="pt-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    {estadisticas.proxima_dosis ? (
                                        <>
                                            <div className="text-lg font-bold">
                                                {formatearHora(estadisticas.proxima_dosis.fecha_hora_programada)}
                                            </div>
                                            <p className="text-sm text-muted-foreground">
                                                {estadisticas.proxima_dosis.medicamentoTratamiento.medicamento.nombre}
                                            </p>
                                        </>
                                    ) : (
                                        <>
                                            <div className="text-lg font-bold text-green-600">
                                                ✓ Al día
                                            </div>
                                            <p className="text-sm text-muted-foreground">
                                                Sin dosis pendientes
                                            </p>
                                        </>
                                    )}
                                </div>
                                <Pill className="h-8 w-8 text-muted-foreground" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {/* Próximas Administraciones */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <Clock className="h-5 w-5" />
                                <span>Próximas Dosis (24h)</span>
                            </CardTitle>
                            <CardDescription>
                                Medicamentos programados para las próximas 24 horas
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {proximas_administraciones.length > 0 ? (
                                <div className="space-y-4">
                                    {proximas_administraciones.map((admin) => (
                                        <div key={admin.id} className="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                                            <div className="flex items-center space-x-3">
                                                <div className="p-2 bg-blue-100 rounded-full">
                                                    <Pill className="h-4 w-4 text-blue-600" />
                                                </div>
                                                <div>
                                                    <div className="font-medium">
                                                        {admin.medicamentoTratamiento.medicamento.nombre}
                                                    </div>
                                                    <div className="text-sm text-gray-500">
                                                        {formatearFecha(admin.fecha_hora_programada)} a las {formatearHora(admin.fecha_hora_programada)}
                                                    </div>
                                                </div>
                                            </div>
                                            <Badge variant="outline">
                                                Pendiente
                                            </Badge>
                                        </div>
                                    ))}
                                    <div className="pt-4 border-t">
                                        <Link href={route('mi-cronograma.index')}>
                                            <Button variant="outline" className="w-full">
                                                Ver cronograma completo
                                                <ArrowRight className="h-4 w-4 ml-2" />
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <CheckCircle className="h-12 w-12 text-green-500 mx-auto mb-3" />
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        ¡Estás al día!
                                    </h3>
                                    <p className="text-gray-500">
                                        No tienes medicamentos pendientes en las próximas 24 horas.
                                    </p>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Tratamientos Activos */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <Heart className="h-5 w-5" />
                                <span>Mis Tratamientos</span>
                            </CardTitle>
                            <CardDescription>
                                Tratamientos médicos que estás siguiendo actualmente
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {tratamientos_activos.length > 0 ? (
                                <div className="space-y-4">
                                    {tratamientos_activos.map((tratamiento) => (
                                        <div key={tratamiento.id} className="p-4 border rounded-lg">
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1">
                                                    <h3 className="font-semibold text-lg">
                                                        {tratamiento.nombre}
                                                    </h3>
                                                    <div className="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                                        <div className="flex items-center space-x-1">
                                                            <User className="h-4 w-4" />
                                                            <span>Dr. {tratamiento.medico.name}</span>
                                                        </div>
                                                        <div className="flex items-center space-x-1">
                                                            <Pill className="h-4 w-4" />
                                                            <span>{tratamiento.medicamentos.length} medicamento{tratamiento.medicamentos.length !== 1 ? 's' : ''}</span>
                                                        </div>
                                                    </div>
                                                    
                                                    {/* Medicamentos */}
                                                    <div className="mt-3 flex flex-wrap gap-2">
                                                        {tratamiento.medicamentos.map((medicamento) => (
                                                            <Badge key={medicamento.id} variant="secondary">
                                                                {medicamento.nombre}
                                                            </Badge>
                                                        ))}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                    <div className="pt-4 border-t">
                                        <Link href={route('mis-tratamientos.index')}>
                                            <Button variant="outline" className="w-full">
                                                Ver todos mis tratamientos
                                                <ArrowRight className="h-4 w-4 ml-2" />
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <AlertCircle className="h-12 w-12 text-gray-400 mx-auto mb-3" />
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        Sin tratamientos activos
                                    </h3>
                                    <p className="text-gray-500 mb-4">
                                        No tienes tratamientos médicos activos en este momento.
                                    </p>
                                    <Link href={route('mis-tratamientos.crear')}>
                                        <Button>
                                            <Plus className="h-4 w-4 mr-2" />
                                            Agregar Tratamiento
                                        </Button>
                                    </Link>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Acciones Rápidas */}
                <Card className="mt-8">
                    <CardHeader>
                        <CardTitle className="flex items-center space-x-2">
                            <Activity className="h-5 w-5" />
                            <span>Acciones Rápidas</span>
                        </CardTitle>
                        <CardDescription>
                            Accede rápidamente a las funciones más utilizadas
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <Link href={route('mi-cronograma.index')}>
                                <Button variant="outline" className="w-full h-20 flex-col space-y-2">
                                    <Calendar className="h-6 w-6" />
                                    <span className="text-sm">Mi Cronograma</span>
                                </Button>
                            </Link>
                            
                            <Link href={route('mis-medicamentos.index')}>
                                <Button variant="outline" className="w-full h-20 flex-col space-y-2">
                                    <Pill className="h-6 w-6" />
                                    <span className="text-sm">Mis Medicamentos</span>
                                </Button>
                            </Link>
                            
                            <Link href={route('mis-tratamientos.index')}>
                                <Button variant="outline" className="w-full h-20 flex-col space-y-2">
                                    <Heart className="h-6 w-6" />
                                    <span className="text-sm">Mis Tratamientos</span>
                                </Button>
                            </Link>
                            
                            <Link href={route('mi-perfil.index')}>
                                <Button variant="outline" className="w-full h-20 flex-col space-y-2">
                                    <User className="h-6 w-6" />
                                    <span className="text-sm">Mi Perfil</span>
                                </Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 