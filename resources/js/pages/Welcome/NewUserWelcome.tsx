import React from 'react';
import { Link } from '@inertiajs/react';
import { useAuth } from '@/hooks/use-auth';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
  Pill, 
  Plus, 
  Calendar, 
  Activity, 
  User, 
  ArrowRight, 
  Heart, 
  Shield, 
  Clock,
  CheckCircle,
  Stethoscope
} from 'lucide-react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface Props {
  hasMedicamentos?: boolean;
  hasTratamientos?: boolean;
  hasProfileCompleted?: boolean;
}

export default function NewUserWelcome({ 
  hasMedicamentos = false, 
  hasTratamientos = false,
  hasProfileCompleted = false 
}: Props) {
  const auth = useAuth();
  const user = auth.user;

  // Calcular progreso del onboarding
  const steps = [
    { id: 'profile', completed: hasProfileCompleted, label: 'Completar perfil' },
    { id: 'medicamentos', completed: hasMedicamentos, label: 'Agregar medicamentos' },
    { id: 'tratamientos', completed: hasTratamientos, label: 'Crear tratamientos' }
  ];
  
  const completedSteps = steps.filter(step => step.completed).length;
  const progressPercentage = (completedSteps / steps.length) * 100;

  return (
    <AppSidebarLayout>
      <div className="container mx-auto py-6 max-w-5xl">
        {/* Header de bienvenida */}
        <div className="text-center mb-8">
          <div className="mx-auto mb-4 p-4 bg-primary/10 rounded-full w-fit">
            <Heart className="h-12 w-12 text-primary" />
          </div>
          <h1 className="text-3xl font-bold mb-2">
            ¡Bienvenido a MediTrack, {user?.name?.split(' ')[0]}!
          </h1>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Tu plataforma personal para llevar un control completo de tus medicamentos y tratamientos. 
            Te ayudamos a mejorar tu adherencia al tratamiento y cuidar tu salud.
          </p>
        </div>

        {/* Progreso del onboarding */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Shield className="h-5 w-5 text-primary" />
              Configuración inicial ({completedSteps}/3 completado)
            </CardTitle>
            <CardDescription>
              Completa estos pasos para aprovechar al máximo MediTrack
            </CardDescription>
          </CardHeader>
          <CardContent>
            {/* Barra de progreso */}
            <div className="mb-6">
              <div className="flex justify-between text-sm mb-2">
                <span>Progreso</span>
                <span className="font-medium">{Math.round(progressPercentage)}%</span>
              </div>
              <div className="w-full bg-muted rounded-full h-3">
                <div 
                  className="h-3 rounded-full bg-primary transition-all duration-500"
                  style={{ width: `${progressPercentage}%` }}
                ></div>
              </div>
            </div>

            {/* Lista de pasos */}
            <div className="space-y-4">
              {steps.map((step, index) => (
                <div key={step.id} className={`flex items-center gap-4 p-4 rounded-lg border transition-colors ${
                  step.completed ? 'bg-green-50 border-green-200' : 'bg-muted/30 border-border'
                }`}>
                  <div className={`flex items-center justify-center w-8 h-8 rounded-full ${
                    step.completed ? 'bg-green-600 text-white' : 'bg-muted text-muted-foreground'
                  }`}>
                    {step.completed ? (
                      <CheckCircle className="h-5 w-5" />
                    ) : (
                      <span className="text-sm font-medium">{index + 1}</span>
                    )}
                  </div>
                  <div className="flex-1">
                    <p className={`font-medium ${step.completed ? 'text-green-800' : 'text-foreground'}`}>
                      {step.label}
                    </p>
                    {step.completed && (
                      <p className="text-sm text-green-600">✓ Completado</p>
                    )}
                  </div>
                  {step.completed && (
                    <Badge variant="secondary" className="bg-green-100 text-green-800">
                      Listo
                    </Badge>
                  )}
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Acciones rápidas */}
        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
          {/* Completar perfil */}
          {!hasProfileCompleted && (
            <Card className="cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-105 border-2 hover:border-primary/50">
              <CardHeader className="text-center pb-4">
                <div className="mx-auto mb-4 p-3 bg-primary/10 rounded-full w-fit">
                  <User className="h-8 w-8 text-primary" />
                </div>
                <CardTitle className="text-lg">Completar Mi Perfil</CardTitle>
              </CardHeader>
              <CardContent className="text-center">
                <CardDescription className="text-sm leading-relaxed">
                  Agrega tu información personal, condiciones médicas y contactos de emergencia.
                </CardDescription>
                <div className="mt-4">
                  <Link href="/mi-perfil/editar">
                    <Button className="w-full" size="sm">
                      <Plus className="h-4 w-4 mr-2" />
                      Completar Perfil
                    </Button>
                  </Link>
                </div>
              </CardContent>
            </Card>
          )}

          {/* Agregar medicamentos */}
          <Card className="cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-105 border-2 hover:border-primary/50">
            <CardHeader className="text-center pb-4">
              <div className="mx-auto mb-4 p-3 bg-primary/10 rounded-full w-fit">
                <Pill className="h-8 w-8 text-primary" />
              </div>
              <CardTitle className="text-lg">Mis Medicamentos</CardTitle>
            </CardHeader>
            <CardContent className="text-center">
              <CardDescription className="text-sm leading-relaxed">
                {hasMedicamentos 
                  ? 'Revisa y gestiona tus medicamentos registrados.'
                  : 'Comienza agregando los medicamentos que tomas regularmente.'
                }
              </CardDescription>
              <div className="mt-4">
                <Link href="/mis-medicamentos">
                  <Button className="w-full" size="sm" variant={hasMedicamentos ? "outline" : "default"}>
                    {hasMedicamentos ? (
                      <>
                        <ArrowRight className="h-4 w-4 mr-2" />
                        Ver Medicamentos
                      </>
                    ) : (
                      <>
                        <Plus className="h-4 w-4 mr-2" />
                        Agregar Medicamentos
                      </>
                    )}
                  </Button>
                </Link>
              </div>
            </CardContent>
          </Card>

          {/* Crear tratamientos */}
          <Card className="cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-105 border-2 hover:border-primary/50">
            <CardHeader className="text-center pb-4">
              <div className="mx-auto mb-4 p-3 bg-primary/10 rounded-full w-fit">
                <Activity className="h-8 w-8 text-primary" />
              </div>
              <CardTitle className="text-lg">Mis Tratamientos</CardTitle>
            </CardHeader>
            <CardContent className="text-center">
              <CardDescription className="text-sm leading-relaxed">
                {hasTratamientos 
                  ? 'Revisa el progreso de tus tratamientos activos.'
                  : 'Organiza tus medicamentos en tratamientos con horarios específicos.'
                }
              </CardDescription>
              <div className="mt-4">
                <Link href="/mis-tratamientos">
                  <Button className="w-full" size="sm" variant={hasTratamientos ? "outline" : "default"}>
                    {hasTratamientos ? (
                      <>
                        <ArrowRight className="h-4 w-4 mr-2" />
                        Ver Tratamientos
                      </>
                    ) : (
                      <>
                        <Plus className="h-4 w-4 mr-2" />
                        Crear Tratamiento
                      </>
                    )}
                  </Button>
                </Link>
              </div>
            </CardContent>
          </Card>

          {/* Cronograma */}
          <Card className="cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-105 border-2 hover:border-primary/50">
            <CardHeader className="text-center pb-4">
              <div className="mx-auto mb-4 p-3 bg-primary/10 rounded-full w-fit">
                <Calendar className="h-8 w-8 text-primary" />
              </div>
              <CardTitle className="text-lg">Mi Cronograma</CardTitle>
            </CardHeader>
            <CardContent className="text-center">
              <CardDescription className="text-sm leading-relaxed">
                Visualiza los horarios de todos tus medicamentos en un calendario semanal.
              </CardDescription>
              <div className="mt-4">
                <Link href="/mi-cronograma">
                  <Button className="w-full" size="sm" variant="outline">
                    <Clock className="h-4 w-4 mr-2" />
                    Ver Cronograma
                  </Button>
                </Link>
              </div>
            </CardContent>
          </Card>

          {/* Consultar con médico */}
          <Card className="cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-105 border-2 hover:border-primary/50">
            <CardHeader className="text-center pb-4">
              <div className="mx-auto mb-4 p-3 bg-primary/10 rounded-full w-fit">
                <Stethoscope className="h-8 w-8 text-primary" />
              </div>
              <CardTitle className="text-lg">Consulta Médica</CardTitle>
            </CardHeader>
            <CardContent className="text-center">
              <CardDescription className="text-sm leading-relaxed">
                ¿Necesitas ayuda médica? Te conectamos con profesionales de la salud.
              </CardDescription>
              <div className="mt-4">
                <Button className="w-full" size="sm" variant="outline" disabled>
                  <Stethoscope className="h-4 w-4 mr-2" />
                  Próximamente
                </Button>
              </div>
            </CardContent>
          </Card>

          {/* Recursos educativos */}
          <Card className="cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-105 border-2 hover:border-primary/50">
            <CardHeader className="text-center pb-4">
              <div className="mx-auto mb-4 p-3 bg-primary/10 rounded-full w-fit">
                <Shield className="h-8 w-8 text-primary" />
              </div>
              <CardTitle className="text-lg">Recursos Educativos</CardTitle>
            </CardHeader>
            <CardContent className="text-center">
              <CardDescription className="text-sm leading-relaxed">
                Aprende sobre adherencia al tratamiento, efectos secundarios y más.
              </CardDescription>
              <div className="mt-4">
                <Button className="w-full" size="sm" variant="outline" disabled>
                  <ArrowRight className="h-4 w-4 mr-2" />
                  Próximamente
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Información importante */}
        <Alert>
          <Shield className="h-4 w-4" />
          <AlertDescription className="text-sm">
            <strong>Importante:</strong> MediTrack es una herramienta de apoyo para el control de medicamentos. 
            Siempre consulta con tu médico antes de hacer cambios en tu tratamiento. En caso de emergencia, 
            contacta inmediatamente a los servicios de urgencia.
          </AlertDescription>
        </Alert>
      </div>
    </AppSidebarLayout>
  );
} 