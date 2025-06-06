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
import { Checkbox } from '@/components/ui/checkbox';
import { 
    ArrowLeft, 
    Save, 
    Package,
    Pill,
    Calendar,
    DollarSign,
    BarChart3
} from 'lucide-react';

interface PrincipioActivo {
    id: number;
    nombre_generico: string;
    grupo_farmacologico: string;
}

interface FormaFarmaceutica {
    id: number;
    nombre: string;
}

interface ViaAdministracion {
    id: number;
    nombre: string;
}

interface UnidadMedida {
    id: number;
    nombre: string;
    simbolo: string;
    tipo: string;
}

interface FormData extends Record<string, any> {
    nombre_comercial: string;
    principio_activo_id: string;
    forma_farmaceutica_id: string;
    via_administracion_id: string;
    unidad_concentracion_id: string;
    concentracion: string;
    codigo_barras: string;
    lote: string;
    fecha_vencimiento: string;
    precio_unitario: string;
    stock_actual: string;
    stock_minimo: string;
    descripcion: string;
    activo: boolean;
}

interface Props {
    principiosActivos: PrincipioActivo[];
    formasFarmaceuticas: FormaFarmaceutica[];
    viasAdministracion: ViaAdministracion[];
    unidadesMedida: UnidadMedida[];
}

export default function Create({ 
    principiosActivos, 
    formasFarmaceuticas, 
    viasAdministracion, 
    unidadesMedida 
}: Props) {
    const { data, setData, post, processing, errors } = useForm<FormData>({
        nombre_comercial: '',
        principio_activo_id: '',
        forma_farmaceutica_id: '',
        via_administracion_id: '',
        unidad_concentracion_id: '',
        concentracion: '',
        codigo_barras: '',
        lote: '',
        fecha_vencimiento: '',
        precio_unitario: '',
        stock_actual: '',
        stock_minimo: '',
        descripcion: '',
        activo: true,
    });

    const [selectedPrincipio, setSelectedPrincipio] = useState<PrincipioActivo | null>(null);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/medicamentos');
    };

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Medicamentos', href: '/medicamentos' },
                { title: 'Nuevo Medicamento', href: '/medicamentos/create' }
            ]}
        >
            <Head title="Crear Medicamento" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/medicamentos">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Nuevo Medicamento</h1>
                        <p className="text-muted-foreground">
                            Complete la información del nuevo medicamento
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Información Básica */}
                        <div className="lg:col-span-2 space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Pill className="h-5 w-5" />
                                        Información Básica
                                    </CardTitle>
                                    <CardDescription>
                                        Datos principales del medicamento
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="nombre_comercial">
                                                Nombre Comercial <span className="text-red-500">*</span>
                                            </Label>
                                            <Input
                                                id="nombre_comercial"
                                                value={data.nombre_comercial}
                                                onChange={(e) => setData('nombre_comercial', e.target.value)}
                                                placeholder="Ej: Paracetamol 500mg"
                                                className={errors.nombre_comercial ? 'border-red-500' : ''}
                                            />
                                            {errors.nombre_comercial && (
                                                <p className="text-sm text-red-500">{errors.nombre_comercial}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="codigo_barras">Código de Barras</Label>
                                            <Input
                                                id="codigo_barras"
                                                value={data.codigo_barras}
                                                onChange={(e) => setData('codigo_barras', e.target.value)}
                                                placeholder="Código de barras del producto"
                                                className={errors.codigo_barras ? 'border-red-500' : ''}
                                            />
                                            {errors.codigo_barras && (
                                                <p className="text-sm text-red-500">{errors.codigo_barras}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="principio_activo_id">
                                            Principio Activo <span className="text-red-500">*</span>
                                        </Label>
                                        <Select 
                                            value={data.principio_activo_id} 
                                            onValueChange={(value) => {
                                                setData('principio_activo_id', value);
                                                const principio = principiosActivos.find(p => p.id.toString() === value);
                                                setSelectedPrincipio(principio || null);
                                            }}
                                        >
                                            <SelectTrigger className={errors.principio_activo_id ? 'border-red-500' : ''}>
                                                <SelectValue placeholder="Seleccione un principio activo" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {principiosActivos.map((principio) => (
                                                    <SelectItem key={principio.id} value={principio.id.toString()}>
                                                        <div>
                                                            <div className="font-medium">{principio.nombre_generico}</div>
                                                            <div className="text-sm text-muted-foreground">
                                                                {principio.grupo_farmacologico}
                                                            </div>
                                                        </div>
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.principio_activo_id && (
                                            <p className="text-sm text-red-500">{errors.principio_activo_id}</p>
                                        )}
                                        {selectedPrincipio && (
                                            <p className="text-sm text-muted-foreground">
                                                Grupo farmacológico: {selectedPrincipio.grupo_farmacologico}
                                            </p>
                                        )}
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="concentracion">
                                                Concentración <span className="text-red-500">*</span>
                                            </Label>
                                            <Input
                                                id="concentracion"
                                                type="number"
                                                step="0.001"
                                                min="0"
                                                value={data.concentracion}
                                                onChange={(e) => setData('concentracion', e.target.value)}
                                                placeholder="500"
                                                className={errors.concentracion ? 'border-red-500' : ''}
                                            />
                                            {errors.concentracion && (
                                                <p className="text-sm text-red-500">{errors.concentracion}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="unidad_concentracion_id">
                                                Unidad de Concentración <span className="text-red-500">*</span>
                                            </Label>
                                            <Select 
                                                value={data.unidad_concentracion_id} 
                                                onValueChange={(value) => setData('unidad_concentracion_id', value)}
                                            >
                                                <SelectTrigger className={errors.unidad_concentracion_id ? 'border-red-500' : ''}>
                                                    <SelectValue placeholder="Seleccione unidad" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {unidadesMedida.map((unidad) => (
                                                        <SelectItem key={unidad.id} value={unidad.id.toString()}>
                                                            {unidad.nombre} ({unidad.simbolo})
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {errors.unidad_concentracion_id && (
                                                <p className="text-sm text-red-500">{errors.unidad_concentracion_id}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="forma_farmaceutica_id">
                                                Forma Farmacéutica <span className="text-red-500">*</span>
                                            </Label>
                                            <Select 
                                                value={data.forma_farmaceutica_id} 
                                                onValueChange={(value) => setData('forma_farmaceutica_id', value)}
                                            >
                                                <SelectTrigger className={errors.forma_farmaceutica_id ? 'border-red-500' : ''}>
                                                    <SelectValue placeholder="Seleccione forma" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {formasFarmaceuticas.map((forma) => (
                                                        <SelectItem key={forma.id} value={forma.id.toString()}>
                                                            {forma.nombre}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {errors.forma_farmaceutica_id && (
                                                <p className="text-sm text-red-500">{errors.forma_farmaceutica_id}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="via_administracion_id">
                                                Vía de Administración <span className="text-red-500">*</span>
                                            </Label>
                                            <Select 
                                                value={data.via_administracion_id} 
                                                onValueChange={(value) => setData('via_administracion_id', value)}
                                            >
                                                <SelectTrigger className={errors.via_administracion_id ? 'border-red-500' : ''}>
                                                    <SelectValue placeholder="Seleccione vía" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {viasAdministracion.map((via) => (
                                                        <SelectItem key={via.id} value={via.id.toString()}>
                                                            {via.nombre}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            {errors.via_administracion_id && (
                                                <p className="text-sm text-red-500">{errors.via_administracion_id}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="descripcion">Descripción</Label>
                                        <Textarea
                                            id="descripcion"
                                            value={data.descripcion}
                                            onChange={(e) => setData('descripcion', e.target.value)}
                                            placeholder="Descripción opcional del medicamento"
                                            rows={3}
                                            className={errors.descripcion ? 'border-red-500' : ''}
                                        />
                                        <div className="text-xs text-muted-foreground">
                                            {data.descripcion.length}/1000 caracteres
                                        </div>
                                        {errors.descripcion && (
                                            <p className="text-sm text-red-500">{errors.descripcion}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Información de Lote y Vencimiento */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Calendar className="h-5 w-5" />
                                        Lote y Vencimiento
                                    </CardTitle>
                                    <CardDescription>
                                        Información de lote y fecha de vencimiento
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="lote">Lote</Label>
                                            <Input
                                                id="lote"
                                                value={data.lote}
                                                onChange={(e) => setData('lote', e.target.value)}
                                                placeholder="Ej: L2024001"
                                                className={errors.lote ? 'border-red-500' : ''}
                                            />
                                            {errors.lote && (
                                                <p className="text-sm text-red-500">{errors.lote}</p>
                                            )}
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="fecha_vencimiento">Fecha de Vencimiento</Label>
                                            <Input
                                                id="fecha_vencimiento"
                                                type="date"
                                                value={data.fecha_vencimiento}
                                                onChange={(e) => setData('fecha_vencimiento', e.target.value)}
                                                className={errors.fecha_vencimiento ? 'border-red-500' : ''}
                                            />
                                            {errors.fecha_vencimiento && (
                                                <p className="text-sm text-red-500">{errors.fecha_vencimiento}</p>
                                            )}
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Panel Lateral */}
                        <div className="space-y-6">
                            {/* Stock */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <BarChart3 className="h-5 w-5" />
                                        Inventario
                                    </CardTitle>
                                    <CardDescription>
                                        Control de stock del medicamento
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="stock_actual">
                                            Stock Actual <span className="text-red-500">*</span>
                                        </Label>
                                        <Input
                                            id="stock_actual"
                                            type="number"
                                            min="0"
                                            value={data.stock_actual}
                                            onChange={(e) => setData('stock_actual', e.target.value)}
                                            placeholder="0"
                                            className={errors.stock_actual ? 'border-red-500' : ''}
                                        />
                                        {errors.stock_actual && (
                                            <p className="text-sm text-red-500">{errors.stock_actual}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="stock_minimo">
                                            Stock Mínimo <span className="text-red-500">*</span>
                                        </Label>
                                        <Input
                                            id="stock_minimo"
                                            type="number"
                                            min="0"
                                            value={data.stock_minimo}
                                            onChange={(e) => setData('stock_minimo', e.target.value)}
                                            placeholder="5"
                                            className={errors.stock_minimo ? 'border-red-500' : ''}
                                        />
                                        {errors.stock_minimo && (
                                            <p className="text-sm text-red-500">{errors.stock_minimo}</p>
                                        )}
                                        <p className="text-xs text-muted-foreground">
                                            Se generará alerta cuando el stock llegue a este nivel
                                        </p>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Precio */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <DollarSign className="h-5 w-5" />
                                        Precio
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2">
                                        <Label htmlFor="precio_unitario">Precio Unitario</Label>
                                        <Input
                                            id="precio_unitario"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            value={data.precio_unitario}
                                            onChange={(e) => setData('precio_unitario', e.target.value)}
                                            placeholder="0.00"
                                            className={errors.precio_unitario ? 'border-red-500' : ''}
                                        />
                                        {errors.precio_unitario && (
                                            <p className="text-sm text-red-500">{errors.precio_unitario}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Estado */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Package className="h-5 w-5" />
                                        Estado
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="activo"
                                            checked={data.activo}
                                            onCheckedChange={(checked) => setData('activo', checked as boolean)}
                                        />
                                        <Label htmlFor="activo">Medicamento activo</Label>
                                    </div>
                                    <p className="text-xs text-muted-foreground mt-2">
                                        Solo los medicamentos activos estarán disponibles para tratamientos
                                    </p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    {/* Botones de acción */}
                    <div className="flex items-center gap-4 pt-6 border-t">
                        <Button
                            type="submit"
                            disabled={processing}
                            className="min-w-32"
                        >
                            <Save className="h-4 w-4 mr-2" />
                            {processing ? 'Guardando...' : 'Guardar Medicamento'}
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href="/medicamentos">Cancelar</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </AppSidebarLayout>
    );
} 