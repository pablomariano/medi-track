import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Calendar, Clock, CheckCircle, XCircle, AlertCircle, Plus, Eye } from 'lucide-react';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';

interface Administracion {
    id: number;
    fecha_hora_programada: string;
    fecha_hora_administrada: string | null;
    dosis_administrada: number;
    unidad_dosis: string;
    estado: 'pendiente' | 'administrado' | 'omitido';
    observaciones: string | null;
    efectos_observados: string | null;
    motivo_no_administracion: string | null;
    medicamento: {
        id: number;
        nombre: string;
        principio_activo: string;
    };
    tratamiento: {
        id: number;
        nombre: string;
        tipo: string;
    };
}

interface Cronograma {
    pendiente: Administracion[];
    administrado: Administracion[];
    omitido: Administracion[];
}

interface Estadisticas {
    total: number;
    administradas: number;
    pendientes: number;
    omitidas: number;
    cumplimiento: number;
}

interface Props {
    paciente: {
        id: number;
        nombre: string;
        apellido: string;
    };
    cronograma: Cronograma;
    fecha: string;
    estadisticas: Estadisticas;
    fechas_disponibles: string[];
}

export default function CronogramaIndex({ paciente, cronograma, fecha, estadisticas, fechas_disponibles }: Props) {
    const [selectedAdministracion, setSelectedAdministracion] = useState<Administracion | null>(null);
    const [dialogType, setDialogType] = useState<'administrar' | 'omitir' | 'ver' | null>(null);
    const [observaciones, setObservaciones] = useState('');
    const [efectosObservados, setEfectosObservados] = useState('');
    const [motivoOmision, setMotivoOmision] = useState('');

    const formatearHora = (fechaHora: string) => {
        return new Date(fechaHora).toLocaleTimeString('es-ES', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    };

    const obtenerColorEstado = (estado: string) => {
        switch (estado) {
            case 'administrado':
                return 'bg-green-100 text-green-800 border-green-200';
            case 'pendiente':
                return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            case 'omitido':
                return 'bg-red-100 text-red-800 border-red-200';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-200';
        }
    };

    const obtenerIconoEstado = (estado: string) => {
        switch (estado) {
            case 'administrado':
                return <CheckCircle className="h-5 w-5 text-green-600" />;
            case 'pendiente':
                return <Clock className="h-5 w-5 text-yellow-600" />;
            case 'omitido':
                return <XCircle className="h-5 w-5 text-red-600" />;
            default:
                return <AlertCircle className="h-5 w-5 text-gray-600" />;
        }
    };

    const manejarAdministracion = (tipo: 'administrar' | 'omitir', administracion: Administracion) => {
        setSelectedAdministracion(administracion);
        setDialogType(tipo);
        setObservaciones('');
        setEfectosObservados('');
        setMotivoOmision('');
    };

    const confirmarAccion = () => {
        if (!selectedAdministracion) return;

        const data: any = {};

        if (dialogType === 'administrar') {
            data.observaciones = observaciones;
            data.efectos_observados = efectosObservados;
            
            router.patch(`/cronograma/${selectedAdministracion.id}/administrar`, data, {
                onSuccess: () => {
                    setDialogType(null);
                    setSelectedAdministracion(null);
                }
            });
        } else if (dialogType === 'omitir') {
            data.motivo_no_administracion = motivoOmision;
            data.observaciones = observaciones;
            
            router.patch(`/cronograma/${selectedAdministracion.id}/omitir`, data, {
                onSuccess: () => {
                    setDialogType(null);
                    setSelectedAdministracion(null);
                }
            });
        }
    };

    const cambiarFecha = (nuevaFecha: string) => {
        router.get('/cronograma', { fecha: nuevaFecha, paciente_id: paciente.id });
    };

    const todasLasAdministraciones = [
        ...cronograma.pendiente,
        ...cronograma.administrado,
        ...cronograma.omitido
    ].sort((a, b) => new Date(a.fecha_hora_programada).getTime() - new Date(b.fecha_hora_programada).getTime());

    return (
        <AppLayout>
            <Head title="Cronograma de Medicamentos" />

            <div className="p-6 space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h2 className="text-xl font-semibold text-gray-800">
                            Cronograma de Medicamentos
                        </h2>
                        <p className="text-sm text-gray-600">
                            {paciente.nombre} {paciente.apellido} - {new Date(fecha).toLocaleDateString('es-ES', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            })}
                        </p>
                    </div>
                    <div className="flex items-center space-x-4">
                        <Select value={fecha} onValueChange={cambiarFecha}>
                            <SelectTrigger className="w-48">
                                <Calendar className="h-4 w-4 mr-2" />
                                <SelectValue placeholder="Seleccionar fecha" />
                            </SelectTrigger>
                            <SelectContent>
                                {fechas_disponibles.map((fechaDisponible) => (
                                    <SelectItem key={fechaDisponible} value={fechaDisponible}>
                                        {new Date(fechaDisponible).toLocaleDateString('es-ES')}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Button
                            variant="outline"
                            onClick={() => router.get('/cronograma/resumen-semanal', { paciente_id: paciente.id })}
                        >
                            <Eye className="h-4 w-4 mr-2" />
                            Resumen Semanal
                        </Button>
                    </div>
                </div>
                {/* Estadísticas del día */}
                <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-2xl font-bold">{estadisticas.total}</div>
                            <p className="text-xs text-muted-foreground">Total del día</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-2xl font-bold text-green-600">{estadisticas.administradas}</div>
                            <p className="text-xs text-muted-foreground">Administradas</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-2xl font-bold text-yellow-600">{estadisticas.pendientes}</div>
                            <p className="text-xs text-muted-foreground">Pendientes</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-2xl font-bold text-red-600">{estadisticas.omitidas}</div>
                            <p className="text-xs text-muted-foreground">Omitidas</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="pt-6">
                            <div className="text-2xl font-bold text-blue-600">{estadisticas.cumplimiento}%</div>
                            <p className="text-xs text-muted-foreground">Cumplimiento</p>
                        </CardContent>
                    </Card>
                </div>

                {/* Cronograma de medicamentos */}
                <Card>
                    <CardHeader>
                        <CardTitle>Horarios del Día</CardTitle>
                        <CardDescription>
                            Administraciones programadas para hoy
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {todasLasAdministraciones.length === 0 ? (
                            <div className="text-center py-8">
                                <AlertCircle className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                                <p className="text-gray-500">No hay medicamentos programados para este día</p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {todasLasAdministraciones.map((administracion) => (
                                    <div
                                        key={administracion.id}
                                        className="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50"
                                    >
                                        <div className="flex items-center space-x-4">
                                            {obtenerIconoEstado(administracion.estado)}
                                            <div>
                                                <div className="font-medium">
                                                    {formatearHora(administracion.fecha_hora_programada)}
                                                </div>
                                                <div className="text-sm text-gray-600">
                                                    {administracion.medicamento.nombre}
                                                </div>
                                                <div className="text-xs text-gray-500">
                                                    {administracion.dosis_administrada} {administracion.unidad_dosis}
                                                </div>
                                            </div>
                                        </div>

                                        <div className="flex items-center space-x-2">
                                            <Badge className={obtenerColorEstado(administracion.estado)}>
                                                {administracion.estado.charAt(0).toUpperCase() + administracion.estado.slice(1)}
                                            </Badge>

                                            {administracion.estado === 'pendiente' && (
                                                <div className="flex space-x-2">
                                                    <Button
                                                        size="sm"
                                                        onClick={() => manejarAdministracion('administrar', administracion)}
                                                    >
                                                        <CheckCircle className="h-4 w-4 mr-1" />
                                                        Tomar
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => manejarAdministracion('omitir', administracion)}
                                                    >
                                                        <XCircle className="h-4 w-4 mr-1" />
                                                        Omitir
                                                    </Button>
                                                </div>
                                            )}

                                            {administracion.estado !== 'pendiente' && (
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    onClick={() => {
                                                        setSelectedAdministracion(administracion);
                                                        setDialogType('ver');
                                                    }}
                                                >
                                                    <Eye className="h-4 w-4" />
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>

            {/* Dialog para administrar medicamento */}
            <Dialog open={dialogType === 'administrar'} onOpenChange={() => setDialogType(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Confirmar Administración</DialogTitle>
                        <DialogDescription>
                            ¿Confirmas que has tomado {selectedAdministracion?.medicamento.nombre}?
                        </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-4">
                        <div>
                            <Label htmlFor="observaciones">Observaciones (opcional)</Label>
                            <Textarea
                                id="observaciones"
                                placeholder="¿Cómo te sientes? ¿Alguna observación?"
                                value={observaciones}
                                onChange={(e) => setObservaciones(e.target.value)}
                            />
                        </div>
                        <div>
                            <Label htmlFor="efectos">Efectos observados (opcional)</Label>
                            <Textarea
                                id="efectos"
                                placeholder="¿Experimentaste algún efecto secundario?"
                                value={efectosObservados}
                                onChange={(e) => setEfectosObservados(e.target.value)}
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDialogType(null)}>
                            Cancelar
                        </Button>
                        <Button onClick={confirmarAccion}>
                            Confirmar Administración
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Dialog para omitir medicamento */}
            <Dialog open={dialogType === 'omitir'} onOpenChange={() => setDialogType(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Omitir Medicamento</DialogTitle>
                        <DialogDescription>
                            Indica el motivo por el cual no tomaste {selectedAdministracion?.medicamento.nombre}
                        </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-4">
                        <div>
                            <Label htmlFor="motivo">Motivo de omisión *</Label>
                            <Select value={motivoOmision} onValueChange={setMotivoOmision}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Seleccionar motivo" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="olvido">Se me olvidó</SelectItem>
                                    <SelectItem value="efectos_secundarios">Efectos secundarios</SelectItem>
                                    <SelectItem value="no_disponible">Medicamento no disponible</SelectItem>
                                    <SelectItem value="enfermedad">Me siento mal/enfermo</SelectItem>
                                    <SelectItem value="decision_propia">Decisión propia</SelectItem>
                                    <SelectItem value="otro">Otro motivo</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <Label htmlFor="observaciones_omision">Observaciones adicionales</Label>
                            <Textarea
                                id="observaciones_omision"
                                placeholder="Detalles adicionales..."
                                value={observaciones}
                                onChange={(e) => setObservaciones(e.target.value)}
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDialogType(null)}>
                            Cancelar
                        </Button>
                        <Button 
                            variant="destructive" 
                            onClick={confirmarAccion}
                            disabled={!motivoOmision}
                        >
                            Confirmar Omisión
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Dialog para ver detalles */}
            <Dialog open={dialogType === 'ver'} onOpenChange={() => setDialogType(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Detalles de Administración</DialogTitle>
                        <DialogDescription>
                            {selectedAdministracion?.medicamento.nombre}
                        </DialogDescription>
                    </DialogHeader>
                    <div className="space-y-4">
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <Label>Hora programada</Label>
                                <p>{selectedAdministracion && formatearHora(selectedAdministracion.fecha_hora_programada)}</p>
                            </div>
                            <div>
                                <Label>Estado</Label>
                                <Badge className={selectedAdministracion && obtenerColorEstado(selectedAdministracion.estado)}>
                                    {selectedAdministracion?.estado}
                                </Badge>
                            </div>
                        </div>
                        
                        {selectedAdministracion?.fecha_hora_administrada && (
                            <div>
                                <Label>Hora de administración</Label>
                                <p>{formatearHora(selectedAdministracion.fecha_hora_administrada)}</p>
                            </div>
                        )}

                        {selectedAdministracion?.observaciones && (
                            <div>
                                <Label>Observaciones</Label>
                                <p className="text-sm">{selectedAdministracion.observaciones}</p>
                            </div>
                        )}

                        {selectedAdministracion?.efectos_observados && (
                            <div>
                                <Label>Efectos observados</Label>
                                <p className="text-sm">{selectedAdministracion.efectos_observados}</p>
                            </div>
                        )}

                        {selectedAdministracion?.motivo_no_administracion && (
                            <div>
                                <Label>Motivo de omisión</Label>
                                <p className="text-sm">{selectedAdministracion.motivo_no_administracion}</p>
                            </div>
                        )}
                    </div>
                    <DialogFooter>
                        <Button onClick={() => setDialogType(null)}>
                            Cerrar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
} 