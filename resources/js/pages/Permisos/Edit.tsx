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
    permiso: {
        id: number;
        nombre: string;
        descripcion: string;
        modulo: string;
    };
}

export default function Edit({ permiso }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        nombre: permiso.nombre,
        descripcion: permiso.descripcion,
        modulo: permiso.modulo,
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(route('permisos.update', permiso.id));
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('permisos.index')}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Editar Permiso</h1>
                        <p className="text-muted-foreground">
                            Modifica la información del permiso
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Información del Permiso</CardTitle>
                        <CardDescription>
                            Actualiza los detalles del permiso
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="nombre">Nombre</Label>
                                <Input
                                    id="nombre"
                                    value={data.nombre}
                                    onChange={(e) => setData('nombre', e.target.value)}
                                    required
                                />
                                {errors.nombre && (
                                    <p className="text-sm text-destructive">{errors.nombre}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="descripcion">Descripción</Label>
                                <Textarea
                                    id="descripcion"
                                    value={data.descripcion}
                                    onChange={(e) => setData('descripcion', e.target.value)}
                                />
                                {errors.descripcion && (
                                    <p className="text-sm text-destructive">{errors.descripcion}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="modulo">Módulo</Label>
                                <Input
                                    id="modulo"
                                    value={data.modulo}
                                    onChange={(e) => setData('modulo', e.target.value)}
                                    placeholder="Ej: usuarios, medicamentos, citas"
                                />
                                {errors.modulo && (
                                    <p className="text-sm text-destructive">{errors.modulo}</p>
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