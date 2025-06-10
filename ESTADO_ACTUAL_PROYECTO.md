# 📋 Estado Actual del Proyecto Medi-Track

## 🏗️ Información General del Proyecto

**Nombre**: Medi-Track  
**Tipo**: Sistema de gestión médica  
**Stack Tecnológico**: Laravel 12 + React 19 + TypeScript + Inertia.js + Tailwind CSS  
**Base de Datos**: SQLite (desarrollo)  
**Containerización**: Docker + Docker Compose  

### 📦 Dependencias Principales

#### Backend (Laravel)
- **Laravel Framework**: ^12.0
- **Inertia.js**: ^2.0 (SPA sin API)
- **Ziggy**: ^2.4 (rutas de Laravel en frontend)
- **Testing**: Pest ^3.8

#### Frontend (React + TypeScript)
- **React**: ^19.0.0
- **TypeScript**: ^5.7.2
- **Vite**: ^6.0
- **Tailwind CSS**: ^4.0.0
- **Radix UI**: Componentes de interfaz
- **Lucide React**: Iconografía
- **Recharts**: ^2.15.3 (gráficos)

---

## 🗄️ Migraciones de Base de Datos

### Migraciones Principales

1. **Sistema de Usuarios y Roles**
   - `2025_06_04_160747_create_roles_table.php` - Roles del sistema
   - `2025_06_04_160748_create_usuarios_table.php` - Usuarios principales
   - `2025_06_04_160753_create_permisos_table.php` - Permisos del sistema
   - `2025_06_04_160753_create_rol_permisos_table.php` - Relación roles-permisos

2. **Tipos de Usuario Específicos**
   - `2025_06_04_160748_create_personal_medico_table.php` - Personal médico
   - `2025_06_04_160749_create_cuidadores_table.php` - Cuidadores
   - `2025_06_04_160749_create_apoderados_table.php` - Apoderados/tutores

3. **Sistema de Pacientes**
   - `2025_06_04_160750_create_generos_table.php` - Géneros (M/F/O)
   - `2025_06_04_160751_create_pacientes_table.php` - Pacientes principales

4. **Relaciones Paciente-Personal**
   - `2025_06_04_160752_create_paciente_apoderados_table.php` - Pacientes ↔ Apoderados
   - `2025_06_04_160753_create_paciente_cuidadores_table.php` - Pacientes ↔ Cuidadores  
   - `2025_06_04_160754_create_paciente_medicos_table.php` - Pacientes ↔ Médicos

5. **Sistema de Auditoría**
   - `2025_06_04_160755_create_sesiones_usuario_table.php` - Gestión de sesiones

6. **Medicamentos**
   - `2024_03_21_000000_create_medicamentos_table.php` - Tabla básica de medicamentos

7. **Laravel Core**
   - `0001_01_01_000000_create_users_table.php` - Usuarios Laravel por defecto
   - `0001_01_01_000001_create_cache_table.php` - Sistema de caché
   - `0001_01_01_000002_create_jobs_table.php` - Cola de trabajos

8. **Correcciones**
   - `2025_06_04_203852_alter_cuidadores_tarifa_hora_to_integer.php` - Ajuste de tipos

---

## 🎮 Controladores del Sistema

### Controladores Principales

1. **Sistema Unificado**
   - `UnifiedUserController.php` (5.1KB, 147 líneas) - Sistema unificado de usuarios por tipo

2. **Gestión de Usuarios**
   - `UserController.php` (3.5KB, 116 líneas) - CRUD de usuarios generales
   - `PermisoController.php` (1.7KB, 67 líneas) - Gestión de permisos
   - `RoleController.php` (1.6KB, 67 líneas) - Gestión de roles

3. **Tipos de Usuario Específicos**
   - `PersonalMedicoController.php` (2.5KB, 78 líneas) - Personal médico
   - `CuidadorController.php` (2.2KB, 78 líneas) - Cuidadores
   - `ApoderadoController.php` (2.0KB, 74 líneas) - Apoderados

4. **Gestión de Pacientes**
   - `PacienteController.php` (4.0KB, 124 líneas) - CRUD de pacientes

5. **Datos Maestros**
   - `GeneroController.php` (1.6KB, 65 líneas) - Gestión de géneros
   - `MedicineController.php` (1.8KB, 69 líneas) - Medicamentos básicos

6. **Autenticación**
   - `Auth/` - Controladores de autenticación Laravel
   - `Settings/` - Controladores de configuración

### Rutas del Sistema

- **`web.php`** (2.0KB, 48 líneas) - Rutas principales web
- **`auth.php`** (2.2KB, 57 líneas) - Rutas de autenticación  
- **`settings.php`** (911B, 22 líneas) - Rutas de configuración
- **`console.php`** (210B, 9 líneas) - Comandos Artisan

---

## 🗃️ Esquema de Base de Datos (DBML)

```dbml
// ========================================
// MEDI-TRACK - ESTADO ACTUAL DE LA BASE DE DATOS
// Representación exacta del esquema actual sin modificaciones
// Generado desde: database/usuarios.sql + migraciones existentes
// ========================================

// ==========================================
// SISTEMA DE ROLES Y PERMISOS
// ==========================================

Table roles {
  id int [pk, increment]
  nombre varchar(50) [unique, not null]
  descripcion text
  activo boolean [default: true]
  created_at timestamp [default: `CURRENT_TIMESTAMP`]
  
  Note: 'Roles del sistema: admin, medico, cuidador, apoderado, paciente'
}

Table permisos {
  id int [pk, increment]
  nombre varchar(50) [unique, not null]
  descripcion text
  modulo varchar(50)
  
  Note: 'Permisos específicos del sistema'
}

Table rol_permisos {
  rol_id int [ref: > roles.id]
  permiso_id int [ref: > permisos.id]
  
  indexes {
    (rol_id, permiso_id) [pk]
  }
  
  Note: 'Permisos asignados a cada rol'
}

// ==========================================
// SISTEMA DE USUARIOS
// ==========================================

Table usuarios {
  id int [pk, increment]
  nombre varchar(100) [not null]
  email varchar(100) [unique, not null]
  password varchar(255) [not null]
  telefono varchar(20)
  rol_id int [not null, ref: > roles.id]
  activo boolean [default: true]
  email_verificado boolean [default: false]
  ultimo_acceso timestamp
  created_at timestamp [default: `CURRENT_TIMESTAMP`]
  updated_at timestamp [default: `CURRENT_TIMESTAMP`]
  
  Note: 'Tabla central de usuarios del sistema'
}

// ==========================================
// TIPOS DE USUARIOS ESPECÍFICOS
// ==========================================

Table personal_medico {
  usuario_id int [pk, ref: - usuarios.id]
  especialidad varchar(100)
  numero_colegiatura varchar(50) [unique]
  institucion varchar(100)
  anos_experiencia int
  
  Note: 'Información específica del personal médico'
}

Table cuidadores {
  usuario_id int [pk, ref: - usuarios.id]
  certificaciones text
  experiencia_anos int
  disponibilidad_horaria varchar(100)
  tarifa_hora decimal(8,2)
  
  Note: 'Información específica de cuidadores'
}

Table apoderados {
  usuario_id int [pk, ref: - usuarios.id]
  relacion_paciente varchar(50)
  es_contacto_emergencia boolean [default: true]
  
  Note: 'Información específica de apoderados/tutores'
}

// ==========================================
// SISTEMA DE PACIENTES
// ==========================================

Table generos {
  id char(1) [pk]
  nombre varchar(20) [unique, not null]
  
  Note: 'M: Masculino, F: Femenino, O: Otro'
}

Table pacientes {
  id int [pk, increment]
  usuario_id int [ref: > usuarios.id, note: 'Opcional - paciente puede no tener cuenta']
  nombre varchar(100) [not null]
  fecha_nacimiento date
  genero_id char(1) [ref: > generos.id]
  numero_documento varchar(20) [unique]
  tipo_documento varchar(10)
  tipo_sangre varchar(10)
  altura decimal(5,2)
  direccion text
  telefono_emergencia varchar(20)
  observaciones_medicas text
  activo boolean [default: true]
  created_at timestamp [default: `CURRENT_TIMESTAMP`]
  
  Note: 'Pacientes del sistema - pueden o no tener cuenta de usuario'
}

// ==========================================
// RELACIONES ENTRE PACIENTES Y USUARIOS
// ==========================================

Table paciente_apoderados {
  paciente_id int [ref: > pacientes.id]
  apoderado_usuario_id int [ref: > apoderados.usuario_id]
  es_principal boolean [default: false]
  created_at timestamp [default: `CURRENT_TIMESTAMP`]
  
  indexes {
    (paciente_id, apoderado_usuario_id) [pk]
  }
  
  Note: 'Un paciente puede tener múltiples apoderados'
}

Table paciente_cuidadores {
  paciente_id int [ref: > pacientes.id]
  cuidador_usuario_id int [ref: > cuidadores.usuario_id]
  fecha_asignacion date [not null]
  fecha_fin date
  activo boolean [default: true]
  
  indexes {
    (paciente_id, cuidador_usuario_id) [pk]
  }
  
  Note: 'Asignación de cuidadores a pacientes'
}

Table paciente_medicos {
  paciente_id int [ref: > pacientes.id]
  medico_usuario_id int [ref: > personal_medico.usuario_id]
  es_medico_principal boolean [default: false]
  fecha_asignacion date [not null]
  fecha_fin date
  especialidad_tratamiento varchar(100)
  
  indexes {
    (paciente_id, medico_usuario_id) [pk]
  }
  
  Note: 'Asignación de médicos a pacientes'
}

// ==========================================
// MEDICAMENTOS (ESTRUCTURA SIMPLE ACTUAL)
// ==========================================

Table medicamentos {
  id int [pk, increment]
  nombre varchar(255) [not null]
  medida varchar(255) [not null]
  unidad_medida varchar(255) [not null]
  descripcion text
  created_at timestamp [default: `CURRENT_TIMESTAMP`]
  updated_at timestamp [default: `CURRENT_TIMESTAMP`]
  
  Note: 'Tabla simple actual de medicamentos - será reemplazada por sistema complejo'
}

// ==========================================
// SISTEMA DE AUDITORÍA Y SESIONES
// ==========================================

Table sesiones_usuario {
  id varchar(255) [pk]
  usuario_id int [ref: > usuarios.id]
  ip_address varchar(45)
  user_agent text
  activa boolean [default: true]
  created_at timestamp [default: `CURRENT_TIMESTAMP`]
  expires_at timestamp [not null]
  
  Note: 'Gestión de sesiones activas de usuarios'
}

// ==========================================
// TABLAS ADICIONALES DE LARAVEL
// ==========================================

Table users {
  id int [pk, increment]
  name varchar(255) [not null]
  email varchar(255) [unique, not null]
  email_verified_at timestamp
  password varchar(255) [not null]
  remember_token varchar(100)
  created_at timestamp
  updated_at timestamp
  
  Note: 'Tabla de usuarios por defecto de Laravel (puede estar en paralelo)'
}

Table cache {
  key varchar(255) [pk]
  value mediumtext [not null]
  expiration int [not null]
}

Table jobs {
  id bigint [pk, increment]
  queue varchar(255) [not null]
  payload longtext [not null]
  attempts tinyint [not null]
  reserved_at int
  available_at int [not null]
  created_at int [not null]
}
```

---

## 📊 Resumen del Historial de Cambios

### Cambios Recientes (10 últimos commits)

1. **a596782** - Add current state DBML for MEDI-TRACK database schema, detailing roles, permissions, users, patients, and relationships. Includes comprehensive notes on system structure and future evolution.

2. **a27a47a** - Integrated medicine tables DBML created

3. **6fdf58a** - Update link in Usuarios index page to route for creating users by type, enhancing user management functionality.

4. **344a70b** - Remove 'Appointments' and 'Medical Records' items from the sidebar navigation, and eliminate the 'New Appointment' button for a cleaner user interface.

5. **93ccd6c** - Reorganize user routes in web.php to prioritize specific routes for the unified user system before the resource route for usuarios, ensuring proper handling of user type management.

6. **dd4c615** - Update package-lock.json to rename project from 'medi-track' to 'html' and add tests

7. **ff2da28** - Add user registration service binding in AppServiceProvider, enhance sidebar with 'Crear Usuario' option, and introduce new routes for UnifiedUserController to manage user types. Add first tests.

8. **4b5e2bf** (origin/main) - Refactor PermisoController to use orderBy for pagination, ensuring consistent ordering of permissions by ID in descending order.

9. **1dfa465** - Enhance Cuidador components to handle user absence gracefully, displaying appropriate messages when user data is unavailable in Edit and Index pages.

10. **109af70** - Add Adminer service to docker-compose for database management, including configuration for MySQL connection and design settings.

---

## 🚨 Problemas de Coherencia Identificados

### Problemas Críticos 🔥

1. **Incoherencia de Roles**: Usuarios con múltiples roles incompatibles (ej: admin + médico + cuidador)
2. **Asignación Incorrecta**: Roles en tabla usuarios no coinciden con registros específicos
3. **Nomenclatura Inconsistente**: Roles duplicados (`Administrador` vs `admin`)

### Estado Actual de Datos
- **Usuarios**: 4 usuarios existentes con roles mixtos problemáticos
- **Roles**: 5 roles con nomenclatura inconsistente
- **Datos de Prueba**: Inconsistentes y no siguen reglas de negocio

### Recomendaciones
- **Opción A (Desarrollo)**: Reseteo completo con datos coherentes
- **Opción B (Producción)**: Corrección gradual de inconsistencias
- **Implementar**: Middleware de autorización y validaciones estrictas

---

## 🎯 Estado del Proyecto

### ✅ Completado
- ✅ Arquitectura base de usuarios con roles y permisos
- ✅ Sistema completo de pacientes con relaciones
- ✅ CRUD para todos los tipos de usuario
- ✅ Sistema básico de medicamentos
- ✅ Interfaz React con Tailwind CSS
- ✅ Contenedorización con Docker
- ✅ Sistema de migraciones completo

### 🚧 En Desarrollo  
- 🚧 Sistema unificado de creación de usuarios
- 🚧 Corrección de coherencia de datos
- 🚧 Sistema de autorización por roles
- 🚧 Tests unitarios y de integración

### 📋 Por Implementar
- 📋 Sistema complejo de medicamentos y tratamientos
- 📋 Módulo de citas médicas
- 📋 Historial médico detallado
- 📋 Notificaciones y recordatorios
- 📋 Dashboard con métricas
- 📋 API RESTful (opcional)
- 📋 Sistema de reportes

---

## 🚀 Comandos Útiles

### Desarrollo
```bash
# Iniciar entorno de desarrollo
npm run dev

# Backend + Frontend + Queue
composer run dev

# Tests
composer run test
```

### Base de Datos
```bash
# Migrar base de datos
php artisan migrate

# Resetear con seeders
php artisan migrate:fresh --seed

# Verificar estado
php artisan migrate:status
```

### Docker
```bash
# Levantar servicios
docker-compose up -d

# Acceder a Adminer (gestión BD)
# http://localhost:8080
```

---

**Última actualización**: Enero 2025  
**Rama actual**: `feature`  
**Estado**: En desarrollo activo con enfoque en sistema unificado de usuarios 