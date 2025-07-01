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
  Clock
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
    }
  };

  const currentUserGuide = userGuides[auth.user?.role?.nombre || 'paciente'];

  const markStepCompleted = (stepId: string) => {
    if (!completedSteps.includes(stepId)) {
      setCompletedSteps([...completedSteps, stepId]);
    }
  };

  const getStepsByPriority = (priority: 'high' | 'medium' | 'low') => {
    return currentUserGuide.steps.filter(step => step.priority === priority);
  };

  const getPriorityColor = (priority: 'high' | 'medium' | 'low') => {
    switch (priority) {
      case 'high': return 'bg-red-100 text-red-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      case 'low': return 'bg-green-100 text-green-800';
    }
  };

  const getPriorityLabel = (priority: 'high' | 'medium' | 'low') => {
    switch (priority) {
      case 'high': return 'Urgente';
      case 'medium': return 'Importante';
      case 'low': return 'Opcional';
    }
  };

  return (
    <AppSidebarLayout>
      <div className="container mx-auto py-6 max-w-4xl">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">
            {currentUserGuide.title}
          </h1>
          <p className="text-gray-600 mb-4">
            {currentUserGuide.subtitle}
          </p>
          <div className="flex items-center gap-2">
            <Badge variant="secondary">
              {auth.user?.role?.nombre?.toUpperCase()}
            </Badge>
            <Badge variant="outline">
              Usuario Nuevo
            </Badge>
          </div>
        </div>

        {/* Acciones Rápidas */}
        <div className="grid md:grid-cols-2 gap-6 mb-8">
          {currentUserGuide.quickActions.map((action, index) => (
            <Card key={index}>
              <CardHeader>
                <CardTitle className="flex items-center gap-3">
                  <div className="p-2 bg-blue-100 rounded-lg">
                    <action.icon className="h-5 w-5 text-blue-600" />
                  </div>
                  {action.title}
                </CardTitle>
                <CardDescription>{action.description}</CardDescription>
              </CardHeader>
              <CardContent>
                <Link href={action.href}>
                  <Button variant={action.variant} className="w-full">
                    Comenzar
                    <ArrowRight className="h-4 w-4 ml-2" />
                  </Button>
                </Link>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Pasos por Prioridad */}
        {(['high', 'medium', 'low'] as const).map(priority => {
          const steps = getStepsByPriority(priority);
          if (steps.length === 0) return null;

          return (
            <div key={priority} className="mb-8">
              <div className="flex items-center gap-2 mb-4">
                <h2 className="text-xl font-semibold">
                  {priority === 'high' && 'Pasos Urgentes'}
                  {priority === 'medium' && 'Pasos Importantes'}
                  {priority === 'low' && 'Pasos Opcionales'}
                </h2>
                <Badge className={getPriorityColor(priority)}>
                  {getPriorityLabel(priority)}
                </Badge>
              </div>

              <div className="grid md:grid-cols-2 gap-4">
                {steps.map((step) => {
                  const isCompleted = completedSteps.includes(step.id);
                  
                  return (
                    <Card key={step.id} className={`${isCompleted ? 'bg-green-50 border-green-200' : ''}`}>
                      <CardHeader>
                        <CardTitle className="flex items-center gap-3">
                          <div className={`p-2 rounded-lg ${isCompleted ? 'bg-green-100' : 'bg-gray-100'}`}>
                            {isCompleted ? (
                              <CheckCircle className="h-5 w-5 text-green-600" />
                            ) : (
                              <step.icon className="h-5 w-5 text-gray-600" />
                            )}
                          </div>
                          <span className={isCompleted ? 'line-through text-gray-500' : ''}>
                            {step.title}
                          </span>
                        </CardTitle>
                        <CardDescription className={isCompleted ? 'text-gray-400' : ''}>
                          {step.description}
                        </CardDescription>
                      </CardHeader>
                      <CardContent>
                        {!isCompleted ? (
                          <Link href={step.href}>
                            <Button className="w-full" variant="outline">
                              Completar paso
                              <ArrowRight className="h-4 w-4 ml-2" />
                            </Button>
                          </Link>
                        ) : (
                          <Button variant="outline" className="w-full" disabled>
                            <CheckCircle className="h-4 w-4 mr-2" />
                            Completado
                          </Button>
                        )}
                      </CardContent>
                    </Card>
                  );
                })}
              </div>
            </div>
          );
        })}
      </div>
    </AppSidebarLayout>
  );
} 