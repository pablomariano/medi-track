import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Heart } from 'lucide-react';
import { Link } from '@inertiajs/react';
import MedicamentoForm from '@/components/ui/medicamento-form';

interface Paciente {
    id: number;
    nombre: string;
    numero_documento: string;
}

interface Medico {
    id: number;
    name: string;
    email: string;
}

interface Medicamento {
    id: number;
    nombre: string;
    principio_activo: string;
    forma_farmaceutica: string;
    concentracion: string;
    unidad_concentracion: string;
}

interface MedicamentoFormData {
    medicamento_id: string;
    dosis_cantidad: string;
    unidad_dosis: string;
    frecuencia_horas: string;
    tolerancia_antes_minutos: string;
    tolerancia_despues_minutos: string;
    intervalo_minimo_horas: string;
    dosis_maxima_dia: string;
    dosis_maxima_consecutiva: string;
    instrucciones_especiales: string;
    orden: string;
}

interface Props {
    pacientes: Paciente[];
    medicos: Medico[];
    medicamentos: Medicamento[];
}

export default function Create({ pacientes, medicos, medicamentos }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        paciente_id: '',
        medico_usuario_id: '',
        nombre: '',
        objetivo: '',
        diagnostico: '',
        tipo: 'Programado',
        estado: 'Activo',
        fecha_inicio: '',
        fecha_fin: '',
        observaciones: '',
        medicamentos: [] as any
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('tratamientos.store'), {
            onSuccess: () => {
                reset();
            }
        });
    };

    const handleMedicamentosChange = (medicamentos: MedicamentoFormData[]) => {
        setData('medicamentos', medicamentos as any);
    };

    const medicamentosArray = Array.isArray(data.medicamentos) ? data.medicamentos as MedicamentoFormData[] : [];

    return (
        <AppLayout>
            <Head title="Crear Tratamiento" />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <Link href={route('tratamientos.index')}>
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Volver
                            </Button>
                        </Link>
                        <Heart className="h-6 w-6 text-green-600" />
                        <h1 className="text-2xl font-bold text-gray-900">Crear Nuevo Tratamiento</h1>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center space-x-2">
                                <Heart className="h-5 w-5" />
                                <span>Información del Tratamiento</span>
                            </CardTitle>
                            <CardDescription>
                                Datos básicos del tratamiento médico
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="paciente_id">Paciente *</Label>
                                    <Input
                                        id="paciente_id"
                                        type="text"
                                        value={data.paciente_id as string}
                                        onChange={(e) => setData('paciente_id', e.target.value)}
                                        placeholder="ID del paciente"
                                        required
                                    />
                                    {errors.paciente_id && <p className="text-red-600 text-sm mt-1">{errors.paciente_id}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="medico_usuario_id">Médico Responsable *</Label>
                                    <Input
                                        id="medico_usuario_id"
                                        type="text"
                                        value={data.medico_usuario_id as string}
                                        onChange={(e) => setData('medico_usuario_id', e.target.value)}
                                        placeholder="ID del médico"
                                        required
                                    />
                                    {errors.medico_usuario_id && <p className="text-red-600 text-sm mt-1">{errors.medico_usuario_id}</p>}
                                </div>
                            </div>

                            <div>
                                <Label htmlFor="nombre">Nombre del Tratamiento *</Label>
                                <Input
                                    id="nombre"
                                    type="text"
                                    value={data.nombre as string}
                                    onChange={(e) => setData('nombre', e.target.value)}
                                    placeholder="Nombre del tratamiento"
                                    required
                                />
                                {errors.nombre && <p className="text-red-600 text-sm mt-1">{errors.nombre}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="tipo">Tipo de Tratamiento *</Label>
                                    <select
                                        id="tipo"
                                        value={data.tipo as string}
                                        onChange={(e) => setData('tipo', e.target.value)}
                                        className="w-full px-3 py-2 border rounded-md"
                                        required
                                        disabled
                                    >
                                        <option value="Programado">Programado</option>
                                    </select>
                                    {errors.tipo && <p className="text-red-600 text-sm mt-1">{errors.tipo}</p>}
                                    <p className="text-sm text-gray-500 mt-1">
                                        Solo se permiten tratamientos programados con horarios fijos
                                    </p>
                                </div>

                                <div>
                                    <Label htmlFor="estado">Estado *</Label>
                                    <select
                                        id="estado"
                                        value={data.estado as string}
                                        onChange={(e) => setData('estado', e.target.value)}
                                        className="w-full px-3 py-2 border rounded-md"
                                        required
                                    >
                                        <option value="Activo">Activo</option>
                                        <option value="Pausado">Pausado</option>
                                        <option value="Suspendido">Suspendido</option>
                                    </select>
                                    {errors.estado && <p className="text-red-600 text-sm mt-1">{errors.estado}</p>}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="fecha_inicio">Fecha de Inicio *</Label>
                                    <Input
                                        id="fecha_inicio"
                                        type="date"
                                        value={data.fecha_inicio as string}
                                        onChange={(e) => setData('fecha_inicio', e.target.value)}
                                        required
                                    />
                                    {errors.fecha_inicio && <p className="text-red-600 text-sm mt-1">{errors.fecha_inicio}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="fecha_fin">Fecha de Fin</Label>
                                    <Input
                                        id="fecha_fin"
                                        type="date"
                                        value={data.fecha_fin as string}
                                        onChange={(e) => setData('fecha_fin', e.target.value)}
                                    />
                                    {errors.fecha_fin && <p className="text-red-600 text-sm mt-1">{errors.fecha_fin}</p>}
                                </div>
                            </div>

                            <div>
                                <Label htmlFor="objetivo">Objetivo del Tratamiento</Label>
                                <Textarea
                                    id="objetivo"
                                    value={data.objetivo}
                                    onChange={(e) => setData('objetivo', e.target.value)}
                                    placeholder="Describe los objetivos terapéuticos..."
                                    rows={3}
                                />
                                {errors.objetivo && <p className="text-red-600 text-sm mt-1">{errors.objetivo}</p>}
                            </div>

                            <div>
                                <Label htmlFor="diagnostico">Diagnóstico</Label>
                                <Input
                                    id="diagnostico"
                                    type="text"
                                    value={data.diagnostico}
                                    onChange={(e) => setData('diagnostico', e.target.value)}
                                    placeholder="Diagnóstico médico"
                                />
                                {errors.diagnostico && <p className="text-red-600 text-sm mt-1">{errors.diagnostico}</p>}
                            </div>

                            <div>
                                <Label htmlFor="observaciones">Observaciones</Label>
                                <Textarea
                                    id="observaciones"
                                    value={data.observaciones}
                                    onChange={(e) => setData('observaciones', e.target.value)}
                                    placeholder="Observaciones adicionales..."
                                    rows={3}
                                />
                                {errors.observaciones && <p className="text-red-600 text-sm mt-1">{errors.observaciones}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    <MedicamentoForm
                        medicamentos={medicamentos}
                        medicamentosSeleccionados={medicamentosArray}
                        onMedicamentosChange={(nuevos) => {
                            setData('medicamentos', nuevos);
                        }}
                        tipoTratamiento="Programado"
                        errors={errors}
                    />

                    <div className="flex items-center justify-end space-x-4">
                        <Link href={route('tratamientos.index')}>
                            <Button variant="outline">
                                Cancelar
                            </Button>
                        </Link>
                        <Button 
                            type="submit" 
                            disabled={processing || medicamentosArray.length === 0}
                            className={medicamentosArray.length === 0 ? 'opacity-50 cursor-not-allowed' : ''}
                        >
                            {processing ? 'Creando...' : 'Crear Tratamiento'}
                        </Button>
                    </div>
                    
                    {medicamentosArray.length === 0 && (
                        <p className="text-yellow-600 text-sm text-center">
                            ⚠️ Debe agregar al menos un medicamento para crear el tratamiento
                        </p>
                    )}
                </form>
            </div>
        </AppLayout>
    );
} 