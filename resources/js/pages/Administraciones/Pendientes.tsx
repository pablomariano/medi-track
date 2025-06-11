import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Clock, User, Pill, CheckCircle, XCircle, AlertTriangle } from 'lucide-react';

interface Paciente {
    id: number;
    nombre: string;
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
    paciente: Paciente;
}

interface Administracion {
    id: number;
    fecha_hora_programada: string;
    dosis_administrada: number;
    unidad_dosis: string;
    estado: string;
    tratamiento: Tratamiento;
    medicamento: Medicamento;
}

interface Props {
    administraciones: Administracion[];
}

export default function Pendientes({ administraciones }: Props) {
    const administrar = (administracionId: number) => {
        router.patch(route('administraciones.administrar', administracionId), {
            dosis_administrada: administraciones.find(a => a.id === administracionId)?.dosis_administrada,
            observaciones: '',
            efectos_observados: ''
        });
    };

    const omitir = (administracionId: number) => {
        const motivo = prompt('Motivo de omisión:');
        if (motivo) {
            router.patch(route('administraciones.omitir', administracionId), {
                motivo_no_administracion: motivo,
                observaciones: ''
            });
        }
    };

    const getUrgencia = (fechaHora: string) => {
        const ahora = new Date();
        const programada = new Date(fechaHora);
        const diferencia = (programada.getTime() - ahora.getTime()) / (1000 * 60); // minutos

        if (diferencia < -30) return { nivel: 'atrasada', color: 'bg-red-100 text-red-800' };
        if (diferencia < 0) return { nivel: 'vencida', color: 'bg-orange-100 text-orange-800' };
        if (diferencia < 30) return { nivel: 'próxima', color: 'bg-yellow-100 text-yellow-800' };
        return { nivel: 'programada', color: 'bg-blue-100 text-blue-800' };
    };

    const formatearHora = (fechaHora: string) => {
        return new Date(fechaHora).toLocaleString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            day: '2-digit',
            month: '2-digit'
        });
    };

    return (
        <AppLayout>
            <Head title="Administraciones Pendientes" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <Clock className="h-6 w-6 text-orange-600" />
                        <h1 className="text-2xl font-bold text-gray-900">Administraciones Pendientes</h1>
                    </div>
                    <Badge variant="outline" className="text-orange-600 border-orange-200">
                        {administraciones.length} pendientes
                    </Badge>
                </div>

                {/* Estadísticas rápidas */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Pendientes</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{administraciones.length}</div>
                            <p className="text-xs text-muted-foreground">administraciones</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Atrasadas</CardTitle>
                            <AlertTriangle className="h-4 w-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">
                                {administraciones.filter(a => getUrgencia(a.fecha_hora_programada).nivel === 'atrasada').length}
                            </div>
                            <p className="text-xs text-muted-foreground">requieren atención</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Próximas</CardTitle>
                            <div className="h-2 w-2 bg-yellow-500 rounded-full"></div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-yellow-600">
                                {administraciones.filter(a => getUrgencia(a.fecha_hora_programada).nivel === 'próxima').length}
                            </div>
                            <p className="text-xs text-muted-foreground">en 30 min</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Pacientes</CardTitle>
                            <User className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {new Set(administraciones.map(a => a.tratamiento.paciente.id)).size}
                            </div>
                            <p className="text-xs text-muted-foreground">únicos</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Lista de administraciones */}
                <Card>
                    <CardHeader>
                        <CardTitle>Administraciones por Realizar</CardTitle>
                        <CardDescription>
                            Medicamentos que deben ser administrados próximamente
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {administraciones.length === 0 ? (
                            <div className="text-center py-8">
                                <CheckCircle className="h-12 w-12 text-green-500 mx-auto mb-4" />
                                <h3 className="text-lg font-semibold text-gray-900 mb-2">
                                    ¡Todas las administraciones están al día!
                                </h3>
                                <p className="text-gray-600">
                                    No hay medicamentos pendientes de administrar en este momento.
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {administraciones
                                    .sort((a, b) => new Date(a.fecha_hora_programada).getTime() - new Date(b.fecha_hora_programada).getTime())
                                    .map((administracion) => {
                                        const urgencia = getUrgencia(administracion.fecha_hora_programada);
                                        return (
                                            <div key={administracion.id} 
                                                 className="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                                                <div className="flex-1">
                                                    <div className="flex items-start justify-between">
                                                        <div className="flex-1">
                                                            <div className="flex items-center space-x-3">
                                                                <Pill className="h-5 w-5 text-blue-500" />
                                                                <div>
                                                                    <h3 className="font-semibold text-lg">
                                                                        {administracion.medicamento.nombre}
                                                                    </h3>
                                                                    <p className="text-sm text-gray-600">
                                                                        {administracion.medicamento.concentracion} {administracion.medicamento.unidad_concentracion}
                                                                    </p>
                                                                </div>
                                                            </div>

                                                            <div className="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                                                <div className="flex items-center space-x-1">
                                                                    <User className="h-4 w-4" />
                                                                    <span>{administracion.tratamiento.paciente.nombre}</span>
                                                                </div>
                                                                <div className="flex items-center space-x-1">
                                                                    <Clock className="h-4 w-4" />
                                                                    <span>{formatearHora(administracion.fecha_hora_programada)}</span>
                                                                </div>
                                                            </div>

                                                            <div className="mt-2">
                                                                <span className="text-sm font-medium">
                                                                    Dosis: {administracion.dosis_administrada} {administracion.unidad_dosis}
                                                                </span>
                                                            </div>

                                                            <div className="mt-2">
                                                                <span className="text-xs text-gray-500">
                                                                    Tratamiento: {administracion.tratamiento.nombre}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div className="flex flex-col items-end space-y-2">
                                                            <Badge className={urgencia.color}>
                                                                {urgencia.nivel}
                                                            </Badge>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div className="flex space-x-2 ml-4">
                                                    <Button 
                                                        onClick={() => administrar(administracion.id)}
                                                        className="bg-green-600 hover:bg-green-700"
                                                        size="sm"
                                                    >
                                                        <CheckCircle className="h-4 w-4 mr-1" />
                                                        Administrar
                                                    </Button>
                                                    <Button 
                                                        onClick={() => omitir(administracion.id)}
                                                        variant="outline"
                                                        size="sm"
                                                    >
                                                        <XCircle className="h-4 w-4 mr-1" />
                                                        Omitir
                                                    </Button>
                                                </div>
                                            </div>
                                        );
                                    })}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
} 