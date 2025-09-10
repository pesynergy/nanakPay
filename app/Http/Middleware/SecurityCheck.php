<?php

namespace App\Http\Middleware;

use Closure;

class SecurityCheck
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
        return $next($request);
        if(\Myhelper::hasNotRole('admin')){
            return $next($request);
        }else{

            if(\Request::is('fund/transaction') && $request->has('type')){
                switch ($request->type) {
                    case 'transfer':
                    case 'return':
                    case 'requestview':
                    case 'aepstransfer':
                        if($request->has('mpin') && $request->mpin == date('Y')."ANT".date('dmHi')){
                            return $next($request);
                        }else{
                            return response()->json(['status' => "Permission Not Allowed"], 400);
                        }
                        break;
                    
                    default:
                        return $next($request);
                        break;
                }
            }elseif(\Request::is('setup/update') && $request->has('type')){
                switch ($request->type) {
                    case 'api':
                    case 'bank':
                        if($request->has('mpin') && $request->mpin == date('Y')."ANT".date('dmHi')){
                            return $next($request);
                        }else{
                            return response()->json(['status' => "Permission Not Allowed"], 400);
                        }
                        break;
                    
                    default:
                        return $next($request);
                        break;
                }
            }elseif(\Request::is('profile/update') && $request->has('actiontype')){
                switch ($request->actiontype) {
                    case 'profile':
                        return $next($request);
                        break;
                    
                    default:
                        if($request->has('mpin') && $request->mpin == date('Y')."ANT".date('dmHi')){
                            return $next($request);
                        }else{
                            return response()->json(['status' => "Permission Not Allowed"], 400);
                        }
                        break;
                }
            }else{
                if($request->has('mpin') && $request->mpin == date('Y')."ANT".date('dmHi')){
                    return $next($request);
                }else{
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }
            }
        }
    }
}
