<?php

namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserBusinessMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response  = $next($request);


        if (str_contains($request->path(), "api/v1/es/email/verify/")){
            return $response;
        }
        $bs = request()->header(BUSINESS_IDENTIFY);
        if(empty($bs)) return response()->json(["error" => __("User not allowed")], status: 401);

        $bs = Business::query()->where('code', $bs )->first();
        if($bs == null) return response()->json(["error" => __("Access not allowed")], status: 401);

        if(Auth::check()){
            $user = auth()->user()->getAuthIdentifier();
            $user = User::query()->where('id', $user)->whereHas(BUSINESS_IDENTIFY, function($q) use($bs){
                $q->where(BUSINESS_IDENTIFY . '.id', $bs->id);
            })->first();
            if(empty($user)) return response()->json(["error" => __("Access not allowed")], status: 401);
            return $response;
        }
        return $response;
    }
}
