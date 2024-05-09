<?php

namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\NewRole;
use Closure;
use Illuminate\Http\Request;

class RolesAdmin
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
        if(auth()->user()->hasAnyRole(ALL_ACCESS)) return $next($request);
        if(in_array('GET', $request->route()->methods())) return $next($request);


        $privates = ['POST', 'PUT', 'PATH', 'DELETE'];
        $business = Business::query()->where('code', session('business'))->first();
        $roles = auth()->user()->getRoleNames()->toArray();
        $roles = NewRole::query()
            ->whereHas(BUSINESS_IDENTIFY, function ($q) use ($business){
                $q->where(BUSINESS_IDENTIFY,$business->id);
            })
            ->whereIn('name', $roles)
            //->where('is_admin', true)
            ->count();

        $methods =  array_intersect($privates, $request->route()->methods());
        if($roles == 0 && count($methods) > 0) return abort(403);
        return $next($request);
    }
}
