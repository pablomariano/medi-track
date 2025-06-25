import React from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Plus, Trash2, AlertCircle } from 'lucide-react';

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
    medicamentos: Medicamento[];
    medicamentosSeleccionados: MedicamentoFormData[];
    onMedicamentosChange: (medicamentos: MedicamentoFormData[]) => void;
    tipoTratamiento: 'Programado';
    errors?: { [key: string]: string };
}

export default function MedicamentoForm({
    medicamentos,
    medicamentosSeleccionados,
    onMedicamentosChange,
    tipoTratamiento,
    errors = {}
}: Props) {
    const agregarMedicamento = () => {
        const nuevoMedicamento: MedicamentoFormData = {
            medicamento_id: '',
            dosis_cantidad: '',
            unidad_dosis: 'tableta',
            frecuencia_horas: '24',
            tolerancia_antes_minutos: '30',
            tolerancia_despues_minutos: '60',
            intervalo_minimo_horas: '',
            dosis_maxima_dia: '',
            dosis_maxima_consecutiva: '',
            instrucciones_especiales: '',
            orden: String(medicamentosSeleccionados.length + 1)
        };

        onMedicamentosChange([...medicamentosSeleccionados, nuevoMedicamento]);
    };

    const removerMedicamento = (index: number) => {
        const nuevosMedicamentos = medicamentosSeleccionados.filter((_, i) => i !== index);
        onMedicamentosChange(nuevosMedicamentos);
    };

    const actualizarMedicamento = (index: number, campo: keyof MedicamentoFormData, valor: string) => {
        const nuevosMedicamentos = [...medicamentosSeleccionados];
        nuevosMedicamentos[index] = {
            ...nuevosMedicamentos[index],
            [campo]: valor
        };
        onMedicamentosChange(nuevosMedicamentos);
    };

    const getMedicamentoNombre = (medicamentoId: string) => {
        const medicamento = medicamentos.find(m => m.id.toString() === medicamentoId);
        return medicamento ? `${medicamento.nombre} ${medicamento.concentracion}${medicamento.unidad_concentracion}` : '';
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center justify-between">
                    <span className="flex items-center space-x-2">
                        <AlertCircle className="h-5 w-5" />
                        <span>Medicamentos del Tratamiento</span>
                    </span>
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        onClick={agregarMedicamento}
                    >
                        <Plus className="h-4 w-4 mr-2" />
                        Agregar Medicamento
                    </Button>
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
                {medicamentosSeleccionados.length === 0 ? (
                    <div className="text-center py-8 text-gray-500">
                        <AlertCircle className="h-12 w-12 mx-auto mb-4 opacity-50" />
                        <p>No hay medicamentos agregados al tratamiento</p>
                        <p className="text-sm">Haga clic en "Agregar Medicamento" para comenzar</p>
                    </div>
                ) : (
                    medicamentosSeleccionados.map((medicamento, index) => (
                        <Card key={index}>
                            <CardHeader className="pb-3">
                                <div className="flex items-center justify-between">
                                    <h4 className="font-medium">Medicamento #{index + 1}</h4>
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        onClick={() => removerMedicamento(index)}
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </Button>
                                </div>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                {/* Selección de Medicamento */}
                                <div>
                                    <Label htmlFor={`medicamento_${index}`}>Medicamento *</Label>
                                    <select
                                        id={`medicamento_${index}`}
                                        value={medicamento.medicamento_id}
                                        onChange={(e) => actualizarMedicamento(index, 'medicamento_id', e.target.value)}
                                        className="w-full px-3 py-2 border rounded-md"
                                        required
                                    >
                                        <option value="">Seleccionar medicamento</option>
                                        {medicamentos.map((med) => (
                                            <option key={med.id} value={med.id}>
                                                {med.nombre} {med.concentracion}{med.unidad_concentracion}
                                                {med.forma_farmaceutica && ` - ${med.forma_farmaceutica}`}
                                            </option>
                                        ))}
                                    </select>
                                    {errors[`medicamentos.${index}.medicamento_id`] && (
                                        <p className="text-red-600 text-sm mt-1">
                                            {errors[`medicamentos.${index}.medicamento_id`]}
                                        </p>
                                    )}
                                </div>

                                {/* Dosis */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <Label htmlFor={`dosis_cantidad_${index}`}>Cantidad de Dosis *</Label>
                                        <Input
                                            id={`dosis_cantidad_${index}`}
                                            type="number"
                                            step="0.1"
                                            min="0.1"
                                            value={medicamento.dosis_cantidad}
                                            onChange={(e) => actualizarMedicamento(index, 'dosis_cantidad', e.target.value)}
                                            placeholder="1"
                                            required
                                        />
                                        {errors[`medicamentos.${index}.dosis_cantidad`] && (
                                            <p className="text-red-600 text-sm mt-1">
                                                {errors[`medicamentos.${index}.dosis_cantidad`]}
                                            </p>
                                        )}
                                    </div>
                                    <div>
                                        <Label htmlFor={`unidad_dosis_${index}`}>Unidad de Dosis *</Label>
                                        <select
                                            id={`unidad_dosis_${index}`}
                                            value={medicamento.unidad_dosis}
                                            onChange={(e) => actualizarMedicamento(index, 'unidad_dosis', e.target.value)}
                                            className="w-full px-3 py-2 border rounded-md"
                                            required
                                        >
                                            <option value="tableta">Tableta</option>
                                            <option value="cápsula">Cápsula</option>
                                            <option value="ml">Mililitros (ml)</option>
                                            <option value="gotas">Gotas</option>
                                            <option value="cucharada">Cucharada</option>
                                            <option value="aplicación">Aplicación</option>
                                        </select>
                                    </div>
                                </div>

                                {/* Configuración específica por tipo */}
                                <div className="bg-blue-50 p-4 rounded-md">
                                    <h5 className="font-medium text-blue-900 mb-3">Configuración Programada</h5>
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <Label htmlFor={`frecuencia_${index}`}>Frecuencia (horas) *</Label>
                                            <Input
                                                id={`frecuencia_${index}`}
                                                type="number"
                                                min="1"
                                                value={medicamento.frecuencia_horas}
                                                onChange={(e) => actualizarMedicamento(index, 'frecuencia_horas', e.target.value)}
                                                placeholder="24"
                                                required
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor={`tolerancia_antes_${index}`}>Ventana Antes (min)</Label>
                                            <Input
                                                id={`tolerancia_antes_${index}`}
                                                type="number"
                                                min="0"
                                                value={medicamento.tolerancia_antes_minutos}
                                                onChange={(e) => actualizarMedicamento(index, 'tolerancia_antes_minutos', e.target.value)}
                                                placeholder="30"
                                            />
                                        </div>
                                        <div>
                                            <Label htmlFor={`tolerancia_despues_${index}`}>Ventana Después (min)</Label>
                                            <Input
                                                id={`tolerancia_despues_${index}`}
                                                type="number"
                                                min="0"
                                                value={medicamento.tolerancia_despues_minutos}
                                                onChange={(e) => actualizarMedicamento(index, 'tolerancia_despues_minutos', e.target.value)}
                                                placeholder="60"
                                            />
                                        </div>
                                    </div>
                                </div>

                                {/* Instrucciones especiales */}
                                <div>
                                    <Label htmlFor={`instrucciones_${index}`}>Instrucciones Especiales</Label>
                                    <Textarea
                                        id={`instrucciones_${index}`}
                                        value={medicamento.instrucciones_especiales}
                                        onChange={(e) => actualizarMedicamento(index, 'instrucciones_especiales', e.target.value)}
                                        placeholder="Ej: Tomar con alimentos, en ayunas, etc."
                                        rows={2}
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    ))
                )}
            </CardContent>
        </Card>
    );
} 