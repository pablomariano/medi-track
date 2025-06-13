import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Save, Pill, AlertTriangle } from 'lucide-react';

interface Medicamento {
  id: number;
  nombre: string;
  principio_activo: string;
  concentracion: string;
  unidad_concentracion: string;
  forma_farmaceutica: string;
  via_administracion: string;
  presentacion: string;
  unidades_por_presentacion: number;
  requiere_receta: boolean;
  categoria_terapeutica?: string;
  laboratorio?: string;
  codigo_barras?: string;
  registro_sanitario?: string;
  contraindicaciones?: string;
  efectos_secundarios?: string;
  interacciones?: string;
  activo: boolean;
}

interface Props {
  medicamento: Medicamento;
}

export default function Edit({ medicamento }: Props) {
  const { data, setData, patch, processing, errors } = useForm({
    nombre: medicamento.nombre || '',
    principio_activo: medicamento.principio_activo || '',
    concentracion: medicamento.concentracion || '',
    unidad_concentracion: medicamento.unidad_concentracion || '',
    forma_farmaceutica: medicamento.forma_farmaceutica || '',
    via_administracion: medicamento.via_administracion || '',
    presentacion: medicamento.presentacion || '',
    unidades_por_presentacion: medicamento.unidades_por_presentacion || 1,
    requiere_receta: medicamento.requiere_receta || false,
    categoria_terapeutica: medicamento.categoria_terapeutica || '',
    laboratorio: medicamento.laboratorio || '',
    codigo_barras: medicamento.codigo_barras || '',
    registro_sanitario: medicamento.registro_sanitario || '',
    contraindicaciones: medicamento.contraindicaciones || '',
    efectos_secundarios: medicamento.efectos_secundarios || '',
    interacciones: medicamento.interacciones || '',
    activo: medicamento.activo
  });

  const submit = (e: React.FormEvent) => {
    e.preventDefault();
    patch(route('medicamentos.update', medicamento.id));
  };

  return (
    <AppLayout>
      <Head title={`Editar ${medicamento.nombre}`} />

      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <Link href={route('medicamentos.show', medicamento.id)}>
              <Button variant="outline" size="sm">
                <ArrowLeft className="h-4 w-4 mr-2" />
                Volver a detalles
              </Button>
            </Link>
            <div className="flex items-center space-x-2">
              <Pill className="h-6 w-6 text-blue-600" />
              <div>
                <h1 className="text-3xl font-bold text-foreground">Editar Medicamento</h1>
                <p className="text-muted-foreground">{medicamento.nombre}</p>
              </div>
            </div>
          </div>
        </div>

        {/* Formulario */}
        <form onSubmit={submit} className="space-y-6">
          {/* Información Básica */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center space-x-2">
                <Pill className="h-5 w-5" />
                <span>Información Básica</span>
              </CardTitle>
              <CardDescription>
                Datos principales del medicamento
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium mb-2">Nombre *</label>
                  <Input
                    value={data.nombre}
                    onChange={(e) => setData('nombre', e.target.value)}
                    className={errors.nombre ? 'border-destructive' : ''}
                    placeholder="Nombre del medicamento"
                  />
                  {errors.nombre && <p className="text-sm text-destructive mt-1">{errors.nombre}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Principio Activo *</label>
                  <Input
                    value={data.principio_activo}
                    onChange={(e) => setData('principio_activo', e.target.value)}
                    className={errors.principio_activo ? 'border-destructive' : ''}
                    placeholder="Componente activo principal"
                  />
                  {errors.principio_activo && <p className="text-sm text-destructive mt-1">{errors.principio_activo}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Concentración *</label>
                  <Input
                    value={data.concentracion}
                    onChange={(e) => setData('concentracion', e.target.value)}
                    className={errors.concentracion ? 'border-destructive' : ''}
                    placeholder="500"
                  />
                  {errors.concentracion && <p className="text-sm text-destructive mt-1">{errors.concentracion}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Unidad de Concentración *</label>
                  <Input
                    value={data.unidad_concentracion}
                    onChange={(e) => setData('unidad_concentracion', e.target.value)}
                    className={errors.unidad_concentracion ? 'border-destructive' : ''}
                    placeholder="mg, g, ml, UI, etc."
                  />
                  {errors.unidad_concentracion && <p className="text-sm text-destructive mt-1">{errors.unidad_concentracion}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Forma Farmacéutica *</label>
                  <Input
                    value={data.forma_farmaceutica}
                    onChange={(e) => setData('forma_farmaceutica', e.target.value)}
                    className={errors.forma_farmaceutica ? 'border-destructive' : ''}
                    placeholder="Tableta, Cápsula, Jarabe, Inyección, etc."
                  />
                  {errors.forma_farmaceutica && <p className="text-sm text-destructive mt-1">{errors.forma_farmaceutica}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Vía de Administración *</label>
                  <Input
                    value={data.via_administracion}
                    onChange={(e) => setData('via_administracion', e.target.value)}
                    className={errors.via_administracion ? 'border-destructive' : ''}
                    placeholder="Oral, Intravenosa, Intramuscular, etc."
                  />
                  {errors.via_administracion && <p className="text-sm text-destructive mt-1">{errors.via_administracion}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Presentación *</label>
                  <Input
                    value={data.presentacion}
                    onChange={(e) => setData('presentacion', e.target.value)}
                    className={errors.presentacion ? 'border-destructive' : ''}
                    placeholder="Caja, Frasco, Ampolla, etc."
                  />
                  {errors.presentacion && <p className="text-sm text-destructive mt-1">{errors.presentacion}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Unidades por Presentación *</label>
                  <Input
                    type="number"
                    min="1"
                    value={data.unidades_por_presentacion}
                    onChange={(e) => setData('unidades_por_presentacion', parseInt(e.target.value) || 1)}
                    className={errors.unidades_por_presentacion ? 'border-destructive' : ''}
                    placeholder="30"
                  />
                  {errors.unidades_por_presentacion && <p className="text-sm text-destructive mt-1">{errors.unidades_por_presentacion}</p>}
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium mb-2">Categoría Terapéutica</label>
                  <Input
                    value={data.categoria_terapeutica}
                    onChange={(e) => setData('categoria_terapeutica', e.target.value)}
                    className={errors.categoria_terapeutica ? 'border-destructive' : ''}
                    placeholder="Analgésico, Antibiótico, Antihipertensivo, etc."
                  />
                  {errors.categoria_terapeutica && <p className="text-sm text-destructive mt-1">{errors.categoria_terapeutica}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Laboratorio</label>
                  <Input
                    value={data.laboratorio}
                    onChange={(e) => setData('laboratorio', e.target.value)}
                    className={errors.laboratorio ? 'border-destructive' : ''}
                    placeholder="Nombre del laboratorio fabricante"
                  />
                  {errors.laboratorio && <p className="text-sm text-destructive mt-1">{errors.laboratorio}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Código de Barras</label>
                  <Input
                    value={data.codigo_barras}
                    onChange={(e) => setData('codigo_barras', e.target.value)}
                    className={errors.codigo_barras ? 'border-destructive' : ''}
                    placeholder="123456789012"
                  />
                  {errors.codigo_barras && <p className="text-sm text-destructive mt-1">{errors.codigo_barras}</p>}
                </div>

                <div>
                  <label className="block text-sm font-medium mb-2">Registro Sanitario</label>
                  <Input
                    value={data.registro_sanitario}
                    onChange={(e) => setData('registro_sanitario', e.target.value)}
                    className={errors.registro_sanitario ? 'border-destructive' : ''}
                    placeholder="Número de registro sanitario"
                  />
                  {errors.registro_sanitario && <p className="text-sm text-destructive mt-1">{errors.registro_sanitario}</p>}
                </div>
              </div>

              <div className="flex items-center space-x-6 pt-2">
                <div className="flex items-center space-x-2">
                  <Checkbox
                    id="requiere_receta"
                    checked={data.requiere_receta}
                    onCheckedChange={(checked) => setData('requiere_receta', checked as boolean)}
                  />
                  <label htmlFor="requiere_receta" className="text-sm font-medium">
                    Requiere receta médica
                  </label>
                </div>

                <div className="flex items-center space-x-2">
                  <Checkbox
                    id="activo"
                    checked={data.activo}
                    onCheckedChange={(checked) => setData('activo', checked as boolean)}
                  />
                  <label htmlFor="activo" className="text-sm font-medium">
                    Medicamento activo
                  </label>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Información Médica */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center space-x-2">
                <AlertTriangle className="h-5 w-5 text-orange-500" />
                <span>Información Médica</span>
              </CardTitle>
              <CardDescription>
                Datos clínicos importantes para el uso seguro del medicamento
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <label className="block text-sm font-medium mb-2">Contraindicaciones</label>
                <Textarea
                  value={data.contraindicaciones}
                  onChange={(e) => setData('contraindicaciones', e.target.value)}
                  className={errors.contraindicaciones ? 'border-destructive' : ''}
                  placeholder="Situaciones o condiciones en las que no se debe administrar el medicamento..."
                  rows={3}
                />
                {errors.contraindicaciones && <p className="text-sm text-destructive mt-1">{errors.contraindicaciones}</p>}
              </div>

              <div>
                <label className="block text-sm font-medium mb-2">Efectos Secundarios</label>
                <Textarea
                  value={data.efectos_secundarios}
                  onChange={(e) => setData('efectos_secundarios', e.target.value)}
                  className={errors.efectos_secundarios ? 'border-destructive' : ''}
                  placeholder="Reacciones adversas conocidas del medicamento..."
                  rows={3}
                />
                {errors.efectos_secundarios && <p className="text-sm text-destructive mt-1">{errors.efectos_secundarios}</p>}
              </div>

              <div>
                <label className="block text-sm font-medium mb-2">Interacciones</label>
                <Textarea
                  value={data.interacciones}
                  onChange={(e) => setData('interacciones', e.target.value)}
                  className={errors.interacciones ? 'border-destructive' : ''}
                  placeholder="Interacciones con otros medicamentos, alimentos o sustancias..."
                  rows={3}
                />
                {errors.interacciones && <p className="text-sm text-destructive mt-1">{errors.interacciones}</p>}
              </div>
            </CardContent>
          </Card>

          {/* Botones de Acción */}
          <div className="flex justify-end space-x-4 pt-4">
            <Link href={route('medicamentos.show', medicamento.id)}>
              <Button type="button" variant="outline">
                Cancelar
              </Button>
            </Link>
            <Button type="submit" disabled={processing}>
              <Save className="h-4 w-4 mr-2" />
              {processing ? 'Guardando...' : 'Guardar Cambios'}
            </Button>
          </div>
        </form>
      </div>
    </AppLayout>
  );
} 