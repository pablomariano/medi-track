import React from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pill, ArrowLeft } from 'lucide-react';
import { Link } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';

interface Medicamento {
  id: number;
  nombre: string;
  principio_activo?: string;
  forma_farmaceutica?: string;
}

interface Props {
  medicamentos?: Medicamento[];
}

export default function CrearTratamiento({ medicamentos = [] }: Props) {
  const { data, setData, post, processing, errors } = useForm({
    medicamento_id: '',
    dosis: '',
    unidad_dosis: 'mg',
    frecuencia: '1',
    tipo_frecuencia: 'diario',
    horario_principal: '08:00',
    duracion: '',
    tipo_duracion: 'dias',
    indicaciones: '',
    es_prn: false
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    post(route('mis-tratamientos.store'), {
      onSuccess: () => {
        // Redirigir automáticamente a la página de éxito
      },
      onError: (errors) => {
        console.error('Error al crear tratamiento:', errors);
      }
    });
  };

  // Función para validar y manejar cambios en campos numéricos
  const handleNumericChange = (field: string, value: string) => {
    const numericValue = parseFloat(value);
    if (value === '' || (!isNaN(numericValue) && numericValue >= 0)) {
      setData(field as any, value);
    }
  };

  return (
    <AppSidebarLayout>
      <div className="container mx-auto py-6 max-w-2xl">
        {/* Header */}
        <div className="mb-6">
          <div className="flex items-center gap-4 mb-4">
            <Link href="/bienvenida">
              <Button variant="outline" size="sm">
                <ArrowLeft className="h-4 w-4 mr-2" />
                Volver a Bienvenida
              </Button>
            </Link>
            <Badge variant="secondary">Usuario Nuevo</Badge>
          </div>
          <h1 className="text-2xl font-bold">
            Registra tu primer tratamiento
          </h1>
          <p className="text-muted-foreground mt-2">
            Completa el formulario con la información básica de tu medicamento
          </p>
        </div>

        {/* Formulario simplificado */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Pill className="h-5 w-5 text-primary" />
              Información del tratamiento
            </CardTitle>
            <CardDescription>
              Selecciona tu medicamento del listado y completa la información
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-6">
              {/* Medicamento - Solo selección desde listado */}
              <div>
                <Label htmlFor="medicamento" className="text-base font-medium">
                  Medicamento <span className="text-destructive">*</span>
                </Label>
                <Select 
                  value={data.medicamento_id} 
                  onValueChange={(value) => setData('medicamento_id', value)}
                  required
                >
                  <SelectTrigger className="mt-2">
                    <SelectValue placeholder="Selecciona tu medicamento..." />
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
                <p className="text-xs text-muted-foreground mt-1">
                  ¿No encuentras tu medicamento? Contacta a tu médico para agregarlo al sistema
                </p>
              </div>

              {/* Dosis */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="dosis" className="text-base font-medium">
                    Dosis <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    id="dosis"
                    value={data.dosis}
                    onChange={(e) => handleNumericChange('dosis', e.target.value)}
                    placeholder="250"
                    type="number"
                    min="0"
                    step="any"
                    className="mt-2"
                    required
                  />
                  {errors.dosis && <p className="text-sm text-destructive mt-1">{errors.dosis}</p>}
                </div>
                <div>
                  <Label htmlFor="unidad_dosis" className="text-base font-medium">
                    Unidad <span className="text-destructive">*</span>
                  </Label>
                  <Select value={data.unidad_dosis} onValueChange={(value) => setData('unidad_dosis', value)}>
                    <SelectTrigger className="mt-2">
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
                  <Label htmlFor="frecuencia" className="text-base font-medium">
                    Frecuencia <span className="text-destructive">*</span>
                  </Label>
                  <Select value={data.frecuencia} onValueChange={(value) => setData('frecuencia', value)}>
                    <SelectTrigger className="mt-2">
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
                  <Label htmlFor="horario_principal" className="text-base font-medium">
                    Horario principal <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    id="horario_principal"
                    type="time"
                    value={data.horario_principal}
                    onChange={(e) => setData('horario_principal', e.target.value)}
                    className="mt-2"
                    required
                  />
                  {errors.horario_principal && <p className="text-sm text-destructive mt-1">{errors.horario_principal}</p>}
                </div>
              </div>

              {/* Duración */}
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="duracion" className="text-base font-medium">
                    Duración <span className="text-destructive">*</span>
                  </Label>
                  <Input
                    id="duracion"
                    value={data.duracion}
                    onChange={(e) => handleNumericChange('duracion', e.target.value)}
                    placeholder="30"
                    type="number"
                    min="1"
                    step="1"
                    className="mt-2"
                    required
                  />
                  {errors.duracion && <p className="text-sm text-destructive mt-1">{errors.duracion}</p>}
                </div>
                <div>
                  <Label htmlFor="tipo_duracion" className="text-base font-medium">
                    Periodo <span className="text-destructive">*</span>
                  </Label>
                  <Select value={data.tipo_duracion} onValueChange={(value) => setData('tipo_duracion', value)}>
                    <SelectTrigger className="mt-2">
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

              {/* Indicaciones opcionales */}
              <div>
                <Label htmlFor="indicaciones" className="text-base font-medium">
                  Indicaciones especiales (opcional)
                </Label>
                <Textarea
                  id="indicaciones"
                  value={data.indicaciones}
                  onChange={(e) => setData('indicaciones', e.target.value)}
                  placeholder="Ej: Tomar con comida, evitar alcohol, etc."
                  rows={3}
                  className="mt-2"
                />
                {errors.indicaciones && <p className="text-sm text-destructive mt-1">{errors.indicaciones}</p>}
              </div>

              {/* Botón de envío */}
              <Button 
                type="submit" 
                className="w-full"
                disabled={processing || !data.medicamento_id || !data.dosis || !data.duracion}
              >
                {processing ? 'Creando tratamiento...' : 'Crear Tratamiento'}
              </Button>
            </form>
          </CardContent>
        </Card>

        {/* Footer de ayuda */}
        <div className="mt-6 text-center">
          <p className="text-sm text-muted-foreground">
            ¿Necesitas ayuda? 
            <Link href="/ayuda" className="text-primary hover:underline ml-1">
              Consulta nuestro centro de ayuda
            </Link>
          </p>
        </div>
      </div>
    </AppSidebarLayout>
  );
} 