<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


class EnsureHasBusiness
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $business = auth()->user()->business()->count();
        if($business == 1) {
            session(['business' => auth()->user()->business()->first()->code]);
            //setcookie(BUSINESS_IDENTIFY, session(BUSINESS_IDENTIFY), time() + 864000 );
        }
        if($request->session()->missing('business'))
            return redirect(route('business.select', app()->getLocale()));
        return $next($request);
    }
}
