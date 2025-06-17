import { Button } from '@/components/ui/button';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { ArrowLeft, UserCheck, Calendar, Users } from 'lucide-react';
import { Link, useForm } from '@inertiajs/react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { Badge } from "@/components/ui/badge";

interface Paciente {
    id: number;
    nombre: string;
    documento: string | null;
    cuidadores_actuales: number;
}

interface Cuidador {
    usuario_id: number;
    nombre: string;
    email: string;
    experiencia_anos: number | null;
    tarifa_hora: number | null;
    pacientes_actuales: number;
}

interface Props {
    pacientes: Paciente[];
    cuidadores: Cuidador[];
}

export default function Create({ pacientes, cuidadores }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        paciente_id: '',
        cuidador_usuario_id: '',
        fecha_asignacion: new Date().toISOString().split('T')[0],
        fecha_fin: '',
        activo: true
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('asignaciones-cuidadores.store'));
    };

    const formatTarifa = (tarifa: number | null) => {
        return tarifa ? `$${new Intl.NumberFormat('es-CL').format(tarifa)}` : 'No especificada';
    };

    const selectedPaciente = pacientes.find(p => p.id.toString() === data.paciente_id);
    const selectedCuidador = cuidadores.find(c => c.usuario_id.toString() === data.cuidador_usuario_id);

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('asignaciones-cuidadores.index')}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Nueva Asignación de Cuidador</h1>
                        <p className="text-muted-foreground">
                            Asigna un cuidador a un paciente
                        </p>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Formulario */}
                    <div className="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <UserCheck className="h-5 w-5" />
                                    Datos de la Asignación
                                </CardTitle>
                                <CardDescription>
                                    Complete la información para crear la asignación
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleSubmit} className="space-y-6">
                                    {/* Selección de Paciente */}
                                    <div className="space-y-2">
                                        <Label htmlFor="paciente_id">Paciente *</Label>
                                        <Select
                                            value={data.paciente_id}
                                            onValueChange={(value) => setData('paciente_id', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecciona un paciente" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {pacientes.map((paciente) => (
                                                    <SelectItem key={paciente.id} value={paciente.id.toString()}>
                                                        <div className="flex items-center justify-between w-full">
                                                            <div>
                                                                <span className="font-medium">{paciente.nombre}</span>
                                                                {paciente.documento && (
                                                                    <span className="text-muted-foreground ml-2">
                                                                        ({paciente.documento})
                                                                    </span>
                                                                )}
                                                            </div>
                                                            {paciente.cuidadores_actuales > 0 && (
                                                                <Badge variant="outline" className="ml-2">
                                                                    {paciente.cuidadores_actuales} cuidadores
                                                                </Badge>
                                                            )}
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.paciente_id && (
                                            <p className="text-sm text-destructive">{errors.paciente_id}</p>
                                        )}
                                    </div>

                                    {/* Selección de Cuidador */}
                                    <div className="space-y-2">
                                        <Label htmlFor="cuidador_usuario_id">Cuidador *</Label>
                                        <Select
                                            value={data.cuidador_usuario_id}
                                            onValueChange={(value) => setData('cuidador_usuario_id', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecciona un cuidador" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {cuidadores.map((cuidador) => (
                                                    <SelectItem key={cuidador.usuario_id} value={cuidador.usuario_id.toString()}>
                                                        <div className="flex flex-col">
                                                            <span className="font-medium">{cuidador.nombre}</span>
                                                            <span className="text-sm text-muted-foreground">{cuidador.email}</span>
                                                            <div className="flex items-center gap-2 mt-1">
                                                                {cuidador.experiencia_anos && (
                                                                    <Badge variant="outline" className="text-xs">
                                                                        {cuidador.experiencia_anos} años exp.
                                                                    </Badge>
                                                                )}
                                                                <Badge variant="secondary" className="text-xs">
                                                                    {formatTarifa(cuidador.tarifa_hora)}
                                                                </Badge>
                                                                {cuidador.pacientes_actuales > 0 && (
                                                                    <Badge variant="outline" className="text-xs">
                                                                        {cuidador.pacientes_actuales} pacientes
                                                                    </Badge>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.cuidador_usuario_id && (
                                            <p className="text-sm text-destructive">{errors.cuidador_usuario_id}</p>
                                        )}
                                    </div>

                                    {/* Fecha de Asignación */}
                                    <div className="space-y-2">
                                        <Label htmlFor="fecha_asignacion">Fecha de Asignación *</Label>
                                        <Input
                                            id="fecha_asignacion"
                                            type="date"
                                            value={data.fecha_asignacion}
                                            onChange={(e) => setData('fecha_asignacion', e.target.value)}
                                        />
                                        {errors.fecha_asignacion && (
                                            <p className="text-sm text-destructive">{errors.fecha_asignacion}</p>
                                        )}
                                    </div>

                                    {/* Fecha Fin (Opcional) */}
                                    <div className="space-y-2">
                                        <Label htmlFor="fecha_fin">Fecha de Fin (Opcional)</Label>
                                        <Input
                                            id="fecha_fin"
                                            type="date"
                                            value={data.fecha_fin}
                                            onChange={(e) => setData('fecha_fin', e.target.value)}
                                        />
                                        <p className="text-sm text-muted-foreground">
                                            Si no se especifica, la asignación será indefinida
                                        </p>
                                        {errors.fecha_fin && (
                                            <p className="text-sm text-destructive">{errors.fecha_fin}</p>
                                        )}
                                    </div>

                                    {/* Mensaje de error general */}
                                    {(errors as any).error && (
                                        <div className="p-3 rounded-md bg-red-50 border border-red-200">
                                            <p className="text-sm text-red-800">{(errors as any).error}</p>
                                        </div>
                                    )}

                                    {/* Botones */}
                                    <div className="flex items-center gap-2 pt-4">
                                        <Button type="submit" disabled={processing}>
                                            {processing ? 'Creando...' : 'Crear Asignación'}
                                        </Button>
                                        <Link href={route('asignaciones-cuidadores.index')}>
                                            <Button type="button" variant="outline">
                                                Cancelar
                                            </Button>
                                        </Link>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar con información */}
                    <div className="space-y-6">
                        {/* Vista previa de la asignación */}
                        {(selectedPaciente || selectedCuidador) && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg">Vista Previa</CardTitle>
                                    <CardDescription>
                                        Información de la asignación
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {selectedPaciente && (
                                        <div>
                                            <h4 className="font-medium text-sm text-muted-foreground">Paciente</h4>
                                            <p className="font-medium">{selectedPaciente.nombre}</p>
                                            {selectedPaciente.documento && (
                                                <p className="text-sm text-muted-foreground">
                                                    Doc: {selectedPaciente.documento}
                                                </p>
                                            )}
                                            {selectedPaciente.cuidadores_actuales > 0 && (
                                                <Badge variant="outline" className="mt-1">
                                                    Ya tiene {selectedPaciente.cuidadores_actuales} cuidadores
                                                </Badge>
                                            )}
                                        </div>
                                    )}

                                    {selectedCuidador && (
                                        <div>
                                            <h4 className="font-medium text-sm text-muted-foreground">Cuidador</h4>
                                            <p className="font-medium">{selectedCuidador.nombre}</p>
                                            <p className="text-sm text-muted-foreground">{selectedCuidador.email}</p>
                                            <div className="flex flex-col gap-1 mt-2">
                                                {selectedCuidador.experiencia_anos && (
                                                    <Badge variant="outline" className="text-xs w-fit">
                                                        {selectedCuidador.experiencia_anos} años de experiencia
                                                    </Badge>
                                                )}
                                                <Badge variant="secondary" className="text-xs w-fit">
                                                    Tarifa: {formatTarifa(selectedCuidador.tarifa_hora)}
                                                </Badge>
                                                {selectedCuidador.pacientes_actuales > 0 && (
                                                    <Badge variant="outline" className="text-xs w-fit">
                                                        Atiende {selectedCuidador.pacientes_actuales} pacientes
                                                    </Badge>
                                                )}
                                            </div>
                                        </div>
                                    )}

                                    {data.fecha_asignacion && (
                                        <div>
                                            <h4 className="font-medium text-sm text-muted-foreground">
                                                Fecha de Inicio
                                            </h4>
                                            <div className="flex items-center gap-2">
                                                <Calendar className="h-4 w-4 text-muted-foreground" />
                                                <p>{new Date(data.fecha_asignacion).toLocaleDateString('es-CL', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric'
                                                })}</p>
                                            </div>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        {/* Información de ayuda */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg flex items-center gap-2">
                                    <Users className="h-5 w-5" />
                                    Información
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm text-muted-foreground">
                                <p>• Los pacientes pueden tener múltiples cuidadores asignados</p>
                                <p>• Los cuidadores pueden atender varios pacientes</p>
                                <p>• Las fechas de fin son opcionales para asignaciones indefinidas</p>
                                <p>• No se pueden crear asignaciones duplicadas activas</p>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppSidebarLayout>
    );
}
