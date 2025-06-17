# Análisis del Sistema MediTrack - Estado Actual y Sistema de Roles/Permisos

## 📊 Estado Actual de la Aplicación

### 🏗️ Arquitectura General
- **Framework**: Laravel 11 con React/TypeScript (Inertia.js)
- **Base de Datos**: MySQL con migraciones organizadas
- **Frontend**: React with Tailwind CSS and components shadcn/ui
- **Autenticación**: Laravel Breeze with email verification

### 📋 Módulos Implementados

#### ✅ Módulos Completamente Funcionales
1. **Gestión de Usuarios**
   - Full CRUD with specific types (doctor, caregiver, guardian, patient)
   - Unified creation system by type
   - Specific validations by role

2. **Medicamentos y Tratamientos**
   - Complete management of medicines
   - Scheduled treatments and PRN system
   - Scheduled times and administrations

3. **Gestión de Pacientes**
   - Full CRUD with relationships
   - Assignment to doctors and caregivers
   - Medical history

4. **Dashboard**
   - Medicine statistics
   - Alerts and notifications
   - Adherence summary

#### 🚧 Módulos Parcialmente Implementados
1. **Sistema de Asignaciones**
   - Basic interface created
   - Missing complete business logic
   - Needs integration with permissions

2. **Sistema de Cronogramas**
   - Basic structure
   - Requires permission validation by user

---

## 🔐 Análisis del Sistema de Roles y Permisos

### ✅ Estado Actual - Lo que YA está implementado

#### 1. **Estructura de Base de Datos**
```sql
-- Tables implemented:
- roles (id, nombre, descripcion, activo)
- permisos (id, nombre, descripcion, modulo)
- rol_permisos (rol_id, permiso_id) -- pivot table
- users.rol_id -- relationship with roles
```

#### 2. **Modelos Definidos**
- ✅ `Role.php` - Basic model
- ✅ `Permiso.php` - Basic model  
- ✅ `User.php` - With relationship to roles

#### 3. **Seeders Completos**
- ✅ **5 Predefined Roles**:
  - `admin` - Full system access
  - `medico` - Patients and diagnosis management
  - `cuidador` - Limited access to assigned patients
  - `apoderado` - Patients' information in charge
  - `paciente` - Only own data

- ✅ **28 Permissions organized by modules**:
  - usuarios (index, create, edit, delete)
  - roles (index, create, edit, delete)
  - pacientes (index, create, edit, delete, own)
  - personal-medico (index, create, edit, delete)
  - cuidadores (index, create, edit, delete)
  - apoderados (index, create, edit, delete)
  - medicines (index, create, edit, delete)

#### 4. **Controladores CRUD**
- ✅ `RoleController.php` - Complete roles management
- ✅ `PermisoController.php` - Complete permissions management

#### 5. **Interfaces Frontend**
- ✅ `resources/js/pages/Roles/` - Complete CRUD
- ✅ `resources/js/pages/Permisos/` - Complete CRUD

### ❌ Lo que FALTA Implementar

#### 1. **Middleware de Autorización**
```php
// FALTA CREAR:
app/Http/Middleware/CheckPermission.php
app/Http/Middleware/CheckRole.php
```

#### 2. **Relaciones en Modelos**
```php
// FALTA EN Role.php:
public function permisos() {
    return $this->belongsToMany(Permiso::class, 'rol_permisos');
}

public function users() {
    return $this->hasMany(User::class, 'rol_id');
}

// FALTA EN User.php:
public function hasPermission($permission) { ... }
public function hasRole($role) { ... }
public function can($permission) { ... }
```

#### 3. **Sistema de Gates/Policies**
```php
// FALTA CREAR:
app/Policies/ - Policies by model
app/Providers/AuthServiceProvider.php - Custom gates
```

#### 4. **Protección de Rutas**
```php
// FALTA APLICAR:
Route::middleware(['auth', 'permission:usuarios.index'])->group(...)
```

#### 5. **Validación en Frontend**
```typescript
// FALTA CREAR:
hooks/usePermissions.tsx
hooks/useRole.tsx
components/ProtectedRoute.tsx
```

---

## 🎯 Plan de Implementación del Sistema de Autorización

### Fase 1: Backend - Middlewares and Business Logic (High Priority)

#### 1.1 Completar Modelos con Relaciones
```php
// Role.php - Agregar relaciones
public function permisos() {
    return $this->belongsToMany(Permiso::class, 'rol_permisos', 'rol_id', 'permiso_id');
}

public function users() {
    return $this->hasMany(User::class, 'rol_id');
}

// User.php - Agregar métodos de autorización  
public function hasPermission(string $permission): bool {
    return $this->role?->permisos()->where('nombre', $permission)->exists() ?? false;
}

public function hasRole(string $roleName): bool {
    return $this->role?->nombre === $roleName;
}

public function can(string $permission): bool {
    return $this->hasPermission($permission);
}
```

#### 1.2 Crear Middlewares de Autorización
```php
// app/Http/Middleware/CheckPermission.php
public function handle($request, Closure $next, ...$permissions) {
    if (!auth()->user()->hasAnyPermission($permissions)) {
        abort(403, 'No tienes permisos para acceder a esta sección');
    }
    return $next($request);
}

// app/Http/Middleware/CheckRole.php  
public function handle($request, Closure $next, ...$roles) {
    if (!auth()->user()->hasAnyRole($roles)) {
        abort(403, 'Tu rol no permite acceder a esta sección');
    }
    return $next($request);
}
```

#### 1.3 Registrar Middlewares
```php
// app/Http/Kernel.php
'permission' => \App\Http\Middleware\CheckPermission::class,
'role' => \App\Http\Middleware\CheckRole::class,
```

#### 1.4 Crear Policies para Recursos Críticos
```php
// app/Policies/PacientePolicy.php
public function viewAny(User $user): bool {
    return $user->hasPermission('pacientes.index');
}

public function view(User $user, Paciente $paciente): bool {
    // Specific logic: doctors see all, caregivers only assigned, etc.
}
```

### Fase 2: Protección de Rutas (High Priority)

#### 2.1 Aplicar Middlewares en Rutas
```php
// routes/web.php
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Admin routes - Only admin
    Route::middleware('role:admin')->group(function () {
        Route::resource('usuarios', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('permisos', PermisoController::class);
    });
    
    // Doctor routes - Admin + Doctors
    Route::middleware('permission:pacientes.index')->group(function () {
        Route::resource('pacientes', PacienteController::class);
        Route::resource('tratamientos', TratamientoController::class);
    });
    
    // Medicine routes - Various roles
    Route::middleware('permission:medicines.index')->group(function () {
        Route::resource('medicines', MedicineController::class);
    });
});
```

#### 2.2 Validaciones en Controladores
```php
// Example in PacienteController.php
public function index() {
    $this->authorize('viewAny', Paciente::class);
    
    $query = Paciente::query();
    
    // Filter according to user role
    if (auth()->user()->hasRole('cuidador')) {
        $query->whereHas('cuidadores', function($q) {
            $q->where('usuario_id', auth()->id());
        });
    }
    
    return Inertia::render('Pacientes/Index', [
        'pacientes' => $query->paginate(10)
    ]);
}
```

### Fase 3: Frontend - Protección de UI (Medium Priority)

#### 3.1 Crear Hooks de Permisos
```typescript
// hooks/usePermissions.tsx
export function usePermissions() {
    const { auth } = usePage<PageProps>().props;
    
    const hasPermission = (permission: string): boolean => {
        return auth.user.permissions?.includes(permission) ?? false;
    };
    
    const hasRole = (role: string): boolean => {
        return auth.user.role?.nombre === role;
    };
    
    return { hasPermission, hasRole };
}
```

#### 3.2 Componente de Protección
```typescript
// components/ProtectedRoute.tsx
interface Props {
    children: React.ReactNode;
    permission?: string;
    role?: string;
    fallback?: React.ReactNode;
}

export function ProtectedRoute({ children, permission, role, fallback = null }: Props) {
    const { hasPermission, hasRole } = usePermissions();
    
    if (permission && !hasPermission(permission)) return fallback;
    if (role && !hasRole(role)) return fallback;
    
    return <>{children}</>;
}
```

#### 3.3 Aplicar en Componentes
```typescript
// Example in a component
<ProtectedRoute permission="usuarios.create">
    <Button href={route('usuarios.create')}>
        <Plus className="h-4 w-4 mr-2" />
        Nuevo Usuario
    </Button>
</ProtectedRoute>
```

### Fase 4: Sistema de Asignaciones Específicas (Medium Priority)

#### 4.1 Lógica de Asignación Paciente-Cuidador
```php
// Specific middleware to verify assignments
public function handle($request, Closure $next) {
    $pacienteId = $request->route('paciente');
    
    if (auth()->user()->hasRole('cuidador')) {
        $tieneAsignacion = PacienteCuidador::where([
            'paciente_id' => $pacienteId,
            'cuidador_usuario_id' => auth()->id(),
            'activo' => true
        ])->exists();
        
        if (!$tieneAsignacion) {
            abort(403, 'No tienes asignación con este paciente');
        }
    }
    
    return $next($request);
}
```

### Fase 5: Auditoría y Logs (Low Priority)

#### 5.1 Log de Accesos por Permisos
```php
// Audit middleware
public function handle($request, Closure $next) {
    $response = $next($request);
    
    if (auth()->check()) {
        Log::info('Acceso autorizado', [
            'user_id' => auth()->id(),
            'route' => $request->route()->getName(),
            'ip' => $request->ip(),
            'timestamp' => now()
        ]);
    }
    
    return $response;
}
```

---

## 🎯 Recomendaciones de Implementación

### 1. **Orden de Prioridades**
1. ✅ **Completar relaciones en modelos** (2-3 hours)
2. ✅ **Crear y registrar middlewares** (3-4 hours)  
3. ✅ **Proteger rutas críticas** (4-5 hours)
4. ✅ **Implementar policies principales** (3-4 hours)
5. ⚠️ **Frontend con hooks de permisos** (5-6 hours)
6. ⚠️ **Sistema de asignaciones específicas** (6-8 hours)

### 2. **Rutas Críticas a Proteger Primero**
- `/usuarios/*` - Solo admin
- `/pacientes/*` - Admin, médicos, cuidadores asignados
- `/tratamientos/*` - Admin, médicos responsables
- `/medicines/*` - Admin, médicos, cuidadores (lectura)

### 3. **Consideraciones de Seguridad**
- Validar permisos tanto en backend como frontend
- Implementar rate limiting in sensitive endpoints
- Access logs for auditing
- Secure sessions with automatic timeout

### 4. **Testing**
- Create unit tests for each permission
- Integration tests for complete flows
- Security tests for unauthorized access attempts

---

## 🔧 Comandos de Implementación Rápida

```bash
# 1. Ejecutar seeders actuales
php artisan db:seed --class=RolesAndPermissionsSeeder

# 2. Crear middlewares faltantes
php artisan make:middleware CheckPermission
php artisan make:middleware CheckRole

# 3. Crear policies
php artisan make:policy PacientePolicy --model=Paciente
php artisan make:policy TratamientoPolicy --model=Tratamiento

# 4. Actualizar modelos con relaciones
# (Editar manualmente los archivos de modelo)

# 5. Testear el sistema
php artisan test --filter=Permission
```

---

## 📈 Métricas de Éxito

### Indicadores de Implementación Correcta:
- ✅ 0 accesos no autorizados en logs
- ✅ Todas las rutas protegidas responden 403 a usuarios sin permisos  
- ✅ Frontend oculta elementos según permisos del usuario
- ✅ Tests de autorización pasan al 100%
- ✅ Tiempo de respuesta < 200ms en verificaciones de permisos

### Timeline Estimado:
- **Fase 1-2 (Backend crítico)**: 2-3 days
- **Fase 3 (Frontend)**: 2-3 days  
- **Fase 4-5 (Funcionalidades avanzadas)**: 3-4 days
- **Testing y ajustes**: 1-2 days

**Total estimado: 8-12 days of development**

---

*Última actualización: Enero 2025*
*Estado: Sistema base implementado, falta middleware y lógica de autorización* 