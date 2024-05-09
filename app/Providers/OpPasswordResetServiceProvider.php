<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\VendorPackage\OpPasswordBrokerManager;

class OpPasswordResetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerPasswordBrokerManager();
    }

    protected function registerPasswordBrokerManager()
    {
        $this->app->singleton('auth.password', function ($app) {
            return new OpPasswordBrokerManager($app);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        return ['auth.password'];
    }
}
