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
    Eye
} from 'lucide-react';

export default function MedicamentosDashboard() {
    // Datos de ejemplo - en un entorno real vendrían del backend
    const stats = {
        medicamentos: { total: 45, activos: 42 },
        tratamientos: { total: 23, activos: 18 },
        pendientes: 12,
        pacientes: 8
    };

    return (
        <AppLayout>
            <Head title="Dashboard - Medicamentos" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900">Dashboard de Medicamentos</h1>
                        <p className="text-gray-600 mt-1">Vista general del sistema de gestión médica</p>
                    </div>
                    <div className="flex space-x-2">
                        <Link href={route('administraciones.pendientes')}>
                            <Button variant="outline" size="sm">
                                <Clock className="h-4 w-4 mr-2" />
                                Pendientes
                            </Button>
                        </Link>
                        <Link href={route('medicamentos.create')}>
                            <Button>
                                <Plus className="h-4 w-4 mr-2" />
                                Nuevo Medicamento
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Métricas principales */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Medicamentos</CardTitle>
                            <Pill className="h-4 w-4 text-blue-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.medicamentos.total}</div>
                            <p className="text-xs text-muted-foreground">
                                {stats.medicamentos.activos} activos
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Tratamientos</CardTitle>
                            <Heart className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.tratamientos.total}</div>
                            <p className="text-xs text-muted-foreground">
                                {stats.tratamientos.activos} activos
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pendientes</CardTitle>
                            <Clock className="h-4 w-4 text-orange-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-orange-600">{stats.pendientes}</div>
                            <p className="text-xs text-muted-foreground">administraciones</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pacientes</CardTitle>
                            <User className="h-4 w-4 text-purple-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.pacientes}</div>
                            <p className="text-xs text-muted-foreground">con tratamientos</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Accesos rápidos */}
                <Card>
                    <CardHeader>
                        <CardTitle>Accesos Rápidos</CardTitle>
                        <CardDescription>Funciones más utilizadas del sistema</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <Link href={route('medicamentos.index')}>
                                <div className="p-4 border rounded-lg hover:bg-gray-50 cursor-pointer text-center">
                                    <Pill className="h-8 w-8 text-blue-600 mx-auto mb-2" />
                                    <p className="font-medium">Medicamentos</p>
                                    <p className="text-xs text-gray-500">{stats.medicamentos.total}</p>
                                </div>
                            </Link>

                            <Link href={route('tratamientos.index')}>
                                <div className="p-4 border rounded-lg hover:bg-gray-50 cursor-pointer text-center">
                                    <Heart className="h-8 w-8 text-green-600 mx-auto mb-2" />
                                    <p className="font-medium">Tratamientos</p>
                                    <p className="text-xs text-gray-500">{stats.tratamientos.total}</p>
                                </div>
                            </Link>

                            <Link href={route('administraciones.pendientes')}>
                                <div className="p-4 border rounded-lg hover:bg-gray-50 cursor-pointer text-center">
                                    <Clock className="h-8 w-8 text-orange-600 mx-auto mb-2" />
                                    <p className="font-medium">Pendientes</p>
                                    <p className="text-xs text-gray-500">{stats.pendientes}</p>
                                </div>
                            </Link>

                            <Link href={route('pacientes.index')}>
                                <div className="p-4 border rounded-lg hover:bg-gray-50 cursor-pointer text-center">
                                    <User className="h-8 w-8 text-purple-600 mx-auto mb-2" />
                                    <p className="font-medium">Pacientes</p>
                                    <p className="text-xs text-gray-500">{stats.pacientes}</p>
                                </div>
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
} 