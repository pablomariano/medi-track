import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { ArrowLeft } from 'lucide-react';
import { Link } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

interface Props {
    cuidador: {
        usuario_id: number;
        certificaciones: string;
        experiencia_anos: number;
        disponibilidad_horaria: string;
        tarifa_hora: number;
        user: {
            id: number;
            name: string;
            email: string;
        } | null;
    };
}

export default function Edit({ cuidador }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        certificaciones: cuidador.certificaciones || '',
        experiencia_anos: cuidador.experiencia_anos?.toString() || '',
        disponibilidad_horaria: cuidador.disponibilidad_horaria || '',
        tarifa_hora: cuidador.tarifa_hora?.toString() || '',
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(route('cuidadores.update', cuidador.usuario_id));
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('cuidadores.index')}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Editar Cuidador</h1>
                        <p className="text-muted-foreground">
                            Modifica la información del cuidador
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Información del Cuidador</CardTitle>
                        <CardDescription>
                            Actualiza los detalles del cuidador - {cuidador.user ? cuidador.user.name : `Usuario ID: ${cuidador.usuario_id} (Usuario no encontrado)`}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label>Usuario Asignado</Label>
                                <div className="p-3 bg-gray-50 rounded-md">
                                    {cuidador.user ? (
                                        <>
                                            <p className="font-medium">{cuidador.user.name}</p>
                                            <p className="text-sm text-gray-600">{cuidador.user.email}</p>
                                        </>
                                    ) : (
                                        <>
                                            <p className="font-medium text-red-600">Usuario no encontrado</p>
                                            <p className="text-sm text-gray-600">ID de usuario: {cuidador.usuario_id}</p>
                                            <p className="text-xs text-red-500 mt-1">
                                                ⚠️ El usuario asociado a este cuidador no existe o fue eliminado
                                            </p>
                                        </>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="certificaciones">Certificaciones</Label>
                                <Textarea
                                    id="certificaciones"
                                    value={data.certificaciones}
                                    onChange={(e) => setData('certificaciones', e.target.value)}
                                    placeholder="Certificado en primeros auxilios, curso de cuidado de adultos mayores, etc."
                                    rows={3}
                                />
                                {errors.certificaciones && (
                                    <p className="text-sm text-destructive">{errors.certificaciones}</p>
                                )}
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="experiencia_anos">Años de Experiencia</Label>
                                    <Input
                                        id="experiencia_anos"
                                        type="number"
                                        min="0"
                                        value={data.experiencia_anos}
                                        onChange={(e) => setData('experiencia_anos', e.target.value)}
                                        placeholder="3"
                                    />
                                    {errors.experiencia_anos && (
                                        <p className="text-sm text-destructive">{errors.experiencia_anos}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="tarifa_hora">Tarifa por Hora (CLP)</Label>
                                    <Input
                                        id="tarifa_hora"
                                        type="number"
                                        min="0"
                                        value={data.tarifa_hora}
                                        onChange={(e) => setData('tarifa_hora', e.target.value)}
                                        placeholder="15000"
                                    />
                                    {errors.tarifa_hora && (
                                        <p className="text-sm text-destructive">{errors.tarifa_hora}</p>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="disponibilidad_horaria">Disponibilidad Horaria</Label>
                                <Input
                                    id="disponibilidad_horaria"
                                    value={data.disponibilidad_horaria}
                                    onChange={(e) => setData('disponibilidad_horaria', e.target.value)}
                                    placeholder="Lunes a Viernes 8:00-18:00, Fines de semana disponible"
                                />
                                {errors.disponibilidad_horaria && (
                                    <p className="text-sm text-destructive">{errors.disponibilidad_horaria}</p>
                                )}
                            </div>

                            <div className="flex justify-end">
                                <Button type="submit" disabled={processing}>
                                    Actualizar
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 