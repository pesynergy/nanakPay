<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Report;
use App\Model\Payoutreport;
use App\Model\PortalSetting;
use App\Model\Upiload;
use App\User;


class ActionController extends Controller
{
    public function update(Request $post)
    {
        switch ($post->actiontype) {
            case 'payout':
                $permission = "payout_statement_edit";
                break;

            case 'collection':
            case 'upi':
                $permission = "upi_statement_edit";
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
        }

        switch ($post->actiontype) {
            case 'payout':
                $rules = array(
                    'id'    => 'required',
                    'status'    => 'required',
                    'txnid'    => 'required',
                    'refno'    => 'required',
                    'payid'    => 'required'
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $report = Payoutreport::where('id', $post->id)->first();
                if(!$report || !in_array($report->status , ['pending', 'success', 'accept', 'processing'])){
                    return response()->json(['status' => "Transaction Editing Not Allowed"], 400);
                }

                $action = Payoutreport::where('id', $post->id)->update($post->except(['id', '_token', 'actiontype', 'user_id']));
                if ($action) {
                    if($post->status == "reversed"){
                        \Myhelper::transactionRefund($post->id, "payoutreports", "payoutwallet");
                    }

                    $report = Payoutreport::where('id', $post->id)->first();
                    if($report->status == "reversed"){
                        $status = "failed";
                    }else{
                        $status = $report->status;
                    }
                    $webhook_payload = [
                        'event_name' => 'payout',
                        'data'       => [
                            'status' => $status,
                            'utr_no' => $report->utr,
                            'txn_id' => $report->txnid,
                            'amount' => $report->amount,
                            'udf1' => $report->option4,
                            'udf2' => $report->option5,
                            'udf3' => $report->option6
                        ]
                    ];

                    $header = array(
                        "Content-Type: application/json"
                    );

                    $token    = \DB::table("apitokens")->where("user_id", $report->user_id)->first();
                    $response = \Myhelper::curl($token->payoutcallbackurl, "POST", json_encode($webhook_payload), $header, "no");
                    \DB::table('log_webhooks')->insert([
                        'url' => $url."?".json_encode($webhook_payload), 
                        'callbackresponse' => json_encode($response),
                        'txnid'      => $report->txnid, 
                        'product'    => 'payout'
                    ]);

                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;
                
            case 'payin':
                $rules = array(
                    'id'     => 'required',
                    'status' => 'required',
                    'txnid'  => 'required',
                    'refno'  => 'required',
                    'payid'  => 'required'
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $report = Report::where('id', $post->id)->first();
                if(!$report || !in_array($report->status , ['success'])){
                    return response()->json(['status' => "Transaction Editing Not Allowed"], 400);
                }

                $action = Report::where('id', $post->id)->update($post->except(['id', '_token', 'actiontype', 'user_id']));
                if ($action) {
                    if($post->status == "chargeback"){
                        \Myhelper::transactionRefund($post->id, "reports", "mainwallet");
                    }
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;
            
            case 'upi':
                $rules = array(
                    'id'     => 'required',
                    'status' => 'required',
                    'txnid'  => 'required',
                    'refno'  => 'required',
                    'payid'  => 'required'
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $report = Upiload::where('id', $post->id)->first();
                if(!$report || !in_array($report->status , ['pending'])){
                    return response()->json(['status' => "Transaction Editing Not Allowed"], 400);
                }

                $action = Upiload::where('id', $post->id)->update([
                    "status" => $post->status,
                    "utr"    => $post->refno,
                    "amount" => $post->payid
                ]);

                if ($action) {
                    if($report->status == "pending" && $post->status == "success"){
                        $user = User::find($report->user_id);
                        $providerProfit = \App\Model\Provider::where('recharge1', 'payin_commission')->first();
                        $providerCharge = \App\Model\Provider::where('recharge1', 'payin_charge')->first();

                        $provider = \App\Model\Provider::where('recharge1', 'payin')->first();
                        $post['profit'] = \Myhelper::getCommission($post->payid, $user->scheme_id, $providerProfit->id, $user->role->slug);
                        $post['charge'] = \Myhelper::getCommission($post->payid, $user->scheme_id, $providerCharge->id, $user->role->slug);

                        $txnreport = Report::where('refno', $post->refno)->count();

                        if(!$txnreport){
                            $insert = [
                                'number'  => $user->mobile,
                                'mobile'  => $user->mobile,
                                'provider_id' => $provider->id,
                                'api_id'  => $provider->api_id,
                                'amount'  => $post->payid,
                                'charge'  => $post->charge,
                                'profit'  => $post->profit,
                                'txnid'   => $report->txnid,
                                'refno'   => $post->refno,
                                'status'  => 'success',
                                'user_id' => $user->id,
                                'credit_by'   => $user->id,
                                'rtype'       => 'main',
                                'balance'     => $user->mainwallet,
                                'trans_type'  => "credit",
                                'product'     => "collection"
                            ];

                            \DB::transaction(function () use($insert, $report, $post){
                                Report::create($insert);
                                User::where('id', $report->user_id)->increment('mainwallet', $post->payid + $post->profit - $post->charge);
                            });

                            $callback["amount"] = $post->payid;
                            $callback["status"] = "success";
                            $callback["txnid"]  = $report->txnid;
                            $callback["utr"]    = $post->refno;
                            $callback["payment_mode"] = "upi";
                            $callback["payid"]   = $report->refid;
                            \Myhelper::curl($report->callback."?".http_build_query($callback), "GET", "", [], "yes", "ApiUpiCallback", $post->refno);
                        }
                    }
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;
        }
    }

    public function delete(Request $post)
    {
        if (\Myhelper::hasNotRole(['admin', 'subadmin'])) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
        }
        
        switch ($post->type) {
            case 'slide':
                try {
                    \Storage::delete($post->slide);
                } catch (\Exception $e) {}
                $action = true;
                if ($action) {
                    PortalSetting::where('value', $post->slide)->delete();
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'appsession':
                $action = \DB::table('securedatas')->where('id', $post->id)->delete();
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'websession':
                $action = \DB::table('sessions')->where('tid', $post->id)->delete();
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            default:
                return response()->json(['status' => "Permission Not Allowed"], 400);
                break;
        }        
    }
}
