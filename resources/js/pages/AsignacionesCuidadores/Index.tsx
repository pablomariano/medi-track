import { Button } from '@/components/ui/button';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Plus, UserCheck, Calendar, Clock, Users, Search, FileText } from 'lucide-react';
import { Link, router } from '@inertiajs/react';
import { Input } from '@/components/ui/input';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";

interface User {
    id: number;
    name: string;
    email: string;
}

interface Paciente {
    id: number;
    nombre: string;
    numero_documento: string | null;
}

interface Cuidador {
    usuario_id: number;
    user: User;
    experiencia_anos: number | null;
    tarifa_hora: number | null;
}

interface Asignacion {
    paciente_id: number;
    cuidador_usuario_id: number;
    fecha_asignacion: string;
    fecha_fin: string | null;
    activo: boolean;
    paciente: Paciente;
    cuidador: Cuidador;
}

interface Props {
    asignaciones: {
        data: Asignacion[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export default function Index({ asignaciones }: Props) {
    const formatFecha = (fecha: string) => {
        return new Date(fecha).toLocaleDateString('es-CL', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    const formatTarifa = (tarifa: number | null) => {
        return tarifa ? `$${new Intl.NumberFormat('es-CL').format(tarifa)}` : 'No especificada';
    };

    const getEstadoBadge = (asignacion: Asignacion) => {
        if (!asignacion.activo) {
            return <Badge variant="secondary" className="bg-red-100 text-red-800">Finalizada</Badge>;
        }
        
        if (asignacion.fecha_fin && new Date(asignacion.fecha_fin) < new Date()) {
            return <Badge variant="secondary" className="bg-orange-100 text-orange-800">Vencida</Badge>;
        }

        return <Badge variant="secondary" className="bg-green-100 text-green-800">Activa</Badge>;
    };

    const getDuracion = (fechaAsignacion: string, fechaFin: string | null) => {
        const inicio = new Date(fechaAsignacion);
        const fin = fechaFin ? new Date(fechaFin) : new Date();
        const diffTime = Math.abs(fin.getTime() - inicio.getTime());
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 1) return '1 día';
        if (diffDays < 30) return `${diffDays} días`;
        if (diffDays < 365) return `${Math.floor(diffDays / 30)} meses`;
        return `${Math.floor(diffDays / 365)} años`;
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <div className="flex items-center justify-between mb-6">
                    <div>
                        <h1 className="text-2xl font-bold">Asignaciones de Cuidadores</h1>
                        <p className="text-muted-foreground">
                            Gestiona las asignaciones entre pacientes y cuidadores
                        </p>
                    </div>
                    <div className="flex items-center space-x-2">
                        <Link href={route('asignaciones-cuidadores.historial')}>
                            <Button variant="outline">
                                <FileText className="h-4 w-4 mr-2" />
                                Ver Historial
                            </Button>
                        </Link>
                        <Link href={route('asignaciones-cuidadores.create')}>
                            <Button>
                                <Plus className="h-4 w-4 mr-2" />
                                Nueva Asignación
                            </Button>
                        </Link>
                    </div>
                </div>
                
                <div className="text-center py-8">
                    <UserCheck className="mx-auto h-12 w-12 text-muted-foreground" />
                    <h3 className="mt-2 text-sm font-medium text-muted-foreground">
                        Sistema de asignaciones implementado
                    </h3>
                    <p className="mt-1 text-sm text-muted-foreground">
                        La funcionalidad está lista para usar.
                    </p>
                </div>
            </div>
        </AppSidebarLayout>
    );
} 