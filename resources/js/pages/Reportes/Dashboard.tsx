import { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { 
    ChartContainer, 
    ChartTooltip, 
    ChartTooltipContent,
    ChartLegend,
    ChartLegendContent
} from '@/components/ui/chart';
import { 
    LineChart, 
    Line, 
    BarChart, 
    Bar, 
    PieChart, 
    Pie, 
    Cell,
    XAxis, 
    YAxis, 
    CartesianGrid, 
    ResponsiveContainer,
    Area,
    AreaChart
} from 'recharts';
import { router } from '@inertiajs/react';

// Iconos de Lucide React
import { 
    TrendingUp, 
    TrendingDown, 
    Activity, 
    Users, 
    Pill, 
    Calendar,
    BarChart3,
    PieChart as PieChartIcon,
    Filter,
    Download
} from 'lucide-react';

interface DatosGraficos {
    consumosPorDia: Array<{
        fecha: string;
        fecha_label: string;
        total: number;
        exitosas: number;
        fallidas: number;
        tasa_exito: number;
    }>;
    consumosPorMedicamento: Array<{
        medicamento: string;
        medicamento_id: number;
        total_administraciones: number;
        administraciones_exitosas: number;
        total_dosis: number;
        tasa_exito: number;
    }>;
    consumosPorPaciente: Array<{
        paciente_id: number;
        nombre: string;
        total_administraciones: number;
        administraciones_exitosas: number;
        tasa_exito: number;
    }>;
    adherenciaTratamientos: Array<{
        tratamiento_id: number;
        descripcion: string;
        paciente: string;
        total_programadas: number;
        total_administradas: number;
        adherencia: number;
        estado_adherencia: string;
    }>;
    estadisticasGenerales: {
        total_administraciones: number;
        administraciones_exitosas: number;
        tasa_exito_global: number;
        pacientes_activos: number;
        medicamentos_usados: number;
        promedio_administraciones_diarias: number;
    };
}

interface Props {
    datosGraficos: DatosGraficos;
    filtros: {
        fecha_inicio: string;
        fecha_fin: string;
    };
}

const coloresAdherencia = {
    excelente: '#22c55e',
    buena: '#3b82f6', 
    regular: '#f59e0b',
    deficiente: '#ef4444'
};

const colorChart = {
    exitosas: 'hsl(var(--chart-1))',
    fallidas: 'hsl(var(--chart-2))',
    total: 'hsl(var(--chart-3))',
    adherencia: 'hsl(var(--chart-4))',
};

const chartConfig = {
    exitosas: {
        label: "Administraciones Exitosas",
        color: colorChart.exitosas,
    },
    fallidas: {
        label: "Administraciones Fallidas", 
        color: colorChart.fallidas,
    },
    total: {
        label: "Total",
        color: colorChart.total,
    },
    adherencia: {
        label: "Adherencia (%)",
        color: colorChart.adherencia,
    },
} as const;

export default function ReportesDashboard({ datosGraficos, filtros }: Props) {
    const [fechaInicio, setFechaInicio] = useState(filtros.fecha_inicio);
    const [fechaFin, setFechaFin] = useState(filtros.fecha_fin);

    const aplicarFiltros = () => {
        router.get(route('reportes.dashboard'), {
            fecha_inicio: fechaInicio,
            fecha_fin: fechaFin,
        });
    };

    const obtenerColorAdherencia = (estado: string) => {
        return coloresAdherencia[estado as keyof typeof coloresAdherencia] || coloresAdherencia.deficiente;
    };

    const estadisticas = datosGraficos.estadisticasGenerales;
    
    // Verificar si hay datos disponibles
    const hayDatos = estadisticas.total_administraciones > 0;
    const hayConsumosPorDia = datosGraficos.consumosPorDia && datosGraficos.consumosPorDia.length > 0;
    const hayMedicamentos = datosGraficos.consumosPorMedicamento && datosGraficos.consumosPorMedicamento.length > 0;
    const hayPacientes = datosGraficos.consumosPorPaciente && datosGraficos.consumosPorPaciente.length > 0;
    const hayTratamientos = datosGraficos.adherenciaTratamientos && datosGraficos.adherenciaTratamientos.length > 0;

    return (
        <AppLayout>
            <Head title="Reportes - Dashboard" />

            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    
                    {/* Header del Dashboard */}
                    <div className="flex justify-between items-center mb-6">
                        <h1 className="font-semibold text-3xl text-gray-800 leading-tight">
                            📊 Reportes y Gráficos de Consumos
                        </h1>
                        <div className="flex items-center gap-4">
                            <Button variant="outline" size="sm">
                                <Download className="w-4 h-4 mr-2" />
                                Exportar PDF
                            </Button>
                        </div>
                    </div>
                    
                    {/* Filtros de Fecha */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Filter className="w-5 h-5" />
                                Filtros de Período
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="flex flex-wrap items-end gap-4">
                                <div>
                                    <Label htmlFor="fecha_inicio">Fecha Inicio</Label>
                                    <Input
                                        id="fecha_inicio"
                                        type="date"
                                        value={fechaInicio}
                                        onChange={(e) => setFechaInicio(e.target.value)}
                                        className="w-auto"
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="fecha_fin">Fecha Fin</Label>
                                    <Input
                                        id="fecha_fin"
                                        type="date"
                                        value={fechaFin}
                                        onChange={(e) => setFechaFin(e.target.value)}
                                        className="w-auto"
                                    />
                                </div>
                                <Button onClick={aplicarFiltros}>
                                    Aplicar Filtros
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Tarjetas de Estadísticas Generales */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Total Administraciones
                                </CardTitle>
                                <Activity className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{estadisticas.total_administraciones}</div>
                                <p className="text-xs text-muted-foreground">
                                    {estadisticas.promedio_administraciones_diarias} promedio diario
                                </p>
                            </CardContent>
                        </Card>
                        
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Tasa de Éxito Global
                                </CardTitle>
                                <TrendingUp className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{estadisticas.tasa_exito_global}%</div>
                                <p className="text-xs text-muted-foreground">
                                    {estadisticas.administraciones_exitosas} de {estadisticas.total_administraciones}
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Pacientes Activos
                                </CardTitle>
                                <Users className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{estadisticas.pacientes_activos}</div>
                                <p className="text-xs text-muted-foreground">
                                    Con tratamientos activos
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">
                                    Medicamentos en Uso
                                </CardTitle>
                                <Pill className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{estadisticas.medicamentos_usados}</div>
                                <p className="text-xs text-muted-foreground">
                                    Diferentes medicamentos
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Mensaje cuando no hay datos */}
                    {!hayDatos && (
                        <Card>
                            <CardContent className="py-8">
                                <div className="text-center">
                                    <BarChart3 className="mx-auto h-12 w-12 text-gray-400 mb-4" />
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        No hay datos disponibles
                                    </h3>
                                    <p className="text-gray-500 mb-4">
                                        No se encontraron administraciones de medicamentos en el período seleccionado ({fechaInicio} - {fechaFin}).
                                    </p>
                                    <p className="text-sm text-gray-400">
                                        Intenta seleccionar un rango de fechas diferente o verifica que haya tratamientos activos con administraciones registradas.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    {/* Gráficos - Solo mostrar si hay datos */}
                    {hayDatos && (
                        <>
                        {/* Gráfico de Líneas: Consumos por Día */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <BarChart3 className="w-5 h-5" />
                                    Tendencia de Administraciones por Día
                                </CardTitle>
                                <CardDescription>
                                    Evolución diaria de administraciones exitosas vs fallidas
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                {hayConsumosPorDia ? (
                                <ChartContainer config={chartConfig} className="h-[300px]">
                                    <LineChart data={datosGraficos.consumosPorDia}>
                                        <CartesianGrid strokeDasharray="3 3" />
                                        <XAxis dataKey="fecha_label" />
                                        <YAxis />
                                        <ChartTooltip content={<ChartTooltipContent />} />
                                        <ChartLegend content={<ChartLegendContent />} />
                                        <Line 
                                            type="monotone" 
                                            dataKey="exitosas" 
                                            stroke="var(--color-exitosas)" 
                                            strokeWidth={2}
                                            dot={{ fill: "var(--color-exitosas)" }}
                                        />
                                        <Line 
                                            type="monotone" 
                                            dataKey="fallidas" 
                                            stroke="var(--color-fallidas)" 
                                            strokeWidth={2}
                                            dot={{ fill: "var(--color-fallidas)" }}
                                        />
                                    </LineChart>
                                </ChartContainer>
                                ) : (
                                    <div className="h-[300px] flex items-center justify-center text-gray-500">
                                        No hay datos por día para mostrar
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Gráfico de Barras: Top Medicamentos */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <BarChart3 className="w-5 h-5" />
                                        Top 10 Medicamentos Más Usados
                                    </CardTitle>
                                    <CardDescription>
                                        Medicamentos con mayor número de administraciones
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <ChartContainer config={chartConfig} className="h-[300px]">
                                        <BarChart data={datosGraficos.consumosPorMedicamento.slice(0, 10)}>
                                            <CartesianGrid strokeDasharray="3 3" />
                                            <XAxis 
                                                dataKey="medicamento" 
                                                angle={-45}
                                                textAnchor="end"
                                                height={80}
                                                tick={{ fontSize: 10 }}
                                            />
                                            <YAxis />
                                            <ChartTooltip content={<ChartTooltipContent />} />
                                            <Bar 
                                                dataKey="total_administraciones" 
                                                fill="var(--color-total)"
                                                radius={[4, 4, 0, 0]}
                                            />
                                        </BarChart>
                                    </ChartContainer>
                                </CardContent>
                            </Card>

                            {/* Gráfico de Barras: Top Pacientes */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Users className="w-5 h-5" />
                                        Top 10 Pacientes con Más Administraciones
                                    </CardTitle>
                                    <CardDescription>
                                        Pacientes con mayor actividad en tratamientos
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <ChartContainer config={chartConfig} className="h-[300px]">
                                        <BarChart data={datosGraficos.consumosPorPaciente.slice(0, 10)}>
                                            <CartesianGrid strokeDasharray="3 3" />
                                            <XAxis 
                                                dataKey="nombre" 
                                                angle={-45}
                                                textAnchor="end"
                                                height={80}
                                                tick={{ fontSize: 10 }}
                                            />
                                            <YAxis />
                                            <ChartTooltip content={<ChartTooltipContent />} />
                                            <Bar 
                                                dataKey="total_administraciones" 
                                                fill="var(--color-adherencia)"
                                                radius={[4, 4, 0, 0]}
                                            />
                                        </BarChart>
                                    </ChartContainer>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Tabla de Adherencia por Tratamientos */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Activity className="w-5 h-5" />
                                    Adherencia por Tratamientos Activos
                                </CardTitle>
                                <CardDescription>
                                    Porcentaje de cumplimiento de cada tratamiento
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-4">
                                    {datosGraficos.adherenciaTratamientos.slice(0, 10).map((tratamiento) => (
                                        <div key={tratamiento.tratamiento_id} className="flex items-center justify-between p-4 border rounded-lg">
                                            <div className="flex-1">
                                                <div className="font-medium">{tratamiento.descripcion}</div>
                                                <div className="text-sm text-muted-foreground">
                                                    Paciente: {tratamiento.paciente}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    {tratamiento.total_administradas} de {tratamiento.total_programadas} administraciones
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <Badge 
                                                    variant="outline"
                                                    style={{ 
                                                        borderColor: obtenerColorAdherencia(tratamiento.estado_adherencia),
                                                        color: obtenerColorAdherencia(tratamiento.estado_adherencia)
                                                    }}
                                                >
                                                    {tratamiento.adherencia}%
                                                </Badge>
                                                <div className="text-xs text-muted-foreground capitalize mt-1">
                                                    {tratamiento.estado_adherencia}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </CardContent>
                        </Card>
                        </>
                    )}
                </div>
            </div>
        </AppLayout>
    );
} 