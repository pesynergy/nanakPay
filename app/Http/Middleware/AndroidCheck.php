<?php

namespace App\Http\Middleware;

use Closure;

class AndroidCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(
            \Request::is('application/auth/v1')
            || \Request::is('application/auth/reset/request')
            || \Request::is('application/auth/reset')
            || \Request::is('application/auth/register')
            || \Request::is('application/auth/slide')
            || \Request::is('application/completekyc')
        ){
            return $next($request);
        }

        $request['user_id'] = intval($request->user_id);
        $apptoken = \App\Model\Securedata::where('apptoken', $request->apptoken)->where('user_id', $request->user_id)->first();
        if(!$apptoken){
            return response()->json(['statuscode'=>'UA', 'status'=>'UA', 'message' => "Unauthorize Access Ip"]);
        }else{
            \App\Model\Securedata::where('apptoken', $request->apptoken)->where('user_id', $request->user_id)->update(['last_activity' => time()]);
        }

        $user = \App\User::where('id', $apptoken->user_id)->first();

        if($user->status == "blocked"){
            return response()->json(['statuscode'=>'ERR', 'message' => "Account Blocked"]);
        }

        if($user->company->status == "0"){
            return response()->json(['statuscode'=>'ERR', 'message' => "Service Down"]);
        }

        if($user->kyc != "verified"){
            return response()->json(['statuscode'=>'ERR', 'message' => "Complete your kyc"]);
        }

        if(!$request->has("lat")){
            $ip = geoip($request->ip());
            $request['lat'] = sprintf('%0.4f', $ip->lat);
            $request['lon'] = sprintf('%0.4f', $ip->lon);
        }

        $request['user_id'] = str_replace(".0", "", $request->user_id);

        $request['via'] = "app";
        return $next($request);
    }
}
