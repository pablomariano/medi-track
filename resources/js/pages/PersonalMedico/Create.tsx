import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
        especialidad: '',
        numero_colegiatura: '',
        institucion: '',
        anos_experiencia: '',
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post(route('personal-medico.store'));
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('personal-medico.index')}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Nuevo Personal Médico</h1>
                        <p className="text-muted-foreground">
                            Agrega un nuevo personal médico al sistema
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Información del Personal Médico</CardTitle>
                        <CardDescription>
                            Ingresa los detalles del personal médico
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

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="especialidad">Especialidad</Label>
                                    <Input
                                        id="especialidad"
                                        value={data.especialidad}
                                        onChange={(e) => setData('especialidad', e.target.value)}
                                        placeholder="Cardiología, Neurología, etc."
                                    />
                                    {errors.especialidad && (
                                        <p className="text-sm text-destructive">{errors.especialidad}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="numero_colegiatura">Número de Colegiatura</Label>
                                    <Input
                                        id="numero_colegiatura"
                                        value={data.numero_colegiatura}
                                        onChange={(e) => setData('numero_colegiatura', e.target.value)}
                                        placeholder="123456"
                                    />
                                    {errors.numero_colegiatura && (
                                        <p className="text-sm text-destructive">{errors.numero_colegiatura}</p>
                                    )}
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="institucion">Institución</Label>
                                    <Input
                                        id="institucion"
                                        value={data.institucion}
                                        onChange={(e) => setData('institucion', e.target.value)}
                                        placeholder="Hospital General, Clínica Privada, etc."
                                    />
                                    {errors.institucion && (
                                        <p className="text-sm text-destructive">{errors.institucion}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="anos_experiencia">Años de Experiencia</Label>
                                    <Input
                                        id="anos_experiencia"
                                        type="number"
                                        min="0"
                                        value={data.anos_experiencia}
                                        onChange={(e) => setData('anos_experiencia', e.target.value)}
                                        placeholder="5"
                                    />
                                    {errors.anos_experiencia && (
                                        <p className="text-sm text-destructive">{errors.anos_experiencia}</p>
                                    )}
                                </div>
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