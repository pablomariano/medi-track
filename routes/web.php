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

// Nuevos controladores del sistema de medicamentos
use App\Http\Controllers\PrincipiosActivosController;
use App\Http\Controllers\MedicamentosController;
use App\Http\Controllers\FormasFarmaceuticasController;
use App\Http\Controllers\ViasAdministracionController;
use App\Http\Controllers\UnidadesMedidaController;
use App\Http\Controllers\TratamientosController;
use App\Http\Controllers\AdministracionesController;
use App\Http\Controllers\DashboardController;

// Rutas del Sistema de Seguimiento de Tratamientos
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\AutorizacionesController;
use App\Http\Controllers\MonitoreoController;

// Sistema de Reportes y Gráficos
use App\Http\Controllers\ReportesController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard principal
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboards específicos por rol
    Route::get('dashboard/medico', [DashboardController::class, 'dashboardMedico'])->name('dashboard.medico');
    Route::get('dashboard/cuidador', [DashboardController::class, 'dashboardCuidador'])->name('dashboard.cuidador');
    Route::get('api/dashboard/stats', [DashboardController::class, 'apiStats'])->name('api.dashboard.stats');
    
    // Página de prueba del sistema de medicamentos
    Route::get('test/medicamentos', [DashboardController::class, 'testMedicamentos'])->name('test.medicamentos');

    // Sistema de usuarios existente
    Route::resource('medicines', MedicineController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permisos', PermisoController::class);
    Route::resource('generos', GeneroController::class);
    Route::resource('personal-medico', PersonalMedicoController::class);
    Route::resource('cuidadores', CuidadorController::class);
    Route::resource('apoderados', ApoderadoController::class);
    Route::resource('pacientes', PacienteController::class);

    // IMPORTANTE: Rutas específicas del sistema unificado ANTES que el resource
    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('select-type', [UnifiedUserController::class, 'selectType'])->name('select-type');
        Route::get('create-by-type', [UnifiedUserController::class, 'create'])->name('create-by-type');
        Route::post('store-by-type', [UnifiedUserController::class, 'store'])->name('store-by-type');
        Route::get('form-data', [UnifiedUserController::class, 'getFormData'])->name('form-data');
    });

    // Resource de usuarios DESPUÉS de las rutas específicas
    Route::resource('usuarios', UserController::class);

    // ========================================
    // 🏥 SISTEMA DE MEDICAMENTOS PROFESIONAL
    // ========================================

    // 📋 CATÁLOGOS BÁSICOS
    Route::prefix('medicamentos')->name('medicamentos.')->group(function () {
        
        // Principios Activos
        Route::resource('principios-activos', PrincipiosActivosController::class);
        Route::post('principios-activos/{principiosActivo}/toggle-status', [PrincipiosActivosController::class, 'toggleStatus'])
             ->name('principios-activos.toggle-status');
        Route::get('api/principios-activos/activos', [PrincipiosActivosController::class, 'getActivos'])
             ->name('api.principios-activos.activos');

        // Formas Farmacéuticas
        Route::resource('formas-farmaceuticas', FormasFarmaceuticasController::class);
        Route::post('formas-farmaceuticas/{formasFarmaceutica}/toggle-status', [FormasFarmaceuticasController::class, 'toggleStatus'])
             ->name('formas-farmaceuticas.toggle-status');
        
        // Vías de Administración  
        Route::resource('vias-administracion', ViasAdministracionController::class);
        Route::post('vias-administracion/{viasAdministracion}/toggle-status', [ViasAdministracionController::class, 'toggleStatus'])
             ->name('vias-administracion.toggle-status');
        
        // Unidades de Medida
        Route::resource('unidades-medida', UnidadesMedidaController::class);
        Route::post('unidades-medida/{unidadesMedida}/toggle-status', [UnidadesMedidaController::class, 'toggleStatus'])
             ->name('unidades-medida.toggle-status');
    });

    // 💊 MEDICAMENTOS
    Route::resource('medicamentos', MedicamentosController::class);
    Route::post('medicamentos/{medicamento}/toggle-status', [MedicamentosController::class, 'toggleStatus'])
         ->name('medicamentos.toggle-status');
    Route::get('medicamentos/inventario/alertas', [MedicamentosController::class, 'inventario'])
         ->name('medicamentos.inventario');
    Route::get('api/medicamentos/activos', [MedicamentosController::class, 'getActivos'])
         ->name('api.medicamentos.activos');

    // 🩺 TRATAMIENTOS
    Route::resource('tratamientos', TratamientosController::class);
    Route::post('tratamientos/{tratamiento}/toggle-status', [TratamientosController::class, 'toggleStatus'])
         ->name('tratamientos.toggle-status');
    Route::post('tratamientos/{tratamiento}/completar', [TratamientosController::class, 'completar'])
         ->name('tratamientos.completar');

    // 👩‍⚕️ ADMINISTRACIONES (Para Cuidadores)
    Route::resource('administraciones', AdministracionesController::class);

    // ========================================
    // 📊 SISTEMA DE REPORTES Y GRÁFICOS
    // ========================================
    Route::prefix('reportes')->name('reportes.')->group(function () {
        // Dashboard principal de reportes con gráficos generales
        Route::get('dashboard', [ReportesController::class, 'dashboard'])
            ->name('dashboard');
        
        // Reportes específicos por paciente
        Route::get('paciente/{paciente}', [ReportesController::class, 'reportePaciente'])
            ->name('paciente');
        
        // Reportes específicos por medicamento
        Route::get('medicamento/{medicamento}', [ReportesController::class, 'reporteMedicamento'])
            ->name('medicamento');
    });

    // ========================================
    // 🏥 SISTEMA DE MEDICAMENTOS PROFESIONAL
    // ========================================

    // Dashboard de Seguimiento para Cuidadores
    Route::prefix('seguimiento')->name('seguimiento.')->group(function () {
        Route::get('/cuidador', [SeguimientoController::class, 'dashboardCuidador'])
            ->name('cuidador.dashboard');
        Route::get('/cuidador/paciente/{paciente}', [SeguimientoController::class, 'verPaciente'])
            ->name('cuidador.paciente');
        Route::post('/administracion/{administracion}/confirmar', [SeguimientoController::class, 'confirmarAdministracion'])
            ->name('administracion.confirmar');
        Route::post('/administracion/{administracion}/reportar-problema', [SeguimientoController::class, 'reportarProblema'])
            ->name('administracion.problema');
        Route::get('/historial-administraciones', [SeguimientoController::class, 'historialAdministraciones'])
            ->name('historial');
    });

    // Portal de Autorizaciones para Apoderados
    Route::prefix('autorizaciones')->name('autorizaciones.')->group(function () {
        Route::get('/dashboard', [AutorizacionesController::class, 'dashboardApoderado'])
            ->name('dashboard');
        Route::get('/solicitud/{solicitud}', [AutorizacionesController::class, 'mostrarSolicitud'])
            ->name('solicitud.mostrar');
        Route::post('/solicitud/{solicitud}/procesar', [AutorizacionesController::class, 'procesarSolicitud'])
            ->name('solicitud.procesar');
        Route::get('/historial', [AutorizacionesController::class, 'historial'])
            ->name('historial');
    });

    // Dashboard de Monitoreo para Médicos
    Route::prefix('monitoreo')->name('monitoreo.')->group(function () {
        Route::get('/dashboard', [MonitoreoController::class, 'dashboardMedico'])
            ->name('dashboard');
        Route::get('/paciente/{paciente}', [MonitoreoController::class, 'verPaciente'])
            ->name('paciente');
        Route::get('/reportes', [MonitoreoController::class, 'reportes'])
            ->name('reportes');
    });

    // API endpoints para el sistema de seguimiento
    Route::prefix('api/seguimiento')->name('api.seguimiento.')->group(function () {
        Route::get('/alertas-pendientes', [SeguimientoController::class, 'alertasPendientes'])
            ->name('alertas.pendientes');
        Route::get('/administraciones-hoy', [SeguimientoController::class, 'administracionesHoy'])
            ->name('administraciones.hoy');
        Route::post('/marcar-alerta-leida/{alerta}', [SeguimientoController::class, 'marcarAlertaLeida'])
            ->name('alerta.marcar-leida');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
