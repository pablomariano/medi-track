<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\EmailPreferencesController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance');

    // Email Preferences Routes
    Route::get('settings/email-preferences', [EmailPreferencesController::class, 'index'])->name('email-preferences.index');
    Route::post('settings/email-preferences', [EmailPreferencesController::class, 'update'])->name('email-preferences.update');
    Route::post('settings/email-preferences/test', [EmailPreferencesController::class, 'sendTestEmail'])->name('email-preferences.send-test');
    Route::post('settings/email-preferences/report', [EmailPreferencesController::class, 'sendAdherenceReport'])->name('email-preferences.send-report');
});
