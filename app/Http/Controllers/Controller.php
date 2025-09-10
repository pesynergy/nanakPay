<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function transcode()
    {
        $code = \DB::table('portal_settings')->where('code', 'transactioncode')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "none";
        }
    }

    public function payoutsuccess()
    {
        $code = \DB::table('portal_settings')->where('code', 'payoutsuccess')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "success";
        }
    }

    public function utrcode()
    {
        $code = \DB::table('portal_settings')->where('code', 'utrcode')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "none";
        }
    }

    public function getAccBalance($id, $wallet)
    {
        $mywallet = \DB::table('users')->where('id', $id)->first([$wallet]);

        $mywallet = (array) $mywallet;
        return $mywallet[$wallet];
    }

    public function pinbased()
    {
        $code = \DB::table('portal_settings')->where('code', 'pincheck')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "no";
        }
    }

    public function applogout()
    {
        $code = \DB::table('portal_settings')->where('code', 'applogout')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return 20;
        }
    }
    
    public function weblogout()
    {
        $code = \DB::table('portal_settings')->where('code', 'weblogout')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return 15;
        }
    }

    public function schememanager()
    {
        $code = \DB::table('portal_settings')->where('code', 'schememanager')->first(['value']);
        if($code){
           return $code->value;
        }else{
            return "none";
        }
    }

    public function pinCheck($data)
    {
        if($this->pinbased() == "yes"){
            if(!\Myhelper::can('pin_check', $data->user_id)){
                $code = \DB::table('pindatas')->where('user_id', $data->user_id)->where('pin', \Myhelper::encrypt($data->tpin, "ipsonline##01012022"))->first();
                if(!$code){
                    return 'fail';
                }
            }
        }
    }
}
