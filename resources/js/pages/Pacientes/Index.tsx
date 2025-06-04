import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Plus, Pencil, Trash2, Eye, CheckCircle, XCircle } from 'lucide-react';
import { router } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

interface Paciente {
    id: number;
    usuario_id: number | null;
    nombre: string;
    fecha_nacimiento: string | null;
    numero_documento: string | null;
    tipo_documento: string | null;
    tipo_sangre: string | null;
    activo: boolean;
    created_at: string;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
    genero: {
        id: string;
        nombre: string;
    } | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    pacientes: {
        data: Paciente[];
        links: PaginationLink[];
    };
}

export default function Index({ pacientes }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('¿Estás seguro de que deseas eliminar este paciente?')) {
            router.delete(route('pacientes.destroy', id));
        }
    };

    const calcularEdad = (fechaNacimiento: string | null) => {
        if (!fechaNacimiento) return '';
        const today = new Date();
        const birthDate = new Date(fechaNacimiento);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age + ' años';
    };

    const formatTipoSangre = (tipo: string | null) => {
        return tipo ? tipo.toUpperCase() : '';
    };

    return (
        <AppSidebarLayout>
            <div className="container mx-auto py-6">
                <Card>
                    <CardHeader>
                        <div className="flex justify-between items-center">
                            <div>
                                <CardTitle>Pacientes</CardTitle>
                                <CardDescription>
                                    Gestiona los pacientes del sistema médico
                                </CardDescription>
                            </div>
                            <Link href={route('pacientes.create')}>
                                <Button>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Nuevo Paciente
                                </Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nombre</TableHead>
                                    <TableHead>Documento</TableHead>
                                    <TableHead>Edad</TableHead>
                                    <TableHead>Género</TableHead>
                                    <TableHead>Tipo Sangre</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead>Usuario</TableHead>
                                    <TableHead className="w-[120px]">Acciones</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pacientes.data.map((paciente) => (
                                    <TableRow key={paciente.id}>
                                        <TableCell className="font-medium">{paciente.nombre}</TableCell>
                                        <TableCell>
                                            {paciente.numero_documento && (
                                                <div className="text-sm">
                                                    <div className="font-medium">{paciente.numero_documento}</div>
                                                    {paciente.tipo_documento && (
                                                        <div className="text-gray-500 text-xs">
                                                            {paciente.tipo_documento.toUpperCase()}
                                                        </div>
                                                    )}
                                                </div>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {paciente.fecha_nacimiento && (
                                                <span className="text-sm">
                                                    {calcularEdad(paciente.fecha_nacimiento)}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {paciente.genero && (
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {paciente.genero.id}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {paciente.tipo_sangre && (
                                                <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    {formatTipoSangre(paciente.tipo_sangre)}
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                {paciente.activo ? (
                                                    <>
                                                        <CheckCircle className="h-4 w-4 text-green-500" />
                                                        <span className="text-sm text-green-600">Activo</span>
                                                    </>
                                                ) : (
                                                    <>
                                                        <XCircle className="h-4 w-4 text-red-500" />
                                                        <span className="text-sm text-red-600">Inactivo</span>
                                                    </>
                                                )}
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {paciente.user ? (
                                                <div className="text-sm">
                                                    <div className="font-medium">{paciente.user.name}</div>
                                                    <div className="text-gray-500 text-xs">{paciente.user.email}</div>
                                                </div>
                                            ) : (
                                                <span className="text-gray-400 text-sm">Sin usuario</span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <div className="flex items-center gap-1">
                                                <Link href={route('pacientes.show', paciente.id)}>
                                                    <Button variant="ghost" size="icon">
                                                        <Eye className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Link href={route('pacientes.edit', paciente.id)}>
                                                    <Button variant="ghost" size="icon">
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                </Link>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => handleDelete(paciente.id)}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 