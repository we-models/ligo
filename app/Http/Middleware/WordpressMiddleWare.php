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

class WordpressMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure(Request): (Response|RedirectResponse) $next
     * @return JsonResponse|RedirectResponse|Response|mixed
     */
    function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        if(!isset($request['email']) ||  !isset($request['password']))
            return response()->json(["error" => __("User not allowed")], status: 401);
        if (Auth::attempt($request->only(['email', 'password']))) {
            $bs = request()->header(BUSINESS_IDENTIFY);
            if(empty($bs)) return response()->json(["error" => __("User not allowed")], status: 401);
            $bs = Business::query()->where('code', $bs )->first();
            if($bs == null) return response()->json(["error" => __("Access not allowed")], status: 401);
            $user = auth()->user()->getAuthIdentifier();
            $user = User::query()->where('id', $user)->whereHas(BUSINESS_IDENTIFY, function($q) use($bs){
                $q->where(BUSINESS_IDENTIFY . '.id', $bs->id);
            })->first();
            if(empty($user)) return response()->json(["error" => __("Access not allowed")], status: 401);
            return $next($request);
        }else{
            return response()->json(["error" => __("User not allowed")], status: 401);
        }
    }
}
