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

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
