import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { 
    Search, 
    Code, 
    Copy, 
    Menu,
    FileText,
    Eye,
    MessageCircle,
    Layers,
    MousePointer,
    BarChart3,
    Stethoscope,
    Check
} from 'lucide-react';

interface Component {
    name: string;
    description: string;
    file: string;
    props: string[];
    example: string;
    status: string;
}

interface Category {
    title: string;
    description: string;
    icon: string;
    components: Component[];
}

interface Props {
    catalog: Record<string, Category>;
    totalComponents: number;
}

const iconMap: Record<string, React.ComponentType<any>> = {
    Menu,
    FileText,
    Eye,
    MessageCircle,
    Layers,
    MousePointer,
    BarChart3,
    Stethoscope
};

export default function ComponentCatalogIndex({ catalog, totalComponents }: Props) {
    const [searchTerm, setSearchTerm] = useState('');
    const [copiedExample, setCopiedExample] = useState<string | null>(null);

    const categories = Object.entries(catalog);
    
    const filteredCategories = categories.filter(([key, category]) => {
        if (!searchTerm) return true;
        
        const searchLower = searchTerm.toLowerCase();
        return (
            category.title.toLowerCase().includes(searchLower) ||
            category.description.toLowerCase().includes(searchLower) ||
            category.components.some(component => 
                component.name.toLowerCase().includes(searchLower) ||
                component.description.toLowerCase().includes(searchLower)
            )
        );
    });

    const copyToClipboard = (text: string, componentName: string) => {
        navigator.clipboard.writeText(text).then(() => {
            setCopiedExample(componentName);
            setTimeout(() => setCopiedExample(null), 2000);
        });
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'stable':
                return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
            case 'beta':
                return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
            case 'alpha':
                return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        }
    };

    return (
        <AppLayout>
            <Head title="Catálogo de Componentes" />
            
            <div className="container mx-auto px-4 py-8 max-w-7xl">
                <div className="mb-8">
                    <div className="flex items-center gap-3 mb-4">
                        <div className="h-12 w-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <Code className="h-6 w-6 text-white" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                Catálogo de Componentes
                            </h1>
                            <p className="text-gray-600 dark:text-gray-400">
                                Biblioteca de componentes basada en Shadcn/UI para MediTrack
                            </p>
                        </div>
                    </div>
                    
                    <Alert className="mb-6">
                        <Stethoscope className="h-4 w-4" />
                        <AlertDescription>
                            Este catálogo incluye {totalComponents} componentes organizados en {categories.length} categorías. 
                            Todos los componentes están basados en Shadcn/UI y siguiendo las mejores prácticas de diseño.
                        </AlertDescription>
                    </Alert>

                    <div className="relative mb-6">
                        <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <Input
                            placeholder="Buscar componentes..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="pl-10"
                        />
                    </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {filteredCategories.map(([categoryKey, category]) => {
                        const IconComponent = iconMap[category.icon] || Code;
                        
                        return (
                            <Card key={categoryKey} className="h-fit">
                                <CardHeader>
                                    <div className="flex items-center gap-3">
                                        <div className="h-10 w-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                            <IconComponent className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div>
                                            <CardTitle className="text-lg">{category.title}</CardTitle>
                                            <CardDescription className="text-sm">
                                                {category.components.length} componentes
                                            </CardDescription>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        {category.description}
                                    </p>
                                    <div className="space-y-3">
                                        {category.components.map((component) => (
                                            <div key={component.name} className="border rounded-lg p-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                                <div className="flex items-start justify-between mb-2">
                                                    <h4 className="font-semibold text-sm">{component.name}</h4>
                                                    <Badge className={'text-xs ' + getStatusColor(component.status)}>
                                                        {component.status}
                                                    </Badge>
                                                </div>
                                                <p className="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                                    {component.description}
                                                </p>
                                                <div className="bg-gray-100 dark:bg-gray-800 rounded p-2 relative">
                                                    <code className="text-xs font-mono text-gray-800 dark:text-gray-200 break-all">
                                                        {component.example}
                                                    </code>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        className="absolute top-1 right-1 h-6 w-6 p-0"
                                                        onClick={() => copyToClipboard(component.example, component.name)}
                                                    >
                                                        {copiedExample === component.name ? (
                                                            <Check className="h-3 w-3 text-green-600" />
                                                        ) : (
                                                            <Copy className="h-3 w-3" />
                                                        )}
                                                    </Button>
                                                </div>
                                                {component.props.length > 0 && (
                                                    <div className="mt-2">
                                                        <p className="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Props:</p>
                                                        <div className="flex flex-wrap gap-1">
                                                            {component.props.map((prop) => (
                                                                <Badge key={prop} variant="outline" className="text-xs">
                                                                    {prop}
                                                                </Badge>
                                                            ))}
                                                        </div>
                                                    </div>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                {filteredCategories.length === 0 && (
                    <div className="text-center py-12">
                        <Code className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                        <h3 className="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            No se encontraron componentes
                        </h3>
                        <p className="text-gray-600 dark:text-gray-400">
                            Intenta ajustar tu búsqueda.
                        </p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
