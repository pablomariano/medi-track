# 🏥 Medi-Track

**Sistema Integral de Gestión Médica y Administración de Medicamentos**

[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)
[![React](https://img.shields.io/badge/React-19-blue.svg)](https://reactjs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.7-blue.svg)](https://typescriptlang.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.0-green.svg)](https://tailwindcss.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-blue.svg)](https://docker.com)

---

## 📋 Descripción

Medi-Track es una aplicación web moderna diseñada para la gestión integral de tratamientos médicos y administración de medicamentos. El sistema facilita el seguimiento de adherencia terapéutica en entornos sanitarios, permitiendo la coordinación eficiente entre personal médico, cuidadores, apoderados y pacientes.

### 🎯 Características Principales

- **🏥 Gestión Multi-Rol**: Médicos, cuidadores, apoderados, pacientes y administradores
- **💊 Medicación Inteligente**: Tratamientos programados y medicación PRN (por necesidad)
- **⏰ Cronogramas Automáticos**: Generación automática de horarios de medicación
- **🚨 Alertas en Tiempo Real**: Notificaciones automáticas para dosis omitidas y eventos críticos
- **📊 Análisis de Adherencia**: Estadísticas detalladas de cumplimiento terapéutico
- **🔐 Sistema de Permisos**: Control granular de acceso basado en roles
- **📱 Diseño Responsivo**: Interfaz moderna optimizada para móviles y escritorio
- **🔍 Auditoría Completa**: Registro detallado de todas las acciones del sistema

### 🩺 Tipos de Medicación Soportados

#### Medicación Programada
- Horarios fijos con tolerancias configurables
- Generación automática de cronogramas
- Seguimiento de adherencia temporal

#### Medicación PRN (Pro Re Nata)
- Administración basada en síntomas específicos
- Controles de dosificación máxima
- Intervalos mínimos entre dosis
- Criterios médicos personalizables

---

## 🛠️ Stack Tecnológico

### Backend
- **Laravel 12** - Framework PHP robusto
- **Inertia.js 2.0** - SPA sin API tradicional
- **SQLite** - Base de datos ligera para desarrollo
- **Pest 3.8** - Framework de testing moderno

### Frontend
- **React 19** - Biblioteca de interfaz de usuario
- **TypeScript 5.7** - Tipado estático para JavaScript
- **Vite 6.0** - Build tool ultra-rápido
- **Tailwind CSS 4.0** - Framework de utilidades CSS
- **Shadcn UI + Radix UI** - Componentes de interfaz accesibles
- **Lucide React** - Iconografía moderna
- **Recharts** - Visualización de datos y gráficos

### DevOps & Herramientas
- **Docker & Docker Compose** - Containerización
- **ESLint & Prettier** - Linting y formateo de código
- **Laravel Sail** - Entorno de desarrollo con Docker

---

## 🚀 Instalación y Configuración

### Requisitos Previos

- **Docker** y **Docker Compose**
- **PHP 8.4+** (si ejecutas sin Docker)
- **Node.js 18+** y **npm/pnpm**
- **Composer**

### Instalación con Docker (Recomendado)

1. **Clonar el repositorio**
```bash
git clone https://github.com/pablomariano/medi-track.git
cd medi-track
```

2. **Configurar el entorno**
```bash
cp .env.example .env
```

3. **Construir y levantar los contenedores**
```bash
./vendor/bin/sail up -d
```

4. **Instalar dependencias PHP**
```bash
./vendor/bin/sail composer install
```

5. **Instalar dependencias JavaScript**
```bash
npm install
```

6. **Generar clave de aplicación**
```bash
./vendor/bin/sail artisan key:generate
```

7. **Ejecutar migraciones y seeders**
```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

8. **Compilar assets**
```bash
npm run dev
```

### Instalación Local (Sin Docker)

1. **Instalar dependencias**
```bash
composer install
npm install
```

2. **Configurar base de datos**
```bash
php artisan migrate:fresh --seed
```

3. **Ejecutar el servidor de desarrollo**
```bash
php artisan serve
npm run dev
```

La aplicación estará disponible en `http://localhost:8000`

---

## 👥 Roles del Sistema

### 👨‍⚕️ Médico (Personal Médico)
- Prescripción de tratamientos programados y PRN
- Configuración de criterios médicos específicos
- Acceso completo a historiales de pacientes
- Generación de reportes médicos

### 👩‍⚕️ Cuidador
- Administración de medicamentos según prescripción
- Registro de síntomas para medicación PRN
- Documentación de efectos adversos
- Seguimiento de cronogramas asignados

### 👨‍👩‍👧 Apoderado/Tutor
- Supervisión del tratamiento de pacientes a cargo
- Recepción de alertas importantes
- Consulta de estadísticas de adherencia
- Comunicación con equipo médico

### 🙋‍♂️ Paciente
- Consulta de tratamiento personal
- Auto-administración (según configuración)
- Reporte de síntomas
- Acceso a historial médico propio

### ⚙️ Administrador del Sistema
- Gestión completa de usuarios y permisos
- Mantenimiento del catálogo de medicamentos
- Configuración global del sistema
- Auditoría y monitoreo

---

## 📁 Estructura del Proyecto

```
medi-track/
├── app/
│   ├── Http/Controllers/    # Controladores de la aplicación
│   ├── Models/             # Modelos Eloquent
│   ├── Policies/           # Políticas de autorización
│   ├── Services/           # Lógica de negocio
│   └── Middleware/         # Middleware personalizado
├── database/
│   ├── migrations/         # Migraciones de base de datos
│   └── seeders/           # Seeders para datos de prueba
├── resources/
│   ├── js/
│   │   ├── components/     # Componentes React reutilizables
│   │   ├── pages/         # Páginas de la aplicación
│   │   ├── layouts/       # Layouts de página
│   │   └── types/         # Definiciones TypeScript
│   └── css/               # Estilos CSS
├── routes/                # Definición de rutas
├── tests/                 # Tests automatizados
└── docker/               # Configuración Docker
```

---

## 🧪 Testing

### Ejecutar Tests

```bash
# Con Sail (Docker)
./vendor/bin/sail artisan test

# Local
php artisan test

# Con cobertura
php artisan test --coverage
```

### Tipos de Tests
- **Unit Tests**: Pruebas de modelos y servicios
- **Feature Tests**: Pruebas de controladores y flujos completos
- **Browser Tests**: Pruebas de interfaz de usuario

---

## 🔧 Comandos Útiles

```bash
# Desarrollo con Sail
./vendor/bin/sail up -d              # Levantar contenedores
./vendor/bin/sail down               # Detener contenedores
./vendor/bin/sail artisan migrate    # Ejecutar migraciones
./vendor/bin/sail composer install   # Instalar dependencias PHP

# Desarrollo frontend
npm run dev                          # Servidor de desarrollo
npm run build                       # Build de producción
npm run lint                        # Linter ESLint
npm run format                      # Formatear código

# Comandos específicos de la aplicación
php artisan medi-track:fix-users     # Corregir usuarios sin rol
php artisan medi-track:test-permissions # Probar sistema de permisos
```

---

## 🐳 Despliegue con Docker

### Producción

1. **Configurar variables de entorno**
```bash
cp .env.example .env.production
# Editar .env.production con valores de producción
```

2. **Build de producción**
```bash
docker-compose -f docker-compose.prod.yml up -d
```

3. **Ejecutar migraciones en producción**
```bash
docker-compose exec app php artisan migrate --force
```

---

## 🤝 Contribución

1. **Fork** el proyecto
2. **Crear** una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. **Commit** tus cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. **Push** a la rama (`git push origin feature/nueva-funcionalidad`)
5. **Crear** un Pull Request

### Estándares de Código
- Seguir **PSR-12** para PHP
- Usar **Prettier** y **ESLint** para JavaScript/TypeScript
- Escribir tests para nuevas funcionalidades
- Mantener cobertura de código >80%

---

## 📚 Documentación Adicional

- [Manual de Usuario](docs/MANUAL_DE_USUARIO_MEDI_TRACK.md)
- [Casos de Uso](docs/casos_de_uso_medi_track.md)
- [Guía de Despliegue](docs/GUIA_DESPLIEGUE_DIGITAL_OCEAN.md)
- [Guía de Testing](docs/GUIA_EJECUCION_TESTS.md)


---

## 💬 Soporte

Para soporte técnico o preguntas sobre el sistema:

- **Issues**: [GitHub Issues](https://github.com/pablomariano/medi-track/issues)
- **Documentación**: Consultar carpeta `docs/`
- **Contribuciones**: Pull Requests bienvenidos

---

## 🔄 Estado del Proyecto

**Versión Actual**: 1.0  
**Estado**: En desarrollo activo  
**Última Actualización**: Julio 2025

### Roadmap
- [ ] Aplicación móvil nativa

---

<p align="center">
  <strong>Hecho con ❤️ para mejorar la adherencia terapéutica</strong>
</p> 