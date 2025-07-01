import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Pill, Clock, Calendar, AlertCircle, CheckCircle, ArrowLeft, Info } from 'lucide-react';
import { Link } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface Medicamento {
  id: number;
  nombre: string;
  principio_activo?: string;
  presentacion?: string;
}

interface Props {
  medicamentos?: Medicamento[];
}

export default function CrearTratamiento({ medicamentos = [] }: Props) {
  const [paso, setPaso] = useState(1);
  const [medicamentoSeleccionado, setMedicamentoSeleccionado] = useState<Medicamento | null>(null);
  
  const { data, setData, post, processing, errors } = useForm({
    medicamento_id: '',
    medicamento_personalizado: '',
    dosis: '',
    unidad_dosis: 'mg',
    frecuencia: '',
    tipo_frecuencia: 'diario',
    horarios: [] as string[],
    duracion: '',
    tipo_duracion: 'dias',
    indicaciones: '',
    es_prn: false
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('mis-tratamientos.store'), {
      onSuccess: () => {
        // Redirigir a página de éxito o cronograma
      }
    });
  };

  const agregarHorario = () => {
    setData('horarios', [...data.horarios, '08:00']);
  };

  const actualizarHorario = (index: number, horario: string) => {
    const nuevosHorarios = [...data.horarios];
    nuevosHorarios[index] = horario;
    setData('horarios', nuevosHorarios);
  };

  const eliminarHorario = (index: number) => {
    const nuevosHorarios = data.horarios.filter((_, i) => i !== index);
    setData('horarios', nuevosHorarios);
  };

  const siguientePaso = () => {
    if (paso < 3) setPaso(paso + 1);
  };

  const pasoAnterior = () => {
    if (paso > 1) setPaso(paso - 1);
  };

  const renderPaso1 = () => (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Pill className="h-5 w-5 text-blue-600" />
          Paso 1: Selecciona tu medicamento
        </CardTitle>
        <CardDescription>
          Busca tu medicamento en nuestra base de datos o agrégalo manualmente
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        <div>
          <Label htmlFor="medicamento">Buscar medicamento</Label>
          <Select 
            value={data.medicamento_id} 
            onValueChange={(value) => {
              setData('medicamento_id', value);
              const med = medicamentos.find(m => m.id.toString() === value);
              setMedicamentoSeleccionado(med || null);
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
                      <div className="text-sm text-gray-500">{medicamento.principio_activo}</div>
                    )}
                  </div>
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          {errors.medicamento_id && <p className="text-red-500 text-sm mt-1">{errors.medicamento_id}</p>}
        </div>

        <div className="text-center text-gray-500">o</div>

        <div>
          <Label htmlFor="medicamento_personalizado">Medicamento no encontrado</Label>
          <Input
            id="medicamento_personalizado"
            value={data.medicamento_personalizado}
            onChange={(e) => setData('medicamento_personalizado', e.target.value)}
            placeholder="Escribe el nombre del medicamento..."
          />
          {errors.medicamento_personalizado && <p className="text-red-500 text-sm mt-1">{errors.medicamento_personalizado}</p>}
        </div>

        {medicamentoSeleccionado && (
          <Alert>
            <Info className="h-4 w-4" />
            <AlertDescription>
              Has seleccionado: <strong>{medicamentoSeleccionado.nombre}</strong>
              {medicamentoSeleccionado.presentacion && ` - ${medicamentoSeleccionado.presentacion}`}
            </AlertDescription>
          </Alert>
        )}

        <Button 
          onClick={siguientePaso} 
          className="w-full"
          disabled={!data.medicamento_id && !data.medicamento_personalizado}
        >
          Continuar
        </Button>
      </CardContent>
    </Card>
  );

  const renderPaso2 = () => (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Clock className="h-5 w-5 text-green-600" />
          Paso 2: Configura la dosis y frecuencia
        </CardTitle>
        <CardDescription>
          Define cuánto y cuándo tomar el medicamento
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="grid grid-cols-2 gap-4">
          <div>
            <Label htmlFor="dosis">Dosis</Label>
            <Input
              id="dosis"
              value={data.dosis}
              onChange={(e) => setData('dosis', e.target.value)}
              placeholder="250"
              type="number"
            />
            {errors.dosis && <p className="text-red-500 text-sm mt-1">{errors.dosis}</p>}
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

        <div className="grid grid-cols-2 gap-4">
          <div>
            <Label htmlFor="frecuencia">Frecuencia</Label>
            <Input
              id="frecuencia"
              value={data.frecuencia}
              onChange={(e) => setData('frecuencia', e.target.value)}
              placeholder="2"
              type="number"
            />
            {errors.frecuencia && <p className="text-red-500 text-sm mt-1">{errors.frecuencia}</p>}
          </div>
          <div>
            <Label htmlFor="tipo_frecuencia">Cada</Label>
            <Select value={data.tipo_frecuencia} onValueChange={(value) => setData('tipo_frecuencia', value)}>
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="diario">vez(es) al día</SelectItem>
                <SelectItem value="semanal">vez(es) a la semana</SelectItem>
                <SelectItem value="mensual">vez(es) al mes</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div>
          <Label>Horarios específicos</Label>
          <div className="space-y-2 mt-2">
            {data.horarios.map((horario, index) => (
              <div key={index} className="flex items-center gap-2">
                <Input
                  type="time"
                  value={horario}
                  onChange={(e) => actualizarHorario(index, e.target.value)}
                  className="flex-1"
                />
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  onClick={() => eliminarHorario(index)}
                >
                  Eliminar
                </Button>
              </div>
            ))}
            <Button
              type="button"
              variant="outline"
              onClick={agregarHorario}
              className="w-full"
            >
              + Agregar horario
            </Button>
          </div>
        </div>

        <div className="flex gap-2">
          <Button variant="outline" onClick={pasoAnterior} className="w-full">
            Anterior
          </Button>
          <Button onClick={siguientePaso} className="w-full">
            Continuar
          </Button>
        </div>
      </CardContent>
    </Card>
  );

  const renderPaso3 = () => (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Calendar className="h-5 w-5 text-purple-600" />
          Paso 3: Duración e indicaciones
        </CardTitle>
        <CardDescription>
          Completa la información del tratamiento
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
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
            {errors.duracion && <p className="text-red-500 text-sm mt-1">{errors.duracion}</p>}
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

        <div>
          <Label htmlFor="indicaciones">Indicaciones especiales (opcional)</Label>
          <Textarea
            id="indicaciones"
            value={data.indicaciones}
            onChange={(e) => setData('indicaciones', e.target.value)}
            placeholder="Tomar con comida, evitar alcohol, etc."
            rows={3}
          />
        </div>

        <Alert>
          <CheckCircle className="h-4 w-4" />
          <AlertDescription>
            Una vez creado el tratamiento, se generarán recordatorios automáticos según los horarios configurados.
          </AlertDescription>
        </Alert>

        <div className="flex gap-2">
          <Button variant="outline" onClick={pasoAnterior} className="w-full">
            Anterior
          </Button>
          <Button 
            onClick={handleSubmit} 
            className="w-full"
            disabled={processing}
          >
            {processing ? 'Creando...' : 'Crear Tratamiento'}
          </Button>
        </div>
      </CardContent>
    </Card>
  );

  const pasos = [
    { numero: 1, titulo: 'Medicamento', descripcion: 'Seleccionar medicamento' },
    { numero: 2, titulo: 'Dosis', descripcion: 'Configurar dosis y horarios' },
    { numero: 3, titulo: 'Detalles', descripcion: 'Duración e indicaciones' }
  ];

  return (
    <AppSidebarLayout>
      <div className="container mx-auto py-6 max-w-4xl">
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
          <h1 className="text-3xl font-bold text-gray-900">
            Registra tu primer tratamiento
          </h1>
          <p className="text-gray-600 mt-2">
            Te guiaremos paso a paso para configurar tu medicamento y recordatorios
          </p>
        </div>

        {/* Indicador de progreso */}
        <div className="mb-8">
          <div className="flex items-center justify-between">
            {pasos.map((pasoInfo, index) => {
              const estaCompleto = index + 1 < paso;
              const esActual = index + 1 === paso;
              
              return (
                <div key={pasoInfo.numero} className="flex items-center">
                  <div className={`
                    flex items-center justify-center w-10 h-10 rounded-full border-2 
                    ${estaCompleto ? 'bg-green-500 border-green-500 text-white' : 
                      esActual ? 'border-blue-500 text-blue-500' : 'border-gray-300 text-gray-300'}
                  `}>
                    {estaCompleto ? (
                      <CheckCircle className="h-5 w-5" />
                    ) : (
                      pasoInfo.numero
                    )}
                  </div>
                  <div className="ml-3 hidden md:block">
                    <div className={`text-sm font-medium ${esActual ? 'text-blue-600' : 'text-gray-500'}`}>
                      {pasoInfo.titulo}
                    </div>
                    <div className="text-xs text-gray-400">
                      {pasoInfo.descripcion}
                    </div>
                  </div>
                  {index < pasos.length - 1 && (
                    <div className={`flex-1 h-0.5 mx-4 ${estaCompleto ? 'bg-green-500' : 'bg-gray-300'}`} />
                  )}
                </div>
              );
            })}
          </div>
        </div>

        {/* Contenido del paso actual */}
        <div className="max-w-2xl mx-auto">
          {paso === 1 && renderPaso1()}
          {paso === 2 && renderPaso2()}
          {paso === 3 && renderPaso3()}
        </div>

        {/* Footer de ayuda */}
        <div className="mt-8 text-center">
          <p className="text-sm text-gray-500">
            ¿Necesitas ayuda? 
            <Link href="/ayuda" className="text-blue-600 hover:underline ml-1">
              Consulta nuestro centro de ayuda
            </Link>
          </p>
        </div>
      </div>
    </AppSidebarLayout>
  );
} 