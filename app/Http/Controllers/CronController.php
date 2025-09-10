<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Report;
use App\User;
use Carbon\Carbon;
use App\Model\Api;
use App\Model\Payoutreport;
use App\Model\Provider;

class CronController extends Controller
{
    public function sessionClear()
  	{
	    \DB::table('sessions')->where('last_activity' , '<', time()- 900)->delete();
  	}
  	
  	public function passwordClear()
  	{
	    \DB::table('password_resets')->where('last_activity' , '<', time()-180)->delete();
  	}
    
    public function utrcodeupdate()
    {
        \DB::table('portal_settings')->where('code', 'utrcode')->increment("value", 1);
    }

  	public function payoutClear()
  	{
	    $payouts = \DB::table('payoutreports')->where('status' , "accept")->get(["id", "txnid", "user_id", "option4", "option5", "option6", "amount", 'api_id', 'remark']);

	    foreach ($payouts as $payout) {

	    	$api = \DB::table("apis")->where("id", $payout->api_id)->first();

            switch ($api->code) {
                case 'nifipay':
                    $url    = "https://nifipayments.com/Api/Payment/directpay/v3";

                    $header = array(
                        'Content-Type: application/json',
                        'X-npay-Auth-Code: '.$api->username,
                        'X-npay-Client-Id: '.$api->password,
                        'X-npay-Client-Secret: '.$api->optional1,
                        'X-npay-Endpoint-Ip: '.$api->optional2
                    );

                    $parameters = [
                        "directpe"      => base64_encode("8447949268"),
                        "type"          => "status",
                        "order_id"      => $payout->txnid
                    ];

                    $result = \Myhelper::curl($url, "POST", json_encode($parameters), $header, "no");

                    if($result['response'] != ""){
                        $response = json_decode($result['response']);
                        if(isset($response->statuscode) && in_array($response->statuscode, ["1001"])){
                            if($response->status == "success"){
                                Report::where('id', $payout->id)->update([
                                    'status' => "success", 
                                    'refno'  => $response->refno,
                                    'payid'  => $response->payid
                                ]);

                                try {
                                    $webhook_payload = [
                                        'event_name' => 'payout',
                                        'data'       => [
                                            'status' => "success",
                                            'utr_no' => $response->refno,
                                            'txn_id' => $payout->txnid,
                                            'amount' => $payout->amount,
                                            'udf1'   => $payout->option4,
                                            'udf2'   => $payout->option5,
                                            'udf3'   => $payout->option6
                                        ]
                                    ];

                                    $token    = \DB::table("apitokens")->where("user_id", $payout->user_id)->first();
                                    $response = \Myhelper::curl($token->payoutcallbackurl, "POST", json_encode($webhook_payload), $header, "no");
                                    \DB::table('log_webhooks')->insert([
                                        'url' => $url."?".json_encode($webhook_payload), 
                                        'callbackresponse' => json_encode($response),
                                        'txnid'      => $payout->txnid, 
                                        'product'    => 'payout'
                                    ]);
                                } catch (\Exception $e) {
                                }
                            }elseif(isset($response->status) && in_array($response->status, ["failed", "reversed"])){
                                Payoutreport::where('id', $payout->id)->update([
                                    'status' => "reversed", 
                                    'refno'  => isset($response->refno) ? $response->refno : "Failed"
                                ]);

                                \Myhelper::transactionRefund($payout->id);
                                $webhook_payload = [
                                    'event_name' => 'payout',
                                    'data'       => [
                                        'status' => "failed",
                                        'utr_no' => isset($response->refno) ? $response->refno : "Failed",
                                        'txn_id' => $payout->txnid,
                                        'amount' => $payout->amount,
                                        'udf1'   => $payout->option4,
                                        'udf2'   => $payout->option5,
                                        'udf3'   => $payout->option6
                                    ]
                                ];

                                $token    = \DB::table("apitokens")->where("user_id", $payout->user_id)->first();
                                $response = \Myhelper::curl($token->payoutcallbackurl, "POST", json_encode($webhook_payload), $header, "no");
                                \DB::table('log_webhooks')->insert([
                                    'url' => $url."?".json_encode($webhook_payload), 
                                    'callbackresponse' => json_encode($response),
                                    'txnid'      => $payout->txnid, 
                                    'product'    => 'payout'
                                ]);
                            }
                        }
                    }
                    break;
                
                default:
                    $url = "http://103.205.64.251:8080/clickncashapi/rest/auth/generateToken";
                    $header = array(
                        'Content-Type: application/json'
                    );

                    $parameter = [
                        "username" => $api->username,
                        "password" => $api->password
                    ];

                    $result   = \Myhelper::curl($url, "POST", json_encode($parameter), $header, "no");
                    $response = json_decode($result['response']);

                    if(isset($response->payload->token)){
                        $header = array(
                            'Content-Type: application/json',
                            'Authorization: Bearer '.$response->payload->token
                        );

                        $parameters = [
                            "orderId"   => $payout->txnid,
                            "txnType"   => "PAYOUT",
                            "txnStartDate" => "2019-04-28",
                            "txnEndDate"   => date("Y-m-d"),
                            "txnId"        => $payout->txnid
                        ];

                        $url    = "http://103.205.64.251:8080/clickncashapi/rest/auth/transaction/checkStatus";
                        $result = \Myhelper::curl($url, "POST", json_encode($parameters), $header, "no");

                        if($result['response'] != ""){
                            $response = json_decode($result['response']);
                            
                            if(isset($response->status) && in_array($response->status, ["FAILED"])){
                                Payoutreport::where('id', $payout->id)->update([
                                    'status' => "reversed", 
                                    'refno'  => isset($response->utr) ? $response->utr : "Failed"
                                ]);

                                \Myhelper::transactionRefund($payout->id, "payoutreports", "payoutwallet");
                                $webhook_payload = [
                                    'event_name' => 'payout',
                                    'data'       => [
                                        'status' => "failed",
                                        'utr_no' => isset($response->utr) ? $response->utr : "Failed",
                                        'txn_id' => $payout->txnid,
                                        'amount' => $payout->amount,
                                        'udf1'   => $payout->option4,
                                        'udf2'   => $payout->option5,
                                        'udf3'   => $payout->option6
                                    ]
                                ];

                                $token    = \DB::table("apitokens")->where("user_id", $payout->user_id)->first();
                                $response = \Myhelper::curl($token->payoutcallbackurl, "POST", json_encode($webhook_payload), $header, "no");
                                \DB::table('log_webhooks')->insert([
                                    'url' => $url."?".json_encode($webhook_payload), 
                                    'callbackresponse' => json_encode($response),
                                    'txnid'      => $payout->txnid, 
                                    'product'    => 'payout'
                                ]);

                            }elseif(isset($response->status) && in_array($response->status, ["SUCCESS"])){
                                Payoutreport::where('id', $payout->id)->update([
                                    'status' => "success", 
                                    'refno'  => $response->utr
                                ]);

                                $webhook_payload = [
                                    'event_name' => 'payout',
                                    'data'       => [
                                        'status' => "success",
                                        'utr_no' => $response->utr,
                                        'txn_id' => $payout->txnid,
                                        'amount' => $payout->amount,
                                        'udf1' => $payout->option4,
                                        'udf2' => $payout->option5,
                                        'udf3' => $payout->option6
                                    ]
                                ];

                                $token    = \DB::table("apitokens")->where("user_id", $payout->user_id)->first();
                                $response = \Myhelper::curl($token->payoutcallbackurl, "POST", json_encode($webhook_payload), $header, "no");

                                \DB::table('log_webhooks')->insert([
                                    'url' => $token->payoutcallbackurl."?".json_encode($webhook_payload), 
                                    'callbackresponse' => json_encode($response),
                                    'txnid'      => $payout->txnid, 
                                    'product'    => 'payout'
                                ]);
                            }
                        }
                    }
                    break;
            }
	    }
  	}

    public function payoutProcessClear()
    {
        $payouts = \DB::table('payoutreports')->where('status' , "processing")->get(["id", "txnid", "user_id", "option4", "option5", "option6", "amount", 'api_id', 'remark']);

        foreach ($payouts as $payout) {
            do {
                $utr = $this->utrcode().rand(11111111, 99999999);
            } while (Payoutreport::where("refno", $utr)->first() instanceof Payoutreport);

            Payoutreport::where('id', $payout->id)->update([
                'status' => "success", 
                'refno'  =>$utr
            ]);

            try {
                $webhook_payload = [
                    'event_name' => 'payout',
                    'data'       => [
                        'status' => "success",
                        'utr_no' => $utr,
                        'txn_id' => $payout->txnid,
                        'amount' => $payout->amount,
                        'udf1'   => $payout->option4,
                        'udf2'   => $payout->option5,
                        'udf3'   => $payout->option6
                    ]
                ];

                $token    = \DB::table("apitokens")->where("user_id", $payout->user_id)->first();
                $response = \Myhelper::curl($token->payoutcallbackurl, "POST", json_encode($webhook_payload), $header, "no");
                \DB::table('log_webhooks')->insert([
                    'url' => $url."?".json_encode($webhook_payload), 
                    'callbackresponse' => json_encode($response),
                    'txnid'      => $payout->txnid, 
                    'product'    => 'payout'
                ]);
            } catch (\Exception $e) {
            }
        }
    }
}
