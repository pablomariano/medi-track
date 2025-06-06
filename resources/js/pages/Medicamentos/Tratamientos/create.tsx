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
    Plus,
    X,
    User,
    UserCheck,
    Pill,
    Calendar,
    FileText,
    Building,
    AlertTriangle
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

interface MedicamentoFormulario {
    medicamento_id: string;
    tipo_esquema: string;
    dosis_cantidad: string;
    unidad_dosis_id: string;
    frecuencia_horas: string;
    duracion_dias: string;
    indicaciones_uso: string;
    orden_prescripcion: number;
}

interface FormData extends Record<string, any> {
    paciente_id: string;
    medico_usuario_id: string;
    nombre: string;
    diagnostico: string;
    objetivo_terapeutico: string;
    fecha_inicio: string;
    fecha_fin_estimada: string;
    medico_prescriptor: string;
    institucion: string;
    observaciones: string;
    medicamentos: MedicamentoFormulario[];
}

interface Props {
    pacientes: Paciente[];
    medicos: Medico[];
    medicamentos: Medicamento[];
    unidadesDosis: UnidadDosis[];
}

export default function Create({ pacientes, medicos, medicamentos, unidadesDosis }: Props) {
    const { data, setData, post, processing, errors } = useForm<FormData>({
        paciente_id: '',
        medico_usuario_id: '',
        nombre: '',
        diagnostico: '',
        objetivo_terapeutico: '',
        fecha_inicio: '',
        fecha_fin_estimada: '',
        medico_prescriptor: '',
        institucion: '',
        observaciones: '',
        medicamentos: []
    });

    const [selectedPaciente, setSelectedPaciente] = useState<Paciente | null>(null);
    const [selectedMedico, setSelectedMedico] = useState<Medico | null>(null);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('tratamientos.store'));
    };

    const agregarMedicamento = () => {
        const nuevoMedicamento: MedicamentoFormulario = {
            medicamento_id: '',
            tipo_esquema: 'Fijo',
            dosis_cantidad: '',
            unidad_dosis_id: '',
            frecuencia_horas: '',
            duracion_dias: '',
            indicaciones_uso: '',
            orden_prescripcion: data.medicamentos.length + 1
        };

        setData('medicamentos', [...data.medicamentos, nuevoMedicamento]);
    };

    const removerMedicamento = (index: number) => {
        const medicamentosActualizados = data.medicamentos.filter((_, i) => i !== index);
        // Reordenar
        medicamentosActualizados.forEach((med, i) => {
            med.orden_prescripcion = i + 1;
        });
        setData('medicamentos', medicamentosActualizados);
    };

    const actualizarMedicamento = (index: number, campo: keyof MedicamentoFormulario, valor: any) => {
        const medicamentosActualizados = [...data.medicamentos];
        medicamentosActualizados[index] = {
            ...medicamentosActualizados[index],
            [campo]: valor
        };
        setData('medicamentos', medicamentosActualizados);
    };

    const getMedicamentoInfo = (medicamentoId: string) => {
        return medicamentos.find(m => m.id.toString() === medicamentoId);
    };

    const tiposEsquema = [
        'Fijo',
        'Variable', 
        'PRN',
        'Escalonamiento',
        'Reduccion',
        'Alterno'
    ];

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Tratamientos', href: '/tratamientos' },
                { title: 'Nuevo', href: '/tratamientos/create' }
            ]}
        >
            <Head title="Nuevo Tratamiento" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/tratamientos">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Nuevo Tratamiento</h1>
                        <p className="text-muted-foreground">
                            Prescripción médica y plan terapéutico
                        </p>
                    </div>
                </div>

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
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="paciente_id">
                                                Paciente <span className="text-red-500">*</span>
                                            </Label>
                                            <Select 
                                                value={data.paciente_id} 
                                                onValueChange={(value) => {
                                                    setData('paciente_id', value);
                                                    const paciente = pacientes.find(p => p.id.toString() === value);
                                                    setSelectedPaciente(paciente || null);
                                                }}
                                            >
                                                <SelectTrigger className={errors.paciente_id ? 'border-red-500' : ''}>
                                                    <SelectValue placeholder="Seleccione un paciente" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {pacientes.map((paciente) => (
                                                        <SelectItem key={paciente.id} value={paciente.id.toString()}>
                                                            <div>
                                                                <div className="font-medium">{paciente.nombre}</div>
                                                                <div className="text-sm text-muted-foreground">
                                                                    {paciente.edad} años
                                                                </div>
                                                            </div>
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {errors.paciente_id && (
                                                <p className="text-sm text-red-500">{errors.paciente_id}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="medico_usuario_id">Médico Tratante</Label>
                                            <Select 
                                                value={data.medico_usuario_id} 
                                                onValueChange={(value) => {
                                                    setData('medico_usuario_id', value);
                                                    const medico = medicos.find(m => m.id.toString() === value);
                                                    setSelectedMedico(medico || null);
                                                }}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Seleccione un médico" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {medicos.map((medico) => (
                                                        <SelectItem key={medico.id} value={medico.id.toString()}>
                                                            <div>
                                                                <div className="font-medium">{medico.nombre}</div>
                                                                <div className="text-sm text-muted-foreground">
                                                                    {medico.especialidad}
                                                                </div>
                                                            </div>
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {selectedMedico && (
                                                <p className="text-sm text-muted-foreground">
                                                    {selectedMedico.institucion}
                                                </p>
                                            )}
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

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                            <Label htmlFor="fecha_inicio">
                                                Fecha de Inicio <span className="text-red-500">*</span>
                                            </Label>
                                            <Input
                                                id="fecha_inicio"
                                                type="date"
                                                value={data.fecha_inicio}
                                                onChange={(e) => setData('fecha_inicio', e.target.value)}
                                                className={errors.fecha_inicio ? 'border-red-500' : ''}
                                            />
                                            {errors.fecha_inicio && (
                                                <p className="text-sm text-red-500">{errors.fecha_inicio}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="objetivo_terapeutico">Objetivo Terapéutico</Label>
                                        <Textarea
                                            id="objetivo_terapeutico"
                                            value={data.objetivo_terapeutico}
                                            onChange={(e) => setData('objetivo_terapeutico', e.target.value)}
                                            placeholder="Describir el objetivo del tratamiento..."
                                            rows={2}
                                            className={errors.objetivo_terapeutico ? 'border-red-500' : ''}
                                        />
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
                                            rows={2}
                                            className={errors.observaciones ? 'border-red-500' : ''}
                                        />
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Medicamentos */}
                            <Card>
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <CardTitle className="flex items-center gap-2">
                                                <Pill className="h-5 w-5" />
                                                Medicamentos del Tratamiento
                                            </CardTitle>
                                            <CardDescription>
                                                Prescripción y esquemas de dosificación
                                            </CardDescription>
                                        </div>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={agregarMedicamento}
                                        >
                                            <Plus className="h-4 w-4 mr-2" />
                                            Agregar Medicamento
                                        </Button>
                                    </div>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {data.medicamentos.length === 0 ? (
                                        <div className="text-center py-8">
                                            <Pill className="h-12 w-12 text-muted-foreground mx-auto mb-3" />
                                            <p className="text-muted-foreground mb-3">
                                                No hay medicamentos agregados al tratamiento
                                            </p>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                onClick={agregarMedicamento}
                                            >
                                                <Plus className="h-4 w-4 mr-2" />
                                                Agregar Primer Medicamento
                                            </Button>
                                        </div>
                                    ) : (
                                        <div className="space-y-4">
                                            {data.medicamentos.map((medicamento, index) => {
                                                const medicamentoInfo = getMedicamentoInfo(medicamento.medicamento_id);
                                                
                                                return (
                                                    <Card key={index} className="border-2">
                                                        <CardHeader className="pb-3">
                                                            <div className="flex items-center justify-between">
                                                                <div className="flex items-center gap-2">
                                                                    <Badge variant="outline">#{medicamento.orden_prescripcion}</Badge>
                                                                    <span className="font-medium">
                                                                        Medicamento {index + 1}
                                                                    </span>
                                                                </div>
                                                                <Button
                                                                    type="button"
                                                                    variant="ghost"
                                                                    size="sm"
                                                                    onClick={() => removerMedicamento(index)}
                                                                >
                                                                    <X className="h-4 w-4" />
                                                                </Button>
                                                            </div>
                                                        </CardHeader>
                                                        <CardContent className="space-y-4">
                                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                <div className="space-y-2">
                                                                    <Label>Medicamento *</Label>
                                                                    <Select
                                                                        value={medicamento.medicamento_id}
                                                                        onValueChange={(value) => 
                                                                            actualizarMedicamento(index, 'medicamento_id', value)
                                                                        }
                                                                    >
                                                                        <SelectTrigger className={
                                                                            errors[`medicamentos.${index}.medicamento_id`] ? 'border-red-500' : ''
                                                                        }>
                                                                            <SelectValue placeholder="Seleccionar medicamento" />
                                                                        </SelectTrigger>
                                                                        <SelectContent>
                                                                            {medicamentos.map((med) => (
                                                                                <SelectItem key={med.id} value={med.id.toString()}>
                                                                                    <div>
                                                                                        <div className="font-medium">
                                                                                            {med.nombre_comercial}
                                                                                        </div>
                                                                                        <div className="text-sm text-muted-foreground">
                                                                                            {med.principio_activo} - {med.concentracion}{med.unidad_concentracion}
                                                                                        </div>
                                                                                        <div className="text-xs text-muted-foreground">
                                                                                            Stock: {med.stock_actual} - {med.forma_farmaceutica}
                                                                                        </div>
                                                                                    </div>
                                                                                </SelectItem>
                                                                            ))}
                                                                        </SelectContent>
                                                                    </Select>
                                                                    {medicamentoInfo && (
                                                                        <div className="text-xs text-muted-foreground bg-gray-50 p-2 rounded">
                                                                            <p><strong>Principio Activo:</strong> {medicamentoInfo.principio_activo}</p>
                                                                            <p><strong>Forma:</strong> {medicamentoInfo.forma_farmaceutica}</p>
                                                                            <p><strong>Vía:</strong> {medicamentoInfo.via_administracion}</p>
                                                                            <p><strong>Stock:</strong> {medicamentoInfo.stock_actual} unidades</p>
                                                                        </div>
                                                                    )}
                                                                </div>

                                                                <div className="space-y-2">
                                                                    <Label>Tipo de Esquema *</Label>
                                                                    <Select
                                                                        value={medicamento.tipo_esquema}
                                                                        onValueChange={(value) => 
                                                                            actualizarMedicamento(index, 'tipo_esquema', value)
                                                                        }
                                                                    >
                                                                        <SelectTrigger>
                                                                            <SelectValue />
                                                                        </SelectTrigger>
                                                                        <SelectContent>
                                                                            {tiposEsquema.map((tipo) => (
                                                                                <SelectItem key={tipo} value={tipo}>
                                                                                    {tipo}
                                                                                </SelectItem>
                                                                            ))}
                                                                        </SelectContent>
                                                                    </Select>
                                                                </div>
                                                            </div>

                                                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                                <div className="space-y-2">
                                                                    <Label>Dosis *</Label>
                                                                    <Input
                                                                        type="number"
                                                                        step="0.001"
                                                                        min="0"
                                                                        value={medicamento.dosis_cantidad}
                                                                        onChange={(e) => 
                                                                            actualizarMedicamento(index, 'dosis_cantidad', e.target.value)
                                                                        }
                                                                        placeholder="1"
                                                                        className={
                                                                            errors[`medicamentos.${index}.dosis_cantidad`] ? 'border-red-500' : ''
                                                                        }
                                                                    />
                                                                </div>

                                                                <div className="space-y-2">
                                                                    <Label>Unidad de Dosis *</Label>
                                                                    <Select
                                                                        value={medicamento.unidad_dosis_id}
                                                                        onValueChange={(value) => 
                                                                            actualizarMedicamento(index, 'unidad_dosis_id', value)
                                                                        }
                                                                    >
                                                                        <SelectTrigger className={
                                                                            errors[`medicamentos.${index}.unidad_dosis_id`] ? 'border-red-500' : ''
                                                                        }>
                                                                            <SelectValue placeholder="Unidad" />
                                                                        </SelectTrigger>
                                                                        <SelectContent>
                                                                            {unidadesDosis.map((unidad) => (
                                                                                <SelectItem key={unidad.id} value={unidad.id.toString()}>
                                                                                    {unidad.nombre} ({unidad.simbolo})
                                                                                </SelectItem>
                                                                            ))}
                                                                        </SelectContent>
                                                                    </Select>
                                                                </div>

                                                                <div className="space-y-2">
                                                                    <Label>Frecuencia (horas)</Label>
                                                                    <Input
                                                                        type="number"
                                                                        min="1"
                                                                        max="168"
                                                                        value={medicamento.frecuencia_horas}
                                                                        onChange={(e) => 
                                                                            actualizarMedicamento(index, 'frecuencia_horas', e.target.value)
                                                                        }
                                                                        placeholder="8"
                                                                    />
                                                                    <p className="text-xs text-muted-foreground">
                                                                        Cada cuántas horas administrar
                                                                    </p>
                                                                </div>
                                                            </div>

                                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                <div className="space-y-2">
                                                                    <Label>Duración (días)</Label>
                                                                    <Input
                                                                        type="number"
                                                                        min="1"
                                                                        value={medicamento.duracion_dias}
                                                                        onChange={(e) => 
                                                                            actualizarMedicamento(index, 'duracion_dias', e.target.value)
                                                                        }
                                                                        placeholder="30"
                                                                    />
                                                                </div>

                                                                <div className="space-y-2">
                                                                    <Label>Indicaciones de Uso</Label>
                                                                    <Input
                                                                        value={medicamento.indicaciones_uso}
                                                                        onChange={(e) => 
                                                                            actualizarMedicamento(index, 'indicaciones_uso', e.target.value)
                                                                        }
                                                                        placeholder="Ej: Tomar con alimentos"
                                                                    />
                                                                </div>
                                                            </div>
                                                        </CardContent>
                                                    </Card>
                                                );
                                            })}
                                        </div>
                                    )}

                                    {errors.medicamentos && (
                                        <div className="flex items-center gap-2 text-red-600 text-sm">
                                            <AlertTriangle className="h-4 w-4" />
                                            <span>{errors.medicamentos}</span>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        </div>

                        {/* Panel Lateral */}
                        <div className="space-y-6">
                            {/* Información del Paciente */}
                            {selectedPaciente && (
                                <Card>
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2">
                                            <User className="h-5 w-5" />
                                            Paciente Seleccionado
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="space-y-2">
                                            <p className="font-medium">{selectedPaciente.nombre}</p>
                                            <p className="text-sm text-muted-foreground">
                                                {selectedPaciente.edad} años
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
                                        </div>
                                    </CardContent>
                                </Card>
                            )}

                            {/* Fechas */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Calendar className="h-5 w-5" />
                                        Fechas del Tratamiento
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="fecha_fin_estimada">Fecha Fin Estimada</Label>
                                        <Input
                                            id="fecha_fin_estimada"
                                            type="date"
                                            value={data.fecha_fin_estimada}
                                            onChange={(e) => setData('fecha_fin_estimada', e.target.value)}
                                            className={errors.fecha_fin_estimada ? 'border-red-500' : ''}
                                        />
                                        {errors.fecha_fin_estimada && (
                                            <p className="text-sm text-red-500">{errors.fecha_fin_estimada}</p>
                                        )}
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
                                </CardHeader>
                                <CardContent className="space-y-4">
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
                                </CardContent>
                            </Card>

                            {/* Resumen */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-base">Resumen</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2 text-sm">
                                    <div>
                                        <span className="text-muted-foreground">Medicamentos:</span> {data.medicamentos.length}
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">Estado:</span> Activo (al crear)
                                    </div>
                                    {data.fecha_inicio && data.fecha_fin_estimada && (
                                        <div>
                                            <span className="text-muted-foreground">Duración estimada:</span>{' '}
                                            {Math.ceil(
                                                (new Date(data.fecha_fin_estimada).getTime() - 
                                                 new Date(data.fecha_inicio).getTime()) / 
                                                (1000 * 60 * 60 * 24)
                                            )} días
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    {/* Botones de acción */}
                    <div className="flex items-center gap-4 pt-6 border-t">
                        <Button
                            type="submit"
                            disabled={processing || data.medicamentos.length === 0}
                            className="min-w-32"
                        >
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Creando...' : 'Crear Tratamiento'}
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href="/tratamientos">Cancelar</Link>
                        </Button>
                        
                        {data.medicamentos.length === 0 && (
                            <p className="text-sm text-muted-foreground">
                                Debe agregar al menos un medicamento para crear el tratamiento
                            </p>
                        )}
                    </div>
                </form>
            </div>
        </AppSidebarLayout>
    );
} 