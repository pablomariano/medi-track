import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
    personalMedico: {
        usuario_id: number;
        especialidad: string;
        numero_colegiatura: string;
        institucion: string;
        anos_experiencia: number;
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
}

export default function Edit({ personalMedico }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        especialidad: personalMedico.especialidad || '',
        numero_colegiatura: personalMedico.numero_colegiatura || '',
        institucion: personalMedico.institucion || '',
        anos_experiencia: personalMedico.anos_experiencia?.toString() || '',
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(route('personal-medico.update', personalMedico.usuario_id));
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
                        <h1 className="text-2xl font-bold">Editar Personal Médico</h1>
                        <p className="text-muted-foreground">
                            Modifica la información del personal médico
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Información del Personal Médico</CardTitle>
                        <CardDescription>
                            Actualiza los detalles del personal médico - {personalMedico.user.name}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label>Usuario Asignado</Label>
                                <div className="p-3 bg-gray-50 rounded-md">
                                    <p className="font-medium">{personalMedico.user.name}</p>
                                    <p className="text-sm text-gray-600">{personalMedico.user.email}</p>
                                </div>
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