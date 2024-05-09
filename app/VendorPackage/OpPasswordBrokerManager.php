<?php

namespace App\VendorPackage;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
use InvalidArgumentException;

class OpPasswordBrokerManager extends PasswordBrokerManager
{
    protected function resolve($name)
    {
        $config = $this->getConfig($name);
        if (is_null($config)) {
            throw new InvalidArgumentException("Password resetter [{$name}] is not defined.");
        }

        return new PasswordBrokerOp(
            $this->createTokenRepository($config),
            $this->app['auth']->createUserProvider($config['provider'])
        );
    }
}
