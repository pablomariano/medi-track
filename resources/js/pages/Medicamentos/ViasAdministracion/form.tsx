import { Head, Link, useForm } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft, Save } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface ViaAdministracion {
    id: number;
    nombre: string;
    descripcion?: string;
    activo: boolean;
}

interface Props {
    viaAdministracion?: ViaAdministracion;
    isEdit?: boolean;
}

interface ViaAdministracionFormData extends Record<string, any> {
    nombre: string;
    descripcion: string;
    activo: boolean;
}

export default function Form({ viaAdministracion, isEdit = false }: Props) {
    const { data, setData, post, put, processing, errors } = useForm<ViaAdministracionFormData>({
        nombre: viaAdministracion?.nombre || '',
        descripcion: viaAdministracion?.descripcion || '',
        activo: viaAdministracion?.activo ?? true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (isEdit && viaAdministracion) {
            put(`/medicamentos/vias-administracion/${viaAdministracion.id}`);
        } else {
            post('/medicamentos/vias-administracion');
        }
    };

    const title = isEdit ? 'Editar Vía de Administración' : 'Crear Vía de Administración';
    const action = isEdit ? 'Actualizar' : 'Crear';

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Medicamentos', href: '#' },
                { title: 'Vías de Administración', href: '/medicamentos/vias-administracion' },
                { title: action, href: '#' }
            ]}
        >
            <Head title={title} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="icon" asChild>
                        <Link href="/medicamentos/vias-administracion">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">{title}</h1>
                        <p className="text-muted-foreground">
                            {isEdit ? 'Modificar vía de administración existente' : 'Añadir nueva vía de administración al sistema'}
                        </p>
                    </div>
                </div>

                {/* Formulario */}
                <Card>
                    <CardHeader>
                        <CardTitle>Información de la Vía de Administración</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Nombre */}
                            <div className="space-y-2">
                                <Label htmlFor="nombre" className="required">
                                    Nombre
                                </Label>
                                <Input
                                    id="nombre"
                                    type="text"
                                    value={data.nombre}
                                    onChange={(e) => setData('nombre', e.target.value)}
                                    placeholder="Ej: Oral, Intravenosa, Intramuscular"
                                    className={errors.nombre ? 'border-red-500' : ''}
                                    required
                                />
                                {errors.nombre && (
                                    <Alert variant="destructive">
                                        <AlertDescription>{errors.nombre}</AlertDescription>
                                    </Alert>
                                )}
                            </div>

                            {/* Descripción */}
                            <div className="space-y-2">
                                <Label htmlFor="descripcion">
                                    Descripción
                                </Label>
                                <Textarea
                                    id="descripcion"
                                    value={data.descripcion}
                                    onChange={(e) => setData('descripcion', e.target.value)}
                                    placeholder="Descripción opcional de la vía de administración..."
                                    rows={3}
                                    maxLength={500}
                                    className={errors.descripcion ? 'border-red-500' : ''}
                                />
                                <div className="text-sm text-muted-foreground">
                                    {data.descripcion.length}/500 caracteres
                                </div>
                                {errors.descripcion && (
                                    <Alert variant="destructive">
                                        <AlertDescription>{errors.descripcion}</AlertDescription>
                                    </Alert>
                                )}
                            </div>

                            {/* Estado Activo */}
                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="activo"
                                    checked={data.activo}
                                    onCheckedChange={(checked) => setData('activo', !!checked)}
                                />
                                <Label htmlFor="activo">
                                    Vía de administración activa
                                </Label>
                            </div>

                            {/* Botones de acción */}
                            <div className="flex gap-4 pt-6">
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className="flex items-center gap-2"
                                >
                                    <Save className="h-4 w-4" />
                                    {processing ? `${action}ndo...` : `${action} Vía de Administración`}
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href="/medicamentos/vias-administracion">
                                        Cancelar
                                    </Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 