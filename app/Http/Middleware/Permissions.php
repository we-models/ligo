<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Spatie\Permission\Exceptions\UnauthorizedException;

/**
 *
 */
class Permissions
{
    /**
     * @var array|string[]
     */
    private array $exceptNames = [
        'LaravelInstaller*',
        'LaravelUpdater*',
        'debugbar*'
    ];

    /**
     * @var array|string[]
     */
    private array $exceptControllers = [
        'LoginController',
        'ForgotPasswordController',
        'ResetPasswordController',
        'RegisterController',
        'PayPalController'
    ];

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $permission = $request->route()->getName();
        if ($this->match($request->route()) && auth()->user()->canNot($permission)) {
            throw new UnauthorizedException(403, "Error: ".  $permission );
        }

        return $next($request);
    }

    /**
     * @param Route $route
     * @return bool
     */
    private function match(Route $route): bool
    {
        if ($route->getName() == '' || $route->getName() === null) {
            return false;
        } else {
            if (in_array(class_basename($route->getController()), $this->exceptControllers)) {
                return false;
            }
            foreach ($this->exceptNames as $except) {
                if (str_contains($except, $route->getName())) {
                    return false;
                }
            }
        }
        return true;
    }
}
