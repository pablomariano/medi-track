<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermisoController;

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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
