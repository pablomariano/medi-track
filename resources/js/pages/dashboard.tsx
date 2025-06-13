import AppLayout from '@/layouts/app-layout'; // Import the AppLayout component
import { type BreadcrumbItem } from '@/types'; // Import the BreadcrumbItem type
import { Head, router } from '@inertiajs/react'; // Import the Head component

import { ChartContainer, ChartConfig, ChartLegend, ChartTooltip, ChartLegendContent, ChartTooltipContent } from '@/components/ui/chart'; // Import the ChartContainer, ChartConfig, ChartLegend, ChartTooltip, ChartLegendContent, and ChartTooltipContent components
import { Bar,  BarChart, Line, LineChart, XAxis, YAxis, Tooltip, ResponsiveContainer, Cell } from 'recharts'; // Import the Bar, BarChart, Line, LineChart, XAxis, YAxis, Tooltip, and ResponsiveContainer components
import { Card } from '@/components/ui/card'; // Import the Card component
import { Badge } from '@/components/ui/badge'; // Import the Badge component
import { useState, useEffect } from 'react'; // Import the useState and useEffect hooks
import { Button } from '@/components/ui/button'; // Import the Button component
import { RefreshCw, TrendingUp, TrendingDown, Minus } from 'lucide-react'; // Import the RefreshCw icon from Lucide      

// Tipos para los datos del backend
interface EstadisticasGenerales {
    pacientes_activos: number;
    tratamientos_activos: number;
    adherencia_media: number;
    alertas_pendientes: number;
}

interface AdherenciaDia {
    day: string;
    fullDate: string;
    adherencia: number;
    dosis_administradas: number;
    dosis_programadas: number;
    dosis_omitidas?: number;
    dosis_tardias?: number;
}

interface ActividadReciente {
    id: number | string;
    user: string;
    action: string;
    time: string;
}

interface DashboardProps {
    estadisticasGenerales: EstadisticasGenerales;
    adherenciaUltimos7Dias: AdherenciaDia[];
    actividadReciente: ActividadReciente[];
}

const adherenceChartConfig = { // Define the adherence chart configuration
    adherencia: {
      label: "Adherencia (%)",
      color: "hsl(var(--chart-1))",
    },
    dosis_administradas: {
      label: "Dosis Administradas",
      color: "hsl(var(--chart-2))",
    },
    dosis_programadas: {
      label: "Dosis Programadas", 
      color: "hsl(var(--muted-foreground))",
    },
  } satisfies ChartConfig

const breadcrumbs: BreadcrumbItem[] = [ // Define the breadcrumbs
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Función para obtener el color según el nivel de adherencia
const getAdherenceColor = (adherencia: number): string => {
    if (adherencia >= 90) return "hsl(142, 76%, 36%)"; // Verde excelente
    if (adherencia >= 80) return "hsl(47, 96%, 53%)"; // Amarillo bueno
    if (adherencia >= 70) return "hsl(25, 95%, 53%)"; // Naranja regular
    return "hsl(0, 84%, 60%)"; // Rojo malo
};

// Función para obtener icono de tendencia
const getTrendIcon = (adherencia: number) => {
    if (adherencia >= 90) return <TrendingUp className="h-4 w-4 text-green-600" />;
    if (adherencia >= 70) return <Minus className="h-4 w-4 text-yellow-600" />;
    return <TrendingDown className="h-4 w-4 text-red-600" />;
};

export default function Dashboard({ estadisticasGenerales, adherenciaUltimos7Dias, actividadReciente }: DashboardProps) { // Define the Dashboard component
    const [adherenceData, setAdherenceData] = useState<AdherenciaDia[]>(adherenciaUltimos7Dias); // Define the adherence data state
    const [statsData, setStatsData] = useState<EstadisticasGenerales>(estadisticasGenerales); // Define the stats data state
    const [recentActivity, setRecentActivity] = useState<ActividadReciente[]>(actividadReciente); // Define the recent activity state
    const [isLoading, setIsLoading] = useState(false); // Define the isLoading state

    const refreshData = async () => { // Define the refreshData function
        setIsLoading(true); // Set the isLoading state to true
        
        try {
            const response = await fetch('/dashboard/refresh', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            
            if (response.ok) {
                const data = await response.json();
                setAdherenceData(data.adherenciaUltimos7Dias);
                setStatsData(data.estadisticasGenerales);
                setRecentActivity(data.actividadReciente);
            }
        } catch (error) {
            console.error('Error refreshing dashboard data:', error);
            // Fallback: recargar la página
            router.reload();
        } finally {
            setIsLoading(false); // Set the isLoading state to false
        }
    };

    useEffect(() => { // Use the useEffect hook to refresh the data every 30 seconds
        const interval = setInterval(refreshData, 30000); // Set an interval to refresh the data every 30 seconds
        return () => clearInterval(interval); // Return a cleanup function to clear the interval
    }, []); // Empty dependency array

    // Calcular adherencia promedio de la semana
    const adherenciaPromedio = adherenceData.length > 0 
        ? adherenceData.reduce((acc, day) => acc + day.adherencia, 0) / adherenceData.length
        : 0;

    // Estadísticas de la semana
    const totalDosisAdministradas = adherenceData.reduce((acc, day) => acc + day.dosis_administradas, 0);
    const totalDosisProgramadas = adherenceData.reduce((acc, day) => acc + day.dosis_programadas, 0);
    const totalDosisOmitidas = adherenceData.reduce((acc, day) => acc + (day.dosis_omitidas || 0), 0);

    // Preparar estadísticas para mostrar
    const statisticsData = [
        { 
            title: 'Pacientes Activos', 
            value: statsData.pacientes_activos.toString(), 
            change: '', 
            trend: 'up' as const 
        },
        { 
            title: 'Tratamientos', 
            value: statsData.tratamientos_activos.toString(), 
            change: '', 
            trend: 'up' as const 
        },
        { 
            title: 'Adherencia Media', 
            value: statsData.adherencia_media.toFixed(1) + '%', 
            change: '', 
            trend: statsData.adherencia_media >= 80 ? 'up' as const : 'down' as const
        },
        { 
            title: 'Alertas Pendientes', 
            value: statsData.alertas_pendientes.toString(), 
            change: '', 
            trend: statsData.alertas_pendientes === 0 ? 'up' as const : 'down' as const 
        },
    ];

    return ( // Return the Dashboard component
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Statistics Cards */}
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    {statisticsData.map((stat, index) => (
                        <Card key={index} className="p-4">
                            <div className="flex flex-col">
                                <span className="text-sm text-gray-500">{stat.title}</span>
                                <span className="text-2xl font-bold">{stat.value}</span>
                                {stat.change && (
                                    <div className="flex items-center gap-2">
                                        <Badge variant={stat.trend === 'up' ? 'default' : 'destructive'}>
                                            {stat.change}
                                        </Badge>
                                    </div>
                                )}
                            </div>
                        </Card>
                    ))}
                </div>

                {/* Charts Section */}
                <div className="grid gap-4 md:grid-cols-2">
                    {/* Enhanced Adherence Chart */}
                    <Card className="p-4">
                        {/* Header */}
                        <div className="mb-4 flex items-center justify-between">
                            <div>
                                <h3 className="text-lg font-semibold">Adherencia - Últimos 7 Días</h3>
                                <div className="flex items-center gap-4 text-sm text-muted-foreground">
                                    <span>Promedio: {adherenciaPromedio.toFixed(1)}%</span>
                                    {getTrendIcon(adherenciaPromedio)}
                                </div>
                            </div>
                            {/* <Button
                                variant="outline"
                                size="sm"
                                onClick={refreshData}
                                disabled={isLoading}
                            >
                                <RefreshCw className={`mr-2 h-4 w-4 ${isLoading ? 'animate-spin' : ''}`} />
                                Refresh
                            </Button> */}
                        </div>

                        {/* Estadísticas Rápidas */}
                        <div className="grid grid-cols-3 gap-2 mb-4 text-xs">
                            <div className="bg-green-50 p-2 rounded">
                                <div className="font-semibold text-green-700">{totalDosisAdministradas}</div>
                                <div className="text-green-600">Administradas</div>
                            </div>
                            <div className="bg-blue-50 p-2 rounded">
                                <div className="font-semibold text-blue-700">{totalDosisProgramadas}</div>
                                <div className="text-blue-600">Programadas</div>
                            </div>
                            <div className="bg-red-50 p-2 rounded">
                                <div className="font-semibold text-red-700">{totalDosisOmitidas}</div>
                                <div className="text-red-600">Omitidas</div>
                            </div>
                        </div>
                        
                        {/* Enhanced Bar Chart for Adherence */}
                        <ChartContainer config={adherenceChartConfig} className="min-h-[200px] w-full">
                            <BarChart data={adherenceData}>
                                <ChartTooltip 
                                    content={({ active, payload, label }) => {
                                        if (active && payload && payload.length) {
                                            const data = payload[0].payload;
                                            const adherenciaColor = getAdherenceColor(data.adherencia);
                                            return (
                                                <div className="rounded-lg border bg-background p-3 shadow-lg">
                                                    <div className="font-semibold mb-2 flex items-center gap-2">
                                                        <span>{label}</span>
                                                        {getTrendIcon(data.adherencia)}
                                                    </div>
                                                    <div className="grid grid-cols-2 gap-3">
                                                        <div className="flex flex-col">
                                                            <span className="text-[0.70rem] uppercase text-muted-foreground">
                                                                Adherencia
                                                            </span>
                                                            <span className="font-bold text-lg" style={{ color: adherenciaColor }}>
                                                                {data.adherencia}%
                                                            </span>
                                                        </div>
                                                        <div className="flex flex-col">
                                                            <span className="text-[0.70rem] uppercase text-muted-foreground">
                                                                Estado
                                                            </span>
                                                            <span className="font-semibold text-sm">
                                                                {data.adherencia >= 90 ? "Excelente" : 
                                                                 data.adherencia >= 80 ? "Bueno" :
                                                                 data.adherencia >= 70 ? "Regular" : "Mejorar"}
                                                            </span>
                                                        </div>
                                                        <div className="flex flex-col">
                                                            <span className="text-[0.70rem] uppercase text-muted-foreground">
                                                                Administradas
                                                            </span>
                                                            <span className="font-bold text-green-700">
                                                                {data.dosis_administradas}
                                                            </span>
                                                        </div>
                                                        <div className="flex flex-col">
                                                            <span className="text-[0.70rem] uppercase text-muted-foreground">
                                                                Programadas
                                                            </span>
                                                            <span className="font-bold text-blue-700">
                                                                {data.dosis_programadas}
                                                            </span>
                                                        </div>
                                                        {data.dosis_omitidas !== undefined && data.dosis_omitidas > 0 && (
                                                            <div className="flex flex-col col-span-2">
                                                                <span className="text-[0.70rem] uppercase text-muted-foreground">
                                                                    Omitidas
                                                                </span>
                                                                <span className="font-bold text-red-700">
                                                                    {data.dosis_omitidas}
                                                                </span>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            );
                                        }
                                        return null;
                                    }} 
                                />
                                <XAxis
                                    dataKey="day"
                                    tickLine={false}
                                    tickMargin={10}
                                    axisLine={false}
                                />
                                <YAxis domain={[0, 100]} />
                                <Bar 
                                    dataKey="adherencia" 
                                    radius={4}
                                    name="Adherencia (%)"
                                >
                                    {adherenceData.map((entry, index) => (
                                        <Cell key={`cell-${index}`} fill={getAdherenceColor(entry.adherencia)} />
                                    ))}
                                </Bar>
                            </BarChart>
                        </ChartContainer>
                    </Card>

                    {/* Line Chart */}
                    <Card className="p-4">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold">Tendencia de Dosis</h3>
                            {/* <Button
                                variant="outline"
                                size="sm"
                                onClick={refreshData}
                                disabled={isLoading}
                            >
                                <RefreshCw className={`mr-2 h-4 w-4 ${isLoading ? 'animate-spin' : ''}`} />
                                Refresh
                            </Button> */}
                        </div>
                        <div className="h-[300px] w-full">
                            <ResponsiveContainer width="90%" height="100%">
                                <LineChart data={adherenceData}>
                                    <XAxis dataKey="day" />
                                    <YAxis />
                                    <Tooltip 
                                        formatter={(value, name) => [
                                            value,
                                            name === 'dosis_administradas' ? 'Administradas' : 'Programadas'
                                        ]}
                                        labelFormatter={(label) => `Día: ${label}`}
                                    />
                                    <Line 
                                        type="monotone" 
                                        dataKey="dosis_administradas" 
                                        stroke="hsl(142, 76%, 36%)" 
                                        strokeWidth={3}
                                        name="Dosis Administradas"
                                        dot={{ fill: "hsl(142, 76%, 36%)", strokeWidth: 2, r: 4 }}
                                    />
                                    <Line 
                                        type="monotone" 
                                        dataKey="dosis_programadas" 
                                        stroke="hsl(217, 91%, 59%)" 
                                        strokeWidth={3}
                                        strokeDasharray="5 5"
                                        name="Dosis Programadas"
                                        dot={{ fill: "hsl(217, 91%, 59%)", strokeWidth: 2, r: 4 }}
                                    />
                                </LineChart>
                            </ResponsiveContainer>
                        </div>
                    </Card>
                </div>

                {/* Recent Activity */}
                {/* <Card className="p-4">
                    <h3 className="mb-4 text-lg font-semibold">Actividad Reciente</h3>
                    <div className="space-y-4">
                        {recentActivity.length > 0 ? (
                            recentActivity.map((activity) => (
                                <div key={activity.id} className="flex items-center justify-between border-b pb-2">
                                    <div>
                                        <span className="font-medium">{activity.user}</span>
                                        <span className="text-gray-500"> {activity.action}</span>
                                    </div>
                                    <span className="text-sm text-gray-500">{activity.time}</span>
                                </div>
                            ))
                        ) : (
                            <div className="text-center text-gray-500 py-4">
                                No hay actividad reciente
                            </div>
                        )}
                    </div>
                </Card> */}
            </div>
        </AppLayout>
    );
}
