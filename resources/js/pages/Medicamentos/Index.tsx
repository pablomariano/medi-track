import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Search, Plus, Eye, Edit, Pill, AlertTriangle } from 'lucide-react';

interface Medicamento {
    id: number;
    nombre: string;
    principio_activo: string;
    concentracion: string;
    unidad_concentracion: string;
    forma_farmaceutica: string;
    via_administracion: string;
    requiere_receta: boolean;
    categoria_terapeutica: string;
    laboratorio: string;
    activo: boolean;
    created_at: string;
}

interface Props {
    medicamentos: {
        data: Medicamento[];
        links: any[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export default function Index({ medicamentos }: Props) {
    const [search, setSearch] = useState('');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('medicamentos.index'), { search }, { preserveState: true });
    };

    return (
        <AppLayout>
            <Head title="Medicamentos" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <Pill className="h-6 w-6 text-blue-600" />
                        <h1 className="text-2xl font-bold text-gray-900">Medicamentos</h1>
                    </div>
                    <Link href={route('medicamentos.create')}>
                        <Button className="flex items-center space-x-2">
                            <Plus className="h-4 w-4" />
                            <span>Nuevo Medicamento</span>
                        </Button>
                    </Link>
                </div>

                {/* Estadísticas */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total</CardTitle>
                            <Pill className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{medicamentos.total}</div>
                            <p className="text-xs text-muted-foreground">medicamentos</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Con Receta</CardTitle>
                            <AlertTriangle className="h-4 w-4 text-orange-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {medicamentos.data.filter(m => m.requiere_receta).length}
                            </div>
                            <p className="text-xs text-muted-foreground">requieren receta</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Activos</CardTitle>
                            <div className="h-2 w-2 bg-green-500 rounded-full"></div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {medicamentos.data.filter(m => m.activo).length}
                            </div>
                            <p className="text-xs text-muted-foreground">disponibles</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Categorías</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {new Set(medicamentos.data.map(m => m.categoria_terapeutica).filter(Boolean)).size}
                            </div>
                            <p className="text-xs text-muted-foreground">terapéuticas</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Búsqueda */}
                <Card>
                    <CardHeader>
                        <CardTitle>Buscar Medicamentos</CardTitle>
                        <CardDescription>
                            Encuentra medicamentos por nombre o principio activo
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSearch} className="flex gap-4">
                            <Input
                                placeholder="Buscar por nombre o principio activo..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="flex-1"
                            />
                            <Button type="submit" variant="outline">
                                <Search className="h-4 w-4 mr-2" />
                                Buscar
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                {/* Lista de medicamentos */}
                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Medicamentos</CardTitle>
                        <CardDescription>
                            Gestiona los medicamentos del sistema
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {medicamentos.data.map((medicamento) => (
                                <div key={medicamento.id} 
                                     className="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                                    <div className="flex-1">
                                        <div className="flex items-center space-x-4">
                                            <div className="flex-1">
                                                <h3 className="font-semibold text-lg">{medicamento.nombre}</h3>
                                                <p className="text-sm text-gray-600">{medicamento.principio_activo}</p>
                                                <p className="text-xs text-gray-500">
                                                    {medicamento.concentracion} {medicamento.unidad_concentracion} - {medicamento.forma_farmaceutica}
                                                </p>
                                                {medicamento.laboratorio && (
                                                    <p className="text-xs text-gray-500">
                                                        Lab: {medicamento.laboratorio}
                                                    </p>
                                                )}
                                            </div>
                                            
                                            <div className="flex flex-col space-y-1">
                                                <Badge variant={medicamento.activo ? "default" : "secondary"}>
                                                    {medicamento.activo ? "Activo" : "Inactivo"}
                                                </Badge>
                                                {medicamento.requiere_receta && (
                                                    <Badge variant="outline" className="text-orange-600 border-orange-200">
                                                        Receta
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div className="flex space-x-2 ml-4">
                                        <Link href={route('medicamentos.show', medicamento.id)}>
                                            <Button variant="outline" size="sm">
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Link href={route('medicamentos.edit', medicamento.id)}>
                                            <Button variant="outline" size="sm">
                                                <Edit className="h-4 w-4" />
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Paginación simple */}
                        {medicamentos.last_page > 1 && (
                            <div className="flex items-center justify-between mt-6">
                                <div className="text-sm text-gray-700">
                                    Página {medicamentos.current_page} de {medicamentos.last_page}
                                </div>
                                <div className="flex space-x-2">
                                    {medicamentos.current_page > 1 && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => router.get(route('medicamentos.index'), { page: medicamentos.current_page - 1 })}
                                        >
                                            Anterior
                                        </Button>
                                    )}
                                    {medicamentos.current_page < medicamentos.last_page && (
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => router.get(route('medicamentos.index'), { page: medicamentos.current_page + 1 })}
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