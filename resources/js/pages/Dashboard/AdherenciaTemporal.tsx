import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Clock, TrendingUp, Users, Activity, Target, AlertTriangle } from 'lucide-react';
import PunctualityChart from '@/components/charts/PunctualityChart';
import PatientTrendsLineChart from '@/components/charts/PatientTrendsLineChart';

interface Paciente {
    id: number;
    nombre: string;
    email: string;
    tratamientos_activos: number;
}

interface MetricasGenerales {
    total_administraciones: number;
    score_promedio: number;
    porcentaje_puntuales: number;
    tiempo_promedio_retraso: number;
    tiempo_promedio_adelanto: number;
    variabilidad_sistema: number;
    distribucion_categorias: {
        muy_temprano: number;
        temprano: number;
        puntual: number;
        tardio: number;
        muy_tardio: number;
    };
}

interface Props {
    pacientes: Paciente[];
    metricas: MetricasGenerales;
}

export default function AdherenciaTemporal({ pacientes, metricas }: Props) {
    const [selectedPaciente, setSelectedPaciente] = useState<string>('general');

    const getScoreColor = (score: number): string => {
        if (score >= 90) return 'text-green-600';
        if (score >= 80) return 'text-yellow-600';
        if (score >= 70) return 'text-orange-600';
        return 'text-red-600';
    };

    const getScoreBadgeVariant = (score: number) => {
        if (score >= 90) return 'default';
        if (score >= 80) return 'secondary';
        if (score >= 70) return 'outline';
        return 'destructive';
    };

    return (
        <AppLayout>
            <Head title="Dashboard de Adherencia Temporal" />
            
            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Dashboard de Adherencia Temporal</h1>
                        <p className="text-muted-foreground">
                            Análisis avanzado de puntualidad en la administración de medicamentos
                        </p>
                    </div>
                    <div className="flex items-center gap-4">
                        <Select value={selectedPaciente} onValueChange={setSelectedPaciente}>
                            <SelectTrigger className="w-[280px]">
                                <SelectValue placeholder="Seleccionar paciente para análisis detallado" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="general">Resumen general</SelectItem>
                                {pacientes.map((paciente) => (
                                    <SelectItem key={paciente.id} value={paciente.id.toString()}>
                                        {paciente.nombre}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                {/* Métricas Generales */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Score Promedio</CardTitle>
                            <Target className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className={`text-2xl font-bold ${getScoreColor(metricas.score_promedio)}`}>
                                {metricas.score_promedio}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                De {metricas.total_administraciones} administraciones
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Puntualidad</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">
                                {metricas.porcentaje_puntuales}%
                            </div>
                            <p className="text-xs text-muted-foreground">
                                {metricas.distribucion_categorias.puntual} dosis puntuales
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Retraso Promedio</CardTitle>
                            <AlertTriangle className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-red-600">
                                {metricas.tiempo_promedio_retraso}min
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Adelanto: {metricas.tiempo_promedio_adelanto}min
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Variabilidad</CardTitle>
                            <Activity className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">
                                {metricas.variabilidad_sistema}
                            </div>
                            <p className="text-xs text-muted-foreground">
                                Desviación estándar
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Distribución de Categorías */}
                <Card>
                    <CardHeader>
                        <CardTitle>Distribución Temporal</CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Clasificación de administraciones por puntualidad
                        </p>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-5">
                            <div className="text-center p-4 border rounded-lg bg-red-50">
                                <div className="text-2xl font-bold text-red-600">
                                    {metricas.distribucion_categorias.muy_temprano}
                                </div>
                                <Badge variant="destructive" className="mt-2">
                                    Muy Temprano
                                </Badge>
                            </div>
                            <div className="text-center p-4 border rounded-lg bg-orange-50">
                                <div className="text-2xl font-bold text-orange-600">
                                    {metricas.distribucion_categorias.temprano}
                                </div>
                                <Badge variant="outline" className="mt-2">
                                    Temprano
                                </Badge>
                            </div>
                            <div className="text-center p-4 border rounded-lg bg-green-50">
                                <div className="text-2xl font-bold text-green-600">
                                    {metricas.distribucion_categorias.puntual}
                                </div>
                                <Badge variant="default" className="mt-2">
                                    Puntual
                                </Badge>
                            </div>
                            <div className="text-center p-4 border rounded-lg bg-yellow-50">
                                <div className="text-2xl font-bold text-yellow-600">
                                    {metricas.distribucion_categorias.tardio}
                                </div>
                                <Badge variant="secondary" className="mt-2">
                                    Tardío
                                </Badge>
                            </div>
                            <div className="text-center p-4 border rounded-lg bg-red-50">
                                <div className="text-2xl font-bold text-red-600">
                                    {metricas.distribucion_categorias.muy_tardio}
                                </div>
                                <Badge variant="destructive" className="mt-2">
                                    Muy Tardío
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Lista de Pacientes */}
                <Card>
                    <CardHeader>
                        <CardTitle>Pacientes con Tratamientos Activos</CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Selecciona un paciente para ver análisis detallado
                        </p>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            {pacientes.map((paciente) => (
                                <div 
                                    key={paciente.id} 
                                    className="p-4 border rounded-lg hover:bg-muted/50 cursor-pointer transition-colors"
                                    onClick={() => setSelectedPaciente(paciente.id.toString())}
                                >
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <h3 className="font-medium">{paciente.nombre}</h3>
                                            <p className="text-sm text-muted-foreground">{paciente.email}</p>
                                        </div>
                                        <Badge variant="outline">
                                            {paciente.tratamientos_activos} tratamientos
                                        </Badge>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>

                {/* Análisis Detallado de Paciente */}
                {selectedPaciente && selectedPaciente !== 'general' && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Análisis Detallado - Paciente Seleccionado</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                Para ver el análisis completo del paciente, use el API endpoint: 
                                <code className="ml-2 px-2 py-1 bg-gray-100 rounded text-xs">
                                    /api/temporal-adherence/patient/{selectedPaciente}/metrics
                                </code>
                            </p>
                        </CardHeader>
                        <CardContent>
                            <div className="text-center py-8 text-gray-500">
                                <Clock className="h-12 w-12 mx-auto mb-4 opacity-50" />
                                <p className="mb-2">Análisis detallado del paciente disponible</p>
                                <p className="text-sm">Use los endpoints de la API para obtener métricas específicas</p>
                                <div className="mt-4 space-y-2">
                                    <Badge variant="outline">Métricas: /api/temporal-adherence/patient/{selectedPaciente}/metrics</Badge>
                                    <Badge variant="outline">Tendencias: /api/temporal-adherence/patient/{selectedPaciente}/trends</Badge>
                                    <Badge variant="outline">Distribución: /api/temporal-adherence/patient/{selectedPaciente}/distribution</Badge>
                                </div>
                            </div>
                            {/* Gráfico de líneas de tendencias */}
                            <div className="mt-8">
                                <PatientTrendsLineChart 
                                    pacienteId={parseInt(selectedPaciente)}
                                    apiEndpoint={`/api/temporal-adherence/patient/${selectedPaciente}/trends`}
                                    theme={typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'}
                                />
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Información sobre métricas */}
                <Card className="bg-blue-50 border-blue-200">
                    <CardContent className="pt-6">
                        <div className="flex items-start gap-3">
                            <div className="p-2 bg-blue-100 rounded-full">
                                <TrendingUp className="h-5 w-5 text-blue-600" />
                            </div>
                            <div>
                                <h3 className="font-medium text-blue-900 mb-1">Sistema de Adherencia Temporal</h3>
                                <p className="text-sm text-blue-700 mb-2">
                                    Este dashboard mide la precisión temporal en la administración de medicamentos, 
                                    no solo el cumplimiento de dosis. Las métricas incluyen:
                                </p>
                                <ul className="text-xs text-blue-600 space-y-1">
                                    <li><strong>Score de Puntualidad:</strong> De 0-100, donde 100 es perfecto (±15 min)</li>
                                    <li><strong>Categorías Temporales:</strong> Muy temprano (&lt;-60min), Temprano (-60 a -15min), Puntual (±15min), Tardío (+15 a +60min), Muy tardío (&gt;+60min)</li>
                                    <li><strong>Variabilidad:</strong> Medida de consistencia en los horarios (menor = más consistente)</li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
} 