import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { 
    ArrowLeft, 
    Save, 
    Eye,
    User,
    UserCheck,
    Pill,
    Calendar,
    FileText,
    Building,
    AlertTriangle,
    Info
} from 'lucide-react';

interface Paciente {
    id: number;
    nombre: string;
    edad: string | number;
}

interface Medico {
    id: number;
    nombre: string;
    especialidad?: string;
    institucion?: string;
}

interface Medicamento {
    id: number;
    nombre_comercial: string;
    principio_activo: string;
    grupo_farmacologico: string;
    concentracion: number;
    unidad_concentracion: string;
    forma_farmaceutica: string;
    via_administracion: string;
    stock_actual: number;
}

interface UnidadDosis {
    id: number;
    nombre: string;
    simbolo: string;
    tipo: string;
}

interface TratamientoData {
    id: number;
    nombre: string;
    diagnostico?: string;
    objetivo_terapeutico?: string;
    estado: string;
    fecha_inicio: string;
    fecha_fin_estimada?: string;
    medico_prescriptor?: string;
    institucion?: string;
    observaciones?: string;
    paciente: {
        id: number;
        nombre: string;
    };
    medico?: {
        user: {
            id: number;
            name: string;
        };
    };
    created_at: string;
    updated_at: string;
}

interface FormData extends Record<string, any> {
    nombre: string;
    diagnostico: string;
    objetivo_terapeutico: string;
    fecha_fin_estimada: string;
    medico_prescriptor: string;
    institucion: string;
    observaciones: string;
}

interface Props {
    tratamiento: TratamientoData;
    pacientes: Paciente[];
    medicos: Medico[];
    medicamentos: Medicamento[];
    unidadesDosis: UnidadDosis[];
}

export default function Edit({ tratamiento, pacientes, medicos, medicamentos, unidadesDosis }: Props) {
    const { data, setData, put, processing, errors } = useForm<FormData>({
        nombre: tratamiento.nombre,
        diagnostico: tratamiento.diagnostico || '',
        objetivo_terapeutico: tratamiento.objetivo_terapeutico || '',
        fecha_fin_estimada: tratamiento.fecha_fin_estimada || '',
        medico_prescriptor: tratamiento.medico_prescriptor || '',
        institucion: tratamiento.institucion || '',
        observaciones: tratamiento.observaciones || '',
    });

    const [selectedPaciente] = useState<Paciente | null>(
        pacientes.find(p => p.id === tratamiento.paciente.id) || null
    );
    const [selectedMedico] = useState<Medico | null>(
        medicos.find(m => m.id === tratamiento.medico?.user.id) || null
    );

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/tratamientos/${tratamiento.id}`);
    };

    // Convertir fecha para el input date
    const formatDateForInput = (dateString: string) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const getEstadoBadge = (estado: string) => {
        const variants = {
            'Activo': { variant: 'default' as const, className: 'bg-green-100 text-green-800' },
            'Pausado': { variant: 'secondary' as const, className: 'bg-yellow-100 text-yellow-800' },
            'Completado': { variant: 'outline' as const, className: 'bg-blue-100 text-blue-800' },
            'Suspendido': { variant: 'destructive' as const, className: '' },
            'Modificado': { variant: 'secondary' as const, className: 'bg-purple-100 text-purple-800' },
        };

        const config = variants[estado as keyof typeof variants] || variants['Activo'];
        
        return (
            <Badge variant={config.variant} className={config.className}>
                {estado}
            </Badge>
        );
    };

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Tratamientos', href: '/tratamientos' },
                { title: tratamiento.nombre, href: `/tratamientos/${tratamiento.id}` },
                { title: 'Editar', href: `/tratamientos/${tratamiento.id}/edit` }
            ]}
        >
            <Head title={`Editar ${tratamiento.nombre}`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={`/tratamientos/${tratamiento.id}`}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div className="flex-1">
                        <h1 className="text-3xl font-bold tracking-tight">Editar Tratamiento</h1>
                        <p className="text-muted-foreground">
                            Modificar información del tratamiento: {tratamiento.nombre}
                        </p>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href={`/tratamientos/${tratamiento.id}`}>
                            <Eye className="h-4 w-4 mr-2" />
                            Ver Detalles
                        </Link>
                    </Button>
                </div>

                {/* Información de Estado */}
                {(tratamiento.estado === 'Completado' || tratamiento.estado === 'Suspendido') && (
                    <Card className="border-yellow-200 bg-yellow-50">
                        <CardContent className="pt-6">
                            <div className="flex items-center gap-2">
                                <AlertTriangle className="h-5 w-5 text-yellow-600" />
                                <div>
                                    <p className="font-medium text-yellow-800">
                                        Tratamiento en estado {tratamiento.estado}
                                    </p>
                                    <p className="text-sm text-yellow-700">
                                        {tratamiento.estado === 'Completado' 
                                            ? 'Este tratamiento está completado. Solo se pueden editar algunos campos básicos.'
                                            : 'Este tratamiento está suspendido. Las modificaciones requieren revisar el estado.'
                                        }
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Información Principal */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Información Básica */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <FileText className="h-5 w-5" />
                                        Información del Tratamiento
                                    </CardTitle>
                                    <CardDescription>
                                        Datos principales de la prescripción médica
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {/* Información No Editable */}
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Paciente</p>
                                            <p className="font-medium">{tratamiento.paciente.nombre}</p>
                                            <p className="text-xs text-muted-foreground">No editable</p>
                                        </div>
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Fecha de Inicio</p>
                                            <p className="font-medium">{formatDate(tratamiento.fecha_inicio)}</p>
                                            <p className="text-xs text-muted-foreground">No editable</p>
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="nombre">
                                            Nombre del Tratamiento <span className="text-red-500">*</span>
                                        </Label>
                                        <Input
                                            id="nombre"
                                            value={data.nombre}
                                            onChange={(e) => setData('nombre', e.target.value)}
                                            placeholder="Ej: Tratamiento Hipertensión Arterial"
                                            className={errors.nombre ? 'border-red-500' : ''}
                                        />
                                        {errors.nombre && (
                                            <p className="text-sm text-red-500">{errors.nombre}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="diagnostico">Diagnóstico</Label>
                                        <Input
                                            id="diagnostico"
                                            value={data.diagnostico}
                                            onChange={(e) => setData('diagnostico', e.target.value)}
                                            placeholder="Ej: Hipertensión Arterial Esencial"
                                            className={errors.diagnostico ? 'border-red-500' : ''}
                                        />
                                        {errors.diagnostico && (
                                            <p className="text-sm text-red-500">{errors.diagnostico}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="objetivo_terapeutico">Objetivo Terapéutico</Label>
                                        <Textarea
                                            id="objetivo_terapeutico"
                                            value={data.objetivo_terapeutico}
                                            onChange={(e) => setData('objetivo_terapeutico', e.target.value)}
                                            placeholder="Describir el objetivo del tratamiento..."
                                            rows={3}
                                            className={errors.objetivo_terapeutico ? 'border-red-500' : ''}
                                        />
                                        <div className="text-xs text-muted-foreground">
                                            {data.objetivo_terapeutico.length}/1000 caracteres
                                        </div>
                                        {errors.objetivo_terapeutico && (
                                            <p className="text-sm text-red-500">{errors.objetivo_terapeutico}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="observaciones">Observaciones</Label>
                                        <Textarea
                                            id="observaciones"
                                            value={data.observaciones}
                                            onChange={(e) => setData('observaciones', e.target.value)}
                                            placeholder="Observaciones adicionales del tratamiento..."
                                            rows={3}
                                            className={errors.observaciones ? 'border-red-500' : ''}
                                        />
                                        <div className="text-xs text-muted-foreground">
                                            {data.observaciones.length}/1000 caracteres
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Información de Fechas */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Calendar className="h-5 w-5" />
                                        Fechas del Tratamiento
                                    </CardTitle>
                                    <CardDescription>
                                        Planificación temporal del tratamiento
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="p-3 bg-gray-50 rounded-lg">
                                            <p className="text-sm font-medium text-muted-foreground">Fecha de Inicio</p>
                                            <p className="font-medium">{formatDate(tratamiento.fecha_inicio)}</p>
                                            <p className="text-xs text-muted-foreground">No se puede modificar</p>
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="fecha_fin_estimada">Fecha Fin Estimada</Label>
                                            <Input
                                                id="fecha_fin_estimada"
                                                type="date"
                                                value={formatDateForInput(data.fecha_fin_estimada)}
                                                onChange={(e) => setData('fecha_fin_estimada', e.target.value)}
                                                className={errors.fecha_fin_estimada ? 'border-red-500' : ''}
                                            />
                                            {errors.fecha_fin_estimada && (
                                                <p className="text-sm text-red-500">{errors.fecha_fin_estimada}</p>
                                            )}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Información Médica */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Building className="h-5 w-5" />
                                        Información Médica
                                    </CardTitle>
                                    <CardDescription>
                                        Detalles del personal médico y la institución
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="medico_prescriptor">Médico Prescriptor</Label>
                                            <Input
                                                id="medico_prescriptor"
                                                value={data.medico_prescriptor}
                                                onChange={(e) => setData('medico_prescriptor', e.target.value)}
                                                placeholder="Dr. Juan Pérez"
                                            />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="institucion">Institución</Label>
                                            <Input
                                                id="institucion"
                                                value={data.institucion}
                                                onChange={(e) => setData('institucion', e.target.value)}
                                                placeholder="Hospital General"
                                            />
                                        </div>
                                    </div>

                                    {/* Médico Tratante No Editable */}
                                    {tratamiento.medico && (
                                        <div className="p-3 bg-gray-50 rounded-lg">
                                            <p className="text-sm font-medium text-muted-foreground">Médico Tratante (Sistema)</p>
                                            <p className="font-medium">{tratamiento.medico.user.name}</p>
                                            <p className="text-xs text-muted-foreground">Asignado en el sistema - No editable</p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        {/* Panel Lateral */}
                        <div className="space-y-6">
                            {/* Estado del Tratamiento */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Info className="h-5 w-5" />
                                        Estado del Tratamiento
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Estado Actual</p>
                                        <div className="mt-1">
                                            {getEstadoBadge(tratamiento.estado)}
                                        </div>
                                    </div>

                                    <div className="text-xs text-muted-foreground p-2 bg-blue-50 rounded">
                                        <p><strong>Nota:</strong> El estado se modifica usando los botones de acción en la página de detalles del tratamiento.</p>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Información del Paciente */}
                            {selectedPaciente && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2">
                                            <User className="h-5 w-5" />
                                            Paciente
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-2">
                                            <p className="font-medium">{selectedPaciente.nombre}</p>
                                            <p className="text-sm text-muted-foreground">
                                                {selectedPaciente.edad} años
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                El paciente no se puede cambiar una vez creado el tratamiento
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Información del Médico */}
                            {selectedMedico && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2">
                                            <UserCheck className="h-5 w-5" />
                                            Médico Tratante
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-2">
                                            <p className="font-medium">{selectedMedico.nombre}</p>
                                            <p className="text-sm text-muted-foreground">
                                                {selectedMedico.especialidad}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {selectedMedico.institucion}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                Asignado por el sistema
                                            </p>
                                        </div>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Información sobre Medicamentos */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Pill className="h-5 w-5" />
                                        Medicamentos
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-sm space-y-2">
                                        <p className="text-muted-foreground">
                                            La edición de medicamentos del tratamiento se realizará en futuras versiones del sistema.
                                        </p>
                                        <p className="text-muted-foreground">
                                            Actualmente solo se pueden modificar los campos de información general del tratamiento.
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Información Original */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-sm">Información Original</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2 text-sm">
                                    <div>
                                        <span className="text-muted-foreground">ID:</span> {tratamiento.id}
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">Creado:</span> {new Date(tratamiento.created_at).toLocaleDateString()}
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">Actualizado:</span> {new Date(tratamiento.updated_at).toLocaleDateString()}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    {/* Botones de acción */}
                    <div className="flex items-center gap-4 pt-6 border-t">
                        <Button
                            type="submit"
                            disabled={processing}
                            className="min-w-32"
                        >
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Guardando...' : 'Actualizar Tratamiento'}
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={`/tratamientos/${tratamiento.id}`}>Cancelar</Link>
                        </Button>
                        <Button variant="ghost" asChild>
                            <Link href="/tratamientos">Volver al Listado</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </AppSidebarLayout>
    );
} 