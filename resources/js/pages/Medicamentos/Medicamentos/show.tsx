import { Head, Link } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { 
    ArrowLeft, 
    Edit, 
    Package,
    Pill,
    Calendar,
    DollarSign,
    BarChart3,
    AlertTriangle,
    Clock,
    User,
    FileText,
    Activity
} from 'lucide-react';

interface PrincipioActivo {
    id: number;
    nombre_generico: string;
    grupo_farmacologico: string;
    descripcion?: string;
}

interface FormaFarmaceutica {
    id: number;
    nombre: string;
    descripcion?: string;
}

interface ViaAdministracion {
    id: number;
    nombre: string;
    descripcion?: string;
}

interface UnidadMedida {
    id: number;
    nombre: string;
    simbolo: string;
    tipo: string;
}

interface Paciente {
    id: number;
    nombres: string;
    apellidos: string;
}

interface Tratamiento {
    id: number;
    paciente: Paciente;
    fecha_inicio: string;
    fecha_fin?: string;
    estado: string;
    created_at: string;
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
    principio_activo: PrincipioActivo;
    forma_farmaceutica: FormaFarmaceutica;
    via_administracion: ViaAdministracion;
    unidad_concentracion: UnidadMedida;
    tratamientos: Tratamiento[];
    created_at: string;
    updated_at: string;
}

interface Props {
    medicamento: Medicamento;
}

export default function Show({ medicamento }: Props) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const formatDateTime = (dateString: string) => {
        return new Date(dateString).toLocaleString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const getDaysUntilExpiry = () => {
        if (!medicamento.fecha_vencimiento) return null;
        const expiryDate = new Date(medicamento.fecha_vencimiento);
        const today = new Date();
        const diffTime = expiryDate.getTime() - today.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    };

    const isStockBajo = () => {
        return medicamento.stock_actual <= medicamento.stock_minimo;
    };

    const isVencido = () => {
        if (!medicamento.fecha_vencimiento) return false;
        return new Date(medicamento.fecha_vencimiento) < new Date();
    };

    const isProximoAVencer = () => {
        const days = getDaysUntilExpiry();
        return days !== null && days <= 90 && days >= 0;
    };

    const daysUntilExpiry = getDaysUntilExpiry();

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Medicamentos', href: '/medicamentos' },
                { title: medicamento.nombre_comercial, href: `/medicamentos/${medicamento.id}` }
            ]}
        >
            <Head title={`${medicamento.nombre_comercial} - Medicamento`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button variant="ghost" size="sm" asChild>
                            <Link href="/medicamentos">
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <div className="flex items-center gap-3">
                                <h1 className="text-3xl font-bold tracking-tight">{medicamento.nombre_comercial}</h1>
                                <Badge 
                                    variant={medicamento.activo ? "default" : "destructive"}
                                    className={medicamento.activo ? "bg-green-100 text-green-800" : ""}
                                >
                                    {medicamento.activo ? 'Activo' : 'Inactivo'}
                                </Badge>
                            </div>
                            <p className="text-muted-foreground">
                                {medicamento.principio_activo.nombre_generico} - {medicamento.concentracion}{medicamento.unidad_concentracion.simbolo}
                            </p>
                        </div>
                    </div>
                    <Button asChild>
                        <Link href={`/medicamentos/${medicamento.id}/edit`}>
                            <Edit className="h-4 w-4 mr-2" />
                            Editar
                        </Link>
                    </Button>
                </div>

                {/* Alertas */}
                {(isStockBajo() || isVencido() || isProximoAVencer()) && (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {isStockBajo() && (
                            <Card className="border-red-200 bg-red-50">
                                <CardContent className="pt-6">
                                    <div className="flex items-center gap-2">
                                        <AlertTriangle className="h-5 w-5 text-red-500" />
                                        <div>
                                            <p className="font-medium text-red-700">Stock Bajo</p>
                                            <p className="text-sm text-red-600">
                                                {medicamento.stock_actual} unidades (mín: {medicamento.stock_minimo})
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {isVencido() && (
                            <Card className="border-red-200 bg-red-50">
                                <CardContent className="pt-6">
                                    <div className="flex items-center gap-2">
                                        <AlertTriangle className="h-5 w-5 text-red-500" />
                                        <div>
                                            <p className="font-medium text-red-700">Medicamento Vencido</p>
                                            <p className="text-sm text-red-600">
                                                Vencido hace {Math.abs(daysUntilExpiry!)} días
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {isProximoAVencer() && !isVencido() && (
                            <Card className="border-yellow-200 bg-yellow-50">
                                <CardContent className="pt-6">
                                    <div className="flex items-center gap-2">
                                        <Clock className="h-5 w-5 text-yellow-500" />
                                        <div>
                                            <p className="font-medium text-yellow-700">Próximo a Vencer</p>
                                            <p className="text-sm text-yellow-600">
                                                {daysUntilExpiry} días restantes
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                )}

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Información Principal */}
                    <div className="lg:col-span-2 space-y-6">
                        {/* Información Básica */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Pill className="h-5 w-5" />
                                    Información Básica
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-3">
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Nombre Comercial</p>
                                            <p className="text-lg font-semibold">{medicamento.nombre_comercial}</p>
                                        </div>
                                        
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Principio Activo</p>
                                            <p className="font-medium">{medicamento.principio_activo.nombre_generico}</p>
                                            <p className="text-sm text-muted-foreground">{medicamento.principio_activo.grupo_farmacologico}</p>
                                        </div>

                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Concentración</p>
                                            <p className="font-medium">
                                                {medicamento.concentracion} {medicamento.unidad_concentracion.simbolo}
                                            </p>
                                            <p className="text-sm text-muted-foreground">{medicamento.unidad_concentracion.nombre}</p>
                                        </div>
                                    </div>

                                    <div className="space-y-3">
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Forma Farmacéutica</p>
                                            <p className="font-medium">{medicamento.forma_farmaceutica.nombre}</p>
                                        </div>

                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">Vía de Administración</p>
                                            <p className="font-medium">{medicamento.via_administracion.nombre}</p>
                                        </div>

                                        {medicamento.codigo_barras && (
                                            <div>
                                                <p className="text-sm font-medium text-muted-foreground">Código de Barras</p>
                                                <p className="font-mono text-sm bg-gray-100 p-2 rounded">{medicamento.codigo_barras}</p>
                                            </div>
                                        )}
                                    </div>
                                </div>

                                {medicamento.descripcion && (
                                    <>
                                        <Separator />
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground mb-2">Descripción</p>
                                            <p className="text-sm leading-6">{medicamento.descripcion}</p>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>

                        {/* Información de Lote y Vencimiento */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Calendar className="h-5 w-5" />
                                    Lote y Vencimiento
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Lote</p>
                                        <p className="font-medium">
                                            {medicamento.lote ? (
                                                <Badge variant="outline">{medicamento.lote}</Badge>
                                            ) : (
                                                <span className="text-muted-foreground">No especificado</span>
                                            )}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Fecha de Vencimiento</p>
                                        {medicamento.fecha_vencimiento ? (
                                            <div className="space-y-1">
                                                <Badge 
                                                    variant={
                                                        isVencido() ? "destructive" : 
                                                        isProximoAVencer() ? "destructive" : "outline"
                                                    }
                                                    className={
                                                        isProximoAVencer() && !isVencido() ? 
                                                        "bg-yellow-100 text-yellow-800" : ""
                                                    }
                                                >
                                                    {formatDate(medicamento.fecha_vencimiento)}
                                                </Badge>
                                                {daysUntilExpiry !== null && (
                                                    <p className="text-xs text-muted-foreground">
                                                        {daysUntilExpiry > 0 ? 
                                                            `${daysUntilExpiry} días restantes` : 
                                                            `Vencido hace ${Math.abs(daysUntilExpiry)} días`
                                                        }
                                                    </p>
                                                )}
                                            </div>
                                        ) : (
                                            <span className="text-muted-foreground">No especificada</span>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Tratamientos Asociados */}
                        {medicamento.tratamientos.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <User className="h-5 w-5" />
                                        Tratamientos Activos ({medicamento.tratamientos.length})
                                    </CardTitle>
                                    <CardDescription>
                                        Pacientes que actualmente usan este medicamento
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        {medicamento.tratamientos.map((tratamiento) => (
                                            <div key={tratamiento.id} className="flex items-center justify-between p-3 border rounded-lg">
                                                <div>
                                                    <p className="font-medium">
                                                        {tratamiento.paciente.nombres} {tratamiento.paciente.apellidos}
                                                    </p>
                                                    <p className="text-sm text-muted-foreground">
                                                        Desde: {formatDate(tratamiento.fecha_inicio)}
                                                    </p>
                                                </div>
                                                <div className="text-right">
                                                    <Badge variant="outline">{tratamiento.estado}</Badge>
                                                    <p className="text-xs text-muted-foreground mt-1">
                                                        ID: {tratamiento.id}
                                                    </p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Panel Lateral */}
                    <div className="space-y-6">
                        {/* Stock e Inventario */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <BarChart3 className="h-5 w-5" />
                                    Inventario
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Stock Actual</p>
                                    <div className="flex items-center gap-2">
                                        <Badge 
                                            variant={isStockBajo() ? "destructive" : "secondary"}
                                            className="text-lg px-3 py-1"
                                        >
                                            {medicamento.stock_actual}
                                        </Badge>
                                        {isStockBajo() && (
                                            <AlertTriangle className="h-4 w-4 text-red-500" />
                                        )}
                                    </div>
                                </div>

                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Stock Mínimo</p>
                                    <Badge variant="outline" className="text-base px-3 py-1">
                                        {medicamento.stock_minimo}
                                    </Badge>
                                </div>

                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Diferencia</p>
                                    <Badge 
                                        variant={
                                            medicamento.stock_actual < medicamento.stock_minimo ? "destructive" : 
                                            medicamento.stock_actual === medicamento.stock_minimo ? "secondary" : "default"
                                        }
                                        className={
                                            medicamento.stock_actual === medicamento.stock_minimo ? 
                                            "bg-yellow-100 text-yellow-800" : ""
                                        }
                                    >
                                        {medicamento.stock_actual - medicamento.stock_minimo > 0 ? '+' : ''}
                                        {medicamento.stock_actual - medicamento.stock_minimo}
                                    </Badge>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Precio */}
                        {medicamento.precio_unitario && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <DollarSign className="h-5 w-5" />
                                        Precio
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div>
                                        <p className="text-sm font-medium text-muted-foreground">Precio Unitario</p>
                                        <p className="text-2xl font-bold">${medicamento.precio_unitario}</p>
                                    </div>
                                    {medicamento.stock_actual > 0 && (
                                        <div className="mt-3 pt-3 border-t">
                                            <p className="text-sm font-medium text-muted-foreground">Valor del Stock</p>
                                            <p className="text-lg font-semibold">
                                                ${(medicamento.precio_unitario * medicamento.stock_actual).toFixed(2)}
                                            </p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        {/* Información del Sistema */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Activity className="h-5 w-5" />
                                    Información del Sistema
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">ID del Medicamento</p>
                                    <p className="font-mono text-sm">{medicamento.id}</p>
                                </div>

                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Fecha de Creación</p>
                                    <p className="text-sm">{formatDateTime(medicamento.created_at)}</p>
                                </div>

                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Última Actualización</p>
                                    <p className="text-sm">{formatDateTime(medicamento.updated_at)}</p>
                                </div>

                                <div>
                                    <p className="text-sm font-medium text-muted-foreground">Estado</p>
                                    <Badge 
                                        variant={medicamento.activo ? "default" : "destructive"}
                                        className={medicamento.activo ? "bg-green-100 text-green-800" : ""}
                                    >
                                        {medicamento.activo ? 'Activo' : 'Inactivo'}
                                    </Badge>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Acciones Rápidas */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Package className="h-5 w-5" />
                                    Acciones
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Button className="w-full" asChild>
                                    <Link href={`/medicamentos/${medicamento.id}/edit`}>
                                        <Edit className="h-4 w-4 mr-2" />
                                        Editar Medicamento
                                    </Link>
                                </Button>
                                
                                <Button variant="outline" className="w-full" asChild>
                                    <Link href="/medicamentos/inventario/alertas">
                                        <AlertTriangle className="h-4 w-4 mr-2" />
                                        Ver Alertas
                                    </Link>
                                </Button>

                                <Button variant="outline" className="w-full" asChild>
                                    <Link href="/medicamentos">
                                        <FileText className="h-4 w-4 mr-2" />
                                        Volver al Listado
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppSidebarLayout>
    );
} 