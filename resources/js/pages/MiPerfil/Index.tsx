import React from 'react';
import { Link } from '@inertiajs/react';
import { useAuth } from '@/hooks/use-auth';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { User, Phone, MapPin, Calendar, Edit, CheckCircle, XCircle, Shield } from 'lucide-react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';


export default function MiPerfil() {
  const auth = useAuth();
  const user = auth.user;

  // Función para formatear fechas
  const formatDate = (dateString: string | unknown): string => {
    if (!dateString || typeof dateString !== 'string') return 'No especificado';
    try {
      return new Date(dateString).toLocaleDateString('es-CL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    } catch {
      return 'Fecha inválida';
    }
  };



  return (
    <AppSidebarLayout>
      <div className="container mx-auto py-6 max-w-4xl">
        {/* Header */}
        <div className="mb-6">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">
                Mi Perfil
              </h1>
              <p className="text-gray-600 mt-2">
                Información personal y de contacto
              </p>
            </div>
            <Link href="/mi-perfil/editar">
              <Button>
                <Edit className="h-4 w-4 mr-2" />
                Editar Perfil
              </Button>
            </Link>
          </div>
        </div>



        <div className="grid gap-6">
          {/* Información Básica */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <User className="h-5 w-5 text-blue-600" />
                Información Personal
              </CardTitle>
              <CardDescription>
                Datos básicos de identificación
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid md:grid-cols-2 gap-6">
                <div>
                  <h4 className="font-medium text-sm text-muted-foreground">Nombre completo</h4>
                  <p className="text-lg">{user?.name ? String(user.name) : 'No especificado'}</p>
                </div>
                <div>
                  <h4 className="font-medium text-sm text-muted-foreground">Email</h4>
                  <p className="text-lg">{user?.email ? String(user.email) : 'No especificado'}</p>
                </div>
              </div>

              <div className="grid md:grid-cols-2 gap-6">
                <div>
                  <h4 className="font-medium text-sm text-muted-foreground">Fecha de nacimiento</h4>
                  <p className="text-lg flex items-center gap-2">
                    <Calendar className="h-4 w-4 text-muted-foreground" />
                    {user?.fecha_nacimiento ? formatDate(user.fecha_nacimiento) : 'No especificado'}
                  </p>
                </div>
                <div>
                  <h4 className="font-medium text-sm text-muted-foreground">Rol en el sistema</h4>
                  <div className="flex items-center gap-2">
                    <Shield className="h-4 w-4 text-muted-foreground" />
                    <Badge variant="outline">
                      {user?.role?.nombre ? String(user.role.nombre) : 'Sin rol asignado'}
                    </Badge>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
          {/* Información de Contacto */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Phone className="h-5 w-5 text-green-600" />
                Información de Contacto
              </CardTitle>
              <CardDescription>
                Datos para comunicación
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid md:grid-cols-2 gap-6">
                <div>
                  <h4 className="font-medium text-sm text-muted-foreground">Teléfono</h4>
                  <p className="text-lg">{user?.telefono ? String(user.telefono) : 'No especificado'}</p>
                </div>
                <div>
                  <h4 className="font-medium text-sm text-muted-foreground">Dirección</h4>
                  <p className="text-lg flex items-center gap-2">
                    <MapPin className="h-4 w-4 text-muted-foreground" />
                    {user?.direccion ? String(user.direccion) : 'No especificado'}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>



          {/* Contacto de Emergencia */}
          {(user?.contacto_emergencia_nombre || user?.contacto_emergencia_telefono) ? (
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Phone className="h-5 w-5 text-orange-600" />
                  Contacto de Emergencia
                </CardTitle>
                <CardDescription>
                  Persona a contactar en caso de emergencia
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid md:grid-cols-3 gap-6">
                  <div>
                    <h4 className="font-medium text-sm text-muted-foreground">Nombre</h4>
                    <p className="text-lg">{user?.contacto_emergencia_nombre ? String(user.contacto_emergencia_nombre) : 'No especificado'}</p>
                  </div>
                  <div>
                    <h4 className="font-medium text-sm text-muted-foreground">Teléfono</h4>
                    <p className="text-lg">{user?.contacto_emergencia_telefono ? String(user.contacto_emergencia_telefono) : 'No especificado'}</p>
                  </div>
                  <div>
                    <h4 className="font-medium text-sm text-muted-foreground">Relación</h4>
                    <p className="text-lg">{user?.contacto_emergencia_relacion ? String(user.contacto_emergencia_relacion) : 'No especificado'}</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          ) : null}

          {/* Información de Cuenta */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Shield className="h-5 w-5 text-purple-600" />
                Información de la Cuenta
              </CardTitle>
              <CardDescription>
                Detalles de tu cuenta en el sistema
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid md:grid-cols-2 gap-6">
                <div>
                  <h4 className="font-medium text-sm text-muted-foreground">Fecha de registro</h4>
                  <p className="text-lg">{user?.created_at ? formatDate(user.created_at) : 'No disponible'}</p>
                </div>
                <div>
                  <h4 className="font-medium text-sm text-muted-foreground">Estado de verificación</h4>
                  <div className="flex items-center gap-2">
                    {user?.email_verified_at && typeof user.email_verified_at === 'string' ? (
                      <>
                        <CheckCircle className="h-4 w-4 text-green-600" />
                        <Badge variant="outline" className="text-green-700 border-green-300">
                          Email verificado
                        </Badge>
                      </>
                    ) : (
                      <>
                        <XCircle className="h-4 w-4 text-red-600" />
                        <Badge variant="outline" className="text-red-700 border-red-300">
                          Email pendiente
                        </Badge>
                      </>
                    )}
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </AppSidebarLayout>
  );
}