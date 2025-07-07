import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
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

interface Genero {
    id: string;
    nombre: string;
}

interface Paciente {
    id: number;
    usuario_id: number | null;
    nombre: string;
    fecha_nacimiento: string | null;
    genero_id: string | null;
    numero_documento: string | null;
    tipo_documento: string | null;
    tipo_sangre: string | null;
    altura: number | null;
    direccion: string | null;
    telefono_emergencia: string | null;
    observaciones_medicas: string | null;
    activo: boolean;
    user: User | null;
    genero: Genero | null;
}

interface Props {
    paciente: Paciente;
    usuarios: User[];
    generos: Genero[];
}

export default function Edit({ paciente, usuarios, generos }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        usuario_id: paciente.usuario_id?.toString() || '',
        nombre: paciente.nombre || '',
        fecha_nacimiento: paciente.fecha_nacimiento || '',
        genero_id: paciente.genero_id || '',
        numero_documento: paciente.numero_documento || '',
        tipo_documento: paciente.tipo_documento || '',
        tipo_sangre: paciente.tipo_sangre || '',
        altura: paciente.altura?.toString() || '',
        direccion: paciente.direccion || '',
        telefono_emergencia: paciente.telefono_emergencia || '',
        observaciones_medicas: paciente.observaciones_medicas || '',
        activo: paciente.activo,
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(route('pacientes.update', paciente.id));
    };

    const handleCheckboxChange = (checked: boolean | string) => {
        setData('activo', checked === true || checked === 'true');
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

    return (
        <AppSidebarLayout>
            <div className="container mx-auto p-6 space-y-6 max-w-none">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('pacientes.index')}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Editar Paciente</h1>
                        <p className="text-muted-foreground">
                            Modifica la información del paciente
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Información Básica */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Información Básica</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="nombre">Nombre Completo *</Label>
                                    <Input
                                        id="nombre"
                                        value={data.nombre}
                                        onChange={(e) => setData('nombre', e.target.value)}
                                        placeholder="Juan Pérez González"
                                        required
                                    />
                                    {errors.nombre && (
                                        <p className="text-sm text-destructive">{errors.nombre}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="usuario_id">Usuario del Sistema</Label>
                                    <Select value={data.usuario_id} onValueChange={(value) => setData('usuario_id', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecciona un usuario (opcional)" />
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
                            </div>

                            <div className="grid grid-cols-3 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="fecha_nacimiento">Fecha de Nacimiento</Label>
                                    <Input
                                        id="fecha_nacimiento"
                                        type="date"
                                        value={data.fecha_nacimiento}
                                        onChange={(e) => setData('fecha_nacimiento', e.target.value)}
                                    />
                                    {errors.fecha_nacimiento && (
                                        <p className="text-sm text-destructive">{errors.fecha_nacimiento}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="genero_id">Género</Label>
                                    <Select value={data.genero_id} onValueChange={(value) => setData('genero_id', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecciona género" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {generos.map((genero) => (
                                                <SelectItem key={genero.id} value={genero.id}>
                                                    {genero.nombre}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.genero_id && (
                                        <p className="text-sm text-destructive">{errors.genero_id}</p>
                                    )}
                                </div>

                                <div className="flex items-center space-x-2 pt-8">
                                    <Checkbox
                                        id="activo"
                                        checked={data.activo}
                                        onCheckedChange={handleCheckboxChange}
                                    />
                                    <Label htmlFor="activo">Paciente activo</Label>
                                    {errors.activo && (
                                        <p className="text-sm text-destructive">{errors.activo}</p>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Documentación */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Documentación</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="tipo_documento">Tipo de Documento</Label>
                                    <Select value={data.tipo_documento} onValueChange={(value) => setData('tipo_documento', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecciona tipo" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {tiposDocumento.map((tipo) => (
                                                <SelectItem key={tipo.value} value={tipo.value}>
                                                    {tipo.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.tipo_documento && (
                                        <p className="text-sm text-destructive">{errors.tipo_documento}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="numero_documento">Número de Documento</Label>
                                    <Input
                                        id="numero_documento"
                                        value={data.numero_documento}
                                        onChange={(e) => setData('numero_documento', e.target.value)}
                                        placeholder="12.345.678-9"
                                    />
                                    {errors.numero_documento && (
                                        <p className="text-sm text-destructive">{errors.numero_documento}</p>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Información Médica */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Información Médica</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-2 gap-4 mb-4">
                                <div className="space-y-2">
                                    <Label htmlFor="tipo_sangre">Tipo de Sangre</Label>
                                    <Select value={data.tipo_sangre} onValueChange={(value) => setData('tipo_sangre', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecciona tipo de sangre" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {tiposSangre.map((tipo) => (
                                                <SelectItem key={tipo.value} value={tipo.value}>
                                                    {tipo.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.tipo_sangre && (
                                        <p className="text-sm text-destructive">{errors.tipo_sangre}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="altura">Altura (cm)</Label>
                                    <Input
                                        id="altura"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="300"
                                        value={data.altura}
                                        onChange={(e) => setData('altura', e.target.value)}
                                        placeholder="175"
                                    />
                                    {errors.altura && (
                                        <p className="text-sm text-destructive">{errors.altura}</p>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="observaciones_medicas">Observaciones Médicas</Label>
                                <Textarea
                                    id="observaciones_medicas"
                                    value={data.observaciones_medicas}
                                    onChange={(e) => setData('observaciones_medicas', e.target.value)}
                                    placeholder="Alergias, condiciones médicas, medicamentos actuales..."
                                    rows={3}
                                />
                                {errors.observaciones_medicas && (
                                    <p className="text-sm text-destructive">{errors.observaciones_medicas}</p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Información de Contacto */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Información de Contacto</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="telefono_emergencia">Teléfono de Emergencia</Label>
                                    <Input
                                        id="telefono_emergencia"
                                        value={data.telefono_emergencia}
                                        onChange={(e) => setData('telefono_emergencia', e.target.value)}
                                        placeholder="+56 9 1234 5678"
                                    />
                                    {errors.telefono_emergencia && (
                                        <p className="text-sm text-destructive">{errors.telefono_emergencia}</p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="direccion">Dirección</Label>
                                    <Textarea
                                        id="direccion"
                                        value={data.direccion}
                                        onChange={(e) => setData('direccion', e.target.value)}
                                        placeholder="Av. Principal 123, Santiago, Chile"
                                        rows={2}
                                    />
                                    {errors.direccion && (
                                        <p className="text-sm text-destructive">{errors.direccion}</p>
                                    )}
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <div className="flex justify-end gap-2">
                        <Link href={route('pacientes.index')}>
                            <Button variant="outline">Cancelar</Button>
                        </Link>
                        <Button type="submit" disabled={processing}>
                            Actualizar Paciente
                        </Button>
                    </div>
                </form>
            </div>
        </AppSidebarLayout>
    );
} 