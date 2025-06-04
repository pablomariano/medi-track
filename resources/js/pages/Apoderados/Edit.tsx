import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
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

interface Props {
    apoderado: {
        usuario_id: number;
        relacion_paciente: string;
        es_contacto_emergencia: boolean;
        user: {
            id: number;
            name: string;
            email: string;
        };
    };
}

export default function Edit({ apoderado }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        relacion_paciente: apoderado.relacion_paciente || '',
        es_contacto_emergencia: apoderado.es_contacto_emergencia,
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(route('apoderados.update', apoderado.usuario_id));
    };

    const handleCheckboxChange = (checked: boolean | string) => {
        setData('es_contacto_emergencia', checked === true || checked === 'true');
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
                        <h1 className="text-2xl font-bold">Editar Apoderado</h1>
                        <p className="text-muted-foreground">
                            Modifica la información del apoderado
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Información del Apoderado</CardTitle>
                        <CardDescription>
                            Actualiza los detalles del apoderado - {apoderado.user.name}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label>Usuario Asignado</Label>
                                <div className="p-3 bg-gray-50 rounded-md">
                                    <p className="font-medium">{apoderado.user.name}</p>
                                    <p className="text-sm text-gray-600">{apoderado.user.email}</p>
                                </div>
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
                                    onCheckedChange={handleCheckboxChange}
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