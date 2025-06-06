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
    BarChart3,
    Eye
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

interface Medicamento {
    id: number;
    nombre_comercial: string;
    codigo_barras?: string;
    concentracion: number;
    lote?: string;
    fecha_vencimiento?: string;
    precio_unitario?: number;
    stock_actual: number;
    stock_minimo: number;
    descripcion?: string;
    activo: boolean;
    principio_activo_id: number;
    forma_farmaceutica_id: number;
    via_administracion_id: number;
    unidad_concentracion_id: number;
    created_at?: string;
    updated_at?: string;
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
    medicamento: Medicamento;
    principiosActivos: PrincipioActivo[];
    formasFarmaceuticas: FormaFarmaceutica[];
    viasAdministracion: ViaAdministracion[];
    unidadesMedida: UnidadMedida[];
}

export default function Edit({ 
    medicamento,
    principiosActivos, 
    formasFarmaceuticas, 
    viasAdministracion, 
    unidadesMedida 
}: Props) {
    const { data, setData, put, processing, errors } = useForm<FormData>({
        nombre_comercial: medicamento.nombre_comercial,
        principio_activo_id: medicamento.principio_activo_id.toString(),
        forma_farmaceutica_id: medicamento.forma_farmaceutica_id.toString(),
        via_administracion_id: medicamento.via_administracion_id.toString(),
        unidad_concentracion_id: medicamento.unidad_concentracion_id.toString(),
        concentracion: medicamento.concentracion.toString(),
        codigo_barras: medicamento.codigo_barras || '',
        lote: medicamento.lote || '',
        fecha_vencimiento: medicamento.fecha_vencimiento || '',
        precio_unitario: medicamento.precio_unitario?.toString() || '',
        stock_actual: medicamento.stock_actual.toString(),
        stock_minimo: medicamento.stock_minimo.toString(),
        descripcion: medicamento.descripcion || '',
        activo: medicamento.activo,
    });

    const [selectedPrincipio, setSelectedPrincipio] = useState<PrincipioActivo | null>(
        principiosActivos.find(p => p.id === medicamento.principio_activo_id) || null
    );

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/medicamentos/${medicamento.id}`);
    };

    // Convertir fecha para el input date
    const formatDateForInput = (dateString: string) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    };

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Medicamentos', href: '/medicamentos' },
                { title: medicamento.nombre_comercial, href: `/medicamentos/${medicamento.id}` },
                { title: 'Editar', href: `/medicamentos/${medicamento.id}/edit` }
            ]}
        >
            <Head title={`Editar ${medicamento.nombre_comercial}`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href={`/medicamentos/${medicamento.id}`}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div className="flex-1">
                        <h1 className="text-3xl font-bold tracking-tight">Editar Medicamento</h1>
                        <p className="text-muted-foreground">
                            Actualice la información del medicamento: {medicamento.nombre_comercial}
                        </p>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href={`/medicamentos/${medicamento.id}`}>
                            <Eye className="h-4 w-4 mr-2" />
                            Ver Detalles
                        </Link>
                    </Button>
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
                                                value={formatDateForInput(data.fecha_vencimiento)}
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

                                    {/* Indicador visual del stock */}
                                    <div className="pt-3 border-t">
                                        <div className="space-y-1">
                                            <p className="text-sm font-medium text-muted-foreground">Estado del Stock</p>
                                            {parseInt(data.stock_actual) <= parseInt(data.stock_minimo) && parseInt(data.stock_actual) > 0 ? (
                                                <div className="flex items-center gap-2 text-yellow-600">
                                                    <div className="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                                    <span className="text-sm">En nivel mínimo</span>
                                                </div>
                                            ) : parseInt(data.stock_actual) === 0 ? (
                                                <div className="flex items-center gap-2 text-red-600">
                                                    <div className="w-2 h-2 bg-red-500 rounded-full"></div>
                                                    <span className="text-sm">Sin stock</span>
                                                </div>
                                            ) : (
                                                <div className="flex items-center gap-2 text-green-600">
                                                    <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                                    <span className="text-sm">Stock normal</span>
                                                </div>
                                            )}
                                        </div>
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

                                    {/* Cálculo automático del valor del stock */}
                                    {data.precio_unitario && data.stock_actual && parseFloat(data.precio_unitario) > 0 && parseInt(data.stock_actual) > 0 && (
                                        <div className="pt-3 border-t">
                                            <p className="text-sm font-medium text-muted-foreground">Valor del Stock</p>
                                            <p className="text-lg font-semibold">
                                                ${(parseFloat(data.precio_unitario) * parseInt(data.stock_actual)).toFixed(2)}
                                            </p>
                                        </div>
                                    )}
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
                                    
                                    {!data.activo && (
                                        <div className="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                            <p className="text-sm text-yellow-800">
                                                ⚠️ Este medicamento estará inactivo y no se podrá usar en nuevos tratamientos.
                                            </p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Información del medicamento actual */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-sm">Información Original</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2 text-sm">
                                    <div>
                                        <span className="text-muted-foreground">ID:</span> {medicamento.id}
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">Creado:</span> {new Date(medicamento.created_at || '').toLocaleDateString()}
                                    </div>
                                    <div>
                                        <span className="text-muted-foreground">Actualizado:</span> {new Date(medicamento.updated_at || '').toLocaleDateString()}
                                    </div>
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
                            {processing ? 'Guardando...' : 'Actualizar Medicamento'}
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={`/medicamentos/${medicamento.id}`}>Cancelar</Link>
                        </Button>
                        <Button variant="ghost" asChild>
                            <Link href="/medicamentos">Volver al Listado</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </AppSidebarLayout>
    );
} 