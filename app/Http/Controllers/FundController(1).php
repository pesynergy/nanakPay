<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Model\Fundreport;
use App\Model\Report;
use App\Model\Payoutreport;
use App\Model\Paymode;
use App\Model\Api;
use App\Model\Apitoken;
use App\Model\Provider;
use App\Model\Fundbank;
use App\Model\Upiload;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FundController extends Controller
{
    public $fundapi, $admin, $openssl_cipher_name;

    public function __construct()
    {
        $this->fundapi = Api::where('code', 'fund')->first();
        $this->admin = User::whereHas('role', function ($q){
            $q->where('slug', 'admin');
        })->first();
        
        $this->openssl_cipher_name = 'aes-256-cbc';
    }

    public function index(Request $post, $type, $action="none")
    {
        $data["type"] = $type;
        $user = \Auth::user();
        switch ($type) {
            case 'tr':
                $permission = ['fund_transfer', 'fund_return'];
                break;
            
            case 'request':
                $permission = 'fund_request';
                break;

            case 'upiload':
            case 'payinload':
            case 'staticqr':
                $data["qrcodes"] = Upiload::where("type" , "static")->where("user_id", \Auth::id())->get();
                $permission = 'qr_request';
                break;

            case 'payout':
                $permission = 'payout_request';
                break;
            
            case 'requestview':
            case 'payoutview':
                $permission = 'fund_requestview';
                break;
            
            case 'statement':
            case 'requestviewall':
            case 'allfund':
                $permission = 'fund_report';
                break;

            default:
                abort(404);
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            abort(403);
        }

        if (isset($role) && !\Myhelper::hasRole($role)) {
            abort(403);
        }

        if ($this->fundapi->status === "0") {
            abort(503);
        }

        switch ($type) {
            case 'request':
                $data['banks'] = Fundbank::where('user_id', \Auth::user()->parent_id)->where('status', '1')->get();
                if(!\Myhelper::can('setup_bank', \Auth::user()->parent_id)){
                    $admin = User::whereHas('role', function ($q){
                        $q->where('slug', 'whitelable');
                    })->where('company_id', \Auth::user()->company_id)->first(['id']);

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
                break;
        }
        return view('fund.'.$type)->with($data);
    }

    public function transaction(Request $post)
    {
        if ($this->fundapi->status == "0") {
            return response()->json(['status' => "This function is down."],400);
        }
        $provide = Provider::where('recharge1', 'fund')->first();
        $post['provider_id'] = $provide->id;

        switch ($post->type) {
            case 'transfer':
                if(!\Myhelper::can('fund_transfer')){
                    return response()->json(["status" => "ERR" , "message" => "Permission not allowed"]);
                }

                $post['refno'] = preg_replace('/[^A-Za-z0-9]/', '', $post->refno);
                $rules = array(
                    'amount' => 'required|numeric|min:1',
                    'refno'  => 'required|unique:reports,refno',
                );
        
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        $error = $value[0];
                    }
                    return response()->json(['status' => "ERR", "message" => $error]);
                }

                if($post->wallet == "payoutwallet"){
                    if(!\Myhelper::hasRole('admin')){
                        return response()->json(["status" => "ERR" , "message" => "Permission not allowed"]);
                    }

                    $payee    = User::where('id', $post->payee_id)->first();
                    $user     = User::where('id', $post->payee_id)->first();
                    $c_wallet = "payoutwallet";
                    $c_table  = Payoutreport::query();

                    $d_wallet = "mainwallet";
                    $d_table  = Report::query();
                }elseif($post->wallet == "directpayoutwallet"){
                    $payee    = \Auth::user();
                    $user     = User::where('id', $post->payee_id)->first();
                    $c_wallet = "payoutwallet";
                    $c_table  = Payoutreport::query();

                    $d_wallet = "payoutwallet";
                    $d_table  = Payoutreport::query();
                }else{
                    
                    $payee    = \Auth::user();
                    $user     = User::where('id', $post->payee_id)->first();
                    $c_wallet = "mainwallet";
                    $d_wallet = "mainwallet";
                    $c_table  = Report::query();
                    $d_table  = Report::query();
                }

                $product = "fund transfer";

                if($this->getAccBalance($payee->id, $d_wallet) < $post->amount){
                    return response()->json(['status'=>"ERR", 'message' => "Insufficient wallet balance."]);
                }

                $debit = [
                    'number'  => $payee->mobile,
                    'mobile'  => $payee->mobile,
                    'provider_id' => $post->provider_id,
                    'api_id'  => $provide->api_id,
                    'amount'  => $post->amount,
                    'txnid'   => "WTR".date('Ymdhis'),
                    'remark'  => $post->remark,
                    'refno'   => $post->refno,
                    'status'  => 'success',
                    'user_id' => $payee->id,
                    'credit_by' => $user->id,
                    'rtype'   => 'main',
                    'via'     => $post->via,
                    'balance' => $this->getAccBalance($payee->id, $d_wallet),
                    'trans_type' => 'debit',
                    'product' => $product
                ];

                if(in_array($post->wallet, ["payoutwallet", "mainwallet"])){
                    $debit['option5'] = "fund";
                    $debit['option1'] = "wallet";
                }

                $credit = [
                    'number' => $user->mobile,
                    'mobile' => $user->mobile,
                    'provider_id' => $post->provider_id,
                    'api_id' => $provide->api_id,
                    'amount' => $post->amount,
                    'txnid'  => "WTR".date('Ymdhis'),
                    'remark' => $post->remark,
                    'refno'  => $post->refno,
                    'status' => 'success',
                    'user_id'   => $user->id,
                    'credit_by' => $payee->id,
                    'rtype' => 'main',
                    'via'   => $post->via,
                    'balance'    => $this->getAccBalance($user->id, $c_wallet),
                    'trans_type' => 'credit',
                    'product'    => $product
                ];

                $request = \DB::transaction(function () use($debit, $credit, $d_table, $c_table, $c_wallet, $d_wallet) {
                    User::where('id', $debit['user_id'])->decrement($d_wallet, $debit['amount']);
                    $debit["closing"] = $this->getAccBalance($debit['user_id'], $d_wallet);
                    $debitReport = $d_table->create($debit);

                    User::where('id', $credit['user_id'])->increment($c_wallet, $credit['amount']);
                    $credit["closing"] = $this->getAccBalance($credit['user_id'], $c_wallet);
                    $creditReport = $c_table->create($credit);
                    return true;
                });

                if($request){
                    return response()->json(['status'=>"TXN", 'message' => "Fund Transfer successfully"]);
                }else{
                    return response()->json(['status'=>"ERR", 'message' => "Something went wrong."]);
                }
                break;

            case 'return':
                if(!\Myhelper::can('fund_return')){
                    return response()->json(["status" => "ERR" , "message" => "Permission not allowed"]);
                }

                $post['refno'] = preg_replace('/[^A-Za-z0-9]/', '', $post->refno);
                $rules = array(
                    'amount' => 'required|numeric|min:1'
                );
        
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        $error = $value[0];
                    }
                    return response()->json(['status' => "ERR", "message" => $error]);
                }
                $product = "fund return";

                if($post->wallet == "payoutwallet"){
                    if(!\Myhelper::hasRole('admin')){
                        return response()->json(["status" => "ERR" , "message" => "Permission not allowed"]);
                    }
                    
                    $payee    = User::where('id', $post->payee_id)->first();
                    $user     = User::where('id', $post->payee_id)->first();
                    $d_wallet = "payoutwallet";
                    $d_table  = Payoutreport::query();

                    $c_wallet = "mainwallet";
                    $c_table  = Report::query();
                }elseif($post->wallet == "directpayoutwallet"){
                    $payee    = \Auth::user();
                    $user     = User::where('id', $post->payee_id)->first();
                    $d_wallet = "payoutwallet";
                    $d_table  = Payoutreport::query();

                    $c_wallet = "payoutwallet";
                    $c_table  = Payoutreport::query();
                }else{
                    $payee    = \Auth::user();
                    $user     = User::where('id', $post->payee_id)->first();
                    $c_wallet = "mainwallet";
                    $d_wallet = "mainwallet";
                    $c_table  = Report::query();
                    $d_table  = Report::query();
                }

                if($this->getAccBalance($payee->id, $d_wallet) < $post->amount){
                    return response()->json(['status'=>"ERR", 'message' => "Insufficient wallet balance."]);
                }

                $debit = [
                    'number' => $payee->mobile,
                    'mobile' => $payee->mobile,
                    'provider_id' => $post->provider_id,
                    'api_id' => $provide->api_id,
                    'amount' => $post->amount,
                    'remark' => $post->remark,
                    'refno'  => $post->refno,
                    'status' => 'success',
                    'user_id'=> $user->id,
                    'credit_by' => $payee->id,
                    'rtype'   => 'main',
                    'via'     => 'portal',
                    'balance' => $this->getAccBalance($user->id, $d_wallet),
                    'trans_type'  => 'debit',
                    'product' => $product
                ];

                $credit = [
                    'number'  => $user->mobile,
                    'mobile'  => $user->mobile,
                    'provider_id' => $post->provider_id,
                    'api_id'  => $provide->api_id,
                    'amount'  => $post->amount,
                    'remark'  => $post->remark,
                    'refno'  => $post->refno,
                    'status'  => 'success',
                    'user_id' => $payee->id,
                    'credit_by' => $user->id,
                    'rtype'   => 'main',
                    'via'     => $post->via,
                    'balance' => $this->getAccBalance($payee->id, $c_wallet),
                    'trans_type' => 'credit',
                    'product' => $product,
                ];

                $request = \DB::transaction(function () use($debit, $credit, $d_table, $c_table, $c_wallet, $d_wallet) {
                    User::where('id', $debit['user_id'])->decrement($d_wallet, $debit['amount']);
                    $debit["closing"] = $this->getAccBalance($debit['user_id'], $d_wallet);
                    $debitReport = $d_table->create($debit);
                
                    User::where('id', $credit['user_id'])->increment($c_wallet, $credit['amount']);
                    $credit["closing"] = $this->getAccBalance($credit['user_id'], $c_wallet);
                    $creditReport = $c_table->create($credit);
                    return true;
                });

                if($request){
                    return response()->json(['status'=>"TXN", 'message' => "Fund Return successfully"]);
                }else{
                    return response()->json(['status'=>"ERR", 'message' => "Something went wrong."]);
                }
                break;

            case 'loadwallet':
                if(\Myhelper::hasNotRole('admin')){
                    return response()->json(['status' => "Permission not allowed"],400);
                }

                $insert = [
                    'number' => \Auth::user()->mobile,
                    'mobile' => \Auth::user()->mobile,
                    'provider_id' => $post->provider_id,
                    'api_id' => $this->fundapi->id,
                    'amount' => $post->amount,
                    'txnid'  => date('ymdhis'),
                    'remark' => $post->remark,
                    'status'     => 'success',
                    'user_id'    => \Auth::id(),
                    'credit_by'  => \Auth::id(),
                    'rtype'      => 'main',
                    'via'        => 'portal',
                    'balance'    => \Auth::user()->payoutwallet,
                    'trans_type' => 'credit',
                    'product'    => "fund ".$post->type
                ];

                $action = \DB::transaction(function () use($insert) {
                    User::where('id', $insert['user_id'])->increment("payoutwallet", $insert['amount']);
                    $insert["closing"] = $this->getAccBalance($insert['user_id'], "payoutwallet");
                    return Report::create($insert);
                });
                
                if($action){
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Technical error, please contact your service provider before doing transaction."],400);
                }
                break;
            
            case 'requestview':
                if(!\Myhelper::can('setup_bank')){
                    return response()->json(['status'=>"ERR", 'message' => "Permission not allowed"]);
                }

                $fundreport = Fundreport::where('id', $post->id)->where('status', 'pending')->first();
                if(!$fundreport){
                    return response()->json(['status'=>"ERR", 'message' => "Already Updated"]);
                }
                
                $post['amount'] = $fundreport->amount;

                if ($post->status == "approved") {
                    if(\Auth::user()->mainwallet < $post->amount){
                        return response()->json(['status'=>"ERR", 'message' => "Insufficient wallet balance."]);
                    }

                    $action = Fundreport::where('id', $post->id)->update([
                        "status" => $post->status,
                        "remark" => $post->remark
                    ]);

                    $payee  = \Auth::user();
                    $user   = User::where('id', $fundreport->user_id)->first();

                    $debit = [
                        'number'  => $payee->mobile,
                        'mobile'  => $payee->mobile,
                        'provider_id' => $post->provider_id,
                        'api_id'  => $provide->api_id,
                        'amount'  => $post->amount,
                        'txnid'   => $fundreport->id,
                        'refno'   => $fundreport->ref_no,
                        'remark'  => $post->remark,
                        'option1' => $fundreport->fundbank_id,
                        'option2' => $fundreport->paymode,
                        'option3' => $fundreport->paydate,
                        'status'  => 'success',
                        'user_id' => $payee->id,
                        'credit_by' => $user->id,
                        'rtype'   => 'main',
                        'via'     => 'portal',
                        'balance' => $payee->mainwallet,
                        'trans_type'  => 'debit',
                        'product' => "fund request"
                    ];

                    $credit = [
                        'number' => $user->mobile,
                        'mobile' => $user->mobile,
                        'provider_id' => $post->provider_id,
                        'api_id' => $provide->api_id,
                        'amount' => $post->amount,
                        'txnid'   => $fundreport->id,
                        'refno'   => $fundreport->ref_no,
                        'remark'  => $post->remark,
                        'option1' => $fundreport->fundbank_id,
                        'option2' => $fundreport->paymode,
                        'option3' => $fundreport->paydate,
                        'status' => 'success',
                        'user_id'   => $user->id,
                        'credit_by' => $payee->id,
                        'rtype' => 'main',
                        'via'   => $post->via,
                        'balance'    => $user->payoutwallet,
                        'trans_type' => 'credit',
                        'product' => "fund request"
                    ];

                    $request = \DB::transaction(function () use($debit, $credit) {
                        $debitReport = Payoutreport::create($debit);
                        User::where('id', $debit['user_id'])->decrement("payoutwallet", $debit['amount']);
                        
                        $creditReport = Payoutreport::create($credit);
                        User::where('id', $credit['user_id'])->increment("payoutwallet", $credit['amount']);
                        return true;
                    });

                    if($request){
                        return response()->json(['status'=>"TXN", 'message' => "Transaction successfully"]);
                    }else{
                        return response()->json(['status'=>"ERR", 'message' => "Something went wrong."]);
                    }
                }else{
                    $action = Fundreport::where('id', $post->id)->update([
                        "status" => $post->status,
                        "remark" => $post->remark
                    ]);

                    if($action){
                        return response()->json(['status'=>"TXN", 'message' => "Transaction successfully"]);
                    }else{
                        return response()->json(['status'=>"ERR", 'message' => "Something went wrong, please try again."]);
                    }
                }
                break;

            case 'request':
                if(!\Myhelper::can('fund_request')){
                    return response()->json(['status' => "Permission not allowed"],400);
                }

                $rules = array(
                    'fundbank_id' => 'required|numeric',
                    'paymode'     => 'required',
                    'amount'      => 'required|numeric|min:100',
                    'ref_no'      => 'required|unique:fundreports,ref_no',
                    'paydate'     => 'required'
                );
        
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $post['user_id'] = \Auth::id();
                $post['credited_by'] = \Auth::user()->parent_id;
                if(!\Myhelper::can('setup_bank', \Auth::user()->parent_id)){
                    $admin = User::whereHas('role', function ($q){
                        $q->where('slug', 'whitelable');
                    })->where('company_id', \Auth::user()->company_id)->first(['id']);

                    if($admin && \Myhelper::can('setup_bank', $admin->id)){
                        $post['credited_by'] = $admin->id;
                    }else{
                        $admin = User::whereHas('role', function ($q){
                            $q->where('slug', 'admin');
                        })->first(['id']);
                        $post['credited_by'] = $admin->id;
                    }
                }
                $post['status'] = "pending";
                if($post->hasFile('payslips')){
                    $post['payslip'] = $post->file('payslips')->store('deposit_slip');
                }
                $action = Fundreport::create($post->all());
                if($action){
                    return response()->json(['status' => "success"],200);
                }else{
                    return response()->json(['status' => "Something went wrong, please try again."],200);
                }
                break;

            case "bank":
                if($provide->status == 0){
                    return response()->json(['statuscode' => "ERR", "message" => "Operator Currently Down."]);
                }
                $rules = array(
                    'accountname' => 'required',
                    'bank'     => 'required',
                    'account'  => 'required',
                    'ifsc'     => 'required',
                    'amount'   => 'required|numeric|min:1|max:200000'
                );
                
                $validate = \Myhelper::FormValidator($rules, $post);
                if($validate != "no"){
                    return $validate;
                }
                
                $user = \Auth::user();

                User::where('id', $user->id)->update([
                    'account' => $post->account,
                    'bank' => $post->bank, 
                    'ifsc' => $post->ifsc, 
                    'accountname' => $post->accountname
                ]);

                $post['mode'] = "IMPS";

                if($post->mode == "IMPS"){
                    if($post->amount > 0 && $post->amount <= 500){
                        $provide = Provider::where('recharge1', 'payout1')->first();
                    }elseif($post->amount > 500 && $post->amount <= 999){
                        $provide = Provider::where('recharge1', 'payout2')->first();
                    }elseif($post->amount > 999 && $post->amount <= 24999){
                        $provide = Provider::where('recharge1', 'payout3')->first();
                    }elseif($post->amount > 24999){
                        $provide = Provider::where('recharge1', 'payout4')->first();
                    }
                }elseif($post->mode == "NEFT"){
                    $provide = Provider::where('recharge1', 'payoutneft')->first();
                }

                if(!$provide){
                    return response()->json(['statuscode' => "ERR", "message" => "Operator Not Found"]);
                }
                
                $post['charge'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $provide->id, $user->role->slug);
                $post['gst'] = ($provide->api->gst * $post->charge)/100;

                $serviceApi = \DB::table("service_managers")->where("provider_id", $provide->id)->where("user_id", $user->id)->first();

                if($serviceApi){
                    $api = Api::find($serviceApi->api_id);
                }else{
                    $api = Api::find($provide->api_id);
                }
                
                if($this->getAccBalance($user->id, "payoutwallet") - $user->lockedamount < $post->amount + $post->charge){
                    return response()->json(['statuscode' => "ERR", "message" => "Low balance to make this request."]);
                }

                switch ($api->code) {
                    case 'payout5':
                        do {
                            $date = substr(date("Y"), -1).(date("z")+1).date('Hi');
                            
                            $post['txnid'] = strtoupper(Str::random((35 - strlen($date)))).$date;
                        } while (Payoutreport::where("txnid", "=", $post->txnid)->first() instanceof Payoutreport);
                        break;

                    case 'payout4':
                        do {
                            $post['txnid'] = rand(111111111111, 999999999999);
                        } while (Payoutreport::where("txnid", "=", $post->txnid)->first() instanceof Payoutreport);
                        break;
                    
                    default:
                        do {
                            $date = substr(date("Y"), -1).(date("z")+1).date('Hi');
                            
                            $post['txnid'] = strtoupper(Str::random((35 - strlen($date)))).$date;
                        } while (Payoutreport::where("txnid", "=", $post->txnid)->first() instanceof Payoutreport);
                        break;
                }

                $debit = [
                    'number'  => $post->account,
                    'mobile'  => $user->mobile,
                    'provider_id' => $provide->id,
                    'api_id'  => $api->id,
                    'amount'  => $post->amount,
                    'charge'  => $post->charge,
                    'gst'     => $post->gst,
                    // 'txnid'   => $post->txnid,
                    // 'apitxnid'=> $post->txnid,
                    'description'  => $post->accountname,
                    'option1' => "bank",
                    'option2' => $post->ifsc,
                    'option3' => $post->bank,
                    'status'  => "pending",
                    'user_id' => $user->id,
                    'credit_by' => $user->id,
                    'rtype'   => 'main',
                    'via'     => $post->via,
                    'balance' => $user->payoutwallet,
                    'trans_type' => 'debit',
                    'product'    => "bankpayout",
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
                    case 'hp-out':
                        // if(!\Myhelper::can('payout_api', \Auth::user()->id)) {
                        //     $refno = $this->getTransferID(12);
                        //     $payId = $this->getTransactionID(13);
                        //     Payoutreport::where('id', $report->id)->update([
                        //             'status' => "success", 
                        //             'refno'  => $refno,
                        //             'payid'  => 'QT-UTR'.$payId,
                        //             'remark' => 'offline Settlement'
                        //         ]);
                                
                        //     return response()->json([ 'statuscode' => 'TXNS' ]);
                        // } else {
                            $url = "https://login.honestpay.in/v/payout/order/create";
                            $header = array(
                                "Content-Type: application/json"
                            );
                        
                            // Generate transaction IDs
                            $transactionID = $this->getTransactionID();
                            $transferID = $this->getTransferID(35);
                            $txnid = "NPSELF".date("ymdhis").$post->user_id;
            
                            $parameters = [
                                "token"     => 'KIBztE9f3s7CB49I9v0cGejfwPO9bL',
                                "mobile"    => $user->mobile,
                                "account"   => $post->account,
                                "bank"      => $post->bank,
                                "mode"      => "IMPS/NEFT",
                                "ifsc"      => strtoupper($post->ifsc),
                                "name"      => $post->accountname,
                                "amount"    => $post->amount,
                                "apitxnid"  => $txnid,
                                "callback"  => "https://dashboard.nanakpay.com/callback/payout",
                            ];
                            $result = \App\Helpers\Permission::curl($url, 'POST', json_encode($parameters), $header, "yes", "Qr", $post->txnid); 
                            $response = json_decode($result['response']);
                            //Log::info('Internal API Response received', ['response' => $response]);
                            logger()->info('API Response:', (array) $response);
                            if(isset($response->statuscode) && $response->statuscode == 'TXF'){
                                Payoutreport::where('id', $report->id)->update([
                                    'status' => "failed", 
                                    'refno'  => isset($response->message) ? $response->message : "Failed",
                                ]);
            
                                User::where('id', $user->id)->increment('payoutwallet', $post->amount + ($post->charge + $post->gst));
                                return response()->json([ 'statuscode' => 'TXNF' ]);
                            }elseif(isset($response->statuscode) && $response->statuscode == 'TXN'){
                                Payoutreport::where('id', $report->id)->update([
                                    'status' => "pending", 
                                    'refno'  => $response->bankutr,
                                    'payid'  => $response->txnid,
                                    'txnid'  => $txnid,
                                    'apitxnid'  => $txnid
                                ]);
            
                                return response()->json([ 'statuscode' => 'TXNS' ]);
                            }
                            else {
                                logger()->error("Payout error: ", (array)$response);
                                Payoutreport::where('id', $report->id)->update([
                                    'status' => "pending", 
                                    'refno'  => 'NULL',
                                    'payid'  => $response->txnid
                                ]);
                                return response()->json([ 'statuscode' => 'TXNP' ]);
                            }
                        // }
                        break;
                    default:
                        
                        break;
                }
                break;

            case "qrcode":
                $user = User::where('id', \Auth::id())->first();
                $api  = \DB::table("apitokens")->where("user_id", $post->user_id)->where("ip", "147.79.64.107")->first(); 

                if(!$api){
                    do {
                        $post['token'] = str_random(30);
                    } while (Apitoken::where("token", "=", $post->token)->first() instanceof Apitoken);

                    $post['user_id'] = $post->user_id;
                    $post['ip'] = "147.79.64.107";
                    $post['status']  = "1";
                    $action = Apitoken::updateOrCreate(['id'=> $post->id], $post->all());
                    $api  = \DB::table("apitokens")->where("user_id", $post->user_id)->where("ip", "147.79.64.107")->first(); 
                }

                $url = 'https://login.honestpay.in/v/qr/create';
                $requestData = json_encode([
                    'token' => 'KIBztE9f3s7CB49I9v0cGejfwPO9bL',
                    'type' => 'dynamic',
                    'amount' => $post->amount,
                    'email' => $user->email,
                    'name' => $user->name,
                    'txnid' => "NPSELF".date("ymdhis").$post->user_id,
                    'callback' => 'https://webhook-test.com/11bb20480dc406a9c30e8a62937e8dca'
                ]);
                
                $headers = [
                    'Content-Type: application/json'
                ];
            
                // Initialize cURL
                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $requestData,
                    CURLOPT_HTTPHEADER => $headers,
                ]);
                
                // Execute cURL request
                $response = curl_exec($curl);
                
                // Log raw response
                Log::info('Internal API Response received', ['response' => $response]);
                
                // Close cURL
                curl_close($curl);
                
                // Handle response
                if ($response === false) {
                    Log::error('cURL Error: ' . curl_error($curl));
                
                    return response()->json([
                        'error' => 'cURL request failed.',
                    ], 500);
                }
                
                $decodedResponse = json_decode($response);
                
                // Log decoded response
                Log::info('Internal Decoded API Response', ['decodedResponse' => $decodedResponse]);
                
                if (isset($decodedResponse->message) && $decodedResponse->message == 'true') {
                    $updatedPaymentLink = str_replace(
                        'pn=HonestPay',
                        'pn=NanakPay',
                        $decodedResponse->upi_string
                    );
                    $upiQrString = $updatedPaymentLink;
                
                    Report::where('txnid', $post->txnid)->update([
                        'payid' => $decodedResponse->upi_qr_id,
                        'apitxnid' => $post->txnid,
                        'remark' => $decodedResponse->message,
                        'qrString' => $upiQrString, // Prefixed UPI string
                    ]);
                
                    $post['upi_string'] = $upiQrString;
                    return response()->json(['statuscode' => "TXN", "code" => $upiQrString]);    
                } else {
                    Log::error('Payment failed or invalid response', ['response' => $decodedResponse]);
                    return response()->json(['statuscode' => "TXF", "message" => isset($response->message)?$response->message : "Something went wrong", "code" => $response->message]);
                }
                break;
                
            case "upiintent":
                $user = \Auth::user();
                if($post->amount > 0 && $post->amount <= 300){
                    $provider = Provider::where('recharge1', 'payin1')->first();
                }elseif($post->amount > 301 && $post->amount <= 500){
                    $provider = Provider::where('recharge1', 'payin2')->first();
                }elseif($post->amount > 501){
                    $provider = Provider::where('recharge1', 'payin3')->first();
                }                
                
                $post['charge'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $provider->id, $user->role->slug);
                $post['gst'] = ($provider->api->gst * $post->charge)/100;

                $serviceApi = \DB::table("service_managers")->where("provider_id", $provider->id)->where("user_id", $user->id)->first();

                if($serviceApi){
                    $api = Api::find($serviceApi->api_id);
                }else{
                    $api = Api::find($provider->api_id);
                }
                
                $user = User::where('id', \Auth::id())->first();
                $apis  = \DB::table("apitokens")->where("user_id", $post->user_id)->where("ip", "147.79.64.107")->first(); 

                if(!$apis){
                    do {
                        $post['token'] = str_random(30);
                    } while (Apitoken::where("token", "=", $post->token)->first() instanceof Apitoken);

                    $post['user_id'] = $post->user_id;
                    $post['ip'] = "147.79.64.107";
                    $post['status']  = "1";
                    $action = Apitoken::updateOrCreate(['id'=> $post->id], $post->all());
                    $apis  = \DB::table("apitokens")->where("user_id", $post->user_id)->where("ip", "147.79.64.107")->first(); 
                }
                
                switch ($api->code) {
                    case 'hp-in':

                        $url = 'https://login.honestpay.in/v/intent/create';
                        $requestData = json_encode([
                            'token' => 'KIBztE9f3s7CB49I9v0cGejfwPO9bL',
                            'type' => 'dynamic',
                            'amount' => $post->amount,
                            'email' => $user->email,
                            'name' => $user->name,
                            'txnid' => "NPSELF".date("ymdhis").$post->user_id,
                            'callback' => 'https://webhook-test.com/11bb20480dc406a9c30e8a62937e8dca'
                        ]);
                        
                        $headers = ['Content-Type: application/json'];
                    
                        // Initialize cURL
                        $curl = curl_init();
                        curl_setopt_array($curl, [
                            CURLOPT_URL => $url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'POST',
                            CURLOPT_POSTFIELDS => $requestData,
                            CURLOPT_HTTPHEADER => $headers,
                        ]);
                        
                        // Execute cURL request
                        $response = curl_exec($curl);
                        
                        // Log raw response
                        Log::info('Internal API Response received', ['response' => $response]);
                        
                        // Close cURL
                        curl_close($curl);
                        
                        // Handle response
                        if ($response === false) {
                            //Log::error('cURL Error: ' . curl_error($curl));
                        
                            return response()->json([
                                'error' => 'cURL request failed.',
                            ], 500);
                        }
                        
                        $decodedResponse = json_decode($response);
                        
                        // Log decoded response
                        Log::info('Internal Decoded API Response', ['decodedResponse' => $decodedResponse]);
                        
                        if ($response) {
                            $updatedPaymentLink = str_replace(
                                'pn=HonestPay',
                                'pn=NanakPay',
                                $decodedResponse->upi_string
                            );
                            $upiQrString = $updatedPaymentLink;
                        
                            Report::where('txnid', $post->txnid)->update([
                                'payid' => $decodedResponse->upi_qr_id,
                                'apitxnid' => $post->txnid,
                                'remark' => $decodedResponse->message,
                                'description' => $upiQrString, // Prefixed UPI string
                            ]);
                        
                            $post['upi_string'] = $upiQrString;
                            return response()->json(['statuscode' => "TXN", "code" => $upiQrString]);    
                        } else {
                            //Log::error('Payment failed or invalid response', ['response' => $decodedResponse]);
                            $post['upi_string'] = $response->error;
                            return response()->json(['statuscode' => "TXF", "message" => isset($response->message)?$response->message : "Something went wrong", "code" => $response->error]);
                        }
                        break;
                    
                    default:
                        # code...
                        break;
                }
                break;

            case 'wallet':
                return response()->json(['status'=>"ERR", 'message' => "Permission not allowed"]);
                $rules = array(
                    'amount'    => 'required|numeric|min:10|max:500000'
                );
        
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        $error = $value[0];
                    }
                    return response()->json(['statuscode'=>'ERR', 'message'=> $error]);
                }
                $user = \Auth::user();
                if($user->mainwallet - $user->lockedamount < $post->amount){
                    return response()->json(['statuscode' => "ERR" , "message" => "Low wallet balance to make this request"]);
                }

                $post['create_time'] = Carbon::now()->toDateTimeString();
                $post['txnid']  = "WTR".date('Ymdhis');
                $post['status'] = "success";

                $debit = [
                    'number'  => "Wallet",
                    'mobile'  => $user->mobile,
                    'provider_id' => $provide->id,
                    'api_id'  => $provide->api_id,
                    'amount'  => $post->amount,
                    'txnid'   => $post->txnid,
                    'payid'   => $post->txnid,
                    'refno'   => ucfirst($post->type)." Fund Recieved",
                    'description' =>  ucfirst($post->type)." Fund Recieved",
                    'remark'  => $post->remark,
                    'option1' => "wallet",
                    'option4' => $post->option4,
                    'option5' => "fund",
                    'status'  => $post->status,
                    'user_id' => $user->id,
                    'credit_by' => $user->id,
                    'rtype'   => 'main',
                    'via'     => $post->via,
                    'balance' => $user->mainwallet,
                    'trans_type' => 'debit',
                    'product'    => "fund request",
                    'create_time'=> $post->create_time
                ];

                $credit = [
                    'number'  => "Wallet",
                    'mobile'  => $user->mobile,
                    'provider_id' => $provide->id,
                    'api_id'  => $provide->api_id,
                    'amount'  => $post->amount,
                    'txnid'   => $post->txnid,
                    'payid'   => $post->txnid,
                    'refno'   => ucfirst($post->type)." Fund Recieved",
                    'description' =>  ucfirst($post->type)." Fund Recieved",
                    'remark'  => $post->remark,
                    'option1' => $post->type,
                    'option5' => "fund",
                    'status'  => 'success',
                    'user_id' => $user->id,
                    'credit_by' => $user->id,
                    'rtype'   => 'main',
                    'via'     => $post->via,
                    'balance' => $user->payoutwallet,
                    'trans_type' => 'credit',
                    'product'    => "fund request",
                    'create_time'=> $post->create_time
                ];

                try {
                    $load = \DB::transaction(function () use($debit, $credit) {
                        User::where('id', $debit["user_id"])->decrement("mainwallet", $debit["amount"]);
                        Report::create($debit);

                        User::where('id', $credit["user_id"])->increment("payoutwallet", $credit["amount"]);
                        Payoutreport::create($credit);
                        return true;
                    });
                } catch (\Exception $e) {
                    $load = false;
                }

                if($load){
                    return response()->json(['statuscode' => "TXN" , "message" => "Transaction Successfull", 'txnid' => $post->txnid]);
                }else{
                    return response()->json(['statuscode' => "ERR" , "message" => "Transaction Failed"]);
                }
                break;

            case 'addmoney':
                $userkey = \DB::table("apitokens")->where("user_id", \Auth::id())->first();

                if(!$userkey){
                    do {
                        $post['token'] = str_random(30);
                    } while (Apitoken::where("token", "=", $post->token)->first() instanceof Apitoken);

                    $post['user_id'] = $post->user_id;
                    $action = Apitoken::updateOrCreate(['id'=> $post->id], $post->all());
                    $userkey  = \DB::table("apitokens")->where("user_id", $post->user_id)->first(); 
                }
                
                $url = "https://login.intentpe.com/api/v1/payin/create_intent";
                $header = array(
                    'Authorization: Bearer '.$userkey->token
                );

                $parameter = [
                    "amount" => $post->amount
                ];

                $result   = \Myhelper::curl($url, "POST", $parameter, $header, "yes", 'AddMoney', $post->txnid);
                $response = json_decode($result['response'], true);

                if(isset($response['status']) && $response['status'] == "success"){
                    $return['status']  = "success";
                    $return['qr_link'] = "https://chart.googleapis.com/chart?cht=qr&chs=250x250&chl=".urlencode($response['data']['intent_data']);
                }else{
                    $return['status']  = "error";
                    $return['message'] = $response['message'];
                }
                return response()->json($return);
                break;
            
            case 'getmoney':
                $userkey = \DB::table("apitokens")->where("user_id", \Auth::id())->first();
                if(!$userkey){
                    do {
                        $post['token'] = str_random(30);
                    } while (Apitoken::where("token", "=", $post->token)->first() instanceof Apitoken);

                    $post['user_id'] = $post->user_id;
                    $action = Apitoken::updateOrCreate(['id'=> $post->id], $post->all());
                    $userkey  = \DB::table("apitokens")->where("user_id", $post->user_id)->first(); 
                }
                
                $url     = "https://login.intentpe.com/api/v1/payout/create";
                $header  = array(
                    'Authorization: Bearer '.$userkey->token
                );

                $parameter = [
                    "name" => $post->name,
                    "account_number" => $post->account_number,
                    "ifsc_code" => $post->ifsc_code,
                    "bank_name" => $post->bank_name,
                    "amount"    => $post->amount,
                    "udf1"      => "loadmoney"
                ];

                $result   = \Myhelper::curl($url, "POST", $parameter, $header, "yes", 'AddMoney', $post->txnid);
                $response = json_decode($result['response'], true);

                if(isset($response["data"]['status']) && $response["data"]['status'] == "success"){
                    $return['status']  = "success";
                }else{
                    $return['status']  = "error";
                    $return['message'] = $response['message'];
                }

                return response()->json($return);
                break;
            
            case 'apitransfer':
                $rules = array(
                    'id' => 'required|numeric'
                );
        
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $key => $value) {
                        $error = $value[0];
                    }
                    return response()->json(['status' => "ERR", "message" => $error]);
                }

                $report = \DB::table("payoutreports")->where("id", $post->id)->first();

                if(!$report){
                    return response()->json(['status' => "error", "message" => "Transaction Not Found"]);
                }

                if($report->status == "success" && $report->option7 != "failed"){
                    return response()->json(['status' => "error", "message" => "Already Transfer"]);
                }

                $api    = Api::find($report->api_id);
                $url    = "http://103.205.64.251:8080/clickncashapi/rest/auth/generateToken";
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
                        "clientReferenceNo" => $report->txnid,
                        "benePhoneNo"   => "9999999991",
                        "beneAccountNo" => $report->number,
                        "beneifsc"      => $report->option2,
                        "beneName"      => $report->option1,
                        "fundTransferType"  => "IMPS",
                        "amount"    => $report->amount,
                        "pincode"   => "201012",
                        "custName"  => "Apna SITI SEVA KENDRA",
                        "custMobNo" => "9999999991",
                        "latlong"   => "22.8031731,88.7874172",
                        "beneBankName" => $report->option1
                    ];

                    $url    = "http://103.205.64.251:8080/clickncashapi/rest/auth/transaction/payOut";
                    $result = \Myhelper::curl($url, "POST", json_encode($parameters), $header, "yes", 'Payout', $post->txnid);

                    if($result['response'] == ""){
                        Payoutreport::where('id', $report->id)->update([
                            'status' => "pending"
                        ]);

                        $return['status']  = "success";
                        $return['message'] = "Successfully Transferred";
                        return response()->json($return);
                    }

                    $response = json_decode($result['response']);
                    
                    if(isset($response->status) && !in_array($response->status, ["SUCCESS", 'PROCESSING'])){
                        Payoutreport::where('id', $report->id)->update([
                            'status'  => "failed", 
                            'refno'   => isset($response->utr) ? $response->utr : "Failed",
                            "option7" => "main"
                        ]);
                        
                        \Myhelper::transactionRefund($post->id, "payoutreports", "payoutwallet");
                        return response()->json([
                            'status'  => 'error', 
                            'message' => "Payout Failed"
                        ]);
                    }elseif(isset($response->status) && in_array($response->status, ["SUCCESS"])){
                        Payoutreport::where('id', $report->id)->update([
                            'status'  => "success", 
                            'refno'   => $response->utr,
                            "option7" => "main"
                        ]);

                        $return['status']  = "success";
                        $return['message'] = "Successfully Transferred";
                        return response()->json($return);
                    }else{
                        Payoutreport::where('id', $report->id)->update([
                            'status'  => "pending", 
                            'refno'   => isset($response->utr) ? $response->utr : "",
                            "option7" => "main"
                        ]);

                        $return['status']  = "success";
                        $return['message'] = "Successfully Transferred";
                        return response()->json($return);
                    }
                }else{
                    return response()->json(['status' => "error", 'message' => "Error While Transferring Amount, try again"]);
                }
                break;
            
            default:
                # code...
                break;
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
    
}
