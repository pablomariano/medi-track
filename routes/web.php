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
use App\Http\Controllers\PacienteCuidadorController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');



Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/refresh', [DashboardController::class, 'refresh'])->name('dashboard.refresh');
    
    Route::get('dashboard/medicamentos', function () {
        return Inertia::render('Dashboard/Medicamentos');
    })->name('dashboard.medicamentos');

    Route::resource('medicines', MedicineController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permisos', PermisoController::class);
    Route::resource('generos', GeneroController::class);
    Route::resource('personal-medico', PersonalMedicoController::class);
    Route::resource('cuidadores', CuidadorController::class);
    Route::resource('apoderados', ApoderadoController::class);
    Route::resource('pacientes', PacienteController::class);

    // === SISTEMA DE ASIGNACIÓN DE CUIDADORES ===
    Route::prefix('asignaciones-cuidadores')->name('asignaciones-cuidadores.')->group(function () {
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

    // IMPORTANTE: Rutas específicas del sistema unificado ANTES que el resource
    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('select-type', [UnifiedUserController::class, 'selectType'])->name('select-type');
        Route::get('create-by-type', [UnifiedUserController::class, 'create'])->name('create-by-type');
        Route::post('store-by-type', [UnifiedUserController::class, 'store'])->name('store-by-type');
        Route::get('form-data', [UnifiedUserController::class, 'getFormData'])->name('form-data');
    });

    // Resource de usuarios DESPUÉS de las rutas específicas
    Route::resource('usuarios', UserController::class);

    // === SISTEMA DE MEDICAMENTOS ===
    
    // Rutas de Medicamentos
    Route::resource('medicamentos', MedicamentoController::class);
    Route::get('medicamentos-search', [MedicamentoController::class, 'search'])->name('medicamentos.search');
    Route::get('medicamentos-datatable', [MedicamentoController::class, 'dataTable'])->name('medicamentos.datatable');

    // Rutas de Tratamientos
    Route::resource('tratamientos', TratamientoController::class);
    Route::patch('tratamientos/{tratamiento}/activar', [TratamientoController::class, 'activar'])->name('tratamientos.activar');
    Route::patch('tratamientos/{tratamiento}/pausar', [TratamientoController::class, 'pausar'])->name('tratamientos.pausar');
    Route::patch('tratamientos/{tratamiento}/finalizar', [TratamientoController::class, 'finalizar'])->name('tratamientos.finalizar');

    // Rutas de Administraciones
    Route::prefix('administraciones')->name('administraciones.')->group(function () {
        Route::get('/', [AdministracionController::class, 'index'])->name('index');
        Route::get('pendientes', [AdministracionController::class, 'pendientes'])->name('pendientes');
        Route::get('historial', [AdministracionController::class, 'historial'])->name('historial');
        Route::post('/', [AdministracionController::class, 'store'])->name('store');
        Route::patch('{administracion}/administrar', [AdministracionController::class, 'administrar'])->name('administrar');
        Route::patch('{administracion}/omitir', [AdministracionController::class, 'omitir'])->name('omitir');
        Route::patch('{administracion}/rechazar', [AdministracionController::class, 'rechazar'])->name('rechazar');
    });

    // Rutas de Cronograma (Horarios de Medicamentos)
    Route::prefix('cronograma')->name('cronograma.')->group(function () {
        Route::get('/', [App\Http\Controllers\CronogramaController::class, 'index'])->name('index');
        Route::get('resumen-semanal', [App\Http\Controllers\CronogramaController::class, 'resumenSemanal'])->name('resumen-semanal');
        Route::patch('{administracion}/administrar', [App\Http\Controllers\CronogramaController::class, 'administrar'])->name('administrar');
        Route::patch('{administracion}/omitir', [App\Http\Controllers\CronogramaController::class, 'omitir'])->name('omitir');
        Route::post('prn', [App\Http\Controllers\CronogramaController::class, 'registrarPrn'])->name('prn');
    });
  });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
