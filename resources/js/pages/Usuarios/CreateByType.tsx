import React from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { ArrowLeft } from 'lucide-react';
import { Link } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

interface UserType {
    label: string;
    description: string;
    icon: string;
}

interface Genero {
    id: number;
    nombre: string;
}

interface Props {
    tipo: string;
    tipoInfo: UserType;
    generos?: Genero[];
}

export default function CreateByType({ tipo, tipoInfo, generos }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        tipo_usuario: tipo,
        user_data: {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            telefono: '',
            activo: true,
            email_verificado: false,
        },
        specific_data: {} as any
    });

    // Inicializar campos específicos según el tipo
    const initializeSpecificData = () => {
        switch (tipo) {
            case 'medico':
                return {
                    especialidad: '',
                    numero_colegiatura: '',
                    institucion: '',
                    anos_experiencia: '',
                };
            case 'cuidador':
                return {
                    certificaciones: '',
                    experiencia_anos: '',
                    disponibilidad_horaria: '',
                    tarifa_hora: '',
                };
            case 'apoderado':
                return {
                    relacion_paciente: '',
                    es_contacto_emergencia: false,
                };
            case 'paciente':
                return {
                    nombre: '',
                    fecha_nacimiento: '',
                    genero_id: '',
                    numero_documento: '',
                    tipo_documento: '',
                    tipo_sangre: '',
                    altura: '',
                    direccion: '',
                    telefono_emergencia: '',
                    observaciones_medicas: '',
                    activo: true,
                };
            default:
                return {};
        }
    };

    // Inicializar datos específicos al cargar
    React.useEffect(() => {
        setData('specific_data', initializeSpecificData());
    }, [tipo]);

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post(route('usuarios.store-by-type'));
    };

    const updateUserData = (field: string, value: any) => {
        setData('user_data', {
            ...data.user_data,
            [field]: value
        });
    };

    const updateSpecificData = (field: string, value: any) => {
        setData('specific_data', {
            ...data.specific_data,
            [field]: value
        });
    };

    const tiposDocumento = [
        { value: 'rut', label: 'RUT' },
        { value: 'ci', label: 'Cédula de Identidad' },
        { value: 'passport', label: 'Pasaporte' },
        { value: 'otro', label: 'Otro' },
    ];

    const tiposSangre = [
        { value: 'A+', label: 'A+' },
        { value: 'A-', label: 'A-' },
        { value: 'B+', label: 'B+' },
        { value: 'B-', label: 'B-' },
        { value: 'AB+', label: 'AB+' },
        { value: 'AB-', label: 'AB-' },
        { value: 'O+', label: 'O+' },
        { value: 'O-', label: 'O-' },
    ];

    const renderUserDataFields = () => {
        // Para pacientes, los campos de usuario son opcionales
        const isRequired = tipo !== 'paciente';
        
        return (
            <Card>
                <CardHeader>
                    <CardTitle>
                        Datos de Usuario {!isRequired && '(Opcional)'}
                    </CardTitle>
                    <CardDescription>
                        {isRequired 
                            ? 'Información para el acceso al sistema'
                            : 'Completar solo si el paciente tendrá acceso al sistema'
                        }
                    </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">
                                Nombre Completo {isRequired && '*'}
                            </Label>
                            <Input
                                id="name"
                                value={data.user_data.name}
                                onChange={(e) => updateUserData('name', e.target.value)}
                                placeholder="Juan Pérez González"
                                required={isRequired}
                            />
                            {errors['user_data.name' as keyof typeof errors] && (
                                <p className="text-sm text-destructive">{errors['user_data.name' as keyof typeof errors]}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="email">
                                Email {isRequired && '*'}
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.user_data.email}
                                onChange={(e) => updateUserData('email', e.target.value)}
                                placeholder="juan@ejemplo.com"
                                required={isRequired}
                            />
                            {errors['user_data.email' as keyof typeof errors] && (
                                <p className="text-sm text-destructive">{errors['user_data.email' as keyof typeof errors]}</p>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Label htmlFor="password">
                                Contraseña {isRequired && '*'}
                            </Label>
                            <Input
                                id="password"
                                type="password"
                                value={data.user_data.password}
                                onChange={(e) => updateUserData('password', e.target.value)}
                                placeholder="••••••••"
                                required={isRequired}
                            />
                            {errors['user_data.password' as keyof typeof errors] && (
                                <p className="text-sm text-destructive">{errors['user_data.password' as keyof typeof errors]}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="password_confirmation">
                                Confirmar Contraseña {isRequired && '*'}
                            </Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                value={data.user_data.password_confirmation}
                                onChange={(e) => updateUserData('password_confirmation', e.target.value)}
                                placeholder="••••••••"
                                required={isRequired}
                            />
                            {errors['user_data.password_confirmation' as keyof typeof errors] && (
                                <p className="text-sm text-destructive">{errors['user_data.password_confirmation' as keyof typeof errors]}</p>
                            )}
                        </div>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="telefono">Teléfono</Label>
                        <Input
                            id="telefono"
                            value={data.user_data.telefono}
                            onChange={(e) => updateUserData('telefono', e.target.value)}
                            placeholder="+56 9 1234 5678"
                        />
                        {errors['user_data.telefono' as keyof typeof errors] && (
                            <p className="text-sm text-destructive">{errors['user_data.telefono' as keyof typeof errors]}</p>
                        )}
                    </div>

                    <div className="flex gap-6">
                        <div className="flex items-center space-x-2">
                            <Checkbox
                                id="activo"
                                checked={data.user_data.activo}
                                onCheckedChange={(checked) => updateUserData('activo', checked === true)}
                            />
                            <Label htmlFor="activo">Usuario activo</Label>
                        </div>

                        <div className="flex items-center space-x-2">
                            <Checkbox
                                id="email_verificado"
                                checked={data.user_data.email_verificado}
                                onCheckedChange={(checked) => updateUserData('email_verificado', checked === true)}
                            />
                            <Label htmlFor="email_verificado">Email verificado</Label>
                        </div>
                    </div>
                </CardContent>
            </Card>
        );
    };

    const renderSpecificFields = () => {
        switch (tipo) {
            case 'medico':
                return (
                    <Card>
                        <CardHeader>
                            <CardTitle>Información Médica</CardTitle>
                            <CardDescription>
                                Datos específicos del personal médico
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="especialidad">Especialidad</Label>
                                    <Input
                                        id="especialidad"
                                        value={data.specific_data.especialidad || ''}
                                        onChange={(e) => updateSpecificData('especialidad', e.target.value)}
                                        placeholder="Cardiología, Neurología, etc."
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="numero_colegiatura">Número de Colegiatura</Label>
                                    <Input
                                        id="numero_colegiatura"
                                        value={data.specific_data.numero_colegiatura || ''}
                                        onChange={(e) => updateSpecificData('numero_colegiatura', e.target.value)}
                                        placeholder="123456"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="institucion">Institución</Label>
                                    <Input
                                        id="institucion"
                                        value={data.specific_data.institucion || ''}
                                        onChange={(e) => updateSpecificData('institucion', e.target.value)}
                                        placeholder="Hospital General, Clínica Privada, etc."
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="anos_experiencia">Años de Experiencia</Label>
                                    <Input
                                        id="anos_experiencia"
                                        type="number"
                                        min="0"
                                        value={data.specific_data.anos_experiencia || ''}
                                        onChange={(e) => updateSpecificData('anos_experiencia', e.target.value)}
                                        placeholder="5"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                );

            case 'cuidador':
                return (
                    <Card>
                        <CardHeader>
                            <CardTitle>Información del Cuidador</CardTitle>
                            <CardDescription>
                                Datos específicos del cuidador
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="certificaciones">Certificaciones</Label>
                                <Textarea
                                    id="certificaciones"
                                    value={data.specific_data.certificaciones || ''}
                                    onChange={(e) => updateSpecificData('certificaciones', e.target.value)}
                                    placeholder="Certificaciones en cuidado, primeros auxilios, etc."
                                    rows={3}
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="experiencia_anos">Años de Experiencia</Label>
                                    <Input
                                        id="experiencia_anos"
                                        type="number"
                                        min="0"
                                        value={data.specific_data.experiencia_anos || ''}
                                        onChange={(e) => updateSpecificData('experiencia_anos', e.target.value)}
                                        placeholder="3"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="tarifa_hora">Tarifa por Hora</Label>
                                    <Input
                                        id="tarifa_hora"
                                        type="number"
                                        min="0"
                                        value={data.specific_data.tarifa_hora || ''}
                                        onChange={(e) => updateSpecificData('tarifa_hora', e.target.value)}
                                        placeholder="15000"
                                    />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="disponibilidad_horaria">Disponibilidad Horaria</Label>
                                <Input
                                    id="disponibilidad_horaria"
                                    value={data.specific_data.disponibilidad_horaria || ''}
                                    onChange={(e) => updateSpecificData('disponibilidad_horaria', e.target.value)}
                                    placeholder="Lunes a Viernes 08:00-18:00"
                                />
                            </div>
                        </CardContent>
                    </Card>
                );

            case 'apoderado':
                return (
                    <Card>
                        <CardHeader>
                            <CardTitle>Información del Apoderado</CardTitle>
                            <CardDescription>
                                Datos específicos del apoderado
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="relacion_paciente">Relación con el Paciente</Label>
                                <Input
                                    id="relacion_paciente"
                                    value={data.specific_data.relacion_paciente || ''}
                                    onChange={(e) => updateSpecificData('relacion_paciente', e.target.value)}
                                    placeholder="Padre, Madre, Cónyuge, etc."
                                />
                            </div>

                            <div className="flex items-center space-x-2">
                                <Checkbox
                                    id="es_contacto_emergencia"
                                    checked={data.specific_data.es_contacto_emergencia || false}
                                    onCheckedChange={(checked) => updateSpecificData('es_contacto_emergencia', checked === true)}
                                />
                                <Label htmlFor="es_contacto_emergencia">Es contacto de emergencia</Label>
                            </div>
                        </CardContent>
                    </Card>
                );

            case 'paciente':
                return (
                    <>
                        <Card>
                            <CardHeader>
                                <CardTitle>Información Personal</CardTitle>
                                <CardDescription>
                                    Datos básicos del paciente
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="nombre">Nombre Completo *</Label>
                                        <Input
                                            id="nombre"
                                            value={data.specific_data.nombre || ''}
                                            onChange={(e) => updateSpecificData('nombre', e.target.value)}
                                            placeholder="María González López"
                                            required
                                        />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="fecha_nacimiento">Fecha de Nacimiento</Label>
                                        <Input
                                            id="fecha_nacimiento"
                                            type="date"
                                            value={data.specific_data.fecha_nacimiento || ''}
                                            onChange={(e) => updateSpecificData('fecha_nacimiento', e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-3 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="genero_id">Género</Label>
                                        <Select 
                                            value={data.specific_data.genero_id || ''} 
                                            onValueChange={(value) => updateSpecificData('genero_id', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Seleccionar" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {generos?.map((genero) => (
                                                    <SelectItem key={genero.id} value={genero.id.toString()}>
                                                        {genero.nombre}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="tipo_documento">Tipo de Documento</Label>
                                        <Select 
                                            value={data.specific_data.tipo_documento || ''} 
                                            onValueChange={(value) => updateSpecificData('tipo_documento', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Seleccionar" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {tiposDocumento.map((tipo) => (
                                                    <SelectItem key={tipo.value} value={tipo.value}>
                                                        {tipo.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="numero_documento">Número de Documento</Label>
                                        <Input
                                            id="numero_documento"
                                            value={data.specific_data.numero_documento || ''}
                                            onChange={(e) => updateSpecificData('numero_documento', e.target.value)}
                                            placeholder="12.345.678-9"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Información Médica</CardTitle>
                                <CardDescription>
                                    Datos médicos del paciente
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="tipo_sangre">Tipo de Sangre</Label>
                                        <Select 
                                            value={data.specific_data.tipo_sangre || ''} 
                                            onValueChange={(value) => updateSpecificData('tipo_sangre', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Seleccionar" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {tiposSangre.map((tipo) => (
                                                    <SelectItem key={tipo.value} value={tipo.value}>
                                                        {tipo.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="altura">Altura (cm)</Label>
                                        <Input
                                            id="altura"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="300"
                                            value={data.specific_data.altura || ''}
                                            onChange={(e) => updateSpecificData('altura', e.target.value)}
                                            placeholder="175"
                                        />
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="observaciones_medicas">Observaciones Médicas</Label>
                                    <Textarea
                                        id="observaciones_medicas"
                                        value={data.specific_data.observaciones_medicas || ''}
                                        onChange={(e) => updateSpecificData('observaciones_medicas', e.target.value)}
                                        placeholder="Alergias, condiciones médicas, medicamentos actuales..."
                                        rows={3}
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Información de Contacto</CardTitle>
                                <CardDescription>
                                    Datos de contacto y ubicación
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="direccion">Dirección</Label>
                                    <Textarea
                                        id="direccion"
                                        value={data.specific_data.direccion || ''}
                                        onChange={(e) => updateSpecificData('direccion', e.target.value)}
                                        placeholder="Calle Principal 123, Providencia, Santiago"
                                        rows={2}
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="telefono_emergencia">Teléfono de Emergencia</Label>
                                        <Input
                                            id="telefono_emergencia"
                                            value={data.specific_data.telefono_emergencia || ''}
                                            onChange={(e) => updateSpecificData('telefono_emergencia', e.target.value)}
                                            placeholder="+56 9 8765 4321"
                                        />
                                    </div>

                                    <div className="flex items-center space-x-2 pt-6">
                                        <Checkbox
                                            id="paciente_activo"
                                            checked={data.specific_data.activo !== false}
                                            onCheckedChange={(checked) => updateSpecificData('activo', checked === true)}
                                        />
                                        <Label htmlFor="paciente_activo">Paciente activo</Label>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </>
                );

            default:
                return null;
        }
    };

    return (
        <AppLayout>
            <div className="container mx-auto p-6 space-y-6 max-w-none">
                <div className="flex items-center gap-4">
                    <Link href={route('usuarios.select-type')}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Crear {tipoInfo.label}</h1>
                        <p className="text-muted-foreground">
                            {tipoInfo.description}
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6 w-full max-w-6xl mx-auto">
                    {renderUserDataFields()}
                    {renderSpecificFields()}

                    <div className="flex justify-end gap-4">
                        <Link href={route('usuarios.select-type')}>
                            <Button type="button" variant="outline">
                                Cancelar
                            </Button>
                        </Link>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Creando...' : `Crear ${tipoInfo.label}`}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
} 