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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
