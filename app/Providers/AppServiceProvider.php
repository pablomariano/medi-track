<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Services\UserRegistrationService;
use Illuminate\Http\Request;

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
        // Configurar confianza en proxies para detectar IPs reales
        $this->configureTrustedProxies();
        
        // Forzar HTTPS en producción
        if (config('app.env') === 'production' || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
        
        // Registrar observer de auditoría para modelos críticos
        $this->registerAuditObservers();
    }

    /**
     * Configurar confianza en proxies para detectar IPs reales
     */
    private function configureTrustedProxies(): void
    {
        // En desarrollo local con Docker/Sail, confiar en todos los proxies
        if (app()->environment('local')) {
            Request::setTrustedProxies(['*'], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
        }
        
        // En producción, configurar según sea necesario
        if (app()->environment('production')) {
            // Si tienes un proxy específico, configúralo aquí
            // Request::setTrustedProxies(['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16'], Request::HEADER_X_FORWARDED_FOR);
        }
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
