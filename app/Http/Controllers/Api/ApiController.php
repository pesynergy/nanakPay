<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\User;
use App\Model\Provider;
use App\Model\Circle;

class ApiController extends Controller
{
    public function getbalance(Request $post)
    {
        $user = User::where('id',$post->user_id)->first(['payoutwallet', 'mainwallet']);
        if(!$user){
            return response()->json(['statuscode' => 'ERR', 'message' => 'Please contact service provider']);
        }else{
            return response()->json(['statuscode' => "TXN", "message" => "Balance Fetched Successfully", "balance" => $user->mainwallet, "payoutbalance" => $user->payoutwallet]);
        }
    }

    public function resources(Request $post)
    {
        $recharge = [
            "mobile"  => "Mobile",
            "dth"     => "Dth",
        ];

        $bill = [
            "electricity"   => "Electricity",
            "lpggas"        => "Lpg Gas",
            "gasutility"    => "Piped Gas",
            "landline"      => "Landline",
            "postpaid"      => "Postpaid",
            "broadband"     => "Broadband",
            "loanrepay"     => "Loan Repay",
            "lifeinsurance" => "Life Insurance",
            "fasttag"       => "Fast Tag",
            "cable"         => "Cable",
            "insurance"     => "Insurance",
            "muncipal"      => "Minicipal",
            "housing"       => "Housing"
        ];

        foreach ($recharge as $key => $value) {
            $data["Recharge"][$value] = Provider::where('type', $key)->get(["name", "id as providerId"]);
        }

        foreach ($bill as $key => $value) {
            $data["Billpayment"][$value] = Provider::where('type', $key)->get(["name", "id as providerId"]);
        }

        $data['circles']  = Circle::get();
        $data['Dmtbank']  = \DB::table("dmtbanks")->get();
        $data['Aepsbank'] = \DB::table("aepsbanks")->get();
        return response()->json(['statuscode' => "TXN", "message" => "Data Fetched Successfully", 'data'=> $data]);
    }

    public function getip(Request $post)
    {
        return response()->json(['statuscode' => "TXN", "message" => "Api Fetched Successfully", 'ip'=> $post->ip()]);
    }
}