import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { ArrowLeft } from 'lucide-react';
import { Link } from '@inertiajs/react';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        nombre: '',
        medida: '',
        unidad_medida: '',
        descripcion: '',
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post(route('medicines.store'));
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('medicines.index')}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <h1 className="text-2xl font-bold">Nuevo Medicamento</h1>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
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
                                <p className="text-sm text-red-500">{errors.nombre}</p>
                            )}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="medida">Medida</Label>
                                <Input
                                    id="medida"
                                    type="number"
                                    value={data.medida}
                                    onChange={(e) => setData('medida', e.target.value)}
                                    required
                                />
                                {errors.medida && (
                                    <p className="text-sm text-red-500">{errors.medida}</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="unidad_medida">Unidad de Medida</Label>
                                <Input
                                    id="unidad_medida"
                                    value={data.unidad_medida}
                                    onChange={(e) => setData('unidad_medida', e.target.value)}
                                    required
                                />
                                {errors.unidad_medida && (
                                    <p className="text-sm text-red-500">{errors.unidad_medida}</p>
                                )}
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="descripcion">Descripción</Label>
                            <Textarea
                                id="descripcion"
                                value={data.descripcion}
                                onChange={(e) => setData('descripcion', e.target.value)}
                            />
                            {errors.descripcion && (
                                <p className="text-sm text-red-500">{errors.descripcion}</p>
                            )}
                        </div>

                        <div className="flex justify-end">
                            <Button type="submit" disabled={processing}>
                                Guardar
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppSidebarLayout>
    );
} 