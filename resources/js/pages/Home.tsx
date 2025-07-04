import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import AppSidebarLayout from '@/layouts/app/app-sidebar-layout';
import { Bell, Calendar, Heart, Settings, User, Pill, Clock, TrendingUp } from 'lucide-react';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";

interface HomeProps {
    user?: {
        name: string;
        role?: string;
    };
}

export default function Home({ user }: HomeProps) {
    const mainOptions = [
        {
            title: "Mis Medicamentos",
            description: "Ver y gestionar mis medicamentos y tratamientos activos",
            icon: Pill,
            href: "/tratamientos",
            color: "text-blue-600",
            bgColor: "bg-blue-50"
        },
        {
            title: "Cronograma",
            description: "Revisar horarios de medicación y próximas dosis",
            icon: Calendar,
            href: "/cronograma",
            color: "text-green-600",
            bgColor: "bg-green-50"
        },
        {
            title: "Alertas",
            description: "Notificaciones y recordatorios importantes",
            icon: Bell,
            href: "/administraciones/pendientes",
            color: "text-amber-600",
            bgColor: "bg-amber-50"
        },
        {
            title: "Mi Adherencia",
            description: "Seguimiento de mi cumplimiento con el tratamiento",
            icon: TrendingUp,
            href: "/dashboard/medicamentos",
            color: "text-purple-600",
            bgColor: "bg-purple-50"
        },
        {
            title: "Historial",
            description: "Ver registro de medicamentos administrados",
            icon: Clock,
            href: "/administraciones/historial",
            color: "text-indigo-600",
            bgColor: "bg-indigo-50"
        },
        {
            title: "Mi Perfil",
            description: "Actualizar información personal y configuración",
            icon: User,
            href: "/mi-perfil/editar",
            color: "text-gray-600",
            bgColor: "bg-gray-50"
        }
    ];

    return (
        <AppSidebarLayout>
            <div className="p-6 space-y-6">
                <div className="flex items-center gap-4">
                    <div className="p-2 bg-primary/10 rounded-full">
                        <Heart className="h-6 w-6 text-primary" />
                    </div>
                    <div>
                        <h1 className="text-2xl font-bold">
                            ¡Bienvenido{user?.name ? `, ${user.name}` : ''}!
                        </h1>
                        <p className="text-muted-foreground">
                            Tu salud es nuestra prioridad. ¿Qué deseas hacer hoy?
                        </p>
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl">
                    {mainOptions.map((option, index) => {
                        const IconComponent = option.icon;
                        
                        return (
                            <Link key={index} href={option.href}>
                                <Card className="cursor-pointer transition-all duration-200 hover:shadow-lg hover:scale-105 border-2 hover:border-primary/50 h-full">
                                    <CardHeader className="text-center pb-4">
                                        <div className={`mx-auto mb-4 p-3 ${option.bgColor} rounded-full w-fit`}>
                                            <IconComponent className={`h-8 w-8 ${option.color}`} />
                                        </div>
                                        <CardTitle className="text-lg">{option.title}</CardTitle>
                                    </CardHeader>
                                    <CardContent className="text-center">
                                        <CardDescription className="text-sm leading-relaxed">
                                            {option.description}
                                        </CardDescription>
                                    </CardContent>
                                </Card>
                            </Link>
                        );
                    })}
                </div>

                <div className="mt-8 max-w-4xl">
                    <Card className="bg-blue-50 border-blue-200">
                        <CardContent className="pt-6">
                            <div className="flex items-start gap-3">
                                <div className="p-2 bg-blue-100 rounded-full">
                                    <Heart className="h-5 w-5 text-blue-600" />
                                </div>
                                <div>
                                    <h3 className="font-medium text-blue-900 mb-1">Recordatorio Importante</h3>
                                    <p className="text-sm text-blue-700">
                                        No olvides tomar tus medicamentos según lo prescrito. 
                                        La consistencia en tu tratamiento es clave para tu bienestar.
                                    </p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppSidebarLayout>
    );
} 