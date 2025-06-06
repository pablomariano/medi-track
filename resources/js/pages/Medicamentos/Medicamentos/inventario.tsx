import { Head, Link } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
} from '@/components/ui/table';
import { 
    AlertTriangle, 
    ArrowLeft,
    Package,
    Clock,
    XCircle,
    BarChart3,
    Calendar
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

interface Medicamento {
    id: number;
    nombre_comercial: string;
    lote?: string;
    fecha_vencimiento?: string;
    stock_actual: number;
    stock_minimo: number;
    principio_activo: PrincipioActivo;
    forma_farmaceutica: FormaFarmaceutica;
    created_at: string;
}

interface Props {
    stockBajo: Medicamento[];
    vencidos: Medicamento[];
    proximosVencer: Medicamento[];
}

export default function Inventario({ stockBajo, vencidos, proximosVencer }: Props) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    };

    const getDaysUntilExpiry = (dateString: string) => {
        const expiryDate = new Date(dateString);
        const today = new Date();
        const diffTime = expiryDate.getTime() - today.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    };

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Medicamentos', href: '/medicamentos' },
                { title: 'Alertas de Inventario', href: '/medicamentos/inventario/alertas' }
            ]}
        >
            <Head title="Alertas de Inventario" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/medicamentos">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Alertas de Inventario</h1>
                        <p className="text-muted-foreground">
                            Monitoreo de stock bajo, medicamentos vencidos y próximos a vencer
                        </p>
                    </div>
                </div>

                {/* Resumen de alertas */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card className="border-red-200 bg-red-50">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-red-700">
                                Stock Bajo
                            </CardTitle>
                            <BarChart3 className="h-4 w-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-700">{stockBajo.length}</div>
                            <p className="text-xs text-red-600">medicamentos por debajo del stock mínimo</p>
                        </CardContent>
                    </Card>

                    <Card className="border-red-200 bg-red-50">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-red-700">
                                Vencidos
                            </CardTitle>
                            <XCircle className="h-4 w-4 text-red-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-700">{vencidos.length}</div>
                            <p className="text-xs text-red-600">medicamentos vencidos</p>
                        </CardContent>
                    </Card>

                    <Card className="border-yellow-200 bg-yellow-50">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-yellow-700">
                                Próximos a Vencer
                            </CardTitle>
                            <Clock className="h-4 w-4 text-yellow-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-yellow-700">{proximosVencer.length}</div>
                            <p className="text-xs text-yellow-600">medicamentos vencen en 3 meses</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Medicamentos con Stock Bajo */}
                {stockBajo.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg flex items-center gap-2">
                                <BarChart3 className="h-5 w-5 text-red-500" />
                                Stock Bajo ({stockBajo.length})
                            </CardTitle>
                            <CardDescription>
                                Medicamentos que han alcanzado o están por debajo del stock mínimo
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Medicamento</TableHead>
                                            <TableHead>Principio Activo</TableHead>
                                            <TableHead>Stock Actual</TableHead>
                                            <TableHead>Stock Mínimo</TableHead>
                                            <TableHead>Diferencia</TableHead>
                                            <TableHead className="text-right">Acciones</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {stockBajo.map((medicamento) => {
                                            const diferencia = medicamento.stock_actual - medicamento.stock_minimo;
                                            return (
                                                <TableRow key={medicamento.id}>
                                                    <TableCell>
                                                        <div>
                                                            <div className="font-medium">{medicamento.nombre_comercial}</div>
                                                            <div className="text-sm text-muted-foreground">
                                                                {medicamento.forma_farmaceutica.nombre}
                                                            </div>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div>
                                                            <div className="font-medium">{medicamento.principio_activo.nombre_generico}</div>
                                                            <div className="text-sm text-muted-foreground">
                                                                {medicamento.principio_activo.grupo_farmacologico}
                                                            </div>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge variant="destructive">
                                                            {medicamento.stock_actual}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge variant="outline">
                                                            {medicamento.stock_minimo}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge 
                                                            variant={diferencia < 0 ? "destructive" : "secondary"}
                                                            className={diferencia === 0 ? "bg-yellow-100 text-yellow-800" : ""}
                                                        >
                                                            {diferencia > 0 ? `+${diferencia}` : diferencia}
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <Button variant="outline" size="sm" asChild>
                                                            <Link href={`/medicamentos/${medicamento.id}`}>
                                                                Ver Detalles
                                                            </Link>
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            );
                                        })}
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Medicamentos Vencidos */}
                {vencidos.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg flex items-center gap-2">
                                <XCircle className="h-5 w-5 text-red-500" />
                                Medicamentos Vencidos ({vencidos.length})
                            </CardTitle>
                            <CardDescription>
                                Medicamentos que ya han superado su fecha de vencimiento
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Medicamento</TableHead>
                                            <TableHead>Principio Activo</TableHead>
                                            <TableHead>Lote</TableHead>
                                            <TableHead>Fecha Vencimiento</TableHead>
                                            <TableHead>Días Vencido</TableHead>
                                            <TableHead className="text-right">Acciones</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {vencidos.map((medicamento) => {
                                            const diasVencido = medicamento.fecha_vencimiento 
                                                ? Math.abs(getDaysUntilExpiry(medicamento.fecha_vencimiento))
                                                : 0;
                                            return (
                                                <TableRow key={medicamento.id}>
                                                    <TableCell>
                                                        <div>
                                                            <div className="font-medium">{medicamento.nombre_comercial}</div>
                                                            <div className="text-sm text-muted-foreground">
                                                                {medicamento.forma_farmaceutica.nombre}
                                                            </div>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div>
                                                            <div className="font-medium">{medicamento.principio_activo.nombre_generico}</div>
                                                            <div className="text-sm text-muted-foreground">
                                                                {medicamento.principio_activo.grupo_farmacologico}
                                                            </div>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        {medicamento.lote ? (
                                                            <Badge variant="outline">{medicamento.lote}</Badge>
                                                        ) : (
                                                            <span className="text-muted-foreground">-</span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        {medicamento.fecha_vencimiento ? (
                                                            <Badge variant="destructive">
                                                                {formatDate(medicamento.fecha_vencimiento)}
                                                            </Badge>
                                                        ) : (
                                                            <span className="text-muted-foreground">-</span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge variant="destructive">
                                                            {diasVencido} días
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <Button variant="outline" size="sm" asChild>
                                                            <Link href={`/medicamentos/${medicamento.id}`}>
                                                                Ver Detalles
                                                            </Link>
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            );
                                        })}
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Medicamentos Próximos a Vencer */}
                {proximosVencer.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg flex items-center gap-2">
                                <Clock className="h-5 w-5 text-yellow-500" />
                                Próximos a Vencer ({proximosVencer.length})
                            </CardTitle>
                            <CardDescription>
                                Medicamentos que vencen en los próximos 3 meses
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Medicamento</TableHead>
                                            <TableHead>Principio Activo</TableHead>
                                            <TableHead>Lote</TableHead>
                                            <TableHead>Fecha Vencimiento</TableHead>
                                            <TableHead>Días Restantes</TableHead>
                                            <TableHead className="text-right">Acciones</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {proximosVencer.map((medicamento) => {
                                            const diasRestantes = medicamento.fecha_vencimiento 
                                                ? getDaysUntilExpiry(medicamento.fecha_vencimiento)
                                                : 0;
                                            return (
                                                <TableRow key={medicamento.id}>
                                                    <TableCell>
                                                        <div>
                                                            <div className="font-medium">{medicamento.nombre_comercial}</div>
                                                            <div className="text-sm text-muted-foreground">
                                                                {medicamento.forma_farmaceutica.nombre}
                                                            </div>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        <div>
                                                            <div className="font-medium">{medicamento.principio_activo.nombre_generico}</div>
                                                            <div className="text-sm text-muted-foreground">
                                                                {medicamento.principio_activo.grupo_farmacologico}
                                                            </div>
                                                        </div>
                                                    </TableCell>
                                                    <TableCell>
                                                        {medicamento.lote ? (
                                                            <Badge variant="outline">{medicamento.lote}</Badge>
                                                        ) : (
                                                            <span className="text-muted-foreground">-</span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        {medicamento.fecha_vencimiento ? (
                                                            <Badge 
                                                                variant="outline"
                                                                className="bg-yellow-100 text-yellow-800"
                                                            >
                                                                {formatDate(medicamento.fecha_vencimiento)}
                                                            </Badge>
                                                        ) : (
                                                            <span className="text-muted-foreground">-</span>
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        <Badge 
                                                            variant={diasRestantes <= 30 ? "destructive" : "secondary"}
                                                            className={diasRestantes > 30 && diasRestantes <= 60 ? "bg-yellow-100 text-yellow-800" : ""}
                                                        >
                                                            {diasRestantes} días
                                                        </Badge>
                                                    </TableCell>
                                                    <TableCell className="text-right">
                                                        <Button variant="outline" size="sm" asChild>
                                                            <Link href={`/medicamentos/${medicamento.id}`}>
                                                                Ver Detalles
                                                            </Link>
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            );
                                        })}
                                    </TableBody>
                                </Table>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Mensaje cuando no hay alertas */}
                {stockBajo.length === 0 && vencidos.length === 0 && proximosVencer.length === 0 && (
                    <Card>
                        <CardContent className="text-center py-12">
                            <Package className="h-12 w-12 text-green-500 mx-auto mb-4" />
                            <h3 className="text-lg font-medium text-green-700 mb-2">¡Todo en orden!</h3>
                            <p className="text-muted-foreground">
                                No hay alertas de inventario en este momento. Todos los medicamentos tienen stock adecuado y están dentro de su fecha de vencimiento.
                            </p>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppSidebarLayout>
    );
} 