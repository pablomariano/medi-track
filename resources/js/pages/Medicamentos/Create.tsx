import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, Save, Pill } from 'lucide-react';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        nombre: '',
        principio_activo: '',
        medida: '', // Usar medida en lugar de concentracion
        unidad_medida: '', // Usar unidad_medida en lugar de unidad_concentracion
        forma_farmaceutica: '',
        via_administracion: '',
        presentacion: '',
        unidades_por_presentacion: '',
        laboratorio: '',
        categoria_terapeutica: '',
        requiere_receta: false as boolean,
        contraindicaciones: '',
        efectos_secundarios: '',
        interacciones: '',
        codigo_barras: '',
        registro_sanitario: '',
        descripcion: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('medicamentos.store'));
    };

    return (
        <AppLayout>
            <Head title="Crear Medicamento" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link href={route('medicamentos.index')}>
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="h-4 w-4 mr-2" />
                                Volver
                            </Button>
                        </Link>
                        <div className="flex items-center space-x-2">
                            <Pill className="h-6 w-6 text-blue-600" />
                            <h1 className="text-2xl font-bold text-gray-900">Crear Medicamento</h1>
                        </div>
                    </div>
                </div>

                {/* Formulario */}
                <form onSubmit={submit}>
                    <div className="space-y-6">
                        {/* Información Básica */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Información Básica</CardTitle>
                                <CardDescription>
                                    Datos principales del medicamento
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium mb-1">Nombre Comercial *</label>
                                        <Input
                                            value={data.nombre}
                                            onChange={(e) => setData('nombre', e.target.value)}
                                            className={errors.nombre ? 'border-red-500' : ''}
                                            placeholder="Ej. Aspirina"
                                        />
                                        {errors.nombre && <p className="text-sm text-red-600 mt-1">{errors.nombre}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium mb-1">Principio Activo *</label>
                                        <Input
                                            value={data.principio_activo}
                                            onChange={(e) => setData('principio_activo', e.target.value)}
                                            className={errors.principio_activo ? 'border-red-500' : ''}
                                            placeholder="Ej. Ácido acetilsalicílico"
                                        />
                                        {errors.principio_activo && <p className="text-sm text-red-600 mt-1">{errors.principio_activo}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium mb-1">Concentración *</label>
                                        <Input
                                            value={data.medida}
                                            onChange={(e) => setData('medida', e.target.value)}
                                            className={errors.medida ? 'border-red-500' : ''}
                                            placeholder="Ej. 500"
                                        />
                                        {errors.medida && <p className="text-sm text-red-600 mt-1">{errors.medida}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium mb-1">Unidad *</label>
                                        <Input
                                            placeholder="mg, g, ml, etc."
                                            value={data.unidad_medida}
                                            onChange={(e) => setData('unidad_medida', e.target.value)}
                                            className={errors.unidad_medida ? 'border-red-500' : ''}
                                        />
                                        {errors.unidad_medida && <p className="text-sm text-red-600 mt-1">{errors.unidad_medida}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium mb-1">Forma Farmacéutica *</label>
                                        <Input
                                            placeholder="Tableta, Cápsula, Jarabe, etc."
                                            value={data.forma_farmaceutica}
                                            onChange={(e) => setData('forma_farmaceutica', e.target.value)}
                                            className={errors.forma_farmaceutica ? 'border-red-500' : ''}
                                        />
                                        {errors.forma_farmaceutica && <p className="text-sm text-red-600 mt-1">{errors.forma_farmaceutica}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium mb-1">Vía de Administración *</label>
                                        <Input
                                            placeholder="Oral, Intravenosa, Tópica, etc."
                                            value={data.via_administracion}
                                            onChange={(e) => setData('via_administracion', e.target.value)}
                                            className={errors.via_administracion ? 'border-red-500' : ''}
                                        />
                                        {errors.via_administracion && <p className="text-sm text-red-600 mt-1">{errors.via_administracion}</p>}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Información Comercial */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Información Comercial</CardTitle>
                                <CardDescription>
                                    Presentación y datos del laboratorio
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium mb-1">Presentación *</label>
                                        <Input
                                            placeholder="Caja con 30 tabletas"
                                            value={data.presentacion}
                                            onChange={(e) => setData('presentacion', e.target.value)}
                                            className={errors.presentacion ? 'border-red-500' : ''}
                                        />
                                        {errors.presentacion && <p className="text-sm text-red-600 mt-1">{errors.presentacion}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium mb-1">Unidades por Presentación *</label>
                                        <Input
                                            type="number"
                                            min="1"
                                            placeholder="30"
                                            value={data.unidades_por_presentacion}
                                            onChange={(e) => setData('unidades_por_presentacion', e.target.value)}
                                            className={errors.unidades_por_presentacion ? 'border-red-500' : ''}
                                        />
                                        {errors.unidades_por_presentacion && <p className="text-sm text-red-600 mt-1">{errors.unidades_por_presentacion}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium mb-1">Laboratorio</label>
                                        <Input
                                            placeholder="Nombre del laboratorio"
                                            value={data.laboratorio}
                                            onChange={(e) => setData('laboratorio', e.target.value)}
                                            className={errors.laboratorio ? 'border-red-500' : ''}
                                        />
                                        {errors.laboratorio && <p className="text-sm text-red-600 mt-1">{errors.laboratorio}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium mb-1">Categoría Terapéutica</label>
                                        <Input
                                            placeholder="Analgésico, Antibiótico, etc."
                                            value={data.categoria_terapeutica}
                                            onChange={(e) => setData('categoria_terapeutica', e.target.value)}
                                            className={errors.categoria_terapeutica ? 'border-red-500' : ''}
                                        />
                                        {errors.categoria_terapeutica && <p className="text-sm text-red-600 mt-1">{errors.categoria_terapeutica}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium mb-1">Código de Barras</label>
                                        <Input
                                            placeholder="Código de barras del producto"
                                            value={data.codigo_barras}
                                            onChange={(e) => setData('codigo_barras', e.target.value)}
                                            className={errors.codigo_barras ? 'border-red-500' : ''}
                                        />
                                        {errors.codigo_barras && <p className="text-sm text-red-600 mt-1">{errors.codigo_barras}</p>}
                                    </div>

                                    <div>
                                        <label className="block text-sm font-medium mb-1">Registro Sanitario</label>
                                        <Input
                                            placeholder="Número de registro sanitario"
                                            value={data.registro_sanitario}
                                            onChange={(e) => setData('registro_sanitario', e.target.value)}
                                            className={errors.registro_sanitario ? 'border-red-500' : ''}
                                        />
                                        {errors.registro_sanitario && <p className="text-sm text-red-600 mt-1">{errors.registro_sanitario}</p>}
                                    </div>
                                </div>

                                <div className="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        id="requiere_receta"
                                        checked={data.requiere_receta}
                                        onChange={(e) => setData('requiere_receta', e.target.checked as any)}
                                        className="rounded border-gray-300"
                                    />
                                    <label htmlFor="requiere_receta" className="text-sm">
                                        Requiere receta médica
                                    </label>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Información Médica */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Información Médica</CardTitle>
                                <CardDescription>
                                    Contraindicaciones, efectos secundarios e interacciones
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium mb-1">Descripción</label>
                                    <Textarea
                                        placeholder="Descripción general del medicamento"
                                        value={data.descripcion}
                                        onChange={(e) => setData('descripcion', e.target.value)}
                                        className={errors.descripcion ? 'border-red-500' : ''}
                                        rows={3}
                                    />
                                    {errors.descripcion && <p className="text-sm text-red-600 mt-1">{errors.descripcion}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium mb-1">Contraindicaciones</label>
                                    <Textarea
                                        placeholder="Situaciones en las que no debe usarse el medicamento"
                                        value={data.contraindicaciones}
                                        onChange={(e) => setData('contraindicaciones', e.target.value)}
                                        className={errors.contraindicaciones ? 'border-red-500' : ''}
                                        rows={3}
                                    />
                                    {errors.contraindicaciones && <p className="text-sm text-red-600 mt-1">{errors.contraindicaciones}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium mb-1">Efectos Secundarios</label>
                                    <Textarea
                                        placeholder="Posibles efectos adversos del medicamento"
                                        value={data.efectos_secundarios}
                                        onChange={(e) => setData('efectos_secundarios', e.target.value)}
                                        className={errors.efectos_secundarios ? 'border-red-500' : ''}
                                        rows={3}
                                    />
                                    {errors.efectos_secundarios && <p className="text-sm text-red-600 mt-1">{errors.efectos_secundarios}</p>}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium mb-1">Interacciones</label>
                                    <Textarea
                                        placeholder="Interacciones con otros medicamentos o alimentos"
                                        value={data.interacciones}
                                        onChange={(e) => setData('interacciones', e.target.value)}
                                        className={errors.interacciones ? 'border-red-500' : ''}
                                        rows={3}
                                    />
                                    {errors.interacciones && <p className="text-sm text-red-600 mt-1">{errors.interacciones}</p>}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Botones */}
                        <Card>
                            <CardContent className="pt-6">
                                <div className="flex justify-end space-x-4">
                                    <Link href={route('medicamentos.index')}>
                                        <Button type="button" variant="outline">
                                            Cancelar
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        <Save className="h-4 w-4 mr-2" />
                                        {processing ? 'Guardando...' : 'Guardar Medicamento'}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
} 