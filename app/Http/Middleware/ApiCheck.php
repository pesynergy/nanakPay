<?php

namespace App\Http\Middleware;

use Closure;

class ApiCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($post, Closure $next)
    {
        // dd($post->REQUEST_URI);
        if(
            \Request::is('v/getip')
        ){
            return $next($post);
        }
        if ($post->is('v/payout/v2/webhook')) {
            return $next($post);
        }

        if(!$post->has('token')){
            return response()->json(['statuscode'=>'ERR', 'message'=> 'Invalid api token']);
        }
        
        $user = \App\Model\Apitoken::where('ip', $post->ip())->where('token', $post->token)->first();
        // \Log::info('IP Data: ' .$post->ip());
        // \Log::info('Request Data: ' .$post);
        if(!$user){
            return response()->json(['statuscode'=>'ERR','status'=>'ERR','message'=> 'Request From Invalid Ip Address, '.$post->ip()]);
        }

        if($user->status == "0"){
            return response()->json(['statuscode'=>'ERR','status'=>'ERR','message'=> 'Ip Address approval is pending, kindly contact service provider']);
        }

        $post['via'] = "api";
        $post['user_id'] = $user->user_id;

        if(\Request::is('api/transaction/query') && $post->has("apitxnid")){
            $report = \App\Model\Report::where("apitxnid", $post->apitxnid)->first();

            if(!$report){
                return response()->json(['statuscode' => "TNF", "message" => "Transaction Not Found"]);
            }
            $post['id'] = $report->id;
        }
        return $next($post);
    }
}
