<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\GeneroController;
use App\Http\Controllers\PersonalMedicoController;
use App\Http\Controllers\CuidadorController;
use App\Http\Controllers\ApoderadoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UnifiedUserController;
use App\Http\Controllers\TratamientoController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\AdministracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PacienteCuidadorController;
use App\Http\Controllers\PacienteMedicoController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\ComponentCatalogController;
use App\Http\Controllers\MisTratamientosController;

// === LANDING PAGE - Página principal ===
Route::get('/', [LandingController::class, 'index'])->name('home');



Route::middleware(['auth', 'verified'])->group(function () {
    // === PÁGINA DE INICIO SIMPLE (temporal) - Acceso para todos los usuarios autenticados ===
    Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
    
    // === DASHBOARD ORIGINAL (comentado temporalmente) ===
    // Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/refresh', [DashboardController::class, 'refresh'])->name('dashboard.refresh');
    Route::get('dashboard/medicamentos', function () {
        return Inertia::render('Dashboard/Medicamentos');
    })->name('dashboard.medicamentos');

    // === DASHBOARD DE ADHERENCIA TEMPORAL ===
    Route::get('dashboard/adherencia-temporal', [DashboardController::class, 'adherenciaTemporal'])->name('dashboard.adherencia-temporal');
    
    // === API DE MÉTRICAS TEMPORALES ===
    Route::prefix('api/temporal-adherence')->name('api.temporal-adherence.')->group(function () {
        Route::get('patient/{pacienteId}/metrics', [\App\Http\Controllers\Api\TemporalAdherenceController::class, 'getPatientMetrics'])->name('patient-metrics');
        Route::get('patient/{pacienteId}/trends', [\App\Http\Controllers\Api\TemporalAdherenceController::class, 'getPatientTrends'])->name('patient-trends');
        Route::get('patient/{pacienteId}/distribution', [\App\Http\Controllers\Api\TemporalAdherenceController::class, 'getDistributionData'])->name('patient-distribution');
        Route::get('comparison', [\App\Http\Controllers\Api\TemporalAdherenceController::class, 'getComparison'])->name('comparison');
    });

    // === BIENVENIDA PARA USUARIOS NUEVOS ===
    Route::get('bienvenida', [WelcomeController::class, 'newUser'])->name('welcome.new-user');
    Route::post('bienvenida/progreso', [WelcomeController::class, 'updateProgress'])->name('welcome.update-progress');
    Route::post('bienvenida/completar', [WelcomeController::class, 'completeOnboarding'])->name('welcome.complete');
    Route::get('bienvenida/estadisticas', [WelcomeController::class, 'getMotivationStats'])->name('welcome.stats');
    
    // Página de bienvenida mejorada para nuevos usuarios
    Route::get('new-user-welcome', function () {
        $user = auth()->user();
        
        // Verificar si el usuario tiene medicamentos y tratamientos
        $hasMedicamentos = false; // Por ahora false, implementar lógica más tarde
        $hasTratamientos = false; // Por ahora false, implementar lógica más tarde
        $hasProfileCompleted = !empty($user->telefono); // Simplificado
        
        return Inertia::render('Welcome/NewUserWelcome', [
            'hasMedicamentos' => $hasMedicamentos,
            'hasTratamientos' => $hasTratamientos,
            'hasProfileCompleted' => $hasProfileCompleted,
        ]);
    })->name('new-user-welcome');

    // === RUTAS ESPECÍFICAS PARA PACIENTES ===
    Route::middleware('role:paciente')->group(function () {
        // Dashboard del paciente
        Route::get('dashboard-paciente', [PacienteController::class, 'miDashboard'])->name('dashboard-paciente');
        Route::get('mi-dashboard', [PacienteController::class, 'miDashboard'])->name('mi-dashboard');
        
        // Mis medicamentos
        Route::prefix('mis-medicamentos')->name('mis-medicamentos.')->group(function () {
            Route::get('/', [PacienteController::class, 'misMedicamentos'])->name('index');
        });

        // Mis tratamientos
        Route::prefix('mis-tratamientos')->name('mis-tratamientos.')->group(function () {
            Route::get('/', [PacienteController::class, 'misTratamientos'])->name('index');
            Route::get('crear', [MisTratamientosController::class, 'create'])->name('crear');
            Route::post('/', [MisTratamientosController::class, 'store'])->name('store');
            Route::get('{tratamiento}', [MisTratamientosController::class, 'show'])->name('show');
            Route::get('{tratamiento}/editar', [MisTratamientosController::class, 'edit'])->name('edit');
            Route::put('{tratamiento}', [MisTratamientosController::class, 'update'])->name('update');
            Route::delete('{tratamiento}', [MisTratamientosController::class, 'destroy'])->name('destroy');
        });

        // Mi cronograma
        Route::prefix('mi-cronograma')->name('mi-cronograma.')->group(function () {
            Route::get('/', [PacienteController::class, 'miCronograma'])->name('index');
            // Rutas para confirmar/omitir administraciones
            Route::post('administracion/{administracion}/confirmar', [PacienteController::class, 'confirmarAdministracion'])->name('confirmar-administracion');
            Route::post('administracion/{administracion}/omitir', [PacienteController::class, 'omitirAdministracion'])->name('omitir-administracion');
        });
    });

    // Mi perfil (disponible para todos los usuarios autenticados)
    Route::prefix('mi-perfil')->name('mi-perfil.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('MiPerfil/Index');
        })->name('index');
        Route::get('editar', function () {
            return Inertia::render('MiPerfil/Editar');
        })->name('editar');
    });

    // === RUTAS DE ADMINISTRACIÓN - Solo Admin ===
    Route::middleware('role:admin')->group(function () {
        // Dashboard de Administración
        Route::get('admin-dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
        
        Route::resource('roles', RoleController::class);
        Route::resource('permisos', PermisoController::class);
        Route::resource('generos', GeneroController::class);
        
        // Catálogo de Componentes - Solo Administradores
        Route::get('component-catalog', [ComponentCatalogController::class, 'index'])->name('component-catalog.index');
    });

    // === RUTAS DE GESTIÓN MÉDICA - Admin + Médicos ===
    Route::middleware('permission:personal-medico.index')->group(function () {
        Route::resource('personal-medico', PersonalMedicoController::class);
    });

    Route::middleware('permission:cuidadores.index')->group(function () {
        Route::resource('cuidadores', CuidadorController::class);
    });

    Route::middleware('permission:apoderados.index')->group(function () {
        Route::resource('apoderados', ApoderadoController::class);
    });

    // === RUTAS DE PACIENTES - Admin, Médicos, Cuidadores ===
    Route::middleware('permission:pacientes.index')->group(function () {
        Route::resource('pacientes', PacienteController::class);
    });

    // === RUTAS DE MEDICAMENTOS - Admin, Médicos, Cuidadores (lectura) ===
    Route::middleware('permission:medicines.index')->group(function () {
        Route::resource('medicines', MedicineController::class);
    });

    // === SISTEMA DE ASIGNACIÓN DE CUIDADORES - Admin, Médicos ===
    Route::middleware('permission:cuidadores.index,personal-medico.index')->prefix('asignaciones-cuidadores')->name('asignaciones-cuidadores.')->group(function () {
        Route::get('/', [PacienteCuidadorController::class, 'index'])->name('index');
        Route::get('historial', [PacienteCuidadorController::class, 'historial'])->name('historial');
        Route::get('create', [PacienteCuidadorController::class, 'create'])->name('create');
        Route::post('/', [PacienteCuidadorController::class, 'store'])->name('store');
        Route::get('{paciente}/{cuidador}', [PacienteCuidadorController::class, 'show'])->name('show');
        Route::get('{paciente}/{cuidador}/edit', [PacienteCuidadorController::class, 'edit'])->name('edit');
        Route::put('{paciente}/{cuidador}', [PacienteCuidadorController::class, 'update'])->name('update');
        Route::delete('{paciente}/{cuidador}', [PacienteCuidadorController::class, 'destroy'])->name('destroy');
        
        // Rutas AJAX
        Route::post('asignar', [PacienteCuidadorController::class, 'asignarDesdeView'])->name('asignar');
        Route::get('cuidadores-disponibles/{paciente}', [PacienteCuidadorController::class, 'cuidadoresDisponibles'])->name('cuidadores-disponibles');
    });

    // === SISTEMA DE ASIGNACIÓN DE MÉDICOS - Admin, Médicos ===
    Route::middleware('permission:personal-medico.index')->prefix('asignaciones-medicos')->name('asignaciones-medicos.')->group(function () {
        Route::get('/', [PacienteMedicoController::class, 'index'])->name('index');
        Route::get('historial', [PacienteMedicoController::class, 'historial'])->name('historial');
        Route::get('create', [PacienteMedicoController::class, 'create'])->name('create');
        Route::post('/', [PacienteMedicoController::class, 'store'])->name('store');
        Route::get('{paciente}/{medico}', [PacienteMedicoController::class, 'show'])->name('show');
        Route::delete('{paciente}/{medico}', [PacienteMedicoController::class, 'destroy'])->name('destroy');
        
        // Rutas AJAX específicas para médicos
        Route::post('asignar', [PacienteMedicoController::class, 'asignarDesdeView'])->name('asignar');
        Route::get('medicos-disponibles/{paciente}', [PacienteMedicoController::class, 'medicosDisponibles'])->name('medicos-disponibles');
        Route::patch('{paciente}/cambiar-medico-principal', [PacienteMedicoController::class, 'cambiarMedicoPrincipal'])->name('cambiar-medico-principal');
    });

    // === SISTEMA DE USUARIOS - Solo Admin ===
    Route::middleware('role:admin')->group(function () {
        // Rutas específicas del sistema unificado ANTES que el resource
        Route::prefix('usuarios')->name('usuarios.')->group(function () {
            Route::get('select-type', [UnifiedUserController::class, 'selectType'])->name('select-type');
            Route::get('create-by-type', [UnifiedUserController::class, 'create'])->name('create-by-type');
            Route::post('store-by-type', [UnifiedUserController::class, 'store'])->name('store-by-type');
            Route::get('form-data', [UnifiedUserController::class, 'getFormData'])->name('form-data');
        });

        // Resource de usuarios DESPUÉS de las rutas específicas
        Route::resource('usuarios', UserController::class);
    });

    // === SISTEMA DE MEDICAMENTOS ===
    
    // Rutas de Medicamentos - Admin, Médicos, Cuidadores (lectura)
    Route::middleware('permission:medicines.index')->group(function () {
        Route::resource('medicamentos', MedicamentoController::class);
        Route::get('medicamentos-search', [MedicamentoController::class, 'search'])->name('medicamentos.search');
        Route::get('medicamentos-datatable', [MedicamentoController::class, 'dataTable'])->name('medicamentos.datatable');
    });

    // Rutas de Tratamientos - Admin, Médicos
    Route::middleware('permission:pacientes.edit')->group(function () {
        Route::resource('tratamientos', TratamientoController::class);
        Route::patch('tratamientos/{tratamiento}/activar', [TratamientoController::class, 'activar'])->name('tratamientos.activar');
        Route::patch('tratamientos/{tratamiento}/pausar', [TratamientoController::class, 'pausar'])->name('tratamientos.pausar');
        Route::patch('tratamientos/{tratamiento}/finalizar', [TratamientoController::class, 'finalizar'])->name('tratamientos.finalizar');
    });

    // === ADMINISTRACIONES - Médicos, Cuidadores ===
    Route::middleware('permission:pacientes.index')->prefix('administraciones')->name('administraciones.')->group(function () {
        Route::get('/', [AdministracionController::class, 'index'])->name('index');
        Route::get('pendientes', [AdministracionController::class, 'pendientes'])->name('pendientes');
        Route::get('historial', [AdministracionController::class, 'historial'])->name('historial');
        Route::post('/', [AdministracionController::class, 'store'])->name('store');
        Route::patch('{administracion}/administrar', [AdministracionController::class, 'administrar'])->name('administrar');
        Route::patch('{administracion}/omitir', [AdministracionController::class, 'omitir'])->name('omitir');
        Route::patch('{administracion}/rechazar', [AdministracionController::class, 'rechazar'])->name('rechazar');
    });

    // === CRONOGRAMA - Médicos, Cuidadores ===
    Route::prefix('cronograma')->name('cronograma.')->group(function () {
        Route::get('/', [App\Http\Controllers\CronogramaController::class, 'index'])->name('index');
        Route::get('resumen-semanal', [App\Http\Controllers\CronogramaController::class, 'resumenSemanal'])->name('resumen-semanal');
        Route::patch('{administracion}/administrar', [App\Http\Controllers\CronogramaController::class, 'administrar'])->name('administrar');
        Route::patch('{administracion}/omitir', [App\Http\Controllers\CronogramaController::class, 'omitir'])->name('omitir');
    });

    // === SISTEMA DE AUDITORÍA - Solo Administradores ===
    Route::middleware('role:admin')->prefix('audit')->name('audit.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AuditController::class, 'index'])->name('index');
        Route::get('dashboard', [\App\Http\Controllers\AuditController::class, 'dashboard'])->name('dashboard');
        Route::get('{auditLog}', [\App\Http\Controllers\AuditController::class, 'show'])->name('show');
        Route::post('export-compliance', [\App\Http\Controllers\AuditController::class, 'exportCompliance'])->name('export-compliance');
        Route::get('user/{userId}/activity', [\App\Http\Controllers\AuditController::class, 'userActivity'])->name('user-activity');
        Route::delete('clean-old-logs', [\App\Http\Controllers\AuditController::class, 'cleanOldLogs'])->name('clean-old-logs');
        Route::get('live-stats', [\App\Http\Controllers\AuditController::class, 'liveStats'])->name('live-stats');
        Route::post('search', [\App\Http\Controllers\AuditController::class, 'search'])->name('search');
    });
  });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
