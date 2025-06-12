import React, { useEffect } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Heart, PencilIcon } from 'lucide-react';
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

interface TratamientoData {
    id: number;
    paciente_id: number;
    medico_usuario_id: number;
    nombre: string;
    objetivo: string;
    diagnostico: string;
    tipo: 'Programado' | 'PRN';
    estado: string;
    fecha_inicio: string;
    fecha_fin: string;
    observaciones: string;
    medicamentos: Array<{
        id: number;
        nombre: string;
        concentracion: string;
        unidad_concentracion: string;
        pivot: {
            dosis_cantidad: number;
            unidad_dosis: string;
            frecuencia_horas: number;
            tolerancia_antes_minutos: number;
            tolerancia_despues_minutos: number;
            intervalo_minimo_horas: number;
            dosis_maxima_dia: number;
            dosis_maxima_consecutiva: number;
            instrucciones_especiales: string;
            orden: number;
        };
    }>;
}

interface Props {
    tratamiento: TratamientoData;
    pacientes: Paciente[];
    medicos: Medico[];
    medicamentos: Medicamento[];
}

export default function EditTratamiento({ tratamiento, pacientes, medicos, medicamentos }: Props) {
    const { data, setData, patch, processing, errors } = useForm({
        paciente_id: tratamiento.paciente_id.toString(),
        medico_usuario_id: tratamiento.medico_usuario_id.toString(),
        nombre: tratamiento.nombre,
        objetivo: tratamiento.objetivo || '',
        diagnostico: tratamiento.diagnostico || '',
        tipo: tratamiento.tipo,
        estado: tratamiento.estado,
        fecha_inicio: tratamiento.fecha_inicio,
        fecha_fin: tratamiento.fecha_fin || '',
        observaciones: tratamiento.observaciones || '',
        medicamentos: [] as any[]
    });

    // Cargar medicamentos existentes al montar el componente
    useEffect(() => {
        const medicamentosExistentes: MedicamentoFormData[] = tratamiento.medicamentos.map(med => ({
            medicamento_id: med.id.toString(),
            dosis_cantidad: med.pivot.dosis_cantidad.toString(),
            unidad_dosis: med.pivot.unidad_dosis || 'tableta',
            frecuencia_horas: med.pivot.frecuencia_horas?.toString() || '',
            tolerancia_antes_minutos: med.pivot.tolerancia_antes_minutos?.toString() || '',
            tolerancia_despues_minutos: med.pivot.tolerancia_despues_minutos?.toString() || '',
            intervalo_minimo_horas: med.pivot.intervalo_minimo_horas?.toString() || '',
            dosis_maxima_dia: med.pivot.dosis_maxima_dia?.toString() || '',
            dosis_maxima_consecutiva: med.pivot.dosis_maxima_consecutiva?.toString() || '',
            instrucciones_especiales: med.pivot.instrucciones_especiales || '',
            orden: med.pivot.orden?.toString() || '1'
        }));
        
        setData('medicamentos', medicamentosExistentes as any);
    }, [tratamiento]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(route('tratamientos.update', tratamiento.id));
    };

    const handleMedicamentosChange = (medicamentos: MedicamentoFormData[]) => {
        setData('medicamentos', medicamentos as any);
    };

    return (
        <AppLayout>
            <Head title={`Editar Tratamiento - ${tratamiento.nombre}`} />
            
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-2">
                        <Link href={route('tratamientos.show', tratamiento.id)}>
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Volver
                            </Button>
                        </Link>
                        <PencilIcon className="h-6 w-6 text-blue-600" />
                        <h1 className="text-2xl font-bold text-gray-900">Editar Tratamiento</h1>
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
                                Editar los datos básicos del tratamiento médico
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="paciente_id">Paciente *</Label>
                                    <select
                                        id="paciente_id"
                                        value={data.paciente_id}
                                        onChange={(e) => setData('paciente_id', e.target.value)}
                                        className="w-full px-3 py-2 border rounded-md"
                                        required
                                    >
                                        <option value="">Seleccionar paciente</option>
                                        {pacientes.map((paciente) => (
                                            <option key={paciente.id} value={paciente.id}>
                                                {paciente.nombre} - {paciente.numero_documento}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.paciente_id && <p className="text-red-600 text-sm mt-1">{errors.paciente_id}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="medico_usuario_id">Médico Responsable *</Label>
                                    <select
                                        id="medico_usuario_id"
                                        value={data.medico_usuario_id}
                                        onChange={(e) => setData('medico_usuario_id', e.target.value)}
                                        className="w-full px-3 py-2 border rounded-md"
                                        required
                                    >
                                        <option value="">Seleccionar médico</option>
                                        {medicos.map((medico) => (
                                            <option key={medico.id} value={medico.id}>
                                                {medico.name} - {medico.email}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.medico_usuario_id && <p className="text-red-600 text-sm mt-1">{errors.medico_usuario_id}</p>}
                                </div>
                            </div>

                            <div>
                                <Label htmlFor="nombre">Nombre del Tratamiento *</Label>
                                <Input
                                    id="nombre"
                                    type="text"
                                    value={data.nombre}
                                    onChange={(e) => setData('nombre', e.target.value)}
                                    required
                                />
                                {errors.nombre && <p className="text-red-600 text-sm mt-1">{errors.nombre}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="tipo">Tipo de Tratamiento *</Label>
                                    <select
                                        id="tipo"
                                        value={data.tipo}
                                        onChange={(e) => setData('tipo', e.target.value as 'Programado' | 'PRN')}
                                        className="w-full px-3 py-2 border rounded-md"
                                        required
                                    >
                                        <option value="Programado">Programado</option>
                                        <option value="PRN">PRN (Según necesidad)</option>
                                    </select>
                                    {errors.tipo && <p className="text-red-600 text-sm mt-1">{errors.tipo}</p>}
                                </div>

                                <div>
                                    <Label htmlFor="estado">Estado *</Label>
                                    <select
                                        id="estado"
                                        value={data.estado}
                                        onChange={(e) => setData('estado', e.target.value)}
                                        className="w-full px-3 py-2 border rounded-md"
                                        required
                                    >
                                        <option value="Activo">Activo</option>
                                        <option value="Pausado">Pausado</option>
                                        <option value="Completado">Completado</option>
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
                                        value={data.fecha_inicio}
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
                                        value={data.fecha_fin}
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
                                />
                                {errors.diagnostico && <p className="text-red-600 text-sm mt-1">{errors.diagnostico}</p>}
                            </div>

                            <div>
                                <Label htmlFor="observaciones">Observaciones</Label>
                                <Textarea
                                    id="observaciones"
                                    value={data.observaciones}
                                    onChange={(e) => setData('observaciones', e.target.value)}
                                    rows={3}
                                />
                                {errors.observaciones && <p className="text-red-600 text-sm mt-1">{errors.observaciones}</p>}
                            </div>
                        </CardContent>
                    </Card>

                    <MedicamentoForm
                        medicamentos={medicamentos}
                        medicamentosSeleccionados={data.medicamentos as MedicamentoFormData[]}
                        onMedicamentosChange={handleMedicamentosChange}
                        tipoTratamiento={data.tipo}
                        errors={errors}
                    />

                    <div className="flex items-center justify-end space-x-4">
                        <Link href={route('tratamientos.show', tratamiento.id)}>
                            <Button variant="outline">
                                Cancelar
                            </Button>
                        </Link>
                        <Button 
                            type="submit" 
                            disabled={processing || data.medicamentos.length === 0}
                        >
                            {processing ? 'Actualizando...' : 'Actualizar Tratamiento'}
                        </Button>
                    </div>
                    
                    {data.medicamentos.length === 0 && (
                        <p className="text-yellow-600 text-sm text-center">
                            ⚠️ Debe agregar al menos un medicamento para el tratamiento
                        </p>
                    )}
                </form>
            </div>
        </AppLayout>
    );
} 