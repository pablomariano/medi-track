import { Head, Link, useForm } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';
import { ArrowLeft, Save } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface UnidadMedida {
    id: number;
    nombre: string;
    simbolo: string;
    tipo: string;
    descripcion?: string;
    activo: boolean;
}

interface Props {
    unidadMedida?: UnidadMedida;
    isEdit?: boolean;
}

interface UnidadMedidaFormData extends Record<string, any> {
    nombre: string;
    simbolo: string;
    tipo: string;
    descripcion: string;
    activo: boolean;
}

const tiposUnidad = [
    { value: 'peso', label: 'Peso' },
    { value: 'volumen', label: 'Volumen' },
    { value: 'concentracion', label: 'Concentración' },
    { value: 'unidad', label: 'Unidad' },
    { value: 'tiempo', label: 'Tiempo' },
];

export default function Form({ unidadMedida, isEdit = false }: Props) {
    const { data, setData, post, put, processing, errors } = useForm<UnidadMedidaFormData>({
        nombre: unidadMedida?.nombre || '',
        simbolo: unidadMedida?.simbolo || '',
        tipo: unidadMedida?.tipo || '',
        descripcion: unidadMedida?.descripcion || '',
        activo: unidadMedida?.activo ?? true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (isEdit && unidadMedida) {
            put(`/medicamentos/unidades-medida/${unidadMedida.id}`);
        } else {
            post('/medicamentos/unidades-medida');
        }
    };

    const title = isEdit ? 'Editar Unidad de Medida' : 'Crear Unidad de Medida';
    const action = isEdit ? 'Actualizar' : 'Crear';

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Medicamentos', href: '#' },
                { title: 'Unidades de Medida', href: '/medicamentos/unidades-medida' },
                { title: action, href: '#' }
            ]}
        >
            <Head title={title} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="icon" asChild>
                        <Link href="/medicamentos/unidades-medida">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">{title}</h1>
                        <p className="text-muted-foreground">
                            {isEdit ? 'Modificar unidad de medida existente' : 'Añadir nueva unidad de medida al sistema'}
                        </p>
                    </div>
                </div>

                {/* Formulario */}
                <Card>
                    <CardHeader>
                        <CardTitle>Información de la Unidad de Medida</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                        placeholder="Ej: Miligramo"
                                        className={errors.nombre ? 'border-red-500' : ''}
                                        required
                                    />
                                    {errors.nombre && (
                                        <Alert variant="destructive">
                                            <AlertDescription>{errors.nombre}</AlertDescription>
                                        </Alert>
                                    )}
                                </div>

                                {/* Símbolo */}
                                <div className="space-y-2">
                                    <Label htmlFor="simbolo" className="required">
                                        Símbolo
                                    </Label>
                                    <Input
                                        id="simbolo"
                                        type="text"
                                        value={data.simbolo}
                                        onChange={(e) => setData('simbolo', e.target.value)}
                                        placeholder="Ej: mg"
                                        className={errors.simbolo ? 'border-red-500' : ''}
                                        maxLength={10}
                                        required
                                    />
                                    {errors.simbolo && (
                                        <Alert variant="destructive">
                                            <AlertDescription>{errors.simbolo}</AlertDescription>
                                        </Alert>
                                    )}
                                </div>

                                {/* Tipo */}
                                <div className="space-y-2">
                                    <Label htmlFor="tipo" className="required">
                                        Tipo
                                    </Label>
                                    <Select 
                                        value={data.tipo} 
                                        onValueChange={(value) => setData('tipo', value)}
                                        required
                                    >
                                        <SelectTrigger className={errors.tipo ? 'border-red-500' : ''}>
                                            <SelectValue placeholder="Selecciona un tipo" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {tiposUnidad.map((tipo) => (
                                                <SelectItem key={tipo.value} value={tipo.value}>
                                                    {tipo.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.tipo && (
                                        <Alert variant="destructive">
                                            <AlertDescription>{errors.tipo}</AlertDescription>
                                        </Alert>
                                    )}
                                </div>
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
                                    placeholder="Descripción opcional de la unidad de medida..."
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
                                    Unidad de medida activa
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
                                    {processing ? `${action}ndo...` : `${action} Unidad de Medida`}
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href="/medicamentos/unidades-medida">
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