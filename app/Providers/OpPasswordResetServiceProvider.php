<?php

namespace App\Providers;

use App\VendorPackage\OpPasswordBrokerManager;
use App\VendorPackage\PasswordBrokerOp;
use Illuminate\Support\ServiceProvider;

class OpPasswordResetServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->registerPasswordBrokerManager();
    }

    protected function registerPasswordBrokerManager()
    {
        $this->app->singleton('auth.password', function ($app) {
            return new OpPasswordBrokerManager($app);
        });
    }

    public function provides()
    {
        return ['auth.password'];
    }
}
