<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ComponentCatalogController extends Controller
{
    /**
     * Display the component catalog.
     */
    public function index()
    {
        // Catálogo de componentes organizados por categorías
        $componentCatalog = [
            'navigation' => [
                'title' => 'Navegación',
                'description' => 'Componentes para la navegación y estructura de la aplicación',
                'icon' => 'Menu',
                'components' => [
                    [
                        'name' => 'Sidebar',
                        'description' => 'Barra lateral principal de navegación con soporte para rutas anidadas',
                        'file' => 'resources/js/components/ui/sidebar.tsx',
                        'props' => ['children', 'className'],
                        'example' => '<Sidebar>...</Sidebar>',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Navigation Menu',
                        'description' => 'Menú de navegación horizontal con dropdowns',
                        'file' => 'resources/js/components/ui/navigation-menu.tsx',
                        'props' => ['orientation', 'className'],
                        'example' => '<NavigationMenu>...</NavigationMenu>',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Breadcrumb',
                        'description' => 'Navegación de migas de pan para mostrar la ubicación actual',
                        'file' => 'resources/js/components/ui/breadcrumb.tsx',
                        'props' => ['children', 'className'],
                        'example' => '<Breadcrumb>...</Breadcrumb>',
                        'status' => 'stable'
                    ]
                ]
            ],
            'forms' => [
                'title' => 'Formularios',
                'description' => 'Componentes para la entrada y validación de datos',
                'icon' => 'FileText',
                'components' => [
                    [
                        'name' => 'Input',
                        'description' => 'Campo de entrada de texto con estilos consistentes',
                        'file' => 'resources/js/components/ui/input.tsx',
                        'props' => ['type', 'placeholder', 'value', 'onChange', 'className'],
                        'example' => '<Input type="text" placeholder="Ingresa texto..." />',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Label',
                        'description' => 'Etiqueta para campos de formulario',
                        'file' => 'resources/js/components/ui/label.tsx',
                        'props' => ['htmlFor', 'children', 'className'],
                        'example' => '<Label htmlFor="email">Email</Label>',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Textarea',
                        'description' => 'Área de texto multi-línea',
                        'file' => 'resources/js/components/ui/textarea.tsx',
                        'props' => ['placeholder', 'value', 'onChange', 'rows', 'className'],
                        'example' => '<Textarea placeholder="Descripción..." rows={4} />',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Select',
                        'description' => 'Selector dropdown con búsqueda',
                        'file' => 'resources/js/components/ui/select.tsx',
                        'props' => ['value', 'onValueChange', 'children'],
                        'example' => '<Select><SelectItem value="option1">Opción 1</SelectItem></Select>',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Checkbox',
                        'description' => 'Casilla de verificación',
                        'file' => 'resources/js/components/ui/checkbox.tsx',
                        'props' => ['checked', 'onCheckedChange', 'id', 'className'],
                        'example' => '<Checkbox id="terms" />',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Button',
                        'description' => 'Botón con múltiples variantes y tamaños',
                        'file' => 'resources/js/components/ui/button.tsx',
                        'props' => ['variant', 'size', 'children', 'onClick', 'disabled'],
                        'example' => '<Button variant="default" size="md">Guardar</Button>',
                        'status' => 'stable'
                    ]
                ]
            ],
            'display' => [
                'title' => 'Visualización',
                'description' => 'Componentes para mostrar información y contenido',
                'icon' => 'Eye',
                'components' => [
                    [
                        'name' => 'Card',
                        'description' => 'Contenedor con estilos de tarjeta para agrupar contenido',
                        'file' => 'resources/js/components/ui/card.tsx',
                        'props' => ['children', 'className'],
                        'example' => '<Card><CardHeader><CardTitle>Título</CardTitle></CardHeader></Card>',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Table',
                        'description' => 'Tabla responsive con estilos consistentes',
                        'file' => 'resources/js/components/ui/table.tsx',
                        'props' => ['children', 'className'],
                        'example' => '<Table><TableHeader>...</TableHeader><TableBody>...</TableBody></Table>',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Badge',
                        'description' => 'Etiqueta pequeña para mostrar estados o categorías',
                        'file' => 'resources/js/components/ui/badge.tsx',
                        'props' => ['variant', 'children', 'className'],
                        'example' => '<Badge variant="secondary">Estado</Badge>',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Avatar',
                        'description' => 'Imagen de perfil circular con fallback',
                        'file' => 'resources/js/components/ui/avatar.tsx',
                        'props' => ['src', 'alt', 'fallback', 'className'],
                        'example' => '<Avatar><AvatarImage src="..." /><AvatarFallback>JD</AvatarFallback></Avatar>',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Progress',
                        'description' => 'Barra de progreso animada',
                        'file' => 'resources/js/components/ui/progress.tsx',
                        'props' => ['value', 'max', 'className'],
                        'example' => '<Progress value={60} max={100} />',
                        'status' => 'stable'
                    ]
                ]
            ],
            'feedback' => [
                'title' => 'Retroalimentación',
                'description' => 'Componentes para mostrar mensajes y estados al usuario',
                'icon' => 'MessageCircle',
                'components' => [
                    [
                        'name' => 'Alert',
                        'description' => 'Mensaje de alerta con diferentes variantes',
                        'file' => 'resources/js/components/ui/alert.tsx',
                        'props' => ['variant', 'children', 'className'],
                        'example' => '<Alert variant="destructive"><AlertTitle>Error</AlertTitle></Alert>',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Tooltip',
                        'description' => 'Información adicional al hacer hover',
                        'file' => 'resources/js/components/ui/tooltip.tsx',
                        'props' => ['content', 'children', 'side'],
                        'example' => '<Tooltip content="Ayuda"><Button>Hover me</Button></Tooltip>',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Skeleton',
                        'description' => 'Placeholder animado para contenido que se está cargando',
                        'file' => 'resources/js/components/ui/skeleton.tsx',
                        'props' => ['className'],
                        'example' => '<Skeleton className="h-4 w-[250px]" />',
                        'status' => 'stable'
                    ]
                ]
            ],
            'overlays' => [
                'title' => 'Overlays',
                'description' => 'Componentes que se muestran sobre el contenido principal',
                'icon' => 'Layers',
                'components' => [
                    [
                        'name' => 'Dialog',
                        'description' => 'Modal para mostrar contenido sobre el fondo',
                        'file' => 'resources/js/components/ui/dialog.tsx',
                        'props' => ['open', 'onOpenChange', 'children'],
                        'example' => '<Dialog><DialogTrigger>Abrir</DialogTrigger><DialogContent>...</DialogContent></Dialog>',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Sheet',
                        'description' => 'Panel deslizante desde los bordes de la pantalla',
                        'file' => 'resources/js/components/ui/sheet.tsx',
                        'props' => ['side', 'open', 'onOpenChange', 'children'],
                        'example' => '<Sheet side="right"><SheetTrigger>Abrir</SheetTrigger></Sheet>',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Dropdown Menu',
                        'description' => 'Menú contextual desplegable',
                        'file' => 'resources/js/components/ui/dropdown-menu.tsx',
                        'props' => ['children'],
                        'example' => '<DropdownMenu><DropdownMenuTrigger>Menú</DropdownMenuTrigger></DropdownMenu>',
                        'status' => 'stable'
                    ]
                ]
            ],
            'actions' => [
                'title' => 'Acciones',
                'description' => 'Componentes para ejecutar acciones e interacciones',
                'icon' => 'MousePointer',
                'components' => [
                    [
                        'name' => 'Toggle',
                        'description' => 'Botón de alternancia para estados on/off',
                        'file' => 'resources/js/components/ui/toggle.tsx',
                        'props' => ['pressed', 'onPressedChange', 'variant', 'size'],
                        'example' => '<Toggle pressed={isPressed} onPressedChange={setIsPressed} />',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Toggle Group',
                        'description' => 'Grupo de botones de alternancia mutuamente exclusivos',
                        'file' => 'resources/js/components/ui/toggle-group.tsx',
                        'props' => ['type', 'value', 'onValueChange'],
                        'example' => '<ToggleGroup type="single"><ToggleGroupItem value="a">A</ToggleGroupItem></ToggleGroup>',
                        'status' => 'stable'
                    ]
                ]
            ],
            'charts' => [
                'title' => 'Gráficos',
                'description' => 'Componentes para visualización de datos',
                'icon' => 'BarChart3',
                'components' => [
                    [
                        'name' => 'Chart',
                        'description' => 'Sistema de gráficos integrado con múltiples tipos',
                        'file' => 'resources/js/components/ui/chart.tsx',
                        'props' => ['data', 'config', 'type', 'className'],
                        'example' => '<ChartContainer config={chartConfig}><BarChart data={data}>...</BarChart></ChartContainer>',
                        'status' => 'stable'
                    ]
                ]
            ],
            'custom' => [
                'title' => 'Componentes Personalizados',
                'description' => 'Componentes específicos de la aplicación MediTrack',
                'icon' => 'Stethoscope',
                'components' => [
                    [
                        'name' => 'App Header',
                        'description' => 'Cabecera principal de la aplicación con navegación',
                        'file' => 'resources/js/components/app-header.tsx',
                        'props' => ['title', 'user', 'className'],
                        'example' => '<AppHeader title="Dashboard" user={user} />',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'App Sidebar',
                        'description' => 'Barra lateral de navegación específica de MediTrack',
                        'file' => 'resources/js/components/app-sidebar.tsx',
                        'props' => ['collapsed', 'onToggle'],
                        'example' => '<AppSidebar collapsed={false} onToggle={handleToggle} />',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'User Info',
                        'description' => 'Componente para mostrar información del usuario',
                        'file' => 'resources/js/components/user-info.tsx',
                        'props' => ['user', 'showRole', 'className'],
                        'example' => '<UserInfo user={user} showRole={true} />',
                        'status' => 'stable'
                    ],
                    [
                        'name' => 'Medicamento Form',
                        'description' => 'Formulario especializado para medicamentos',
                        'file' => 'resources/js/components/ui/medicamento-form.tsx',
                        'props' => ['medicamento', 'onSubmit', 'mode'],
                        'example' => '<MedicamentoForm medicamento={data} onSubmit={handleSubmit} mode="create" />',
                        'status' => 'stable'
                    ]
                ]
            ]
        ];

        return Inertia::render('ComponentCatalog/Index', [
            'catalog' => $componentCatalog,
            'totalComponents' => collect($componentCatalog)->sum(fn($category) => count($category['components'])),
            'categories' => array_keys($componentCatalog)
        ]);
    }
} 