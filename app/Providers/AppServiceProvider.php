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
        //
    }
}
