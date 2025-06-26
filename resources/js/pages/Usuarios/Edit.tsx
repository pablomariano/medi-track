import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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

interface Role {
    id: number;
    nombre: string;
    descripcion: string;
    activo: boolean;
}

interface Usuario {
    id: number;
    name: string;
    nombre?: string;
    apellido_paterno?: string;
    apellido_materno?: string;
    email: string;
    telefono: string | null;
    activo: boolean;
    email_verified_at: string | null;
    role: Role | null;
}

interface Props {
    usuario: Usuario;
    roles: Role[];
}

export default function Edit({ usuario, roles }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        nombre: usuario.nombre || '',
        apellido_paterno: usuario.apellido_paterno || '',
        apellido_materno: usuario.apellido_materno || '',
        email: usuario.email || '',
        password: '',
        password_confirmation: '',
        telefono: usuario.telefono || '',
        rol_id: usuario.role?.id.toString() || '',
        activo: usuario.activo,
        email_verificado: !!usuario.email_verified_at,
    });

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        put(route('usuarios.update', usuario.id));
    };

    const handleActivoChange = (checked: boolean | string) => {
        setData('activo', checked === true || checked === 'true');
    };

    const handleEmailVerificadoChange = (checked: boolean | string) => {
        setData('email_verificado', checked === true || checked === 'true');
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('usuarios.index')}>
                        <Button variant="ghost" size="icon">
                            <ArrowLeft className="h-4 w-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 className="text-2xl font-bold">Editar Usuario</h1>
                        <p className="text-muted-foreground">
                            Modifica la información del usuario
                        </p>
                    </div>
                </div>

                <div className="max-w-2xl">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Información Básica */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Información Básica</CardTitle>
                                <CardDescription>
                                    Datos principales del usuario
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-3 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="nombre">Nombre *</Label>
                                        <Input
                                            id="nombre"
                                            value={data.nombre}
                                            onChange={(e) => setData('nombre', e.target.value)}
                                            placeholder="Juan"
                                            required
                                        />
                                        {errors.nombre && (
                                            <p className="text-sm text-destructive">{errors.nombre}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="apellido_paterno">Apellido Paterno *</Label>
                                        <Input
                                            id="apellido_paterno"
                                            value={data.apellido_paterno}
                                            onChange={(e) => setData('apellido_paterno', e.target.value)}
                                            placeholder="Pérez"
                                            required
                                        />
                                        {errors.apellido_paterno && (
                                            <p className="text-sm text-destructive">{errors.apellido_paterno}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="apellido_materno">Apellido Materno</Label>
                                        <Input
                                            id="apellido_materno"
                                            value={data.apellido_materno}
                                            onChange={(e) => setData('apellido_materno', e.target.value)}
                                            placeholder="González"
                                        />
                                        {errors.apellido_materno && (
                                            <p className="text-sm text-destructive">{errors.apellido_materno}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">

                                    <div className="space-y-2">
                                        <Label htmlFor="email">Email *</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            placeholder="juan@ejemplo.com"
                                            required
                                        />
                                        {errors.email && (
                                            <p className="text-sm text-destructive">{errors.email}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="password">Nueva Contraseña</Label>
                                        <Input
                                            id="password"
                                            type="password"
                                            value={data.password}
                                            onChange={(e) => setData('password', e.target.value)}
                                            placeholder="••••••••"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Déjalo en blanco si no quieres cambiar la contraseña
                                        </p>
                                        {errors.password && (
                                            <p className="text-sm text-destructive">{errors.password}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="password_confirmation">Confirmar Nueva Contraseña</Label>
                                        <Input
                                            id="password_confirmation"
                                            type="password"
                                            value={data.password_confirmation}
                                            onChange={(e) => setData('password_confirmation', e.target.value)}
                                            placeholder="••••••••"
                                        />
                                        {errors.password_confirmation && (
                                            <p className="text-sm text-destructive">{errors.password_confirmation}</p>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Información Adicional */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Información Adicional</CardTitle>
                                <CardDescription>
                                    Configuración del usuario
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="telefono">Teléfono</Label>
                                        <Input
                                            id="telefono"
                                            value={data.telefono}
                                            onChange={(e) => setData('telefono', e.target.value)}
                                            placeholder="+56 9 1234 5678"
                                        />
                                        {errors.telefono && (
                                            <p className="text-sm text-destructive">{errors.telefono}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="rol_id">Rol</Label>
                                        <Select value={data.rol_id} onValueChange={(value) => setData('rol_id', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecciona un rol" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {roles.map((rol) => (
                                                    <SelectItem key={rol.id} value={rol.id.toString()}>
                                                        {rol.nombre} - {rol.descripcion}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.rol_id && (
                                            <p className="text-sm text-destructive">{errors.rol_id}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="flex gap-6">
                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="activo"
                                            checked={data.activo}
                                            onCheckedChange={handleActivoChange}
                                        />
                                        <Label htmlFor="activo">Usuario activo</Label>
                                        {errors.activo && (
                                            <p className="text-sm text-destructive">{errors.activo}</p>
                                        )}
                                    </div>

                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="email_verificado"
                                            checked={data.email_verificado}
                                            onCheckedChange={handleEmailVerificadoChange}
                                        />
                                        <Label htmlFor="email_verificado">Email verificado</Label>
                                        {errors.email_verificado && (
                                            <p className="text-sm text-destructive">{errors.email_verificado}</p>
                                        )}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <div className="flex justify-end gap-2">
                            <Link href={route('usuarios.index')}>
                                <Button variant="outline">Cancelar</Button>
                            </Link>
                            <Button type="submit" disabled={processing}>
                                Actualizar Usuario
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AppSidebarLayout>
    );
} 