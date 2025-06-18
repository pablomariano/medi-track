import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft, UserPlus, AlertCircle, Calendar, Stethoscope } from 'lucide-react';
import { useState } from 'react';
import { format } from 'date-fns';

interface PersonalMedico {
    usuario_id: number;
    nombre: string;
    email: string;
    especialidad: string;
    anos_experiencia: number;
    pacientes_actuales: number;
    pacientes_principales: number;
}

interface Paciente {
    id: number;
    nombre: string;
    documento: string;
    medicos_actuales: number;
    tiene_medico_principal: boolean;
}

interface Props {
    pacientes: Paciente[];
    medicos: PersonalMedico[];
}

export default function Create({ pacientes, medicos }: Props) {
    const [selectedPaciente, setSelectedPaciente] = useState<Paciente | null>(null);
    const [selectedMedico, setSelectedMedico] = useState<PersonalMedico | null>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        paciente_id: '',
        medico_usuario_id: '',
        es_medico_principal: false,
        fecha_asignacion: format(new Date(), 'yyyy-MM-dd'),
        fecha_fin: '',
        especialidad_tratamiento: ''
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('asignaciones-medicos.store'), {
            onSuccess: () => reset(),
        });
    };

    const handlePacienteChange = (pacienteId: string) => {
        const paciente = pacientes.find(p => p.id.toString() === pacienteId);
        setSelectedPaciente(paciente || null);
        setData(prev => ({
            ...prev,
            paciente_id: pacienteId,
            es_medico_principal: paciente?.tiene_medico_principal ? false : prev.es_medico_principal
        }));
    };

    const handleMedicoChange = (medicoId: string) => {
        const medico = medicos.find(m => m.usuario_id.toString() === medicoId);
        setSelectedMedico(medico || null);
        setData('medico_usuario_id', medicoId);
    };

    const canBePrincipal = selectedPaciente && !selectedPaciente.tiene_medico_principal;

    return (
        <AppLayout>
            <Head title="Nueva Asignación Médico-Paciente" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="sm" asChild>
                        <Link href={route('asignaciones-medicos.index')}>
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Volver
                        </Link>
                    </Button>
                    
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900">
                            Nueva Asignación Médico-Paciente
                        </h1>
                        <p className="text-muted-foreground">
                            Asigna un médico a un paciente específico
                        </p>
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    {/* Formulario Principal */}
                    <div className="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <UserPlus className="h-5 w-5" />
                                    Datos de la Asignación
                                </CardTitle>
                                <CardDescription>
                                    Completa los datos para crear la nueva asignación médico-paciente
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={handleSubmit} className="space-y-6">
                                    {/* Selección de Paciente */}
                                    <div className="grid gap-2">
                                        <Label htmlFor="paciente_id">Paciente *</Label>
                                        <Select
                                            value={data.paciente_id}
                                            onValueChange={handlePacienteChange}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecciona un paciente" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {pacientes.map((paciente) => (
                                                    <SelectItem 
                                                        key={paciente.id} 
                                                        value={paciente.id.toString()}
                                                    >
                                                        <div className="flex items-center justify-between w-full">
                                                            <span>{paciente.nombre}</span>
                                                            <div className="flex items-center gap-2 text-xs text-muted-foreground ml-2">
                                                                <span>Doc: {paciente.documento}</span>
                                                                <span>•</span>
                                                                <span>{paciente.medicos_actuales} médicos</span>
                                                                {paciente.tiene_medico_principal && (
                                                                    <>
                                                                        <span>•</span>
                                                                        <span className="text-blue-600">Con principal</span>
                                                                    </>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.paciente_id && (
                                            <p className="text-sm text-red-600">{errors.paciente_id}</p>
                                        )}
                                    </div>

                                    {/* Selección de Médico */}
                                    <div className="grid gap-2">
                                        <Label htmlFor="medico_usuario_id">Médico *</Label>
                                        <Select
                                            value={data.medico_usuario_id}
                                            onValueChange={handleMedicoChange}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecciona un médico" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {medicos.map((medico) => (
                                                    <SelectItem 
                                                        key={medico.usuario_id} 
                                                        value={medico.usuario_id.toString()}
                                                    >
                                                        <div className="flex items-center justify-between w-full">
                                                            <div>
                                                                <div className="font-medium">{medico.nombre}</div>
                                                                <div className="text-xs text-muted-foreground">
                                                                    {medico.especialidad}
                                                                </div>
                                                            </div>
                                                            <div className="flex items-center gap-2 text-xs text-muted-foreground ml-2">
                                                                <span>{medico.anos_experiencia} años exp.</span>
                                                                <span>•</span>
                                                                <span>{medico.pacientes_actuales} pacientes</span>
                                                                <span>•</span>
                                                                <span>{medico.pacientes_principales} principales</span>
                                                            </div>
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.medico_usuario_id && (
                                            <p className="text-sm text-red-600">{errors.medico_usuario_id}</p>
                                        )}
                                    </div>

                                    {/* Grid de fechas */}
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="fecha_asignacion">Fecha de Asignación *</Label>
                                            <Input
                                                id="fecha_asignacion"
                                                type="date"
                                                value={data.fecha_asignacion}
                                                onChange={(e) => setData('fecha_asignacion', e.target.value)}
                                                className="w-full"
                                            />
                                            {errors.fecha_asignacion && (
                                                <p className="text-sm text-red-600">{errors.fecha_asignacion}</p>
                                            )}
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="fecha_fin">Fecha de Fin (Opcional)</Label>
                                            <Input
                                                id="fecha_fin"
                                                type="date"
                                                value={data.fecha_fin}
                                                onChange={(e) => setData('fecha_fin', e.target.value)}
                                                className="w-full"
                                            />
                                            {errors.fecha_fin && (
                                                <p className="text-sm text-red-600">{errors.fecha_fin}</p>
                                            )}
                                        </div>
                                    </div>

                                    {/* Especialidad del Tratamiento */}
                                    <div className="grid gap-2">
                                        <Label htmlFor="especialidad_tratamiento">Especialidad del Tratamiento (Opcional)</Label>
                                        <Input
                                            id="especialidad_tratamiento"
                                            type="text"
                                            value={data.especialidad_tratamiento}
                                            onChange={(e) => setData('especialidad_tratamiento', e.target.value)}
                                            placeholder="ej. Cardiología, Neurología, etc."
                                            maxLength={100}
                                        />
                                        {errors.especialidad_tratamiento && (
                                            <p className="text-sm text-red-600">{errors.especialidad_tratamiento}</p>
                                        )}
                                    </div>

                                    {/* Checkbox de médico principal */}
                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="es_medico_principal"
                                            checked={data.es_medico_principal}
                                            onCheckedChange={(checked) => setData('es_medico_principal', checked as boolean)}
                                            disabled={!canBePrincipal}
                                        />
                                        <Label 
                                            htmlFor="es_medico_principal" 
                                            className={!canBePrincipal ? 'text-muted-foreground' : ''}
                                        >
                                            Asignar como médico principal
                                        </Label>
                                    </div>

                                    {selectedPaciente?.tiene_medico_principal && (
                                        <div className="flex items-start gap-2 p-3 bg-amber-50 border border-amber-200 rounded-md">
                                            <AlertCircle className="h-4 w-4 text-amber-600 mt-0.5" />
                                            <div className="text-sm text-amber-800">
                                                <p className="font-medium">Este paciente ya tiene un médico principal</p>
                                                <p>Para asignar un nuevo médico principal, primero debes finalizar la asignación actual.</p>
                                            </div>
                                        </div>
                                    )}

                                    {errors.error && (
                                        <div className="flex items-start gap-2 p-3 bg-red-50 border border-red-200 rounded-md">
                                            <AlertCircle className="h-4 w-4 text-red-600 mt-0.5" />
                                            <p className="text-sm text-red-800">{errors.error}</p>
                                        </div>
                                    )}

                                    {/* Botones */}
                                    <div className="flex items-center gap-3 pt-6">
                                        <Button 
                                            type="submit" 
                                            disabled={processing}
                                            className="min-w-[120px]"
                                        >
                                            {processing ? 'Guardando...' : 'Crear Asignación'}
                                        </Button>
                                        
                                        <Button 
                                            type="button" 
                                            variant="outline" 
                                            asChild
                                        >
                                            <Link href={route('asignaciones-medicos.index')}>
                                                Cancelar
                                            </Link>
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Panel de Información */}
                    <div className="space-y-6">
                        {/* Información del Paciente */}
                        {selectedPaciente && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg">Información del Paciente</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <div>
                                        <p className="font-medium">{selectedPaciente.nombre}</p>
                                        <p className="text-sm text-muted-foreground">
                                            Doc: {selectedPaciente.documento}
                                        </p>
                                    </div>
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm">Médicos actuales:</span>
                                        <span className="font-medium">{selectedPaciente.medicos_actuales}</span>
                                    </div>
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm">Médico principal:</span>
                                        <span className={`text-sm font-medium ${selectedPaciente.tiene_medico_principal ? 'text-green-600' : 'text-gray-500'}`}>
                                            {selectedPaciente.tiene_medico_principal ? 'Asignado' : 'Sin asignar'}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Información del Médico */}
                        {selectedMedico && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg">Información del Médico</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <div>
                                        <p className="font-medium">{selectedMedico.nombre}</p>
                                        <p className="text-sm text-muted-foreground">
                                            {selectedMedico.email}
                                        </p>
                                    </div>
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm">Especialidad:</span>
                                        <span className="font-medium text-sm">{selectedMedico.especialidad}</span>
                                    </div>
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm">Experiencia:</span>
                                        <span className="font-medium">{selectedMedico.anos_experiencia} años</span>
                                    </div>
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm">Pacientes actuales:</span>
                                        <span className="font-medium">{selectedMedico.pacientes_actuales}</span>
                                    </div>
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm">Como principal:</span>
                                        <span className="font-medium">{selectedMedico.pacientes_principales}</span>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Guía de uso */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg flex items-center gap-2">
                                    <Stethoscope className="h-4 w-4" />
                                    Guía de Asignación
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3 text-sm">
                                <div>
                                    <p className="font-medium text-blue-600">Médico Principal</p>
                                    <p className="text-muted-foreground">
                                        Responsable principal del paciente. Solo puede haber uno por paciente.
                                    </p>
                                </div>
                                
                                <div>
                                    <p className="font-medium text-green-600">Médico Secundario</p>
                                    <p className="text-muted-foreground">
                                        Especialistas que apoyan en el tratamiento. Puede haber varios.
                                    </p>
                                </div>
                                
                                <div>
                                    <p className="font-medium text-orange-600">Fecha de Fin</p>
                                    <p className="text-muted-foreground">
                                        Opcional. Si no se especifica, la asignación será indefinida.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
} 