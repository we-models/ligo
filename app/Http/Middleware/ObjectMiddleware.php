<?php

namespace App\Http\Middleware;

use App\Models\ObjectType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ObjectMiddleware
{
    /**
     * Handle an incoming request.
     * Verify By SLUG if the user have permissions for current object type
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->user()->hasAnyRole(ALL_ACCESS)) return $next($request);
        $input = $request->all();

        if(empty($input['object_type'])) return $next($request);

        $object_type = ObjectType::find($input['object_type']);
        if(empty($object_type)) return abort(403);

        $permission = $request->route()->action['as'];
        $permission = explode('.', $permission);
        array_shift($permission);

        if(count($permission) == 0){
            return abort(403);
        }
        $user = auth()->user();

        if($user->can($object_type->slug . '.'. $permission[0])){
            return $next($request);
        }else{
            return abort(403);
        }
    }
}
