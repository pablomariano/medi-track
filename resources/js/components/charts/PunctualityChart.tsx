import React, { useMemo } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { Badge } from '@/components/ui/badge';
import { 
    BarChart, 
    Bar, 
    PieChart, 
    Pie, 
    Cell, 
    LineChart, 
    Line, 
    XAxis, 
    YAxis, 
    CartesianGrid, 
    Tooltip, 
    ResponsiveContainer,
    Legend
} from 'recharts';
import { 
    Clock, 
    Target, 
    TrendingUp, 
    AlertTriangle, 
    CheckCircle,
    Timer,
    Activity
} from 'lucide-react';

interface TemporalMetrics {
    total_administraciones: number;
    puntualidad_promedio: number;
    dosis_puntuales: number;
    dosis_tempranas: number;
    dosis_tardias: number;
    tiempo_promedio_adelanto: number;
    tiempo_promedio_retraso: number;
    variabilidad_horaria: number;
    distribucion_por_horas: number[];
    patrones_semanales: Record<string, { count: number; avg_score: number }>;
    categorias_detalle: {
        muy_temprano: number;
        temprano: number;
        puntual: number;
        tardio: number;
        muy_tardio: number;
    };
}

interface PunctualityChartProps {
    metrics: TemporalMetrics;
    title?: string;
    showDetails?: boolean;
    className?: string;
}

// Colores para las categorías temporales
const TEMPORAL_COLORS = {
    muy_temprano: '#dc2626', // red-600
    temprano: '#f59e0b',     // amber-500
    puntual: '#10b981',      // emerald-500
    tardio: '#f59e0b',       // amber-500
    muy_tardio: '#dc2626',   // red-600
};

const TEMPORAL_LABELS = {
    muy_temprano: 'Muy Temprano',
    temprano: 'Temprano',
    puntual: 'Puntual',
    tardio: 'Tardío',
    muy_tardio: 'Muy Tardío',
};

export default function PunctualityChart({ 
    metrics, 
    title = "Métricas de Puntualidad", 
    showDetails = true,
    className = ""
}: PunctualityChartProps) {
    
    // Calcular porcentajes para las métricas
    const percentages = useMemo(() => {
        const total = metrics.total_administraciones;
        if (total === 0) return { puntuales: 0, tempranas: 0, tardias: 0 };
        
        return {
            puntuales: Math.round((metrics.dosis_puntuales / total) * 100),
            tempranas: Math.round((metrics.dosis_tempranas / total) * 100),
            tardias: Math.round((metrics.dosis_tardias / total) * 100),
        };
    }, [metrics]);

    // Preparar datos para el gráfico de distribución temporal
    const distributionData = useMemo(() => {
        return Object.entries(metrics.categorias_detalle).map(([categoria, count]) => ({
            name: TEMPORAL_LABELS[categoria as keyof typeof TEMPORAL_LABELS],
            value: count,
            color: TEMPORAL_COLORS[categoria as keyof typeof TEMPORAL_COLORS],
            percentage: metrics.total_administraciones > 0 
                ? Math.round((count / metrics.total_administraciones) * 100) 
                : 0
        }));
    }, [metrics.categorias_detalle, metrics.total_administraciones]);

    // Preparar datos para el gráfico de distribución horaria
    const hourlyData = useMemo(() => {
        return metrics.distribucion_por_horas.map((count, hour) => ({
            hour: `${hour}:00`,
            administraciones: count,
            hourNum: hour
        })).filter(item => item.administraciones > 0);
    }, [metrics.distribucion_por_horas]);

    // Preparar datos para patrones semanales
    const weeklyData = useMemo(() => {
        const daysOrder = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        return daysOrder.map(day => ({
            day,
            count: metrics.patrones_semanales[day]?.count || 0,
            avg_score: metrics.patrones_semanales[day]?.avg_score || 0
        }));
    }, [metrics.patrones_semanales]);

    // Determinar el nivel de consistencia
    const getConsistencyLevel = (variability: number) => {
        if (variability <= 10) return { level: 'Excelente', color: 'bg-green-500', icon: CheckCircle };
        if (variability <= 20) return { level: 'Buena', color: 'bg-blue-500', icon: Target };
        if (variability <= 35) return { level: 'Regular', color: 'bg-yellow-500', icon: Timer };
        return { level: 'Inconsistente', color: 'bg-red-500', icon: AlertTriangle };
    };

    const consistencyInfo = getConsistencyLevel(metrics.variabilidad_horaria);
    const ConsistencyIcon = consistencyInfo.icon;

    // Custom tooltip para los gráficos
    const CustomTooltip = ({ active, payload, label }: any) => {
        if (active && payload && payload.length) {
            return (
                <div className="bg-white p-3 border border-gray-200 rounded-lg shadow-lg">
                    <p className="font-medium">{label}</p>
                    {payload.map((entry: any, index: number) => (
                        <p key={index} style={{ color: entry.color }}>
                            {entry.dataKey}: {entry.value}
                            {entry.dataKey === 'avg_score' && ' pts'}
                        </p>
                    ))}
                </div>
            );
        }
        return null;
    };

    if (metrics.total_administraciones === 0) {
        return (
            <Card className={className}>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                        <Clock className="h-5 w-5" />
                        {title}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="text-center py-8 text-gray-500">
                        <Activity className="h-12 w-12 mx-auto mb-4 opacity-50" />
                        <p>No hay datos de administraciones para mostrar</p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <div className={`space-y-6 ${className}`}>
            {/* Tarjetas de resumen */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {/* Score de Puntualidad */}
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Score Puntualidad</p>
                                <p className="text-2xl font-bold text-blue-600">
                                    {metrics.puntualidad_promedio.toFixed(1)}
                                </p>
                                <p className="text-xs text-gray-500">de 100 puntos</p>
                            </div>
                            <Target className="h-8 w-8 text-blue-500" />
                        </div>
                        <Progress 
                            value={metrics.puntualidad_promedio} 
                            className="mt-2"
                        />
                    </CardContent>
                </Card>

                {/* Porcentaje Puntual */}
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Dosis Puntuales</p>
                                <p className="text-2xl font-bold text-green-600">
                                    {percentages.puntuales}%
                                </p>
                                <p className="text-xs text-gray-500">
                                    {metrics.dosis_puntuales} de {metrics.total_administraciones}
                                </p>
                            </div>
                            <CheckCircle className="h-8 w-8 text-green-500" />
                        </div>
                    </CardContent>
                </Card>

                {/* Promedio de Retraso */}
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Retraso Promedio</p>
                                <p className="text-2xl font-bold text-orange-600">
                                    {metrics.tiempo_promedio_retraso.toFixed(0)}m
                                </p>
                                <p className="text-xs text-gray-500">cuando hay retraso</p>
                            </div>
                            <Clock className="h-8 w-8 text-orange-500" />
                        </div>
                    </CardContent>
                </Card>

                {/* Consistencia */}
                <Card>
                    <CardContent className="p-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <p className="text-sm font-medium text-gray-600">Consistencia</p>
                                <Badge className={`${consistencyInfo.color} text-white mt-1`}>
                                    {consistencyInfo.level}
                                </Badge>
                                <p className="text-xs text-gray-500 mt-1">
                                    Variabilidad: {metrics.variabilidad_horaria.toFixed(1)}
                                </p>
                            </div>
                            <ConsistencyIcon className="h-8 w-8 text-gray-500" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            {showDetails && (
                <>
                    {/* Gráficos principales */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Distribución de Categorías Temporales */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg">Distribución Temporal</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <ResponsiveContainer width="100%" height={300}>
                                    <BarChart data={distributionData}>
                                        <CartesianGrid strokeDasharray="3 3" />
                                        <XAxis 
                                            dataKey="name" 
                                            fontSize={12}
                                            angle={-45}
                                            textAnchor="end"
                                            height={80}
                                        />
                                        <YAxis />
                                        <Tooltip content={CustomTooltip} />
                                        <Bar dataKey="value" radius={[4, 4, 0, 0]}>
                                            {distributionData.map((entry, index) => (
                                                <Cell key={`cell-${index}`} fill={entry.color} />
                                            ))}
                                        </Bar>
                                    </BarChart>
                                </ResponsiveContainer>
                            </CardContent>
                        </Card>

                        {/* Gráfico de Pastel */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg">Proporción Temporal</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <ResponsiveContainer width="100%" height={300}>
                                    <PieChart>
                                        <Pie
                                            data={distributionData.filter(d => d.value > 0)}
                                            cx="50%"
                                            cy="50%"
                                            innerRadius={60}
                                            outerRadius={100}
                                            paddingAngle={2}
                                            dataKey="value"
                                        >
                                            {distributionData.map((entry, index) => (
                                                <Cell key={`cell-${index}`} fill={entry.color} />
                                            ))}
                                        </Pie>
                                        <Tooltip 
                                            formatter={(value, name) => [`${value} dosis`, name]}
                                        />
                                        <Legend />
                                    </PieChart>
                                </ResponsiveContainer>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Análisis temporal detallado */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Distribución Horaria */}
                        {hourlyData.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg">Distribución por Hora</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <ResponsiveContainer width="100%" height={250}>
                                        <BarChart data={hourlyData}>
                                            <CartesianGrid strokeDasharray="3 3" />
                                            <XAxis dataKey="hour" fontSize={12} />
                                            <YAxis />
                                            <Tooltip content={CustomTooltip} />
                                            <Bar 
                                                dataKey="administraciones" 
                                                fill="#3b82f6" 
                                                radius={[2, 2, 0, 0]}
                                            />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </CardContent>
                            </Card>
                        )}

                        {/* Patrones Semanales */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg">Patrones Semanales</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <ResponsiveContainer width="100%" height={250}>
                                    <LineChart data={weeklyData}>
                                        <CartesianGrid strokeDasharray="3 3" />
                                        <XAxis 
                                            dataKey="day" 
                                            fontSize={12}
                                            angle={-45}
                                            textAnchor="end"
                                            height={60}
                                        />
                                        <YAxis yAxisId="left" />
                                        <YAxis yAxisId="right" orientation="right" />
                                        <Tooltip content={CustomTooltip} />
                                        <Bar 
                                            yAxisId="left"
                                            dataKey="count" 
                                            fill="#8b5cf6" 
                                            radius={[2, 2, 0, 0]}
                                            name="Cantidad"
                                        />
                                        <Line 
                                            yAxisId="right"
                                            type="monotone" 
                                            dataKey="avg_score" 
                                            stroke="#f59e0b" 
                                            strokeWidth={3}
                                            name="Score Promedio"
                                        />
                                    </LineChart>
                                </ResponsiveContainer>
                            </CardContent>
                        </Card>
                    </div>
                </>
            )}
        </div>
    );
} 