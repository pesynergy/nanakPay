<?php

namespace App\Http\Controllers\Services;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Model\Provider;
use App\Model\Report;
use Carbon\Carbon;
use App\Model\Api;
use App\User;

class CollectionController extends Controller
{
    protected $admin;
    public function __construct()
    {
        $this->admin  = User::whereHas('role', function ($q){
            $q->where("slug", "admin");
        })->first();
    }

    public function query(Request $post)
    {
        $rules = array(
            'check_by' => 'required',
            'check_value' => 'required'
        );
        
        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        switch ($post->check_by) {
            case 'udf1':
                $report = Report::where("option1", $post->check_value)->where("product", "qrcode")->first();
                break;

            case 'udf2':
                $report = Report::where("option2", $post->check_value)->where("product", "qrcode")->first();
                break;

            case 'udf3':
                $report = Report::where("option3", $post->check_value)->where("product", "qrcode")->first();
                break;
            
            default:
                $report = Report::where("txnid", $post->check_value)->where("product", "qrcode")->first();
                break;
        }
        

        if($report){
            return response()->json([
                'status'  => "success",
                'message' => "Successfully Found", 
                'data'    => [
                    'amount' => $report->amount,
                    'utr_no' => $report->refno,
                    'status' => $report->status,
                    'txn_id' => $report->txnid,
                    'udf1' => $report->option1,
                    'udf2' => $report->option2,
                    'udf3' => $report->option3,
                    'payer_name' => $report->option4,
                    'payer_upi'  => $report->option5
                ]
            ]);
        }else{
            return response()->json([
                'status'    => 'error',
                'message'   => "No Transaction found"
            ]);
        }
    }

    public function getQrcode(Request $post)
    {
        if (!\Myhelper::can('payin_service', $post->user_id)) {
            return response()->json(['status' => "error", "message" => "Service Not Allowed"]);
        }

        $rules['amount'] = "required|numeric|min:100|max:200000";
        
        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user     = User::where('id', $post->user_id)->first();
        $provider = \App\Model\Provider::where('recharge1', 'payin')->first();

        if(!$provider){
            return response()->json(['status' => "error", "message" => "Operator Not Found"]);
        }

        if($provider->status == 0){
            return response()->json(['status' => "error", "message" => "Operator Currently Down."]);
        }

        $post['charge'] = \Myhelper::getCommission(0, $user->scheme_id, $provider->id, $user->role->slug);
        $post['gst']    = ($provider->api->gst * $post->charge)/100;

        $serviceApi = \DB::table("service_managers")->where("provider_id", $provider->id)->where("user_id", $user->id)->first();
        if($serviceApi){
            $api = Api::find($serviceApi->api_id);
        }else{
            $api = Api::find($provider->api_id);
        }

        if(!$api || $api->status == 0){
            return response()->json(['status' => "error", "message" => "Service Currently Down"]);
        }

        if($this->getAccBalance($user->id, "mainwallet") < $post->charge){
            return response()->json(['status' => "error", "message" => "Insufficient Wallet Balance"]);
        }

        switch ($api->code) {
            default:
                do {
                    $post['txnid'] = $this->transcode().'PO'.rand(111111111111, 999999999999);
                } while (Report::where("txnid", "=", $post->txnid)->first() instanceof Report);

                $url = "https://api.gameupi.com/v/qr/collection";
                $parameter = [
                    "txnid"  => $post->txnid,
                    "email"  => $user->email,
                    "name"   => $user->name,
                    "mobile" => $user->mobile,
                    "amount" => $post->amount,
                    "type"   => "dynamic",
                    "callback"  => url("callback/collection/verifyapi"),
                    "token"  => $api->username,
                ];

                $header = array(
                    "Content-Type: application/json",
                );

                $method = "POST";
                $query  = json_encode($parameter);

                $result   = \Myhelper::curl($url, 'POST', $query, $header, "yes", "Qr", $post->txnid);
                $response = json_decode($result['response']);

                if(isset($response->statuscode) && $response->statuscode == "TXN"){
                    $debit = [
                        'number'  => $response->upi_tr,
                        'mobile'  => $user->mobile,
                        'provider_id' => $provider->id,
                        'api_id'  => $api->id,
                        'amount'  => $post->amount,
                        'charge'  => $post->charge,
                        'gst'     => $post->gst,
                        'txnid'   => $post->txnid,
                        'create_time' => $post->txnid,
                        'payid'   => $response->upi_id,
                        'refno'   => "UPI Intent Creation Fee",
                        'description'  => $response->upi_string,
                        'option1' => $post->udf1,
                        'option2' => $post->udf2,
                        'option3' => $post->udf3,
                        'status'  => "pending",
                        'user_id' => $user->id,
                        'credit_by' => $user->id,
                        'rtype'   => 'main',
                        'via'     => $post->via,
                        'trans_type' => 'debit',
                        'product'    => "qrcode"
                    ];

                    try {
                        $report = \DB::transaction(function () use($debit, $post) {
                            $debit["balance"] = $this->getAccBalance($debit['user_id'], "mainwallet");
                            User::where('id', $debit['user_id'])->decrement("mainwallet", $post->charge + $post->gst);
                            $debit["closing"] = $this->getAccBalance($debit['user_id'], "mainwallet");
                            return Report::create($debit);
                        });
                    } catch (\Exception $e) {
                        \DB::table('log_500')->insert([
                            'line' => $e->getLine(),
                            'file' => $e->getFile(),
                            'log'  => $e->getMessage(),
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                        $report = false;
                    }

                    if(!$report){
                        return response()->json(['status' => "error", "message" => "Error While creating intent, try again"]);
                    }

                    $output['status']  = "success";
                    $output['message'] = "QR Code successfully Generated";
                    $output['data'] = [
                        'txn_id'      => $post->txnid,
                        'intent_data' => $response->upi_string,
                        'status'      => 'INITIATED'
                    ];

                    return response()->json($output);

                }else{
                    return response()->json(['status' => "error", "message" => isset($response->message)?$response->message : "Something went wrong"]);
                }
                break;
        }
    }

    public function verifyapi(Request $post)
    {
        $log = \DB::table('log_webhooks')->insert([
            'txnid'      => $post->apitxnid, 
            'product'    => 'walletpay', 
            'response'   => json_encode($post->all()),
            "created_at" => date("Y-m-d H:i:s")
        ]);

        if(isset($post->status) && $post->status == "success"){
            $report = \DB::table('reports')->where('txnid', $post->apitxnid)->where("product", "qrcode")->where("status", "pending")->first();

            if($report){
                $providerCharge = \App\Model\Provider::where('range1', "<=", $post->amount)->where('range2', ">=", $post->amount)->first();

                if(!$providerCharge){
                    $providerCharge = \App\Model\Provider::where('recharge1', 'payin_charge')->first();
                }
                
                $serviceApi = \DB::table("service_managers")->where("provider_id", $providerCharge->id)->where("user_id", $report->user_id)->first();
                if($serviceApi){
                    $api = Api::find($serviceApi->api_id);
                }else{
                    $api = Api::find($providerCharge->api_id);
                }
                
                if($api->code == "selfcollect"){
                    $user = \DB::table("users")->where('id', $this->admin->id)->first(["mobile", "id", "scheme_id", "role_id"]);
                }else{
                    $user = \DB::table("users")->where('id', $report->user_id)->first(["mobile", "id", "scheme_id", "role_id"]);
                }

                $role = \DB::table("roles")->where("id", $user->role_id)->first(["slug"]);
                $provider = \DB::table("providers")->where('recharge1', 'payin')->first();
                $post['profit'] = 0;
                $post['charge'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $providerCharge->id, $role->slug);
                $post['tds'] = 0;
                $post['gst'] = ($post->charge * $api->gst)/100;

                $insert = [
                    'number'  => $post->payid,
                    'mobile'  => $user->mobile,
                    'provider_id' => $providerCharge->id,
                    'api_id'  => $api->id,
                    'amount'  => $post->amount,
                    'charge'  => $post->charge,
                    'profit'  => $post->profit,
                    'tds'  => $post->tds,
                    'gst'  => $post->gst,
                    'txnid'   => $report->txnid,
                    'payid'   => $post->payid,
                    'option4' => $post->payment_mode,
                    'refno'   => $post->utr,
                    'create_time' => $post->utr,
                    'status'  => 'success',
                    'user_id' => $user->id,
                    'credit_by'   => $report->user_id,
                    'rtype'       => 'main',
                    'trans_type'  => "credit",
                    'product'     => "payin"
                ];

                try {
                    $myreport = \DB::transaction(function () use($insert, $report, $post, $api){
                        $insert["balance"] = $this->getAccBalance($insert['user_id'], "mainwallet");
                        User::where('id', $insert['user_id'])->increment("mainwallet", $post->amount + ($post->profit - $post->tds) - ($post->charge + $post->gst));
                        $insert["closing"] = $this->getAccBalance($insert['user_id'], "mainwallet");
                        Report::create($insert);
                        \DB::table('reports')->where('id', $report->id)->update([
                            "status"  => "success",
                            "option4" => $post->name,
                            "option5" => $post->upi
                        ]);
                    });

                    if($api->code != "selfcollect" ){
                        $token = \DB::table("apitokens")->where("user_id", $user->id)->first();

                        $url = $token->upicallbackurl;
                        $webhook_payload = [
                            'event_name' => 'payin',
                            'data' => [
                                'status' => "success",
                                'utr_no' => $post->utr,
                                'txn_id' => $report->txnid,
                                'amount' => $post->amount,
                                'payer_name' => $post->payment_mode,
                                'payer_upi'  => $post->payid,
                                'udf1' => $report->option1,
                                'udf2' => $report->option2,
                                'udf3' => $report->option3
                            ]
                        ];

                        $header = array(
                            'Content-Type: application/json',
                        );

                        $response = \Myhelper::curl($url, "POST", json_encode($webhook_payload), $header, "no");

                        \DB::table('log_webhooks')->where('txnid', $post->txnId)->update([
                            'url' => $url."?".json_encode($webhook_payload), 
                            'callbackresponse' => json_encode($response)
                        ]);
                    }
                } catch (\Exception $e) {
                    \DB::table('log_500')->insert([
                        'line' => $e->getLine(),
                        'file' => $e->getFile(),
                        'log'  => $e->getMessage(),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }
    
    public function hpayin(Request $post)
    {
        $data = json_decode(json_encode($post->all()), true);
        \DB::table('microlog')->insert(['product'=>'HPPAYIN','response'=>json_encode($post->all()), "created_at" => date("Y-m-d H:i:s")]);
        $report = Report::where('apitxnid', $data["apitxn_id"])->first();
        //Log::info("PIN Webhook Received", ['response' => $post]);
        if($report){
            if(isset($data["status"]) && strtolower($data["status"]) == "success") {
                $update['status'] = $data["status"];
                $update['refno']  = isset($data["utr"]) ? $data["utr"] : "NA";
                $this->sendCallback($data["apitxn_id"], $data["transaction_id"], $data["utr"], $report->amount, $report->user_id, $report->product, $data["status"]);
            }
            elseif(isset($data["status"]) && strtolower($data["status"]) == "refunded") {
                $update['status'] = 'failed';
                $update['refno']  = isset($data["utr"]) ? $data["utr"] : "NA";
                $this->sendCallback($data["apitxn_id"], $data["transaction_id"], $data["utr"], $report->amount, $report->user_id, $report->product, 'failed');
            }
            else {
                $update['status'] = $data["status"];
                $update['refno']  = isset($data["utr"]) ? $data["utr"] : "NA";
                $this->sendCallback($data["apitxn_id"], $data["transaction_id"], $data["utr"], $report->amount, $report->user_id, $report->product, $data["status"]);
            }
            $report->update([
                'status' => $update['status'],
                'refno'  => $update['refno'],
                'txnid'  => $data["transaction_id"]
            ]);
            
            return response()->json(["status" => true, "message" => "ok"]);
        }else{
            return response()->json(["status" => false, "message" => "Not Matched"]);
        }
    }
    
    public function sendCallback($apitxnId,$transactionId, $utr, $amt, $user_id, $product, $status, $method = 'GET')
    {
        $user = User::where('id', $user_id)->first();
        $callbackUrlBase = $user->callbackurl;
    
        $callbackData = [
            'transaction_id' => $transactionId,
            'apitxn_id' => $apitxnId,
            'amount' => $amt,
            'utr' => $utr,
            'status' => $status,
            'product' => $product
        ];
    
        try {
            $header = ["Content-Type: application/json"];
            $response = null;
            $response = \App\Helpers\Permission::curl($callbackUrlBase, 'POST', json_encode($callbackData), $header, "yes", "Qr");
            $callbackUrl = $callbackUrlBase . '?' . http_build_query($callbackData);
            $response = \App\Helpers\Permission::curl($callbackUrl, 'GET', null, $header, "yes", "Qr");
    
            Log::info("Webhook callback sent successfully", ['response' => $response]);
    
            if (isset($response['response']) && $response['response']) {
                LogCallback::create([
                    'url' => $callbackUrlBase,
                    'response' => $response['response'] != '' ? $response['response'] : $response['error'],
                    'status' => $response['code'],
                    'product' => $product,
                    'user_id' => $user_id,
                    'transaction_id' => $transactionId
                ]);
            } else {
                Log::error("Error sending UniqPay webhook callback", ['error' => $response]);
            }
        } catch (\Exception $e) {
            Log::error("Error sending UniqPay webhook callback", ['error' => $e->getMessage()]);
        }
    }
}
