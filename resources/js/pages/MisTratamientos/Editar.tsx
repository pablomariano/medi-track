import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Pill, ArrowLeft, Trash2 } from 'lucide-react';
import { Link } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';

interface Medicamento {
  id: number;
  nombre: string;
  principio_activo?: string;
  forma_farmaceutica?: string;
}

interface TratamientoMedicamento {
  id: number;
  nombre: string;
  pivot: {
    dosis_cantidad: number;
    unidad_dosis: string;
    frecuencia_horas: number;
    instrucciones_especiales?: string;
  };
}

interface Tratamiento {
  id: number;
  nombre: string;
  observaciones?: string;
  fecha_fin?: string;
  medicamentos: TratamientoMedicamento[];
}

interface Props {
  tratamiento: Tratamiento;
  medicamentos?: Medicamento[];
}

export default function EditarTratamiento({ tratamiento, medicamentos = [] }: Props) {
  // Estado para el dialog de confirmación de eliminación
  const [showDeleteDialog, setShowDeleteDialog] = useState(false);
  const [isDeleting, setIsDeleting] = useState(false);

  // Obtener datos del primer medicamento del tratamiento (simplificado)
  const primerMedicamento = tratamiento.medicamentos[0];
  const pivot = primerMedicamento?.pivot;
  
  // Convertir frecuencia en horas a veces al día
  const frecuenciaVecesPorDia = pivot?.frecuencia_horas ? Math.round(24 / pivot.frecuencia_horas) : 1;

  const { data, setData, put, processing, errors } = useForm({
    nombre: tratamiento.nombre || '',
    medicamento_id: primerMedicamento?.id?.toString() || '',
    medicamento_personalizado: '',
    dosis: pivot?.dosis_cantidad?.toString() || '',
    unidad_dosis: pivot?.unidad_dosis || 'mg',
    frecuencia: frecuenciaVecesPorDia.toString(),
    tipo_frecuencia: 'diario',
    horario_principal: '08:00',
    duracion: '30',
    tipo_duracion: tratamiento.fecha_fin ? 'dias' : 'indefinido',
    indicaciones: tratamiento.observaciones || '',
    fecha_fin: tratamiento.fecha_fin || ''
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    put(route('mis-tratamientos.update', tratamiento.id), {
      onSuccess: () => {
        // Redirigir automáticamente a la página de tratamientos
      },
      onError: (errors) => {
        console.error('Error al actualizar tratamiento:', errors);
      }
    });
  };

  const handleDelete = () => {
    setIsDeleting(true);
    
    router.delete(route('mis-tratamientos.destroy', tratamiento.id), {
      onSuccess: () => {
        // Redirigir automáticamente a la página de tratamientos
      },
      onError: (errors) => {
        console.error('Error al eliminar tratamiento:', errors);
        setIsDeleting(false);
        setShowDeleteDialog(false);
      },
      onFinish: () => {
        setIsDeleting(false);
        setShowDeleteDialog(false);
      }
    });
  };

  return (
    <AppSidebarLayout>
      <div className="container mx-auto py-6 max-w-2xl">
        {/* Header */}
        <div className="mb-6">
          <div className="flex items-center gap-4 mb-4">
            <Link href={route('mis-tratamientos.index')}>
              <Button variant="outline" size="sm">
                <ArrowLeft className="h-4 w-4 mr-2" />
                Volver a Mis Tratamientos
              </Button>
            </Link>
          </div>
          <h1 className="text-2xl font-bold">
            Editar tratamiento
          </h1>
          <p className="text-muted-foreground mt-2">
            Actualiza la información de tu tratamiento médico
          </p>
        </div>

        {/* Formulario de edición */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Pill className="h-5 w-5 text-primary" />
              Información del tratamiento
            </CardTitle>
            <CardDescription>
              Modifica la información de tu medicamento y horarios
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Nombre del tratamiento */}
              <div>
                <Label htmlFor="nombre">Nombre del tratamiento</Label>
                <Input
                  id="nombre"
                  value={data.nombre}
                  onChange={(e) => setData('nombre', e.target.value)}
                  placeholder="Ej: Medicamentos matutinos"
                />
                {errors.nombre && <p className="text-sm text-destructive mt-1">{errors.nombre}</p>}
              </div>

              {/* Medicamento */}
              <div className="space-y-4">
                <div>
                  <Label htmlFor="medicamento">Medicamento actual</Label>
                  <Select 
                    value={data.medicamento_id} 
                    onValueChange={(value) => {
                      setData('medicamento_id', value);
                      // Limpiar el campo personalizado si se selecciona uno de la BD
                      if (value) setData('medicamento_personalizado', '');
                    }}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Busca tu medicamento..." />
                    </SelectTrigger>
                    <SelectContent>
                      {medicamentos.map((medicamento) => (
                        <SelectItem key={medicamento.id} value={medicamento.id.toString()}>
                          <div>
                            <div className="font-medium">{medicamento.nombre}</div>
                            {medicamento.principio_activo && (
                              <div className="text-sm text-muted-foreground">{medicamento.principio_activo}</div>
                            )}
                            {medicamento.forma_farmaceutica && (
                              <div className="text-xs text-muted-foreground">{medicamento.forma_farmaceutica}</div>
                            )}
                          </div>
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  {errors.medicamento_id && <p className="text-sm text-destructive mt-1">{errors.medicamento_id}</p>}
                </div>

                <div className="text-center text-muted-foreground text-sm">o cambia a uno nuevo</div>

                <div>
                  <Label htmlFor="medicamento_personalizado">Nuevo medicamento</Label>
                  <Input
                    id="medicamento_personalizado"
                    value={data.medicamento_personalizado}
                    onChange={(e) => {
                      setData('medicamento_personalizado', e.target.value);
                      // Limpiar la selección si se escribe algo
                      if (e.target.value) setData('medicamento_id', '');
                    }}
                    placeholder="Escribe el nombre del nuevo medicamento..."
                  />
                  {errors.medicamento_personalizado && <p className="text-sm text-destructive mt-1">{errors.medicamento_personalizado}</p>}
                </div>
              </div>

              {/* Dosis */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="dosis">Dosis</Label>
                  <Input
                    id="dosis"
                    value={data.dosis}
                    onChange={(e) => setData('dosis', e.target.value)}
                    placeholder="250"
                    type="number"
                    required
                  />
                  {errors.dosis && <p className="text-sm text-destructive mt-1">{errors.dosis}</p>}
                </div>
                <div>
                  <Label htmlFor="unidad_dosis">Unidad</Label>
                  <Select value={data.unidad_dosis} onValueChange={(value) => setData('unidad_dosis', value)}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="mg">mg (miligramos)</SelectItem>
                      <SelectItem value="g">g (gramos)</SelectItem>
                      <SelectItem value="ml">ml (mililitros)</SelectItem>
                      <SelectItem value="tabletas">tabletas</SelectItem>
                      <SelectItem value="capsulas">cápsulas</SelectItem>
                      <SelectItem value="gotas">gotas</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              {/* Frecuencia y horario */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="frecuencia">Veces al día</Label>
                  <Select value={data.frecuencia} onValueChange={(value) => setData('frecuencia', value)}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="1">1 vez al día</SelectItem>
                      <SelectItem value="2">2 veces al día</SelectItem>
                      <SelectItem value="3">3 veces al día</SelectItem>
                      <SelectItem value="4">4 veces al día</SelectItem>
                    </SelectContent>
                  </Select>
                  {errors.frecuencia && <p className="text-sm text-destructive mt-1">{errors.frecuencia}</p>}
                </div>
                <div>
                  <Label htmlFor="horario_principal">Horario principal</Label>
                  <Input
                    id="horario_principal"
                    type="time"
                    value={data.horario_principal}
                    onChange={(e) => setData('horario_principal', e.target.value)}
                    required
                  />
                </div>
              </div>

              {/* Duración */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="duracion">Duración</Label>
                  <Input
                    id="duracion"
                    value={data.duracion}
                    onChange={(e) => setData('duracion', e.target.value)}
                    placeholder="30"
                    type="number"
                  />
                  {errors.duracion && <p className="text-sm text-destructive mt-1">{errors.duracion}</p>}
                </div>
                <div>
                  <Label htmlFor="tipo_duracion">Unidad</Label>
                  <Select value={data.tipo_duracion} onValueChange={(value) => setData('tipo_duracion', value)}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="dias">días</SelectItem>
                      <SelectItem value="semanas">semanas</SelectItem>
                      <SelectItem value="meses">meses</SelectItem>
                      <SelectItem value="indefinido">Tratamiento indefinido</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              {/* Indicaciones */}
              <div>
                <Label htmlFor="indicaciones">Indicaciones especiales</Label>
                <Textarea
                  id="indicaciones"
                  value={data.indicaciones}
                  onChange={(e) => setData('indicaciones', e.target.value)}
                  placeholder="Ej: Tomar con comida, evitar alcohol, etc."
                  rows={3}
                />
              </div>

              {/* Botones de acción */}
              <div className="flex gap-4">
                <Link href={route('mis-tratamientos.index')} className="flex-1">
                  <Button type="button" variant="outline" className="w-full">
                    Cancelar
                  </Button>
                </Link>
                
                {/* Botón de eliminar con confirmación */}
                <Dialog open={showDeleteDialog} onOpenChange={setShowDeleteDialog}>
                  <DialogTrigger asChild>
                    <Button type="button" variant="destructive" size="default">
                      <Trash2 className="h-4 w-4 mr-2" />
                      Eliminar
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>¿Eliminar tratamiento?</DialogTitle>
                      <DialogDescription>
                        Esta acción eliminará permanentemente el tratamiento "{tratamiento.nombre || 'Sin nombre'}" 
                        y todos sus recordatorios programados. Esta acción no se puede deshacer.
                      </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                      <Button 
                        variant="outline" 
                        onClick={() => setShowDeleteDialog(false)}
                        disabled={isDeleting}
                      >
                        Cancelar
                      </Button>
                      <Button 
                        variant="destructive" 
                        onClick={handleDelete}
                        disabled={isDeleting}
                      >
                        {isDeleting ? (
                          <>
                            <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                            Eliminando...
                          </>
                        ) : (
                          <>
                            <Trash2 className="h-4 w-4 mr-2" />
                            Eliminar Tratamiento
                          </>
                        )}
                      </Button>
                    </DialogFooter>
                  </DialogContent>
                </Dialog>

                <Button 
                  type="submit" 
                  className="flex-1"
                  disabled={processing || !data.dosis}
                >
                  {processing ? 'Actualizando...' : 'Actualizar Tratamiento'}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </AppSidebarLayout>
  );
} 