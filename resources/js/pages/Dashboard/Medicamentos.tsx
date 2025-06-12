import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

import { 
    Pill, 
    Heart, 
    Clock, 
    User, 
    AlertTriangle, 
    Activity,
    TrendingUp,
    CheckCircle,
    Plus,
    Eye,
    ArrowUpRight,
    Calendar,
    BarChart3
} from 'lucide-react';

export default function MedicamentosDashboard() {
    // Datos de ejemplo - en un entorno real vendrían del backend
    const stats = {
        medicamentos: { total: 45, activos: 42, inactivos: 3 },
        tratamientos: { total: 23, activos: 18, pausados: 3, completados: 2 },
        pendientes: { urgentes: 4, normales: 8, total: 12 },
        pacientes: { total: 8, conTratamientos: 6 },
        adherencia: 87,
        alertas: 3
    };

    const getAdherenciaColor = (value: number) => {
        if (value >= 85) return 'text-green-600';
        if (value >= 70) return 'text-yellow-600';
        return 'text-red-600';
    };

    const getAdherenciaVariant = (value: number) => {
        if (value >= 85) return 'default';
        if (value >= 70) return 'secondary';
        return 'destructive';
    };

    return (
        <AppLayout>
            <Head title="Dashboard - Medicamentos" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col space-y-2 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
                    <div className="space-y-1">
                        <h1 className="text-3xl font-bold tracking-tight">Dashboard de Medicamentos</h1>
                        <p className="text-muted-foreground">
                            Vista general del sistema de gestión médica
                        </p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Link href={route('administraciones.pendientes')}>
                            <Button variant="outline" size="sm">
                                <Clock className="h-4 w-4 mr-2" />
                                Pendientes
                                {stats.pendientes.total > 0 && (
                                    <Badge variant="secondary" className="ml-2">
                                        {stats.pendientes.total}
                                    </Badge>
                                )}
                            </Button>
                        </Link>
                        <Link href={route('medicamentos.create')}>
                            <Button size="sm">
                                <Plus className="h-4 w-4 mr-2" />
                                Nuevo Medicamento
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Métricas principales */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Medicamentos</CardTitle>
                            <Pill className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.medicamentos.total}</div>
                            <div className="flex items-center space-x-2 text-xs text-muted-foreground">
                                <CheckCircle className="h-3 w-3 text-green-600" />
                                <span>{stats.medicamentos.activos} activos</span>
                                <span>•</span>
                                <span>{stats.medicamentos.inactivos} inactivos</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tratamientos</CardTitle>
                            <Heart className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.tratamientos.total}</div>
                            <div className="flex items-center space-x-2 text-xs text-muted-foreground">
                                <Activity className="h-3 w-3 text-green-600" />
                                <span>{stats.tratamientos.activos} activos</span>
                                <span>•</span>
                                <span>{stats.tratamientos.pausados} pausados</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Administraciones</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pendientes.total}</div>
                            <div className="flex items-center space-x-2 text-xs">
                                <Badge variant="destructive" className="text-xs px-1 py-0">
                                    {stats.pendientes.urgentes}
                                </Badge>
                                <span className="text-muted-foreground">urgentes</span>
                                <span className="text-muted-foreground">•</span>
                                <span className="text-muted-foreground">{stats.pendientes.normales} normales</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Adherencia</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className={`text-2xl font-bold ${getAdherenciaColor(stats.adherencia)}`}>
                                {stats.adherencia}%
                            </div>
                            <div className="flex items-center space-x-2 text-xs text-muted-foreground">
                                <BarChart3 className="h-3 w-3" />
                                <span>promedio general</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Accesos rápidos y alertas */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {/* Accesos rápidos */}
                    <Card className="md:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <Activity className="h-5 w-5" />
                                <span>Accesos Rápidos</span>
                            </CardTitle>
                            <CardDescription>
                                Funciones más utilizadas del sistema
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                <Link href={route('medicamentos.index')}>
                                    <Card className="hover:bg-accent transition-colors cursor-pointer">
                                        <CardContent className="flex flex-col items-center justify-center p-4 text-center">
                                            <Pill className="h-8 w-8 text-blue-600 mb-2" />
                                            <p className="font-medium text-sm">Medicamentos</p>
                                            <div className="flex items-center space-x-1 mt-1">
                                                <Badge variant="secondary" className="text-xs">
                                                    {stats.medicamentos.total}
                                                </Badge>
                                                <ArrowUpRight className="h-3 w-3 text-muted-foreground" />
                                            </div>
                                        </CardContent>
                                    </Card>
                                </Link>

                                <Link href={route('tratamientos.index')}>
                                    <Card className="hover:bg-accent transition-colors cursor-pointer">
                                        <CardContent className="flex flex-col items-center justify-center p-4 text-center">
                                            <Heart className="h-8 w-8 text-green-600 mb-2" />
                                            <p className="font-medium text-sm">Tratamientos</p>
                                            <div className="flex items-center space-x-1 mt-1">
                                                <Badge variant="secondary" className="text-xs">
                                                    {stats.tratamientos.total}
                                                </Badge>
                                                <ArrowUpRight className="h-3 w-3 text-muted-foreground" />
                                            </div>
                                        </CardContent>
                                    </Card>
                                </Link>

                                <Link href={route('administraciones.pendientes')}>
                                    <Card className="hover:bg-accent transition-colors cursor-pointer">
                                        <CardContent className="flex flex-col items-center justify-center p-4 text-center">
                                            <Clock className="h-8 w-8 text-orange-600 mb-2" />
                                            <p className="font-medium text-sm">Pendientes</p>
                                            <div className="flex items-center space-x-1 mt-1">
                                                <Badge variant="outline" className="text-xs">
                                                    {stats.pendientes.total}
                                                </Badge>
                                                <ArrowUpRight className="h-3 w-3 text-muted-foreground" />
                                            </div>
                                        </CardContent>
                                    </Card>
                                </Link>

                                <Link href={route('pacientes.index')}>
                                    <Card className="hover:bg-accent transition-colors cursor-pointer">
                                        <CardContent className="flex flex-col items-center justify-center p-4 text-center">
                                            <User className="h-8 w-8 text-purple-600 mb-2" />
                                            <p className="font-medium text-sm">Pacientes</p>
                                            <div className="flex items-center space-x-1 mt-1">
                                                <Badge variant="secondary" className="text-xs">
                                                    {stats.pacientes.total}
                                                </Badge>
                                                <ArrowUpRight className="h-3 w-3 text-muted-foreground" />
                                            </div>
                                        </CardContent>
                                    </Card>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Panel de alertas y estado */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center justify-between">
                                <div className="flex items-center space-x-2">
                                    <AlertTriangle className="h-5 w-5" />
                                    <span>Estado del Sistema</span>
                                </div>
                                <Badge variant={stats.alertas > 0 ? "destructive" : "default"}>
                                    {stats.alertas} alertas
                                </Badge>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <div className="flex items-center justify-between text-sm">
                                    <span className="text-muted-foreground">Adherencia General</span>
                                    <Badge variant={getAdherenciaVariant(stats.adherencia)}>
                                        {stats.adherencia}%
                                    </Badge>
                                </div>
                                <div className="w-full bg-secondary rounded-full h-2">
                                    <div 
                                        className="bg-primary h-2 rounded-full transition-all" 
                                        style={{ width: `${stats.adherencia}%` }}
                                    ></div>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <div className="flex items-center justify-between text-sm">
                                    <span className="text-muted-foreground">Pacientes Activos</span>
                                    <span className="font-medium">{stats.pacientes.conTratamientos}/{stats.pacientes.total}</span>
                                </div>
                                <div className="w-full bg-secondary rounded-full h-2">
                                    <div 
                                        className="bg-green-500 h-2 rounded-full transition-all" 
                                        style={{ width: `${(stats.pacientes.conTratamientos / stats.pacientes.total) * 100}%` }}
                                    ></div>
                                </div>
                            </div>

                            <div className="pt-2 border-t">
                                <div className="flex items-center justify-between text-sm">
                                    <span className="flex items-center space-x-2">
                                        <Calendar className="h-4 w-4 text-muted-foreground" />
                                        <span className="text-muted-foreground">Última actualización</span>
                                    </span>
                                    <span className="text-xs text-muted-foreground">Hace 2 min</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
} 