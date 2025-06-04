import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
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
        relacion_paciente: '',
        es_contacto_emergencia: false,
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post(route('apoderados.store'));
    };

    const relacionesOpciones = [
        { value: 'padre', label: 'Padre' },
        { value: 'madre', label: 'Madre' },
        { value: 'hermano', label: 'Hermano/a' },
        { value: 'abuelo', label: 'Abuelo/a' },
        { value: 'tutor', label: 'Tutor Legal' },
        { value: 'otro', label: 'Otro' },
    ];

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('apoderados.index')}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Nuevo Apoderado</h1>
                        <p className="text-muted-foreground">
                            Agrega un nuevo apoderado al sistema
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Información del Apoderado</CardTitle>
                        <CardDescription>
                            Ingresa los detalles del apoderado
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
                                <Label htmlFor="relacion_paciente">Relación con el Paciente</Label>
                                <Select value={data.relacion_paciente} onValueChange={(value) => setData('relacion_paciente', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecciona la relación" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {relacionesOpciones.map((relacion) => (
                                            <SelectItem key={relacion.value} value={relacion.value}>
                                                {relacion.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.relacion_paciente && (
                                    <p className="text-sm text-destructive">{errors.relacion_paciente}</p>
                                )}
                            </div>

                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="es_contacto_emergencia"
                                    checked={data.es_contacto_emergencia}
                                    onCheckedChange={(checked) => setData('es_contacto_emergencia', !!checked)}
                                />
                                <Label htmlFor="es_contacto_emergencia">
                                    Es contacto de emergencia
                                </Label>
                                {errors.es_contacto_emergencia && (
                                    <p className="text-sm text-destructive">{errors.es_contacto_emergencia}</p>
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