<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Model\Aepsfundrequest;
use App\Model\Fundreport;
use App\Model\Fundbank;
use App\Model\Paymode;
use App\Model\PortalSetting;
use App\Model\Provider;
use App\Model\Payoutreport;
use App\Model\Aepsreport;
use App\Model\Microatmfundrequest;
use App\Model\Microatmreport;
use App\Model\Upiload;
use App\Model\Report;
use App\Model\Api;
use App\Model\Apitoken;
use Carbon\Carbon;

class FundController extends Controller
{
    public $fundapi, $admin;

    public function __construct()
    {
        $this->fundapi = Api::where('code', 'fund')->first();
        $this->admin = User::whereHas('role', function ($q){
            $q->where('slug', 'admin');
        })->first();
    }
    
    public function transaction(Request $request)
    {
        $rules = array(
            'apptoken' => 'required',
            'type'     => 'required',
            'user_id'  => 'required|numeric',
            'mode'     => 'sometimes|required',
        );

        $validate = \Myhelper::FormValidator($rules, $request);
        if($validate != "no"){
            return $validate;
        }
        $provide = Provider::where('recharge1', 'fund')->first();
        $user = User::where('id', $request->user_id)->first();

        if(!$user){
            $output['statuscode'] = "ERR";
            $output['message'] = "User details not matched";
            return response()->json($output);
        }

        switch ($request->type) {            
            case "bank":
                if($provide->status == 0){
                    return response()->json(['statuscode' => "ERR", "message" => "Operator Currently Down."]);
                }
                $rules = array(
                    'bank'     => 'required',
                    'account'  => 'required',
                    'ifsc'     => 'required',
                    'amount'   => 'required|numeric|min:10|max:200000'
                );
                
                $validate = \Myhelper::FormValidator($rules, $request);
                if($validate != "no"){
                    return $validate;
                }

                if($user->account == '' || $user->account == null){
                    User::where('id', $user->id)->update([
                        'account' => $request->account,
                        'bank' => $request->bank, 
                        'ifsc' => $request->ifsc
                    ]);
                }else{
                    $request['account'] = $user->account;
                    $request['bank'] = $user->bank;
                    $request['ifsc'] = $user->ifsc;
                }

                $request['mode'] = "IMPS";

                if($request->mode == "IMPS"){
                    if($request->amount > 0 && $request->amount <= 100){
                        $provider = Provider::where('recharge1', 'payout1')->first();
                    }elseif($request->amount > 100 && $request->amount <= 500){
                        $provider = Provider::where('recharge1', 'payout2')->first();
                    }elseif($request->amount > 500 && $request->amount <= 1000){
                        $provider = Provider::where('recharge1', 'payout3')->first();
                    }elseif($request->amount > 1000 && $request->amount <= 5000){
                        $provider = Provider::where('recharge1', 'payout4')->first();
                    }elseif($request->amount > 5000 && $request->amount <= 10000){
                        $provider = Provider::where('recharge1', 'payout5')->first();
                    }elseif($request->amount > 10000 && $request->amount <= 15000){
                        $provider = Provider::where('recharge1', 'payout6')->first();
                    }elseif($request->amount > 15000 && $request->amount <= 20000){
                        $provider = Provider::where('recharge1', 'payout7')->first();
                    }elseif($request->amount > 20000 && $request->amount <= 25000){
                        $provider = Provider::where('recharge1', 'payout8')->first();
                    }elseif($request->amount > 25000 && $request->amount <= 50000){
                        $provider = Provider::where('recharge1', 'payout9')->first();
                    }elseif($request->amount > 50000 && $request->amount <= 100000){
                        $provider = Provider::where('recharge1', 'payout10')->first();
                    }elseif($request->amount > 100000 && $request->amount <= 150000){
                        $provider = Provider::where('recharge1', 'payout11')->first();
                    }elseif($request->amount > 150000 && $request->amount <= 200000){
                        $provider = Provider::where('recharge1', 'payout12')->first();
                    }
                }elseif($request->mode == "NEFT"){
                    $provider = Provider::where('recharge1', 'payoutneft')->first();
                }

                if(!$provider){
                    return response()->json(['statuscode' => "ERR", "message" => "Operator Not Found"]);
                }
                
                $request['charge'] = \Myhelper::getCommission($request->amount, $user->scheme_id, $provider->id, $user->role->slug);
                $request['gst'] = ($provider->api->gst * $request->charge)/100;

                $serviceApi = \DB::table("service_managers")->where("provider_id", $provider->id)->where("user_id", $user->id)->first();

                if($serviceApi){
                    $api = Api::find($serviceApi->api_id);
                }else{
                    $api = Api::find($provider->api_id);
                }

                if($this->getAccBalance($user->id, "payoutwallet") < $request->amount + $request->charge){
                    return response()->json(['statuscode' => "ERR", "message" => "Low balance to make this request."]);
                }

                do {
                    $request['txnid'] = $this->transcode().'PO'.rand(111111111111, 999999999999);
                } while (Report::where("txnid", "=", $request->txnid)->first() instanceof Report);

                $debit = [
                    'number'  => $request->account,
                    'mobile'  => $user->mobile,
                    'provider_id' => $provider->id,
                    'api_id'  => $api->id,
                    'amount'  => $request->amount,
                    'charge'  => $request->charge,
                    'gst'     => $request->gst,
                    'txnid'   => $request->txnid,
                    'apitxnid'=> $request->txnid,
                    'description'  => $user->name,
                    'option1' => "bank",
                    'option2' => $request->ifsc,
                    'option3' => $request->bank,
                    'status'  => "pending",
                    'user_id' => $user->id,
                    'credit_by' => $user->id,
                    'rtype'   => 'main',
                    'via'     => $request->via,
                    'balance' => $user->payoutwallet,
                    'trans_type' => 'debit',
                    'product'    => "bankpayout",
                    'create_time'=> $user->id.Carbon::now()->toDateTimeString()
                ];

                try {
                    $report = \DB::transaction(function () use($debit, $request) {
                        $report = Payoutreport::create($debit);
                        User::where('id', $debit['user_id'])->decrement("payoutwallet", $request->amount + ($request->charge + $request->gst));
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
                    case 'payout3':
                        $utr = "300".date("Hdmi");
                        Payoutreport::where('id', $report->id)->update([
                            'status' => "success", 
                            'refno'  => $utr
                        ]);

                        return response()->json([
                            'statuscode'=> 'TXN', 
                            'message'   => "Transaction Successfull",
                            "apitxnid"  => $request->txnid, 
                            "txnid"     => $request->txnid, 
                            "bankutr"   => $utr, 
                        ]);
                        break;
                    
                    case 'payout2':
                        $header = array(
                            "Authorization: Basic ".base64_encode($api->username.":".$api->password),
                            "Content-Type: application/json"
                        );

                        $parameters = [
                            "customername" => $user->name,
                            "customerMob"  => $user->mobile,
                            "amount"       => (float)$request->amount,
                            "bankaccount"  => $request->account,
                            "bankifsccode" => $request->ifsc,
                            "transactionType" => $request->mode,
                            "emailid" => $user->email
                        ];

                        $result = \Myhelper::curl($api->url."FundDirectPay", "POST", json_encode($parameters), $header, "yes", 'Payout', $request->txnid);
                        if($result['response'] == ""){
                            return response()->json([
                                'statuscode'=> 'TUP', 
                                'message'   => 'Transaction Under Process',
                                "apitxnid"  => $request->txnid, 
                                "txnid"     => $request->txnid, 
                                "bankutr"   => $request->txnid, 
                            ]);
                        }

                        $response = json_decode($result['response']);
                        if(isset($response->responseCode) && $response->responseCode == "02"){
                            Payoutreport::where('id', $report->id)->update([
                                'status' => "failed", 
                                'refno'  => isset($response->endToEndIdentification) ? $response->endToEndIdentification : $response->status,
                                'remark' => $response->status
                            ]);

                            User::where('id', $user->id)->increment('payoutwallet', $request->amount + ($request->charge + $request->gst));
                            return response()->json([
                                'statuscode'=> 'TXF', 
                                'message'   => $response->status,
                                "apitxnid"  => $request->txnid, 
                                "txnid"     => $request->txnid,
                                "bankutr"   => isset($response->endToEndIdentification) ? $response->endToEndIdentification : $response->status, 
                            ]);
                        }elseif(isset($response->status) && $response->status == "400"){
                            Payoutreport::where('id', $report->id)->update([
                                'status' => "failed", 
                                'refno'  => isset($response->title) ? $response->title : $response->status,
                                'remark' => $response->title
                            ]);

                            User::where('id', $user->id)->increment('payoutwallet', $request->amount + ($request->charge + $request->gst));
                            return response()->json([
                                'statuscode'=> 'TXF', 
                                'message'   => $response->title,
                                "apitxnid"  => $request->txnid, 
                                "txnid"     => $request->txnid,
                                "bankutr"   => isset($response->title) ? $response->title : $response->status, 
                            ]);
                        }elseif(isset($response->responseCode) && in_array($response->responseCode, ["00"])){
                            Payoutreport::where('id', $report->id)->update([
                                'status' => "success", 
                                'refno'  => $response->transaction_refid,
                                'remark' => $response->status
                            ]);

                            return response()->json([
                                'statuscode'=> 'TXN', 
                                'message'   => "Transaction Successfull",
                                "apitxnid"  => $request->txnid, 
                                "txnid"     => $request->txnid, 
                                "bankutr"   => $response->transaction_refid, 
                            ]);
                        }else{
                            Payoutreport::where('id', $report->id)->update([
                                'status' => "pending", 
                                'refno'  => isset($response->transaction_refid) ? $response->transaction_refid : "",
                                'remark' => isset($response->status) ? $response->status : ""
                            ]);

                            return response()->json([
                                'statuscode'=> 'TUP', 
                                'message'   => 'Transaction Under Process',
                                "apitxnid"  => $request->txnid, 
                                "txnid"     => $request->txnid, 
                                "bankutr"   => isset($response->transaction_refid) ? $response->transaction_refid : "pending", 
                            ]);
                        }
                        break;
                    
                    default:
                        $header = array(
                            "Content-Type: application/json"
                        );     
                        
                        $parameters = [
                            "apitxnid" => $request->txnid,
                            "account"  => $request->account,
                            "ifsc"     => $request->ifsc,
                            "token"    => $api->username,
                            "name"     => $user->name,
                            "purpose"  => "Payout",
                            "amount"   => $request->amount,
                            "bank"     => $request->bank
                        ];

                        $result = \Myhelper::curl($api->url, "POST", json_encode($parameters), $header, "yes", 'Payout', $request->txnid);
                        if($result['response'] == ""){
                            return response()->json([
                                'statuscode'=> 'TUP', 
                                'message'   => 'Transaction Under Process',
                                "apitxnid"  => $request->txnid, 
                                "txnid"     => $request->txnid, 
                                "bankutr"   => $request->txnid, 
                            ]);
                        }

                        $response = json_decode($result['response']);
                        
                        if(isset($response->statuscode) && in_array($response->statuscode, ["TXF", 'ERR'])){
                            Payoutreport::where('id', $report->id)->update([
                                'status' => "failed", 
                                'refno'  => isset($response->bankutr) ? $response->bankutr : $response->message,
                                'remark' => $response->message
                            ]);

                            User::where('id', $user->id)->increment('payoutwallet', $request->amount + ($request->charge + $request->gst));
                            return response()->json([
                                'statuscode'=> 'TXF', 
                                'message'   => $response->message,
                                "apitxnid"  => $request->txnid, 
                                "txnid"     => $request->txnid,
                                "bankutr"   => $response->bankutr, 
                            ]);
                        }elseif(isset($response->statuscode) && in_array($response->statuscode, ["TXN"])){
                            Payoutreport::where('id', $report->id)->update([
                                'status' => "success", 
                                'refno'  => $response->bankutr,
                                'remark' => $response->message
                            ]);

                            return response()->json([
                                'statuscode'=> 'TXN', 
                                'message'   => "Transaction Successfull",
                                "apitxnid"  => $request->txnid, 
                                "txnid"     => $request->txnid, 
                                "bankutr"   => $response->bankutr, 
                            ]);
                        }else{
                            Payoutreport::where('id', $report->id)->update([
                                'status' => "pending", 
                                'refno'  => isset($response->bankutr) ? $response->bankutr : "",
                                'remark' => isset($response->message) ? $response->message : ""
                            ]);

                            return response()->json([
                                'statuscode'=> 'TUP', 
                                'message'   => 'Transaction Under Process',
                                "apitxnid"  => $request->txnid, 
                                "txnid"     => $request->txnid, 
                                "bankutr"   => isset($response->bankutr) ? $response->bankutr : "pending", 
                            ]);
                        }
                        break;
                }
                break;

            case "qrcode":

                $qrcodes  = Upiload::where("type" , "portal")->where("user_id", $user->id)->count();
                if($qrcodes > 10){
                    return response()->json(['statuscode' => 'ERR', 'message' => 'Qr code generate limit exceeded']);
                }

                $provider = \App\Model\Provider::where('recharge1', 'payin')->first();
                $request['charge'] = \Myhelper::getCommission(0, $user->scheme_id, $provider->id, $user->role->slug);

                $serviceApi = \DB::table("service_managers")->where("provider_id", $provider->id)->where("user_id", $user->id)->first();

                if($serviceApi){
                    $api = Api::find($serviceApi->api_id);
                }else{
                    $api = Api::find($provider->api_id);
                }

                if($user->payoutwallet < $request->charge){
                    return response()->json(['statuscode' => "ERR", "message" => "Insufficient Wallet Balance"]);
                }
                
                do {
                    $request['txnid'] = rand(1111111111, 9999999999);
                } while (Payoutreport::where("txnid", "=", $request->txnid)->first() instanceof Report);
                
                $debit = [
                    'number'  => $user->mobile,
                    'mobile'  => $user->mobile,
                    'provider_id' => $provider->id,
                    'api_id'  => $api->id,
                    'amount'  => $request->charge,
                    'txnid'   => "QRCHR".$user->id.date("ymdhis"),
                    'apitxnid'=> "QRCHR".$user->id.date("ymdhis"),
                    'option1' => "Qr Charge",
                    'description'  => $user->name,
                    'description'  => $user->name,
                    'status'  => "pending",
                    'user_id' => $user->id,
                    'credit_by' => $user->id,
                    'rtype'   => 'main',
                    'via'     => $request->via,
                    'balance' => $user->payoutwallet,
                    'trans_type' => 'debit',
                    'product'    => "collection",
                    'create_time'=> $user->id.Carbon::now()->toDateTimeString()
                ];

                try {
                    $report = \DB::transaction(function () use($debit, $request) {
                        $report = Payoutreport::create($debit);
                        User::where('id', $debit['user_id'])->decrement("payoutwallet", $request->charge);
                        return $report;
                    });
                } catch (\Exception $e) {
                    $report = false;
                }

                if(!$report){
                    return response()->json(['statuscode' => "ERR", "message" => "Something went wrong, try again"]);
                }

                switch ($api->code) {
                    case 'ecollection2':
                        $url  = "https://apitranxt.paynnow.com/api/Account/Login";
                        $parameter = [
                            "email"    => "opentechnologynoida@gmail.com",
                            "password" => "Opentech@487hd$232"
                        ];
                        
                        $header = array(
                            'Content-Type: application/json'
                        );
                        
                        $result   = \Myhelper::curl($url, 'POST', json_encode($parameter), $header, "yes", "Qr", $request->txnid); 
                        $response = json_decode($result['response']);
                        
                        if(isset($response->accessToken)){
                            $url  = "https://apitranxt.paynnow.com/api/VirtualInfo/CreateVirtualAccount";
                            $parameter = [
                                "mobileNo" => $request->txnid,
                                "panNo"    => $user->pancard,
                                "aadharNo" => $user->aadharcard,
                                "beneficiaryName" => $request->name,
                            ];
                            
                            $header = array(
                                'Content-Type: application/json',
                                'Authorization: Bearer '.$response->accessToken
                            );
                        
                            $result   = \Myhelper::curl($url, 'POST', json_encode($parameter), $header, "yes", "Qr", $request->txnid); 
                            $response = json_decode($result['response']);

                            if(isset($response->data->sCode) && $response->data->sCode == 0){
                                $request['refid'] = $response->data->accountno;
                                $request['upiid'] = $response->data->ifsccode;
                                $request['type']  = "payload";

                                if($request->type == "dynamic"){
                                    $request['upi_string'] = "upi://pay?pn=Merchant&pa=".$request->refid."@yesbankltd&cu=INR&am=".$request->amount;
                                }else{
                                    $request['upi_string'] = "upi://pay?pn=Merchant&pa=".$request->refid."@yesbankltd&cu=INR";
                                }

                                Upiload::create($request->all());
                                return response()->json([
                                    'statuscode' => "TXN", 
                                    "message"    => "Success", 
                                    "upi_tr"     => $response->data->accountno,
                                    "upi_id"     => $response->data->ifsccode,
                                    "upi_string" => $request->upi_string
                                ]);
                            }else{  
                                Payoutreport::where('id', $report->id)->update(["status" => "failed"]);
                                User::where('id', $debit['user_id'])->increment("payoutwallet", $request->charge);
                                return response()->json(['statuscode' => "TXF", "message" => isset($response->message)?$response->message : "Something went wrong"]);
                            }
                        }else{
                            Payoutreport::where('id', $report->id)->update(["status" => "failed"]);
                            User::where('id', $debit['user_id'])->increment("payoutwallet", $request->charge);
                            return response()->json(['statuscode' => "TXF", "message" => isset($response->message)?$response->message : "Something went wrong"]);
                        }
                        break;
                    
                    default:
                        $url  = "https://api.verifyapi.in/api/v1/collection/virtual_account";
                        $parameter = [
                            "mobile_number" => $request->txnid,
                            "email" => $user->email,
                            "name"  => $user->name,
                            "provider_id" => $api->optional1,
                        ];
                        
                        $header = array(
                            'Content-Type: application/json',
                            'Token: 120|QVjIY3qg1FsoQa7QRu6Z56llA0d7HHMO75vYNpUV',
                            'Authorization: Bearer 120|QVjIY3qg1FsoQa7QRu6Z56llA0d7HHMO75vYNpUV'
                        );
                    
                        $result   = \Myhelper::curl($url, 'POST', json_encode($parameter), $header, "yes", "Qr", $request->txnid); 
                        $response = json_decode($result['response']);


                        if(isset($response->status_id) && $response->status_id == "1"){
                            $request['refid'] = $response->virtual_account_number;
                            $request['upiid'] = $response->virtual_upi;
                            $request['type']   = "payload";

                            if($request->type == "dynamic"){
                                $request['upi_string'] = "upi://pay?pn=Merchant&pa=".$request->upiid."&cu=INR&am=".$request->amount;
                            }else{
                                $request['upi_string'] = "upi://pay?pn=Merchant&pa=".$request->upiid."&cu=INR";
                            }

                            Upiload::create($request->all());

                            return response()->json([
                                'statuscode' => "TXN", 
                                "message"    => "Success", 
                                "upi_tr"     => $response->virtual_account_number,
                                "upi_id"     => $response->virtual_upi,
                                "upi_string" => $request->upi_string
                            ]);
                        }else{
                            Payoutreport::where('id', $report->id)->update(["status" => "failed"]);
                            User::where('id', $debit['user_id'])->increment("payoutwallet", $request->charge);
                            return response()->json(['statuscode' => "TXF", "message" => isset($response->message)?$response->message : "Something went wrong"]);
                        }
                        break;
                }
                break;

            case 'transfer':
                if(!\Myhelper::can('fund_transfer', $request->user_id)){
                    return response()->json(["statuscode" => "ERR" , "message" => "Permission not alloweds"]);
                }

                $request['refno'] = preg_replace('/[^A-Za-z0-9]/', '', $request->refno);
                $rules = array(
                    'amount' => 'required|numeric|min:1',
                );
        
                $validator = \Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        $error = $value[0];
                    }
                    return response()->json(['statuscode' => "ERR", "message" => $error]);
                }

                $payee = User::where('id', $request->payee_id)->first();
            
                $balance = "mainwallet";
                $table   = Report::query();
                $product = "fund transfer";

                if($balance == "mainwallet" && $user[$balance] < $request->amount){
                    return response()->json(['statuscode'=>"ERR", 'message' => "Insufficient wallet balance."]);
                }

                $debit = [
                    'number'  => $user->mobile,
                    'mobile'  => $user->mobile,
                    'provider_id' => $provide->id,
                    'api_id'  => $provide->api_id,
                    'amount'  => $request->amount,
                    'txnid'   => "WTR".date('Ymdhis'),
                    'remark'  => $request->remark,
                    'refno'   => $request->refno,
                    'status'  => 'success',
                    'user_id' => $user->id,
                    'credit_by' => $payee->id,
                    'rtype'   => 'main',
                    'via'     => $request->via,
                    'balance' => $user[$balance],
                    'trans_type' => 'debit',
                    'product' => $product
                ];

                if(in_array($request->wallet, ["aepswallet", "matmwallet"])){
                    $debit['option5'] = "fund";
                    $debit['option1'] = "wallet";
                }

                $credit = [
                    'number' => $payee->mobile,
                    'mobile' => $payee->mobile,
                    'provider_id' => $provide->id,
                    'api_id' => $provide->api_id,
                    'amount' => $request->amount,
                    'txnid'  => "WTR".date('Ymdhis'),
                    'remark' => $request->remark,
                    'refno'  => $request->refno,
                    'status' => 'success',
                    'user_id'   => $payee->id,
                    'credit_by' => $user->id,
                    'rtype' => 'main',
                    'via'   => $request->via,
                    'balance'    => $payee[$balance],
                    'trans_type' => 'credit',
                    'product'    => $product
                ];

                $request = \DB::transaction(function () use($debit, $credit, $balance, $table) {
                    if($balance == "mainwallet"){
                        $debitReport = $table->create($debit);
                        User::where('id', $debit['user_id'])->decrement($balance, $debit['amount']);
                    }

                    $creditReport = $table->create($credit);
                    User::where('id', $credit['user_id'])->increment($balance, $credit['amount']);
                    return true;
                });

                if($request){
                    return response()->json(['statuscode'=>"TXN", 'message' => "Fund Transfer successfully"]);
                }else{
                    return response()->json(['statuscode'=>"ERR", 'message' => "Something went wrong."]);
                }
                break;

            case 'return':
                if($request->type == "return" && !\Myhelper::can('fund_return', $request->user_id)){
                    return response()->json(["statuscode" => "ERR" , "message" => "Permission not alloweds"]);
                }

                $request['refno'] = preg_replace('/[^A-Za-z0-9]/', '', $request->refno);
                $rules = array(
                    'amount' => 'required|numeric|min:1'
                );
        
                $validator = \Validator::make($request->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        $error = $value[0];
                    }
                    return response()->json(['statuscode' => "ERR", "message" => $error]);
                }

                $product = "fund return";
                $balance = "mainwallet";
                $table   = Report::query();

                $payee   = User::where('id', $request->payee_id)->first();

                if($balance == "mainwallet" && $payee[$balance] < $request->amount){
                    return response()->json(['statuscode' => "ERR", "message" => "Insufficient wallet balance"]);
                }

                $debit = [
                    'number' => $payee->mobile,
                    'mobile' => $payee->mobile,
                    'provider_id' => $request->provider_id,
                    'api_id' => $provide->api_id,
                    'amount' => $request->amount,
                    'remark' => $request->remark,
                    'refno'  => $request->refno,
                    'status' => 'success',
                    'user_id'=> $payee->id,
                    'credit_by' => $user->id,
                    'rtype'   => 'main',
                    'via'     => 'portal',
                    'balance' => $payee[$balance],
                    'trans_type'  => 'debit',
                    'product' => $product
                ];

                $credit = [
                    'number'  => $user->mobile,
                    'mobile'  => $user->mobile,
                    'provider_id' => $request->provider_id,
                    'api_id'  => $provide->api_id,
                    'amount'  => $request->amount,
                    'remark'  => $request->remark,
                    'refno'  => $request->refno,
                    'status'  => 'success',
                    'user_id' => $user->id,
                    'credit_by' => $payee->id,
                    'rtype'   => 'main',
                    'via'     => $request->via,
                    'balance' => $user->mainwallet,
                    'trans_type' => 'credit',
                    'product' => $product,
                ];

                $request = \DB::transaction(function () use($debit, $credit, $balance, $table) {
                    $debitReport = $table->create($debit);
                    User::where('id', $debit['user_id'])->decrement($balance, $debit['amount']);

                    if($balance == "mainwallet"){
                        $creditReport = $table->create($credit);
                        User::where('id', $credit['user_id'])->increment($balance, $credit['amount']);
                    }
                    return true;
                });

                if($request){
                    return response()->json(['statuscode'=>"TXN", 'message' => "Fund Return successfully"]);
                }else{
                    return response()->json(['statuscode'=>"ERR", 'message' => "Something went wrong."]);
                }
                break;

            case 'request':
                if(!\Myhelper::can('fund_request', $request->user_id)){
                    return response()->json(['statuscode' => "ERR", "message" => "Permission not allowed"]);
                }

                $rules = array(
                    'fundbank_id' => 'required|numeric',
                    'paymode'     => 'required',
                    'amount'      => 'required|numeric|min:100',
                    'ref_no'      => 'required|unique:fundreports,ref_no',
                    'paydate'     => 'required',
                    'apptoken'    => 'required'
                );
        
                $validate = \Myhelper::FormValidator($rules, $request);
                if($validate != "no"){
                    return $validate;
                }

                $request['user_id'] = $user->id;
                $request['credited_by'] = $user->parent_id;
                if(!\Myhelper::can('setup_bank', $user->parent_id)){
                    $admin = User::whereHas('role', function ($q){
                        $q->where('slug', 'whitelable');
                    })->where('company_id', $user->company_id)->first(['id']);

                    if($admin && \Myhelper::can('setup_bank', $admin->id)){
                        $request['credited_by'] = $admin->id;
                    }else{
                        $admin = User::whereHas('role', function ($q){
                            $q->where('slug', 'admin');
                        })->first(['id']);
                        $request['credited_by'] = $admin->id;
                    }
                }
                $request['status'] = "pending";
                $action = Fundreport::create($request->all());
                if($action){
                    return response()->json(['statuscode' => "TXN", "message" => "Fund request send successfully", "txnid" => $action->id]);
                }else{
                    return response()->json(['statuscode' => "ERR", "message" => "Something went wrong, please try again."]);
                }
                break;

            case 'getfundbank':
                $data['banks'] = Fundbank::where('user_id', $user->parent_id)->where('status', '1')->get();
                if(!\Myhelper::can('setup_bank', $user->parent_id)){
                    $admin = User::whereHas('role', function ($q){
                        $q->where('slug', 'whitelable');
                    })->where('company_id', $user->company_id)->first(['id']);

                    if($admin && \Myhelper::can('setup_bank', $admin->id)){
                        $data['banks'] = Fundbank::where('user_id', $admin->id)->where('status', '1')->get();
                    }else{
                        $admin = User::whereHas('role', function ($q){
                            $q->where('slug', 'admin');
                        })->first(['id']);
                        $data['banks'] = Fundbank::where('user_id', $admin->id)->where('status', '1')->get();
                    }
                }
                $data['paymodes'] = Paymode::where('status', '1')->get();
                return response()->json(['statuscode' => "TXN", "message" => "Get successfully", "data" => $data]);
                break;

            case 'addtoken':
                $rules = array(
                    'ip'  => 'required'
                );
        
                $validate = \Myhelper::FormValidator($rules, $request);
                if($validate != "no"){
                    return $validate;
                }

                do {
                    $request['token'] = str_random(30);
                } while (Apitoken::where("token", "=", $request->token)->first() instanceof Apitoken);
                $action = Apitoken::updateOrCreate(['id'=> $request->id], $request->all());
                if ($action) {
                    return response()->json(['statuscode' => "TXN", "message" => "Api Token generated successfully"]);
                }else{
                    return response()->json(['statuscode' => "ERR", "message" => "Task Failed, please try again"]);
                }
                break;

            case 'deletetoken':
                $rules = array(
                    'token'  => 'required'
                );
        
                $validate = \Myhelper::FormValidator($rules, $request);
                if($validate != "no"){
                    return $validate;
                }

                $delete = Apitoken::where('token', $request->token)->where('user_id', $request->user_id)->delete();
                if ($delete) {
                    return response()->json(['statuscode' => "TXN", "message" => "Api Token deleted successfully"]);
                }else{
                    return response()->json(['statuscode' => "ERR", "message" => "Task Failed, please try again"]);
                }
                break;

            case 'getcommission':
                $product = [
                    'collection' => ['collection'], 
                    'payout'     => ['payout']
                ];

                $keys = [];

                foreach ($product as $key => $value) {
                    $commission = \App\Model\Commission::where('scheme_id', $user->scheme_id)->whereHas('provider', function ($q) use($value){
                        $q->whereIn('type' , $value);
                    })->get();
                    $mydata1 = [];
                    foreach ($commission as $commissions){
                        $mydata["value"] = $commissions[$user->role->slug];
                        $mydata["name"]  = $commissions->provider->name;
                        $mydata["type"]  = $commissions->type;
                        
                        $mydata1[] = $mydata;
                    }
                    
                    $data[] = $mydata1;
                    $keys[] = $key;
                }
                return response()->json(['statuscode' => "TXN", "key" => $keys, "role" => $user->role->slug,"data" => $data]);
                break;

            default :
                return response()->json(['statuscode' => "ERR", 'message' => "Bad Parameter Request"]);
            break;
        }
    }
}
