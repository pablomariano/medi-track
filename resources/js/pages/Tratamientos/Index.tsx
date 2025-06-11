import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Search, Plus, Eye, Edit, Heart, Clock, User, Calendar } from 'lucide-react';

interface Paciente {
    id: number;
    nombre: string;
}

interface Medico {
    id: number;
    name: string;
}

interface Medicamento {
    id: number;
    nombre: string;
    concentracion: string;
    unidad_concentracion: string;
}

interface Tratamiento {
    id: number;
    nombre: string;
    tipo: string;
    objetivo: string;
    fecha_inicio: string;
    fecha_fin: string;
    estado: string;
    paciente: Paciente;
    medico: Medico;
    medicamentos: Medicamento[];
    created_at: string;
}

interface Props {
    tratamientos: {
        data: Tratamiento[];
        links: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export default function Index({ tratamientos }: Props) {
    const [search, setSearch] = useState('');
    const [filtroEstado, setFiltroEstado] = useState('');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('tratamientos.index'), { search, estado: filtroEstado }, { preserveState: true });
    };

    const getEstadoBadge = (estado: string) => {
        const variants = {
            'Activo': 'default',
            'Pausado': 'secondary',
            'Completado': 'outline',
            'Suspendido': 'destructive'
        };
        return variants[estado as keyof typeof variants] || 'secondary';
    };

    const getTipoBadge = (tipo: string) => {
        return tipo === 'Programado' ? 'default' : 'outline';
    };

    return (
        <AppLayout>
            <Head title="Tratamientos" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <Heart className="h-6 w-6 text-green-600" />
                        <h1 className="text-2xl font-bold text-gray-900">Tratamientos</h1>
                    </div>
                    <Link href={route('tratamientos.create')}>
                        <Button className="flex items-center space-x-2">
                            <Plus className="h-4 w-4" />
                            <span>Nuevo Tratamiento</span>
                        </Button>
                    </Link>
                </div>

                {/* Estadísticas */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total</CardTitle>
                            <Heart className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{tratamientos.total}</div>
                            <p className="text-xs text-muted-foreground">tratamientos</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Activos</CardTitle>
                            <div className="h-2 w-2 bg-green-500 rounded-full"></div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {tratamientos.data.filter(t => t.estado === 'Activo').length}
                            </div>
                            <p className="text-xs text-muted-foreground">en curso</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Programados</CardTitle>
                            <Clock className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {tratamientos.data.filter(t => t.tipo === 'Programado').length}
                            </div>
                            <p className="text-xs text-muted-foreground">con horarios</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">PRN</CardTitle>
                            <div className="h-4 w-4 bg-orange-100 rounded"></div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {tratamientos.data.filter(t => t.tipo === 'PRN').length}
                            </div>
                            <p className="text-xs text-muted-foreground">según necesidad</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filtros y búsqueda */}
                <Card>
                    <CardHeader>
                        <CardTitle>Buscar Tratamientos</CardTitle>
                        <CardDescription>
                            Encuentra tratamientos por nombre, paciente o médico
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSearch} className="flex gap-4">
                            <Input
                                placeholder="Buscar tratamientos..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="flex-1"
                            />
                            <select
                                value={filtroEstado}
                                onChange={(e) => setFiltroEstado(e.target.value)}
                                className="px-3 py-2 border rounded-md"
                            >
                                <option value="">Todos los estados</option>
                                <option value="Activo">Activo</option>
                                <option value="Pausado">Pausado</option>
                                <option value="Completado">Completado</option>
                                <option value="Suspendido">Suspendido</option>
                            </select>
                            <Button type="submit" variant="outline">
                                <Search className="h-4 w-4 mr-2" />
                                Buscar
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                {/* Lista de tratamientos */}
                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Tratamientos</CardTitle>
                        <CardDescription>
                            Gestiona los tratamientos médicos del sistema
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {tratamientos.data.map((tratamiento) => (
                                <div key={tratamiento.id} 
                                     className="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                                    <div className="flex-1">
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <h3 className="font-semibold text-lg">{tratamiento.nombre}</h3>
                                                <p className="text-sm text-gray-600 mt-1">{tratamiento.objetivo}</p>
                                                
                                                <div className="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                                    <div className="flex items-center space-x-1">
                                                        <User className="h-4 w-4" />
                                                        <span>{tratamiento.paciente.nombre}</span>
                                                    </div>
                                                    <div className="flex items-center space-x-1">
                                                        <Calendar className="h-4 w-4" />
                                                        <span>Dr. {tratamiento.medico.name}</span>
                                                    </div>
                                                </div>

                                                <div className="flex items-center space-x-2 mt-2 text-xs text-gray-500">
                                                    <span>Inicio: {new Date(tratamiento.fecha_inicio).toLocaleDateString()}</span>
                                                    {tratamiento.fecha_fin && (
                                                        <span>• Fin: {new Date(tratamiento.fecha_fin).toLocaleDateString()}</span>
                                                    )}
                                                </div>

                                                {tratamiento.medicamentos.length > 0 && (
                                                    <div className="mt-2">
                                                        <p className="text-xs text-gray-500 mb-1">Medicamentos:</p>
                                                        <div className="flex flex-wrap gap-1">
                                                            {tratamiento.medicamentos.slice(0, 3).map((med) => (
                                                                <span key={med.id} className="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                                                                    {med.nombre}
                                                                </span>
                                                            ))}
                                                            {tratamiento.medicamentos.length > 3 && (
                                                                <span className="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">
                                                                    +{tratamiento.medicamentos.length - 3} más
                                                                </span>
                                                            )}
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                            
                                            <div className="flex flex-col space-y-2 ml-4">
                                                <Badge variant={getEstadoBadge(tratamiento.estado) as any}>
                                                    {tratamiento.estado.charAt(0).toUpperCase() + tratamiento.estado.slice(1)}
                                                </Badge>
                                                <Badge variant={getTipoBadge(tratamiento.tipo) as any}>
                                                    {tratamiento.tipo}
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div className="flex space-x-2 ml-4">
                                        <Link href={route('tratamientos.show', tratamiento.id)}>
                                            <Button variant="outline" size="sm">
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Link href={route('tratamientos.edit', tratamiento.id)}>
                                            <Button variant="outline" size="sm">
                                                <Edit className="h-4 w-4" />
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Paginación simple */}
                        {tratamientos.last_page > 1 && (
                            <div className="flex items-center justify-between mt-6">
                                <div className="text-sm text-gray-700">
                                    Página {tratamientos.current_page} de {tratamientos.last_page}
                                </div>
                                <div className="flex space-x-2">
                                    {tratamientos.current_page > 1 && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => router.get(route('tratamientos.index'), { page: tratamientos.current_page - 1 })}
                                        >
                                            Anterior
                                        </Button>
                                    )}
                                    {tratamientos.current_page < tratamientos.last_page && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => router.get(route('tratamientos.index'), { page: tratamientos.current_page + 1 })}
                                        >
                                            Siguiente
                                        </Button>
                                    )}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
} 