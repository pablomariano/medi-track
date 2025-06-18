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
import { Plus, Pencil, Trash2, Eye, CheckCircle, XCircle, AlertTriangle } from 'lucide-react';
import { router } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";

// Componentes de autorización
import { CanAccess, AdminOnly, MedicalOnly } from '@/components/auth/CanAccess';
import { ProtectedButton, CreateButton, EditButton, DeleteButton, ViewButton } from '@/components/auth/ProtectedButton';
import { ProtectedLink } from '@/components/auth/ProtectedLink';
import { useAuth } from '@/hooks/use-auth';
import { usePermissions, useRoles } from '@/hooks/use-permissions';

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

export default function ProtectedIndex({ pacientes }: Props) {
    const auth = useAuth();
    const permissions = usePermissions();
    const roles = useRoles();

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

    // Verificar si el usuario puede ver esta página
    if (!permissions.canViewPacientes) {
        return (
            <AppSidebarLayout>
                <div className="container mx-auto py-6">
                    <Alert>
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>
                            No tienes permisos para ver la lista de pacientes.
                        </AlertDescription>
                    </Alert>
                </div>
            </AppSidebarLayout>
        );
    }

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
                                    {roles.isCuidador && " - Mostrando solo tus pacientes asignados"}
                                    {roles.isPaciente && " - Mostrando solo tu información"}
                                </CardDescription>
                            </div>
                            
                            {/* Botón crear - Solo para usuarios autorizados */}
                            <CanAccess resource="pacientes" action="create">
                                <CreateButton resource="pacientes" asChild>
                                    <Link href={route('pacientes.create')}>
                                        <Plus className="h-4 w-4 mr-2" />
                                        Nuevo Paciente
                                    </Link>
                                </CreateButton>
                            </CanAccess>
                        </div>
                    </CardHeader>

                    {/* Información contextual según el rol */}
                    <MedicalOnly>
                        <CardContent className="pt-0">
                            <Alert className="mb-4">
                                <AlertDescription>
                                    Como personal médico, puedes gestionar todos los pacientes del sistema.
                                </AlertDescription>
                            </Alert>
                        </CardContent>
                    </MedicalOnly>

                    <CanAccess role="cuidador">
                        <CardContent className="pt-0">
                            <Alert className="mb-4">
                                <AlertDescription>
                                    Como cuidador, solo puedes ver y gestionar los pacientes que tienes asignados.
                                </AlertDescription>
                            </Alert>
                        </CardContent>
                    </CanAccess>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nombre</TableHead>
                                    <TableHead>Documento</TableHead>
                                    <TableHead>Edad</TableHead>
                                    <TableHead>Género</TableHead>
                                    
                                    {/* Solo mostrar tipo de sangre a personal médico */}
                                    <MedicalOnly>
                                        <TableHead>Tipo Sangre</TableHead>
                                    </MedicalOnly>
                                    
                                    <TableHead>Estado</TableHead>
                                    
                                    {/* Solo mostrar información de usuario a admin y médicos */}
                                    <MedicalOnly>
                                        <TableHead>Usuario</TableHead>
                                    </MedicalOnly>
                                    
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
                                        
                                        {/* Tipo de sangre solo para personal médico */}
                                        <MedicalOnly>
                                            <TableCell>
                                                {paciente.tipo_sangre && (
                                                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        {formatTipoSangre(paciente.tipo_sangre)}
                                                    </span>
                                                )}
                                            </TableCell>
                                        </MedicalOnly>
                                        
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
                                        
                                        {/* Información de usuario solo para personal médico */}
                                        <MedicalOnly>
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
                                        </MedicalOnly>
                                        
                                        <TableCell>
                                            <div className="flex items-center gap-1">
                                                {/* Botón Ver - Todos los roles autorizados pueden ver */}
                                                <ViewButton 
                                                    resource="pacientes" 
                                                    variant="ghost" 
                                                    size="icon"
                                                    asChild
                                                >
                                                    <Link href={route('pacientes.show', paciente.id)}>
                                                        <Eye className="h-4 w-4" />
                                                    </Link>
                                                </ViewButton>

                                                {/* Botón Editar - Solo personal autorizado */}
                                                <EditButton 
                                                    resource="pacientes" 
                                                    variant="ghost" 
                                                    size="icon"
                                                    hideWhenDenied
                                                    asChild
                                                >
                                                    <Link href={route('pacientes.edit', paciente.id)}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                </EditButton>

                                                {/* Botón Eliminar - Solo admin y médicos */}
                                                <DeleteButton
                                                    resource="pacientes"
                                                    variant="ghost"
                                                    size="icon"
                                                    hideWhenDenied
                                                    onClick={() => handleDelete(paciente.id)}
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </DeleteButton>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {/* Mostrar estadísticas solo a admin */}
                        <AdminOnly>
                            <div className="mt-6 p-4 bg-gray-50 rounded-lg">
                                <h3 className="text-sm font-medium text-gray-700 mb-2">Estadísticas (Solo Admin)</h3>
                                <div className="grid grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span className="text-gray-500">Total pacientes:</span>
                                        <span className="ml-2 font-medium">{pacientes.data.length}</span>
                                    </div>
                                    <div>
                                        <span className="text-gray-500">Pacientes activos:</span>
                                        <span className="ml-2 font-medium">
                                            {pacientes.data.filter(p => p.activo).length}
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-gray-500">Con usuario asignado:</span>
                                        <span className="ml-2 font-medium">
                                            {pacientes.data.filter(p => p.user).length}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </AdminOnly>
                    </CardContent>
                </Card>
            </div>
        </AppSidebarLayout>
    );
} 