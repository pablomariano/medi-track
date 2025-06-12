import React from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Calendar, TrendingUp, TrendingDown, ArrowLeft, BarChart3 } from 'lucide-react';

interface ResumenDia {
    fecha: string;
    dia_semana: string;
    total: number;
    administradas: number;
    omitidas: number;
    pendientes: number;
}

interface ResumenTotales {
    administradas: number;
    omitidas: number;
    pendientes: number;
    total: number;
}

interface Resumen {
    semana: Record<string, ResumenDia>;
    totales: ResumenTotales;
}

interface Props {
    resumen: Resumen;
    fecha_inicio: string;
}

export default function ResumenSemanal({ resumen, fecha_inicio }: Props) {
    const calcularPorcentajeCumplimiento = (administradas: number, total: number) => {
        return total > 0 ? Math.round((administradas / total) * 100) : 0;
    };

    const obtenerColorCumplimiento = (porcentaje: number) => {
        if (porcentaje >= 90) return 'text-green-600';
        if (porcentaje >= 70) return 'text-yellow-600';
        return 'text-red-600';
    };

    const obtenerColorBarra = (porcentaje: number) => {
        if (porcentaje >= 90) return 'bg-green-500';
        if (porcentaje >= 70) return 'bg-yellow-500';
        return 'bg-red-500';
    };

    const diasSemana = Object.values(resumen.semana).sort(
        (a, b) => new Date(a.fecha).getTime() - new Date(b.fecha).getTime()
    );

    const porcentajeTotalCumplimiento = calcularPorcentajeCumplimiento(
        resumen.totales.administradas,
        resumen.totales.total
    );

    const promedioDiario = resumen.totales.total / 7;

    return (
        <AppLayout>
            <Head title="Resumen Semanal - Cronograma" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h2 className="text-xl font-semibold text-gray-800">
                            Resumen Semanal de Cumplimiento
                        </h2>
                        <p className="text-sm text-gray-600">
                            Semana del {new Date(fecha_inicio).toLocaleDateString('es-ES')} al{' '}
                            {new Date(new Date(fecha_inicio).getTime() + 6 * 24 * 60 * 60 * 1000).toLocaleDateString('es-ES')}
                        </p>
                    </div>
                    <div className="flex items-center space-x-4">
                        <Button
                            variant="outline"
                            onClick={() => router.get('/cronograma')}
                        >
                            <ArrowLeft className="h-4 w-4 mr-2" />
                            Volver al Cronograma
                        </Button>
                    </div>
                </div>
                {/* Resumen general */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-2xl font-bold">{resumen.totales.total}</div>
                            <p className="text-xs text-muted-foreground">Total de dosis</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-2xl font-bold text-green-600">{resumen.totales.administradas}</div>
                            <p className="text-xs text-muted-foreground">Administradas</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-2xl font-bold text-red-600">{resumen.totales.omitidas}</div>
                            <p className="text-xs text-muted-foreground">Omitidas</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6">
                            <div className={`text-2xl font-bold ${obtenerColorCumplimiento(porcentajeTotalCumplimiento)}`}>
                                {porcentajeTotalCumplimiento}%
                            </div>
                            <p className="text-xs text-muted-foreground">Cumplimiento</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Barra de progreso general */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center">
                            <BarChart3 className="h-5 w-5 mr-2" />
                            Progreso Semanal
                        </CardTitle>
                        <CardDescription>
                            Cumplimiento general de medicamentos esta semana
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            <div className="flex justify-between text-sm">
                                <span>Administradas: {resumen.totales.administradas}</span>
                                <span>Total: {resumen.totales.total}</span>
                            </div>
                            <Progress 
                                value={porcentajeTotalCumplimiento} 
                                className="h-2"
                            />
                            <div className="flex justify-between text-xs text-gray-500">
                                <span>0%</span>
                                <span className={obtenerColorCumplimiento(porcentajeTotalCumplimiento)}>
                                    {porcentajeTotalCumplimiento}%
                                </span>
                                <span>100%</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Desglose por día */}
                <Card>
                    <CardHeader>
                        <CardTitle>Desglose Diario</CardTitle>
                        <CardDescription>
                            Cumplimiento de medicamentos por cada día de la semana
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {diasSemana.map((dia) => {
                                const porcentajeDia = calcularPorcentajeCumplimiento(dia.administradas, dia.total);
                                
                                return (
                                    <div key={dia.fecha} className="border rounded-lg p-4">
                                        <div className="flex justify-between items-center mb-2">
                                            <div>
                                                <h3 className="font-medium capitalize">{dia.dia_semana}</h3>
                                                <p className="text-sm text-gray-500">
                                                    {new Date(dia.fecha).toLocaleDateString('es-ES')}
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <div className={`text-lg font-bold ${obtenerColorCumplimiento(porcentajeDia)}`}>
                                                    {porcentajeDia}%
                                                </div>
                                                <div className="text-xs text-gray-500">
                                                    {dia.administradas}/{dia.total}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div className="grid grid-cols-3 gap-2 mb-3">
                                            <div className="text-center">
                                                <div className="text-green-600 font-semibold">{dia.administradas}</div>
                                                <div className="text-xs text-gray-500">Administradas</div>
                                            </div>
                                            <div className="text-center">
                                                <div className="text-red-600 font-semibold">{dia.omitidas}</div>
                                                <div className="text-xs text-gray-500">Omitidas</div>
                                            </div>
                                            <div className="text-center">
                                                <div className="text-yellow-600 font-semibold">{dia.pendientes}</div>
                                                <div className="text-xs text-gray-500">Pendientes</div>
                                            </div>
                                        </div>

                                        {dia.total > 0 && (
                                            <Progress 
                                                value={porcentajeDia} 
                                                className="h-2"
                                            />
                                        )}
                                        
                                        {dia.total === 0 && (
                                            <div className="text-center text-gray-400 text-sm py-2">
                                                Sin medicamentos programados
                                            </div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </CardContent>
                </Card>

                {/* Análisis y recomendaciones */}
                <Card>
                    <CardHeader>
                        <CardTitle>Análisis de Cumplimiento</CardTitle>
                        <CardDescription>
                            Insights sobre tu adherencia al tratamiento
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {/* Evaluación general */}
                            <div className="p-4 rounded-lg bg-gray-50">
                                <h4 className="font-medium mb-2">Evaluación General</h4>
                                {porcentajeTotalCumplimiento >= 90 && (
                                    <div className="flex items-center text-green-700">
                                        <TrendingUp className="h-4 w-4 mr-2" />
                                        <span>¡Excelente cumplimiento! Mantén esta constancia.</span>
                                    </div>
                                )}
                                {porcentajeTotalCumplimiento >= 70 && porcentajeTotalCumplimiento < 90 && (
                                    <div className="flex items-center text-yellow-700">
                                        <BarChart3 className="h-4 w-4 mr-2" />
                                        <span>Buen cumplimiento, pero puedes mejorarlo un poco más.</span>
                                    </div>
                                )}
                                {porcentajeTotalCumplimiento < 70 && (
                                    <div className="flex items-center text-red-700">
                                        <TrendingDown className="h-4 w-4 mr-2" />
                                        <span>Necesitas mejorar tu adherencia al tratamiento.</span>
                                    </div>
                                )}
                            </div>

                            {/* Estadísticas adicionales */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="p-4 border rounded-lg">
                                    <h5 className="font-medium text-sm text-gray-600 mb-1">Promedio diario</h5>
                                    <div className="text-xl font-bold">{promedioDiario.toFixed(1)}</div>
                                    <div className="text-xs text-gray-500">dosis por día</div>
                                </div>
                                <div className="p-4 border rounded-lg">
                                    <h5 className="font-medium text-sm text-gray-600 mb-1">Días con 100% cumplimiento</h5>
                                    <div className="text-xl font-bold">
                                        {diasSemana.filter(dia => 
                                            dia.total > 0 && calcularPorcentajeCumplimiento(dia.administradas, dia.total) === 100
                                        ).length}
                                    </div>
                                    <div className="text-xs text-gray-500">de 7 días</div>
                                </div>
                            </div>

                            {/* Recomendaciones */}
                            {resumen.totales.omitidas > 0 && (
                                <div className="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <h5 className="font-medium text-yellow-800 mb-2">💡 Recomendaciones</h5>
                                    <ul className="text-sm text-yellow-700 space-y-1">
                                        <li>• Configura alarmas en tu teléfono para recordar las dosis</li>
                                        <li>• Usa un pastillero semanal para organizar tus medicamentos</li>
                                        <li>• Habla con tu médico si tienes efectos secundarios</li>
                                        <li>• Lleva un diario de síntomas para evaluar la efectividad</li>
                                    </ul>
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
} 