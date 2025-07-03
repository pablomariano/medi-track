<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\UserRegistrationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UserRegistrationService::class, function ($app) {
            return new UserRegistrationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar HTTPS en producción
        if (config('app.env') === 'production' || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }    
        // Registrar observer de auditoría para modelos críticos
        $this->registerAuditObservers();
    }

    /**
     * Registrar observers de auditoría para modelos críticos
     */
    private function registerAuditObservers(): void
    {
        $modelosAuditables = [
            \App\Models\User::class,
            \App\Models\Paciente::class,
            \App\Models\PersonalMedico::class,
            \App\Models\Cuidador::class,
            \App\Models\Apoderado::class,
            \App\Models\Tratamiento::class,
            \App\Models\Medicamento::class,
            \App\Models\MedicamentoTratamiento::class,
            \App\Models\PacienteMedico::class,
            \App\Models\PacienteCuidador::class,
            \App\Models\Role::class,
            \App\Models\Permiso::class,
            \App\Models\Administracion::class,
        ];

        foreach ($modelosAuditables as $modelo) {
            if (class_exists($modelo)) {
                $modelo::observe(\App\Observers\AuditableObserver::class);
            }
        }
    }
}
