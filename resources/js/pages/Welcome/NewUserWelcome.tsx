import React, { useState } from 'react';
import { useAuth } from '@/hooks/use-auth';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
  User, 
  Heart, 
  Pill, 
  Calendar, 
  UserPlus, 
  Stethoscope, 
  CheckCircle, 
  ArrowRight,
  Users,
  Clock,
  BarChart3,
  Shield,
  Eye,
  ArrowLeft,
  Plus
} from 'lucide-react';
import { Link } from '@inertiajs/react';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';

interface WelcomeStep {
  id: string;
  title: string;
  description: string;
  icon: React.ComponentType<{ className?: string }>;
  href: string;
  completed: boolean;
  priority: 'high' | 'medium' | 'low';
}

interface UserGuide {
  role: string;
  title: string;
  subtitle: string;
  steps: WelcomeStep[];
  quickActions: QuickAction[];
}

interface QuickAction {
  title: string;
  description: string;
  icon: React.ComponentType<{ className?: string }>;
  href: string;
  variant: 'default' | 'secondary' | 'outline';
}

export default function NewUserWelcome() {
  const auth = useAuth();
  const [completedSteps, setCompletedSteps] = useState<string[]>([]);
  
  // Guías específicas por rol
  const userGuides: Record<string, UserGuide> = {
    paciente: {
      role: 'paciente',
      title: '¡Bienvenido a MediTrack!',
      subtitle: 'Te ayudamos a gestionar tu salud de manera sencilla',
      steps: [
        {
          id: 'profile',
          title: 'Completa tu perfil',
          description: 'Agrega información básica como fecha de nacimiento y contactos de emergencia',
          icon: User,
          href: '/mi-perfil/editar',
          completed: false,
          priority: 'high'
        },
        {
          id: 'treatment',
          title: 'Registra tu primer tratamiento',
          description: 'Agrega los medicamentos que tomas actualmente',
          icon: Pill,
          href: '/mis-tratamientos/crear',
          completed: false,
          priority: 'high'
        },
        {
          id: 'schedule',
          title: 'Configura tus horarios',
          description: 'Establece recordatorios para tomar tus medicamentos',
          icon: Calendar,
          href: '/mi-cronograma',
          completed: false,
          priority: 'medium'
        }
      ],
      quickActions: [
        {
          title: 'Agregar Medicamento',
          description: 'Registra un medicamento que tomas',
          icon: Pill,
          href: '/mis-tratamientos/crear',
          variant: 'default'
        }
      ]
    },
    apoderado: {
      role: 'apoderado',
      title: '¡Bienvenido, Apoderado!',
      subtitle: 'Gestiona la salud de las personas que cuidas',
      steps: [
        {
          id: 'profile',
          title: 'Completa tu perfil',
          description: 'Información de contacto y relación con el paciente',
          icon: User,
          href: '/perfil/editar',
          completed: false,
          priority: 'high'
        },
        {
          id: 'patient',
          title: 'Registra a la persona que cuidas',
          description: 'Agrega información del paciente bajo tu cuidado',
          icon: UserPlus,
          href: '/pacientes/crear',
          completed: false,
          priority: 'high'
        },
        {
          id: 'treatment',
          title: 'Registra tratamientos',
          description: 'Agrega los medicamentos del paciente',
          icon: Pill,
          href: '/tratamientos/crear',
          completed: false,
          priority: 'medium'
        }
      ],
      quickActions: [
        {
          title: 'Registrar Paciente',
          description: 'Agrega a la persona que cuidas',
          icon: UserPlus,
          href: '/pacientes/crear',
          variant: 'default'
        }
      ]
    },
    cuidador: {
      role: 'cuidador',
      title: '¡Bienvenido, Cuidador!',
      subtitle: 'Herramientas profesionales para el cuidado de pacientes',
      steps: [
        {
          id: 'profile',
          title: 'Completa tu perfil profesional',
          description: 'Certificaciones, experiencia y disponibilidad',
          icon: User,
          href: '/perfil/editar',
          completed: false,
          priority: 'high'
        },
        {
          id: 'assignments',
          title: 'Revisa tus asignaciones',
          description: 'Ve los pacientes bajo tu cuidado',
          icon: Users,
          href: '/mis-pacientes',
          completed: false,
          priority: 'high'
        },
        {
          id: 'schedule',
          title: 'Configura tu cronograma',
          description: 'Horarios de administración de medicamentos',
          icon: Clock,
          href: '/cronograma',
          completed: false,
          priority: 'medium'
        }
      ],
      quickActions: [
        {
          title: 'Ver Pacientes',
          description: 'Revisa tus asignaciones',
          icon: Users,
          href: '/mis-pacientes',
          variant: 'default'
        }
      ]
    },
    medico: {
      role: 'medico',
      title: '¡Bienvenido, Doctor!',
      subtitle: 'Gestiona tus pacientes y prescripciones digitalmente',
      steps: [
        {
          id: 'profile',
          title: 'Completa tu perfil médico',
          description: 'Especialidad, colegiatura e institución',
          icon: User,
          href: '/perfil/editar',
          completed: false,
          priority: 'high'
        },
        {
          id: 'patients',
          title: 'Gestiona tus pacientes',
          description: 'Ve y administra tu lista de pacientes',
          icon: Users,
          href: '/mis-pacientes',
          completed: false,
          priority: 'high'
        },
        {
          id: 'prescriptions',
          title: 'Crea prescripciones',
          description: 'Prescribe tratamientos digitalmente',
          icon: Pill,
          href: '/prescripciones/crear',
          completed: false,
          priority: 'medium'
        }
      ],
      quickActions: [
        {
          title: 'Crear Prescripción',
          description: 'Prescribir nuevo tratamiento',
          icon: Pill,
          href: '/prescripciones/crear',
          variant: 'default'
        },
        {
          title: 'Ver Pacientes',
          description: 'Lista de pacientes activos',
          icon: Users,
          href: '/pacientes',
          variant: 'secondary'
        }
      ]
    },
    admin: {
      role: 'admin',
      title: '¡Bienvenido, Administrador!',
      subtitle: 'Gestiona el sistema y supervisa todas las operaciones',
      steps: [
        {
          id: 'system-overview',
          title: 'Revisión del sistema',
          description: 'Monitorea el estado general del sistema',
          icon: BarChart3,
          href: '/dashboard',
          completed: false,
          priority: 'high'
        },
        {
          id: 'user-management',
          title: 'Gestión de usuarios',
          description: 'Administra usuarios del sistema',
          icon: Users,
          href: '/usuarios',
          completed: false,
          priority: 'high'
        },
        {
          id: 'role-permissions',
          title: 'Configurar roles y permisos',
          description: 'Gestiona el sistema de autorización',
          icon: Shield,
          href: '/roles',
          completed: false,
          priority: 'medium'
        },
        {
          id: 'audit-logs',
          title: 'Revisar auditoría',
          description: 'Supervisa los logs del sistema',
          icon: Eye,
          href: '/audit',
          completed: false,
          priority: 'medium'
        }
      ],
      quickActions: [
        {
          title: 'Dashboard del Sistema',
          description: 'Ver métricas generales',
          icon: BarChart3,
          href: '/dashboard',
          variant: 'default'
        },
        {
          title: 'Gestionar Usuarios',
          description: 'Administrar cuentas de usuario',
          icon: Users,
          href: '/usuarios',
          variant: 'secondary'
        },
        {
          title: 'Logs de Auditoría',
          description: 'Revisar actividad del sistema',
          icon: Eye,
          href: '/audit',
          variant: 'outline'
        }
      ]
    }
  };

  const currentUserGuide = userGuides[auth.user?.role?.nombre || 'paciente'] || userGuides['paciente'];

  const markStepCompleted = (stepId: string) => {
    if (!completedSteps.includes(stepId)) {
      setCompletedSteps([...completedSteps, stepId]);
    }
  };

  const getStepsByPriority = (priority: 'high' | 'medium' | 'low') => {
    return currentUserGuide.steps.filter(step => step.priority === priority);
  };

  const getAllSteps = () => {
    return currentUserGuide.steps;
  };

  return (
    <AppSidebarLayout>
      <div className="p-6 space-y-6">
        {/* Header */}
        <div className="flex items-center gap-4">
          <Link href={route('dashboard')}>
            <Button variant="ghost" size="icon">
              <ArrowLeft className="h-4 w-4" />
            </Button>
          </Link>
          <div>
            <h1 className="text-2xl font-bold">
              {currentUserGuide.title}
            </h1>
            <p className="text-muted-foreground">
              {currentUserGuide.subtitle}
            </p>
          </div>
        </div>

        {/* User Role Badge */}
        <div className="flex items-center gap-2">
          <Badge variant="outline">
            Usuario Nuevo
          </Badge>
          <Badge variant="secondary">
            {auth.user?.role?.nombre?.toUpperCase()}
          </Badge>
        </div>

        {/* Quick Actions */}
        <div>
          <h2 className="text-xl font-semibold mb-4">Acciones Rápidas</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {currentUserGuide.quickActions.map((action, index) => (
              <Link key={index} href={action.href}>
                <Card className="cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-105 border-2 hover:border-primary/50">
                  <CardHeader className="text-center pb-4">
                    <div className="mx-auto mb-4 p-3 bg-primary/10 rounded-full w-fit">
                      <action.icon className="h-8 w-8 text-primary" />
                    </div>
                    <CardTitle className="text-lg">{action.title}</CardTitle>
                  </CardHeader>
                  <CardContent className="text-center">
                    <CardDescription className="text-sm leading-relaxed">
                      {action.description}
                    </CardDescription>
                    <div className="mt-4">
                      <Button className="w-full" size="sm">
                        <Plus className="h-4 w-4 mr-2" />
                        Comenzar
                      </Button>
                    </div>
                  </CardContent>
                </Card>
              </Link>
            ))}
          </div>
        </div>

        {/* Steps to Complete */}
        <div>
          <h2 className="text-xl font-semibold mb-4">Pasos para Completar</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {getAllSteps().map((step) => {
              const isCompleted = completedSteps.includes(step.id);
              const IconComponent = step.icon;
              
              return (
                <Link key={step.id} href={step.href}>
                  <Card className={`cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-105 border-2 hover:border-primary/50 ${
                    isCompleted ? 'bg-green-50 border-green-200' : ''
                  }`}>
                    <CardHeader className="text-center pb-4">
                      <div className={`mx-auto mb-4 p-3 rounded-full w-fit ${
                        isCompleted ? 'bg-green-100' : 'bg-primary/10'
                      }`}>
                        {isCompleted ? (
                          <CheckCircle className="h-8 w-8 text-green-600" />
                        ) : (
                          <IconComponent className="h-8 w-8 text-primary" />
                        )}
                      </div>
                      <CardTitle className={`text-lg ${
                        isCompleted ? 'line-through text-gray-500' : ''
                      }`}>
                        {step.title}
                      </CardTitle>
                    </CardHeader>
                    <CardContent className="text-center">
                      <CardDescription className={`text-sm leading-relaxed ${
                        isCompleted ? 'text-gray-400' : ''
                      }`}>
                        {step.description}
                      </CardDescription>
                      <div className="mt-4">
                        {!isCompleted ? (
                          <Button className="w-full" size="sm">
                            <Plus className="h-4 w-4 mr-2" />
                            Completar
                          </Button>
                        ) : (
                          <Button variant="outline" className="w-full" size="sm" disabled>
                            <CheckCircle className="h-4 w-4 mr-2" />
                            Completado
                          </Button>
                        )}
                      </div>
                    </CardContent>
                  </Card>
                </Link>
              );
            })}
          </div>
        </div>
      </div>
    </AppSidebarLayout>
  );
} 