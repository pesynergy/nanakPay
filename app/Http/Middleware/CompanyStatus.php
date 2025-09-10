<?php

namespace App\Http\Middleware;

use Closure;

class CompanyStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
    
        $request['user_id'] = \Auth::id();
        $request['via'] = "portal";
        if(\Request::is("profile/update") && $request->actiontype == "kycdata"){
            return $next($request);
        }
        
        if($request->user() && $request->user()->company && !$request->user()->company->status && $request->user()->role->slug !="admin"){
            abort(503);
        }

        if($request->user() && $request->user()->role->slug !="admin" && $request->user()->kyc == "pending"){
            return redirect(route('memberkyc'));
        }

        if(!\Request::is("dashboard") && $request->user() && $request->user()->role->slug !="admin" && $request->user()->kyc == "submitted"){
            return redirect(route('home'));
        }

        if($request->user() && $request->user()->role->slug !="admin" && $request->user()->kyc == "rejected"){
            return redirect(route('memberkyc'));
        }

        if($request->user() && $request->user()->role->slug !="admin" && $request->user()->status == "blocked"){
            return redirect(route('logout'));
        }
        
        return $next($request);
    }
}
