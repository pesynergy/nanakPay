<?php

namespace App\Http\Controllers;

use App\Model\Complaint;
use Illuminate\Http\Request;
use App\Model\Payoutreport;
use App\Model\Upiload;
use App\User;

class ComplaintController extends Controller
{
    public function index()
    {
        return view('complaint');
    }

    public function store(Request $post)
    {
        if($post->has("status")){
            $rules = array(
                'status'   => 'required',
                'solution' => 'required'
            );
        }else{
            $rules = array(
                'product'        => 'required',
                'transaction_id' => 'required',
            );
        }
        
        $validator = \Validator::make($post->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $complaint = Complaint::where("product", $post->product)->where("transaction_id", $post->transaction_id)->whereIn("status", ["pending", "resolved"])->first();
        if ($complaint) {
            return response()->json(['status' => "Complaint already submitted or resolved"], 200);
        }

        if($post->id == "new"){
            $post['user_id'] = \Auth::id();
        }else{
            $post['resolve_id'] = \Auth::id();
        }

        if($post->hasFile('descriptions')){
            $post['description'] = asset('public')."/".$post->file('descriptions')->store('complaint');
        }

        $action = Complaint::updateOrCreate(['id'=> $post->id], $post->all());
        if ($action) {
            
            $complaint = Complaint::where("id", $post->id)->first();

            if($complaint->product == "upiloads"){
                $url  = "https://partners.payoutapi.in/v/utr/query";
                $parameter = [
                    "refno" => $complaint->subject
                ];

                $header = array(
                    'Content-Type: application/json'
                );

                $result   = \Myhelper::curl($url, 'POST', json_encode($parameter), $header, "no");
                $response = json_decode($result['response']);

                if(isset($response->statuscode) && $response->statuscode == "TXN"){
                    Upiload::where("id", $complaint->transaction_id)->update(["status" => "success", "utr" => $complaint->subject]);
                    $report    = Upiload::where("id", $complaint->transaction_id)->first();

                    $user = User::find($report->user_id);
                    $providerProfit = \App\Model\Provider::where('recharge1', 'payin_commission')->first();
                    $providerCharge = \App\Model\Provider::where('recharge1', 'payin_charge')->first();

                    $provider = \App\Model\Provider::where('recharge1', 'payin')->first();
                    $post['profit'] = \Myhelper::getCommission($response->amount, $user->scheme_id, $providerProfit->id, $user->role->slug);
                    $post['charge'] = \Myhelper::getCommission($response->amount, $user->scheme_id, $providerCharge->id, $user->role->slug);

                    $txnreport = Payoutreport::where('refno', $complaint->subject)->where('product', "collection")->where('user_id', $report->user_id)->count();
                    if(!$txnreport){
                        $insert = [
                            'number'  => $user->mobile,
                            'mobile'  => $user->mobile,
                            'provider_id' => $provider->id,
                            'api_id'  => $provider->api_id,
                            'amount'  => $response->amount,
                            'charge'  => $post->charge,
                            'profit'  => $post->profit,
                            'apitxnid'=> $report->apitxnid,
                            'txnid'   => $report->txnid,
                            'refno'   => $complaint->subject,
                            'payid'   => $response->payid,
                            'option1' => $response->option1,
                            'status'  => 'success',
                            'user_id' => $user->id,
                            'credit_by'   => $user->id,
                            'rtype'       => 'main',
                            'balance'     => $user->payoutwallet,
                            'trans_type'  => "credit",
                            'product'     => "collection"
                        ];

                        \DB::transaction(function () use($insert, $report, $post, $response){
                            Payoutreport::create($insert);
                            User::where('id', $report->user_id)->increment('payoutwallet', $response->amount + $post->profit - $post->charge);
                        });

                        $callback["amount"] = $response->amount;
                        $callback["status"] = "success";
                        $callback["txnid"]  = $report->apitxnid;
                        $callback["utr"]    = $complaint->subject;
                        $callback["payment_mode"] = "upi";
                        $callback["payid"]   = $report->refid;
                        \Myhelper::curl($report->callback."?".http_build_query($callback), "GET", "", [], "yes", "ApiUpiCallback", $post->refno);
                    }
                }
            }

            if($post->has("status")){
                if($post->status == "resolved"){

                    if($complaint->product == "payout"){
                        $report = Payoutreport::where("id", $complaint->transaction_id)->first();
                        \Myhelper::transactionRefund($report->id);
                        \Myhelper::callback($report->id);
                    }

                    if($complaint->product == "upiloads"){
                        Upiload::where("id", $complaint->transaction_id)->update(["status" => "success", "utr" => $complaint->subject]);
                        $report    = Upiload::where("id", $complaint->transaction_id)->first();

                        $user = User::find($report->user_id);
                        $providerProfit = \App\Model\Provider::where('recharge1', 'payin_commission')->first();
                        $providerCharge = \App\Model\Provider::where('recharge1', 'payin_charge')->first();

                        $provider = \App\Model\Provider::where('recharge1', 'payin')->first();
                        $post['profit'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $providerProfit->id, $user->role->slug);
                        $post['charge'] = \Myhelper::getCommission($post->amount, $user->scheme_id, $providerCharge->id, $user->role->slug);

                        $txnreport = Payoutreport::where('refno', $complaint->subject)->where('product', "collection")->where('user_id', $report->user_id)->count();
                        if(!$txnreport){
                            $insert = [
                                'number'  => $user->mobile,
                                'mobile'  => $user->mobile,
                                'provider_id' => $provider->id,
                                'api_id'  => $provider->api_id,
                                'amount'  => $post->amount,
                                'charge'  => $post->charge,
                                'profit'  => $post->profit,
                                'apitxnid'   => $report->apitxnid,
                                'txnid'   => $report->txnid,
                                'refno'   => $complaint->subject,
                                'status'  => 'success',
                                'user_id' => $user->id,
                                'credit_by'   => $user->id,
                                'rtype'       => 'main',
                                'balance'     => $user->payoutwallet,
                                'trans_type'  => "credit",
                                'product'     => "collection"
                            ];

                            \DB::transaction(function () use($insert, $report, $post){
                                Payoutreport::create($insert);
                                User::where('id', $report->user_id)->increment('payoutwallet', $post->amount + $post->profit - $post->charge);
                            });

                            $callback["amount"] = $post->amount;
                            $callback["status"] = "success";
                            $callback["txnid"]  = $report->apitxnid;
                            $callback["utr"]    = $complaint->subject;
                            $callback["payment_mode"] = "upi";
                            $callback["payid"]   = $report->refid;
                            \Myhelper::curl($report->callback."?".http_build_query($callback), "GET", "", [], "yes", "ApiUpiCallback", $post->refno);
                        }
                    }
                }
            }

            return response()->json(['status' => "success"], 200);
        }else{
            return response()->json(['status' => "Task Failed, please try again"], 200);
        }
    }
}
