<?php

namespace App\Http\Controllers\Services;

use App\User;
use App\Model\Api;
use Carbon\Carbon;
use App\Model\Report;
use App\Http\Requests;
use App\Model\Provider;
use App\Model\LogCallback;
use App\Helpers\UniqpayDTO;
use App\Model\Payoutreport;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    protected $api, $admin;
    protected $uniqpayWebhookService;
    public function __construct()
    {  
        $this->api = Api::where('code', 'payout')->first();
        $this->admin = User::whereHas('role', function ($q){
            $q->where('slug', 'admin');
        })->first();
        // $this->uniqpayWebhookService = $service;
    }

    public function payment(Request $post)
    {
        if (!\Myhelper::can('payout_service', $post->user_id)) {
            return response()->json(['statuscode' => "ERR", "message" => "Service Not Allowed"]);
        }

        $rules = array(
            'name'     => 'required',
            'bank'     => 'required',
            'account'  => 'required',
            'mobile'   => 'required|numeric|digits:10',
            'ifsc'     => 'required',
            'apitxnid' => 'required|unique:payoutreports,apitxnid',
            'amount'   => 'required|numeric|min:10|max:200000',
        );
        
        if(in_array($post->user_id, [103, 113])){
            $rules["amount"] = 'required|numeric|min:10|max:25000';
        }
        
        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }
        
        $user = User::where('id', $post->user_id)->first();
        $post['mode'] = "IMPS";
        if($post->mode == "IMPS"){
            if($post->amount > 0 && $post->amount <= 500){
                $provider = Provider::where('recharge1', 'payout1')->first();
            }elseif($post->amount > 500 && $post->amount <= 999){
                $provider = Provider::where('recharge1', 'payout2')->first();
            }elseif($post->amount > 999 && $post->amount <= 24999){
                $provider = Provider::where('recharge1', 'payout3')->first();
            }elseif($post->amount > 24999){
                $provider = Provider::where('recharge1', 'payout4')->first();
            }
        }elseif($post->mode == "NEFT"){
            $provider = Provider::where('recharge1', 'payoutneft')->first();
        }

        if(!$provider){
            return response()->json(['statuscode' => "ERR", "message" => "Operator Not Found"]);
        }

        if($provider->status == 0){
            return response()->json(['statuscode' => "ERR", "message" => "Operator Currently Down."]);
        }

        if(!$provider->api || $provider->api->status == 0){
            return response()->json(['statuscode' => "ERR", "message" => "Service Currently Down."]);
        }

        $post['charge'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $provider->id, $user->role->slug);
        $post['gst']    = ($provider->api->gst * $post->charge)/100;

        if($this->getAccBalance($user->id, "payoutwallet") - $user->lockedamount < $post->amount + $post->charge + $post->gst){
            return response()->json(['statuscode' => "ERR", "message" => "Low balance to make this request."]);
        }

        $serviceApi = \DB::table("service_managers")->where("provider_id", $provider->id)->where("user_id", $user->id)->first();
        if($serviceApi){
            $api = Api::find($serviceApi->api_id);
        }else{
            $api = Api::find($provider->api_id);
        }

        $debit = [
            'number'  => $post->account,
            'mobile'  => $user->mobile,
            'provider_id' => $provider->id,
            'api_id'  => $api->id,
            'amount'  => $post->amount,
            'charge'  => $post->charge,
            'gst'     => $post->gst,
            'txnid'   => $post->txnid,
            'apitxnid'=> $post->apitxnid,
            'description'  => $post->name,
            'option1' => "bank",
            'option2' => $post->ifsc,
            'option3' => $post->bank,
            'option4' => $post->callback,
            // 'status'  => "pending",
            'user_id' => $user->id,
            'credit_by' => $user->id,
            'rtype'   => 'main',
            'via'     => $post->via,
            'balance' => $user->payoutwallet,
            'trans_type' => 'debit',
            'product'    => "payout",
            'create_time'=> $user->id.Carbon::now()->toDateTimeString()
        ];

        try {
            $report = \DB::transaction(function () use($debit, $post) {
                $report = Payoutreport::create($debit);
                User::where('id', $debit['user_id'])->decrement("payoutwallet", $post->amount + ($post->charge + $post->gst));
                return $report;
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
            return response()->json(['statuscode' => "ERR", 'message' => "Something went wrong."]);
        }

        switch ($api->code) {
            case 'payout':
                $payUrl = "https://pay.paytara.in/";
                $pay_id = "OYOM000001";
                $pay_key = "MTBPWU9NMDAwMDAxMTI=";
                
                //generate randomaly
                $apitxnid = $post->apitxnid;
                $transferID = $this->getTransferID();

                $string = "{$pay_id}|{$transferID}|{$post->amount}|TB|IMPS|{$post->name}|{$post->mobile}";
                $checksum = hash_hmac('sha256', $string, $pay_key);
                
                $data = [
                    "merchantTransactionId" => $transferID,
                    "amount" => (float)$post->amount,
                    "payoutChannel" => "TB",
                    "payoutType"=> "IMPS",
                    "beneficiaryVPA"=> "",
                    "beneficiaryAccount"=>$post->account,
                    "beneficiaryIFSC"=>$post->ifsc,
                    "beneficiaryName"=> $post->name,
                    "beneficiaryMobNo"=>$post->mobile,
                    "payoutRemark"=> "Test Payout",
                    "checksum"=>$checksum,
                ];

                $PAYMENT_API_URL = $payUrl."api/v1/payout/initiatePayout";

                $header = array(
                    "Content-Type: application/json",
                    "X-MERCHANT-ID: OYOM000001",
                    "X-MERCHANT-KEY: MTBPWU9NMDAwMDAxMTI="
                );
                $result = \App\Helpers\Permission::curl($PAYMENT_API_URL, 'POST', json_encode($data), $header, "yes", "Qr", $post->txnid);
                if($result['response']){
                    //$response = $result['response'];
                    $response = json_decode($result['response']);
                    //print_r($response);
                    //Sample Response: {"code":"0","msg":"Success","data":{"message":"Payout initiated successfully.","merchantTransactionId":"HPTEST_67ab21aa8495c","apitxnid":"AP01025021115384343107","amount":"10.00","txn_status":1,"payout_status":"SUCCESS","bankref":"504215512311"}}
                    if (isset($response->code) == '0' && isset($response->data->txn_status) == '2' ) {
                        Payoutreport::where('id', $report->id)->update([
                            'status' => "failed",
                            'refno'  =>  $response->data->message,
                            'payid'  => $response->data->apitxnid,
                            'txnid'  => $response->data->merchantTransactionId,
                            'apitxnid' => $apitxnid
                        ]);
    
                        User::where('id', $user->id)->increment('payoutwallet', $post->amount + ($post->charge + $post->gst));
                        return response()->json([
                            'statuscode' => $response->data->txn_status,
                            'message'   => $response->data->message,
                            'payid'  => $response->data->apitxnid,
                            'txnid'  => $response->data->merchantTransactionId,
                            'apitxnid' => $apitxnid,
                            "bankutr"   => "failed",
                        ]);
                    } else {
                        // $report = Report::where('id', $report->id)->first();
                        $report = Payoutreport::where('id', $report->id)->first();
                        if(isset($response->code) == '0' && isset($response->data->txn_status) == '1' ){
                            $status = 'sucess';
                        }else{
                            $status = 'pending';
                        }
                        if($report){
                            $report->update([
                                'status' => $response->msg,
                                'refno'  => $response->data->bankref,
                                'payid'  => $response->data->apitxnid,
                                'txnid'  => $response->data->merchantTransactionId,
                                'apitxnid' => $apitxnid,
                            ]);
                        }
    
                        try {
                            \App\Helpers\Permission::commission($report->id, 'payout');
                        } catch (\Exception $e) {
                        }
                        return response()->json([
                            'statuscode' => $response->data->txn_status,
                            'message'   => $response->msg,
                            'payid'  => $response->data->apitxnid,
                            'txnid'  => $response->data->merchantTransactionId,
                            'apitxnid' => $apitxnid,
                            "bankutr"   => $response->data->bankref,
                        ]);
                    }
                }else{
                    return response()->json([
                        'statuscode' => 'Something went wrong'
                    ]);
                }
                break;
                
            case 'hp-out':
                $payUrl = "https://login.honestpay.in/";
                
                //generate randomaly
                $apitxnid = $post->apitxnid;
                $transferID = $this->getTransferID();
                
                $data = [
                    "token"     => 'KIBztE9f3s7CB49I9v0cGejfwPO9bL',
                    "mobile"    => $post->mobile,
                    "account"   => $post->account,
                    "bank"      => $post->bank,
                    "mode"      => "IMPS/NEFT",
                    "ifsc"      => strtoupper($post->ifsc),
                    "name"      => $post->name,
                    "amount"    => $post->amount,
                    "apitxnid"  => $post->apitxnid,
                    "callback"  => "https://dashboard.nanakpay.com/callback/payout",
                ];
                
                $PAYMENT_API_URL = $payUrl."v/payout/order/create";

                $header = array(
                    "Content-Type: application/json"
                );
                $result = \App\Helpers\Permission::curl($PAYMENT_API_URL, 'POST', json_encode($data), $header, "yes", "Qr", $post->txnid);
                if($result['response']){
                    //$response = $result['response'];
                    $response = json_decode($result['response']);
                    //print_r($response);
                    if(isset($response->statuscode) && $response->statuscode == 'TXN'){
                        Log::info("PO Success API Response", ['response' => $response]);
                        Payoutreport::where('id', $report->id)->update([
                            'status' => 'pending', 
                            'refno'  => $response->bankutr,
                            'payid'  => $response->apitxnid,
                            'txnid'  => $report->txnid,
                            'remark' => $response->message
                        ]);
                        return response()->json([
                            'statuscode'    => $response->statuscode,
                            'message'       => $response->message,
                            'merchant_txnid'=> $response->txnid,
                            'txnid'         => $response->apitxnid,
                            'bank_rrn'      => $response->bankutr
                        ]);
                    }
                    elseif(isset($response->statuscode) && $response->statuscode == 'TXF') {
                        Log::info("PO Failed API Response", ['response' => $response]);
                        Payoutreport::where('id', $report->id)->update([
                            'status' => 'failed', 
                            'refno'  => 'NULL',
                            'payid'  => $response->txnid,
                            'txnid'  => $report->txnid,
                            'remark' => $response->message
                        ]);
                        return response()->json([
                            'statuscode'    => $response->statuscode,
                            'message'       => $response->message,
                            'merchant_txnid'=> $response->txnid,
                            'txnid'         => $response->apitxnid,
                            'bank_rrn'      => $response->bankutr
                        ]);
                    }
                    else {
                        Log::info("PO Others API Response", ['response' => $response]);
                        Payoutreport::where('id', $report->id)->update([
                            'status' => 'failed', 
                            'refno'  => 'NULL',
                            'payid'  => $response->txnid,
                            'txnid'  => $report->txnid,
                            'remark' => $response->message
                        ]);
                        return response()->json([
                            'statuscode'    => $response->statuscode,
                            'message'       => $response->message,
                            'merchant_txnid'=> $report->txnid,
                            'txnid'         => $response->txnid,
                            'bank_rrn'      => $response->bankutr
                        ]);
                    }
                }
                else {
                    return response()->json([
                        'statuscode'    => 'ERR',
                        'message'       => 'Bank Server Busy at the moment, Try Again Later!',
                        'merchant_txnid'=> $report->txnid,
                    ]);
                }
                break;
            
            default:
                return response()->json([
                    'statuscode' => 'Something went wrong'
                ]);
                break;
        }
    }

    public function transaction(Request $post)
    {
        $user = User::where('id', $post->user_id)->first();
        switch ($post->type) {
            case 'bulkpayout':
                $post["name"] = $post->file("import")->getClientOriginalName();
                $rules = array(
                    'import' => 'required',
                    'transactionType' => "required",
                    'name'   => 'required'
                );

                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        $error = $value[0];
                    }
                    return response()->json(['status'=>'ERR', 'message'=> $error]);
                }

                switch ($post->transactionType) {
                    case 'check':
                        $import = new \App\Imports\Bulkpayout("check", $post->file("import")->getClientOriginalName());
                        $check  = \Excel::import($import, $post->file("import"));

                        if($import->getAmount() + $import->getCharge() > $this->getAccBalance($user->id, "payoutwallet")){
                            return response()->json(['status' => "ERR" , "message" => "Low wallet balance to make this request"]);
                        }

                        return response()->json(['status' => "PRE" , "message" => "Confirmation", "data" => $import->getData(), "amount" => $import->getAmount(), "charge" => $import->getCharge(), "count" => $import->getCount()]);
                        break;
                    
                    default:
                        $import = new \App\Imports\Bulkpayout("check", $post->file("import")->getClientOriginalName());
                        $check  = \Excel::import($import, $post->file("import"));

                        if($import->getAmount() + $import->getCharge() > $this->getAccBalance($user->id, "payoutwallet")){
                            return response()->json(['status' => "ERR" , "message" => "Low wallet balance to make this request"]);
                        }

                        $upload = new \App\Imports\Bulkpayout("upload", $post->file("import")->getClientOriginalName());
                        $action = \Excel::import($upload, $post->file("import"), $post->file("import")->getClientOriginalName());
                        if($action){
                            return response()->json(['status' => "TXN" , "message" => "Transaction Successfull"]);
                        }else{
                            return response()->json(['status' => "ERR" , "message" => "Transaction Failed"]);
                        }
                        break;
                }
                break;
        }
    }

    public function query(Request $post)
    {
        $rules = array(
            'apitxnid' => 'required'
        );
        
        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }
        
        $report = Payoutreport::where("apitxnid", $post->apitxnid)->first();
        if(!$report){
            return response()->json(['statuscode' => "TNF", 'message' => "Transaction Not Found"]);
        }
        
        switch($report->provider->api->code){
            case "payout5":
                $api = Api::find($report->provider->api_id);
                $header = array(
                    "partnerId: ".$api->username,
                    "consumersecret: ".$api->password,
                    "consumerkey: ".$api->optional1
                );
                
                $result = \Myhelper::curl($api->url."/payout/getDataByQuintusId?quintus_transaction_id=".$report->payid, "GET", "", $header, "no", 'Payout', $report->txnid);
                
                //dd($result);
                if($result['response'] == ""){
                    return response()->json([
                        'statuscode'=> 'TXN', 
                        'message'   => "Transaction Successfull",
                        "txn_status"=> $report->status,
                        "apitxnid"  => $report->apitxnid, 
                        "txnid"     => $report->txnid, 
                        "bankutr"   => $report->refno, 
                    ]);
                }
                
                $response = json_decode($result['response']);
                if(isset($response->data->status) && $response->data->status == "success"){
                    Payoutreport::where('id', $report->id)->update([
                        'status' => "success", 
                        'refno'  => isset($response->data->remark) ? $response->data->remark : "success"
                    ]);
                }elseif(isset($response->data->status) && in_array($response->data->status, ["failed", "refund"])){
                    Payoutreport::where('id', $report->id)->update([
                        'status' => "reversed", 
                        'refno'  => isset($response->data->remark) ? $response->data->remark : "failed"
                    ]);
                    
                    \Myhelper::transactionRefund($report->id, "payoutreports", "payoutwallet");
                }
                
                $report = Payoutreport::where("apitxnid", $post->apitxnid)->first();
                
                return response()->json([
                    'statuscode'=> 'TXN', 
                    'message'   => "Transaction Successfull",
                    "txn_status"=> $report->status,
                    "apitxnid"  => $report->apitxnid, 
                    "txnid"     => $report->txnid, 
                    "bankutr"   => $report->refno, 
                ]);
                break;
                
            default:
                return response()->json([
                    'statuscode'=> 'TXN', 
                    'message'   => "Transaction Successfull",
                    "txn_status"=> $report->status,
                    "apitxnid"  => $report->apitxnid, 
                    "txnid"     => $report->txnid, 
                    "bankutr"   => $report->refno, 
                ]);
                break;
        }
    }

    public function settlement(Request $post)
    {
        $count = \App\Model\PortalSetting::where('code', 'settlementcount')->first();
        $completecount = \App\Model\PortalSetting::where('code', 'settlementcountcomplete')->first();

        if($completecount->value < $count->value){
            $users = \DB::table("users")->where("role_id", "!=", "1")->where("mainwallet", ">" , 1000)->get(["id", "name", "mobile", "mainwallet", "payoutwallet"]);

            $provider = Provider::where('recharge1', 'fund')->first();
            foreach ($users as $user) {
                $payoutwallet = $user->mainwallet;
                $txnid = "WT".date("ymdhis");

                $debit = [
                    'number'  => $user->mobile,
                    'mobile'  => $user->mobile,
                    'provider_id' => $provider->id,
                    'api_id'  => $provider->api_id,
                    'amount'  => $user->mainwallet,
                    'txnid'   => $txnid,
                    'description' => $user->name,
                    'option1' => "wallet",
                    'status'  => "success",
                    'user_id' => $user->id,
                    'credit_by' => $user->id,
                    'rtype'   => 'main',
                    'balance' => $user->mainwallet,
                    'trans_type' => 'debit',
                    'product'    => "payout",
                    'create_time'=> $user->id.Carbon::now()->toDateTimeString()
                ];

                $payoutcredit = [
                    'number'  => $user->mobile,
                    'mobile'  => $user->mobile,
                    'provider_id' => $provider->id,
                    'api_id'  => $provider->api_id,
                    'amount'  => $payoutwallet,
                    'txnid'   => $txnid,
                    'description' => $user->name,
                    'option1' => "wallet",
                    'status'  => "success",
                    'user_id' => $user->id,
                    'credit_by' => $user->id,
                    'rtype'   => 'main',
                    'balance' => $user->payoutwallet,
                    'trans_type' => 'credit',
                    'product'    => "payout",
                    'create_time'=> $user->id.Carbon::now()->toDateTimeString()
                ];

                try {
                    $report = \DB::transaction(function () use($debit, $payoutcredit, $securecredit) {
                        User::where('id', $debit['user_id'])->decrement("mainwallet", $debit['amount']);
                        Report::create($debit);

                        User::where('id', $payoutcredit['user_id'])->increment("payoutwallet", $payoutcredit['amount']);
                        Payoutreport::create($payoutcredit);
                        return true;
                    });
                } catch (\Exception $e) {

                }
            }

            \App\Model\PortalSetting::where('code', 'settlementcountcomplete')->increment("value", 1);
        }
    }

    // generate Transfer ID
    public function getTransferID($length=10)
    {
         // Define the character set (alphanumeric)
         $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
         $charactersLength = strlen($characters);
         $transferId = '';
 
         // Generate a random string
         for ($i = 0; $i < $length; $i++) {
             $transferId .= $characters[rand(0, $charactersLength - 1)];
         }
 
         return $transferId;
    }
    // generate Transaction ID
    public function getTransactionID($length=16){
        {
            // Define the character set (alphanumeric)
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $charactersLength = strlen($characters);
            $transferId = '';
    
            // Generate a random string
            for ($i = 0; $i < $length; $i++) {
                $transferId .= $characters[rand(0, $charactersLength - 1)];
            }
    
            return $transferId;
       }
    }
    
    public function hpayout(Request $post)
    {
        $data = json_decode(json_encode($post->all()), true);
        \DB::table('microlog')->insert(['product'=>'HPPAYOUT','response'=>json_encode($post->all()), "created_at" => date("Y-m-d H:i:s")]);
        //Log::info("PO Webhook Received", ['response' => $post]);
        $report = Payoutreport::where('apitxnid', $data["apitxn_id"])->first();
        Log::info("PO Webhook Received", ['response' => $data["apitxn_id"]]);
        if($report){
            if(isset($data["status"]) && strtolower($data["status"]) == "success") {
                $update['status'] = 'success';
                $update['refno']  = isset($data["utr"]) ? $data["utr"] : "NA";
            }
            elseif(isset($data["status"]) && strtolower($data["status"]) == "refunded") {
                $update['status'] = 'failed';
                $update['refno']  = isset($data["utr"]) ? $data["utr"] : "NA";
            }
            else {
                $update['status'] = $data["status"];
                $update['refno']  = isset($data["utr"]) ? $data["utr"] : "NA";
            }
            $report->update([
                'status' => $update['status'],
                'refno'  => $update['refno'],
                'txnid'  => $data["transaction_id"]
            ]);
            $this->sendCallback($data["apitxn_id"], $data["transaction_id"], $data["utr"], $report->amount, $report->user_id, $report->product, $data["status"]);
            
            return response()->json(["status" => true, "message" => "ok"]);
        }else{
            return response()->json(["status" => false, "message" => "Not Matched"]);
        }
    }
    
    public function sendCallback($apitxnId,$transactionId, $utr, $amt, $user_id, $product, $status, $method = 'GET')
    {
        $user = User::where('id', $user_id)->first();
        $callbackUrlBase = $user->payout_callback;
    
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
