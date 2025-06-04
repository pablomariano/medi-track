import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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

interface User {
    id: number;
    name: string;
    email: string;
}

interface Props {
    usuarios: User[];
}

export default function Create({ usuarios }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        usuario_id: '',
        certificaciones: '',
        experiencia_anos: '',
        disponibilidad_horaria: '',
        tarifa_hora: '',
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post(route('cuidadores.store'));
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
                        <h1 className="text-2xl font-bold">Nuevo Cuidador</h1>
                        <p className="text-muted-foreground">
                            Agrega un nuevo cuidador al sistema
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Información del Cuidador</CardTitle>
                        <CardDescription>
                            Ingresa los detalles del cuidador
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="usuario_id">Usuario</Label>
                                <Select value={data.usuario_id} onValueChange={(value) => setData('usuario_id', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona un usuario" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {usuarios.map((usuario) => (
                                            <SelectItem key={usuario.id} value={usuario.id.toString()}>
                                                {usuario.name} - {usuario.email}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.usuario_id && (
                                    <p className="text-sm text-destructive">{errors.usuario_id}</p>
                                )}
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
                                    Guardar
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 