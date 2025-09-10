<?php

namespace App\Http\Middleware;

use Closure;

class CheckBalance
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
        try {
            if($post->has("amount")){
                if($post->has("user_id")){
                    $user = \DB::table("users")->where("id", $post->user_id)->first(['mainwallet', 'lockedamount']);
                }else{
                    $user = \DB::table("users")->where("id", \Auth::id())->first(['mainwallet', 'lockedamount']);
                }

                if($post->amount > ($user->payoutwallet - $user->lockedamount)){
                    return response()->json(['statuscode' => "ERR", "message" => 'Low Balance, Kindly recharge your wallet.']);
                }
            }
        } catch (\Exception $e) {}

        return $next($request);
    }
}
