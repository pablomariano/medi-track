import { useState } from 'react';
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
import { ArrowLeft, Save, X } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface Props {
    grupos: string[];
}

interface PrincipioActivoFormData extends Record<string, any> {
    nombre_generico: string;
    nombre_comercial: string;
    clasificacion_atc: string;
    grupo_farmacologico: string;
    descripcion: string;
    activo: boolean;
}

export default function Create({ grupos }: Props) {
    const [selectedGrupo, setSelectedGrupo] = useState('');
    const [newGrupo, setNewGrupo] = useState('');
    const [showNewGrupo, setShowNewGrupo] = useState(false);

    const { data, setData, post, processing, errors } = useForm<PrincipioActivoFormData>({
        nombre_generico: '',
        nombre_comercial: '',
        clasificacion_atc: '',
        grupo_farmacologico: '',
        descripcion: '',
        activo: true,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        // Update data before submitting
        setData('grupo_farmacologico', showNewGrupo ? newGrupo : selectedGrupo);
        
        post('/medicamentos/principios-activos');
    };

    const handleGrupoChange = (value: string) => {
        if (value === 'nuevo') {
            setShowNewGrupo(true);
            setSelectedGrupo('');
        } else {
            setShowNewGrupo(false);
            setSelectedGrupo(value);
            setData('grupo_farmacologico', value);
        }
    };

    const handleNewGrupoChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setNewGrupo(e.target.value);
        setData('grupo_farmacologico', e.target.value);
    };

    return (
        <AppSidebarLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Medicamentos', href: '#' },
                { title: 'Principios Activos', href: '/medicamentos/principios-activos' },
                { title: 'Crear', href: '/medicamentos/principios-activos/create' }
            ]}
        >
            <Head title="Crear Principio Activo" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Button variant="outline" size="icon" asChild>
                        <Link href="/medicamentos/principios-activos">
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Crear Principio Activo</h1>
                        <p className="text-muted-foreground">
                            Añadir un nuevo principio activo al sistema
                        </p>
                    </div>
                </div>

                {/* Formulario */}
                <Card>
                    <CardHeader>
                        <CardTitle>Información del Principio Activo</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Nombre Genérico */}
                                <div className="space-y-2">
                                    <Label htmlFor="nombre_generico" className="required">
                                        Nombre Genérico
                                    </Label>
                                    <Input
                                        id="nombre_generico"
                                        type="text"
                                        value={data.nombre_generico}
                                        onChange={(e) => setData('nombre_generico', e.target.value)}
                                        placeholder="Ej: Paracetamol"
                                        className={errors.nombre_generico ? 'border-red-500' : ''}
                                        required
                                    />
                                    {errors.nombre_generico && (
                                        <Alert variant="destructive">
                                            <AlertDescription>{errors.nombre_generico}</AlertDescription>
                                        </Alert>
                                    )}
                                </div>

                                {/* Nombre Comercial */}
                                <div className="space-y-2">
                                    <Label htmlFor="nombre_comercial">
                                        Nombre Comercial
                                    </Label>
                                    <Input
                                        id="nombre_comercial"
                                        type="text"
                                        value={data.nombre_comercial}
                                        onChange={(e) => setData('nombre_comercial', e.target.value)}
                                        placeholder="Ej: Acetaminofén"
                                        className={errors.nombre_comercial ? 'border-red-500' : ''}
                                    />
                                    {errors.nombre_comercial && (
                                        <Alert variant="destructive">
                                            <AlertDescription>{errors.nombre_comercial}</AlertDescription>
                                        </Alert>
                                    )}
                                </div>

                                {/* Clasificación ATC */}
                                <div className="space-y-2">
                                    <Label htmlFor="clasificacion_atc">
                                        Clasificación ATC
                                    </Label>
                                    <Input
                                        id="clasificacion_atc"
                                        type="text"
                                        value={data.clasificacion_atc}
                                        onChange={(e) => setData('clasificacion_atc', e.target.value)}
                                        placeholder="Ej: N02BE01"
                                        maxLength={10}
                                        className={errors.clasificacion_atc ? 'border-red-500' : ''}
                                    />
                                    {errors.clasificacion_atc && (
                                        <Alert variant="destructive">
                                            <AlertDescription>{errors.clasificacion_atc}</AlertDescription>
                                        </Alert>
                                    )}
                                </div>

                                {/* Grupo Farmacológico */}
                                <div className="space-y-2">
                                    <Label htmlFor="grupo_farmacologico" className="required">
                                        Grupo Farmacológico
                                    </Label>
                                    {!showNewGrupo ? (
                                        <Select onValueChange={handleGrupoChange} required>
                                            <SelectTrigger className={errors.grupo_farmacologico ? 'border-red-500' : ''}>
                                                <SelectValue placeholder="Selecciona un grupo" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {grupos.map((grupo) => (
                                                    <SelectItem key={grupo} value={grupo}>
                                                        {grupo}
                                                    </SelectItem>
                                                ))}
                                                <SelectItem value="nuevo">
                                                    + Crear nuevo grupo
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    ) : (
                                        <div className="flex gap-2">
                                            <Input
                                                type="text"
                                                value={newGrupo}
                                                onChange={handleNewGrupoChange}
                                                placeholder="Nombre del nuevo grupo"
                                                className={errors.grupo_farmacologico ? 'border-red-500' : ''}
                                                required
                                            />
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="icon"
                                                onClick={() => {
                                                    setShowNewGrupo(false);
                                                    setNewGrupo('');
                                                    setData('grupo_farmacologico', '');
                                                }}
                                            >
                                                <X className="h-4 w-4" />
                                            </Button>
                                        </div>
                                    )}
                                    {errors.grupo_farmacologico && (
                                        <Alert variant="destructive">
                                            <AlertDescription>{errors.grupo_farmacologico}</AlertDescription>
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
                                    placeholder="Descripción del principio activo, indicaciones, mecanismo de acción..."
                                    rows={4}
                                    maxLength={1000}
                                    className={errors.descripcion ? 'border-red-500' : ''}
                                />
                                <div className="text-sm text-muted-foreground">
                                    {data.descripcion.length}/1000 caracteres
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
                                    Principio activo activo
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
                                    {processing ? 'Guardando...' : 'Guardar Principio Activo'}
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href="/medicamentos/principios-activos">
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