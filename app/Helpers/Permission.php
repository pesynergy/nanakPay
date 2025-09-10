<?php
namespace App\Helpers;
 
use Illuminate\Http\Request;
use App\Model\LogCallback;
use App\Model\UserPermission;
use App\Model\Apilog;
use App\Model\Scheme;
use App\Model\Commission;
use App\User;
use App\Model\Report;
use App\Model\Payoutreport;
use App\Model\Provider;

class Permission {
    /**
     * @param String $permissions
     * 
     * @return boolean
     */
    public static function can($permission , $id="none") {
        if($id == "none"){
            $id = \Auth::id();
        }
        $user = User::where('id', $id)->first();

        if(is_array($permission)){
            $mypermissions = \DB::table('permissions')->whereIn('slug' ,$permission)->get(['id'])->toArray();
            if($mypermissions){
                foreach ($mypermissions as $value) {
                    $mypermissionss[] = $value->id;
                }
            }else{
                $mypermissionss = [];
            }
            $output = UserPermission::where('user_id', $id)->whereIn('permission_id', $mypermissionss)->count();
        }else{
            $mypermission = \DB::table('permissions')->where('slug' ,$permission)->first(['id']);
            if($mypermission){
                $output = UserPermission::where('user_id', $id)->where('permission_id', $mypermission->id)->count();
            }else{
                $output = 0;
            }
        }

        if($output > 0 || $user->role->slug == "admin"){
            return true;
        }else{
            return false;
        }
    }

    public static function getAccBalance($id, $wallet)
    {
        $mywallet = \DB::table('users')->where('id', $id)->first([$wallet]);

        $mywallet = (array) $mywallet;
        return $mywallet[$wallet];
    }

    public static function hasRole($roles) {
        if(\Auth::check()){
            if(is_array($roles)){
                if(in_array(\Auth::user()->role->slug, $roles)){
                    return true;
                }else{
                    return false;
                }
            }else{
                if(\Auth::user()->role->slug == $roles){
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }

    public static function hasNotRole($roles) {
        if(\Auth::check()){
            if(is_array($roles)){
                if(!in_array(\Auth::user()->role->slug, $roles)){
                    return true;
                }else{
                    return false;
                }
            }else{
                if(\Auth::user()->role->slug != $roles){
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }

    public static function apiLog($url, $modal, $txnid, $header, $request, $response)
    {
        try {
            $apiresponse = Apilog::create([
                "url" => $url,
                "modal" => $modal,
                "txnid" => $txnid,
                "header" => $header,
                "request" => $request,
                "response" => $response
            ]);
        } catch (\Exception $e) {
            $apiresponse = "error";
        }
        return $apiresponse;
    }

    public static function mail($view, $data, $mailto, $name, $mailvia, $namevia, $subject)
    {
        \Mail::send($view, $data, function($message) use($mailto, $name, $mailvia, $namevia, $subject) {
            $message->to($mailto, $name)->subject($subject);
            $message->from($mailvia, $namevia);
        });

        if (\Mail::failures()) {
            return "fail";
        }
        return "success";
    }

    public static function notification($product, $data)
    {
        $otpmailid   = \App\Model\PortalSetting::where('code', 'otpsendmailid')->first();
        $otpmailname = \App\Model\PortalSetting::where('code', 'otpsendmailname')->first();

        switch ($product) {
            case 'otp':
                try {
                    $msg  = "Do not share your login OTP with anyone.".$data["otp"]." OTP to accessing your Account. Please report unauthorised access to customer care. Powered by MULTIPE";
                    $send = \Myhelper::sms($data['mobile'], $msg, "1207164277480037350");
                    \Myhelper::mail('mail.otp', ["name" => $data['name'], "otp" => $data["otp"], "type" => "Login"], $data["email"], $data['name'], $otpmailid->value, $otpmailname->value, "Otp Login");
                } catch (\Exception $e) {}
                break;

            case 'tpin':
                try {
                    $msg  = "Dear partner, your T-Pin generate OTP for ETPL is ".$data["otp"].", please do not share otp with anyone";
                    $send = \Myhelper::sms($data['mobile'], $msg, "1607100000000229361");
                    \Myhelper::mail('mail.otp', ["name" => $data['name'], "otp" => $data["otp"], "type" => "T-Pin"], $data["email"], $data['name'], $otpmailid->value, $otpmailname->value, "T-Pin Reset");
                } catch (\Exception $e) {}
                break;
            
            case 'password':
                try {
                    $msg  = "Dear partner, your password reset TOKEN for you is ".$data["otp"].", please do not share token with anyone Powered by MULTIPE Services Private Limited";
                    $send = \Myhelper::sms($data['mobile'], $msg, "1207166184373726635");
                    \Myhelper::mail('mail.otp', ["name" => $data['name'], "otp" => $data["otp"], "type" => "Password Reset"], $data["email"], $data['name'], $otpmailid->value, $otpmailname->value, "Password Reset");
                } catch (\Exception $e) {}
                break;
            
            case 'id':
                try {
                    $msg = "Welcome  ".$data['name']."  Do not share your login otp. Your username ".$data['mobile']." and password ".$data['mobile']." Thank You for connecting with us. Powered by MULTIPE ";
                    $send = \Myhelper::sms($data['mobile'], $msg, '1207164277404353701');
                    \Myhelper::mail('mail.member', ["username" => $data['mobile'], "password" => $data["mobile"], "name" => $data['name']], $data["email"], $data['name'], $otpmailid->value, $otpmailname->value, "Member Registration");
                } catch (\Exception $e) {}
                break;
        }

        return $send;
    }

    public static function sms($mobile, $content, $temip)
    {
        $smsdata = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();

        $smsapi = \App\Model\Api::where("code", "smsfortis")->first();
        if($smsapi && $smsapi->status == "1"){
            $url = "http://smsfortius.com/api/mt/SendSMS?user=".$smsapi->username."&password=".$smsapi->password."&senderid=".$smsapi->optional1."&channel=Trans&DCS=0&flashsms=0&number=".$mobile."&text=".urlencode($content)."&route=2&peid=1601374162626631756&DLTTemplateId=".$temip;

            $result = \Myhelper::curl($url, "GET", "", [], "yes", "Mobile", $mobile);
            if($result['response'] != ''){
                $response = json_decode($result['response']);
                if ($response->ErrorCode == "000") {
                    return "success";
                }
            }
        }

        $smsapi2 = \App\Model\Api::where("code", "smsalerts")->first();
        if($smsapi2 && $smsapi2->status == "1"){
            $url = "https://alerts.cbis.in/SMSApi/send?userid=".$smsapi2->username."&password=".$smsapi2->password."&sendMethod=quick&mobile=".$mobile."&msg=".urlencode($content)."&senderid=".$smsapi2->optional1."&msgType=unicode&dltEntityId=&dltTemplateId=".$temip."&duplicatecheck=true&output=json";

            $result = \Myhelper::curl($url, "GET", "", [], "yes", "Mobile", $mobile);
            if($result['response'] != ''){
                $response = json_decode($result['response']);
                if ($response->status == "success") {
                    return "success";
                }
            }
        }

        return "fail";
    }

    public static function commission($id, $product)
    {
        if($product == "collection"){
            $report = Report::where('id', $id)->first();
        }else{
            $report = Payoutreport::where('id', $id)->first();
        }

        $insert = [
            'number' => $report->number,
            'mobile' => $report->mobile,
            'provider_id' => $report->provider_id,
            'api_id' => $report->api_id,
            'txnid'  => $report->id,
            'payid'  => $report->payid,
            'refno'  => $report->refno,
            'status' => 'success',
            'rtype'  => 'commission',
            'via'    => $report->via,
            'trans_type' => "credit",
            'product' => $report->product
        ];
        
        $precommission = $report->charge;
        $provider = $report->provider_id;
        $parent   = User::where('id', $report->user->parent_id)->first(['id', 'mainwallet', 'scheme_id', 'role_id', 'parent_id']);

        if($parent->role->slug == "subadmin"){
            if($product == "collection"){
                $insert['balance']   = $parent->mainwallet;
            }else{
                $insert['balance']   = $parent->payoutwallet;
            }

            $insert['balance']   = $parent->mainwallet;
            $insert['user_id']   = $parent->id;
            $insert['credit_by'] = $report->user_id;
            $parentcommission    = \Myhelper::getCommission($report->amount, $parent->scheme_id, $provider, 'apiuser');
            $insert['amount']    = $precommission - $parentcommission;

            if($insert['amount'] > 0){
                if($product == "collection"){
                    User::where('id', $parent->id)->increment('mainwallet', $insert['amount']);
                    Report::create($insert);
                    Report::where('id', $report->id)->update(['disid' => $parent->id, "disprofit" => $insert['amount']]);
                }else{
                    User::where('id', $parent->id)->increment('payoutwallet', $insert['amount']);
                    Payoutreport::create($insert);
                    Payoutreport::where('id', $report->id)->update(['disid' => $parent->id, "disprofit" => $insert['amount']]);
                }
            }
        }
    }

    public static function getCommission($amount, $scheme, $slab, $role)
    {
        // Initialize commission variable
        $commission = 0;
        
        // Check if the scheme is active
        $myscheme = Scheme::where('id', $scheme)->first(['status']);
        if ($myscheme && $myscheme->status == "1") {
            // Retrieve commission data for the given scheme and slab
            $comdata = Commission::where('scheme_id', $scheme)->where('slab', $slab)->first();
            
            if ($comdata) {
                // Calculate commission based on type
                if ($comdata->type == "percent") {
                    $commission = $amount * $comdata['apiuser'] / 100;
                } else {
                    $commission = $comdata['apiuser'];
                }
            }
        }
        
        return $commission;
    }


    public static function curl($url , $method='GET', $parameters, $header, $log="no", $modal="none", $txnid="none")
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_TIMEOUT, 240);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        if($parameters != ""){
            curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        }

        if(sizeof($header) > 0){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if($log != "no"){
            Apilog::create([
                "url" => $url,
                "modal" => $modal,
                "txnid" => $txnid,
                "header" => $header,
                "request" => $parameters,
                "response" => $code."/".$err."/".$response
            ]);
        }

        if($log == "status"){
            Statuslog::create([
                "url" => $url,
                "modal" => $modal,
                "txnid" => $txnid,
                "header" => $header,
                "request" => $parameters,
                "response" => $code."/".$err."/".$response
            ]);
        }

        return ["response" => $response, "error" => $err, 'code' => $code];
    }

    public static function getParents($id)
    {
        $data = [];
        $user = User::where('id', $id)->first(['id', 'role_id']);
        if($user){
            $data[] = $id;
            switch ($user->role->slug) {
                case 'admin':
                    $whitelabels = \App\User::whereIn('parent_id', $data)->whereHas('role', function($q){
                        $q->where('slug', 'whitelable');
                    })->get(['id']);

                    if(sizeOf($whitelabels) > 0){
                        foreach ($whitelabels as $value) {
                            $data[] = $value->id;
                        }
                    }

                    $mds = \App\User::whereIn('parent_id', $data)->whereHas('role', function($q){
                        $q->where('slug', 'md');
                    })->get(['id']);

                    if(sizeOf($mds) > 0){
                        foreach ($mds as $value) {
                            $data[] = $value->id;
                        }
                    }
                    
                    $distributors = \App\User::whereIn('parent_id', $data)->whereHas('role', function($q){
                        $q->where('slug', 'distributor');
                    })->get(['id']);

                    if(sizeOf($distributors) > 0){
                        foreach ($distributors as $value) {
                            $data[] = $value->id;
                        }
                    }
                    
                    $retailers = \App\User::whereIn('parent_id', $data)->whereHas('role', function($q){
                        $q->whereIn('slug', ['retailer', 'apiuser', 'retaillite']);
                    })->get(['id']);

                    if(sizeOf($retailers) > 0){
                        foreach ($retailers as $value) {
                            $data[] = $value->id;
                        }
                    }
                    break;
                
                case 'subadmin':
                    $retailers = \App\User::whereIn('parent_id', $data)->whereHas('role', function($q){
                        $q->whereIn('slug', ['apiuser']);
                    })->get(['id']);

                    if(sizeOf($retailers) > 0){
                        foreach ($retailers as $value) {
                            $data[] = $value->id;
                        }
                    }
                    break;
            }
        }
        return $data;
    }
    
    public static function transactionRefund($id, $table, $wallet)
    {
        $report = \DB::table($table)->where('id', $id)->first();
        $count  = \DB::table($table)->where('user_id', $report->user_id)->where('status', 'refunded')->where('txnid', $report->id)->count();
        if($count == 0){
            $user = User::where('id', $report->user_id)->first(['id', $wallet]);
            if($report->trans_type == "debit"){
                if($report->product == "collection"){
                    User::where('id', $report->user_id)->increment($wallet,$report->amount - ($report->charge + $report->gst) + ($report->profit  - $report->tds));
                }else{
                    User::where('id', $report->user_id)->increment($wallet,$report->amount + ($report->charge + $report->gst) - ($report->profit  - $report->tds));
                }
            }else{
                if($report->product == "collection"){
                    User::where('id', $report->user_id)->decrement($wallet,$report->amount - ($report->charge + $report->gst) + ($report->profit  - $report->tds));
                }else{
                    User::where('id', $report->user_id)->decrement($wallet,$report->amount + ($report->charge + $report->gst) - ($report->profit  - $report->tds));
                }
            }
            $insert = [
                'number' => $report->number,
                'mobile' => $report->mobile,
                'provider_id' => $report->provider_id,
                'api_id' => $report->api_id,
                'apitxnid' => $report->apitxnid,
                'txnid' => $report->id,
                'payid' => $report->payid,
                'refno' => $report->refno,
                'description' => "Transaction Reversed, amount refunded",
                'remark' => $report->remark,
                'option1' => $report->option1,
                'option2' => $report->option2,
                'option3' => $report->option3,
                'option4' => $report->option4,
                'option5' => $report->option5,
                'option6' => $report->option6,
                'option7' => $report->option7,
                'option8' => $report->option8,
                'status' => 'refunded',
                'rtype' => $report->rtype,
                'via' => $report->via,
                'trans_type' => ($report->trans_type == "credit") ? "debit" : "credit",
                'product' => $report->product,
                'amount' => $report->amount,
                'profit' => $report->profit,
                'charge' => $report->charge,
                'gst' => $report->gst,
                'tds' => $report->tds,
                'balance' => $user->mainwallet,
                'user_id' => $report->user_id,
                'credit_by' => $report->credit_by,
                'adminprofit' => $report->adminprofit
            ];

            if($table == "reports"){
                Report::create($insert);
            }else{
                Payoutreport::create($insert);
            }

            $commissionReports = \DB::table($table)->where('rtype', 'commission')->where('txnid', $report->id)->get();
            foreach ($commissionReports as $report) {
                $user = User::where('id', $report->user_id)->first(['id', $wallet]);

                if($report->trans_type == "debit"){
                    User::where('id', $report->user_id)->increment($wallet, $report->amount - $report->profit);
                }else{
                    User::where('id', $report->user_id)->decrement($wallet, $report->amount - $report->profit);
                }
                
                $insert = [
                    'number'  => $report->number,
                    'mobile'  => $report->mobile,
                    'provider_id' => $report->provider_id,
                    'api_id'  => $report->api_id,
                    'apitxnid'=> $report->apitxnid,
                    'txnid'   => $report->id,
                    'payid'   => $report->payid,
                    'refno'   => $report->refno,
                    'description' => "Transaction Reversed, amount refunded",
                    'remark'  => $report->remark,
                    'option1' => $report->option1,
                    'option2' => $report->option2,
                    'option3' => $report->option3,
                    'option4' => $report->option4,
                    'option5' => $report->option5,
                    'option6' => $report->option6,
                    'option7' => $report->option7,
                    'option8' => $report->option8,
                    'status'  => 'refunded',
                    'rtype'   => $report->rtype,
                    'via'     => $report->via,
                    'trans_type' => ($report->trans_type == "credit") ? "debit" : "credit",
                    'product' => $report->product,
                    'amount'  => $report->amount,
                    'profit'  => $report->profit,
                    'charge'  => $report->charge,
                    'gst'     => $report->gst,
                    'tds'     => $report->tds,
                    'balance' => $user->mainwallet,
                    'user_id' => $report->user_id,
                    'credit_by'   => $report->credit_by,
                    'adminprofit' => $report->adminprofit
                ];
                if($table == "reports"){
                    Report::create($insert);
                }else{
                    Payoutreport::create($insert);
                }
            }
        }
    }

    public static function getTds($amount)
    {
        return $amount*5/100;
    }

    public static function callback($id)
    {
        //$report = Payoutreport::where('id', $id)->first();
        $report = Payoutreport::find($id);
        if (!$report) {
            $report = Report::find($id);
        }
        $callback['product'] = $report->product;
        $callback['status']  = $report->status;
        $callback['refno']   = $report->refno;
        $callback['txnid']   = $report->apitxnid;
        $query = http_build_query($callback);
        $url = $report->user->callbackurl."?".$query;

        $result = \Myhelper::curl($url, "GET", "", [], "no", "", "");
        LogCallback::create([
            'url' => $url,
            'response' => ($result['response'] != '') ? $result['response'] : $result['error'],
            'status'   => $result['code'],
            'product'  => $report->product,
            'user_id'  => $report->user_id,
            'transaction_id' => $report->id
        ]);
    }

    public static function FormValidator($rules, $post)
    {
        $validator = \Validator::make($post->all(), array_reverse($rules));
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $error = $value[0];
            }
            return response()->json(array(
                'statuscode' => 'ERR',
                'status' => 'ERR',
                'message'    => $error
            ));
        }else{
            return "no";
        }
    }
    
    public static  function encrypt($plainText, $key)
    {
        $secretKey = \Myhelper::hextobin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }
    
    public static function decrypt($encryptedText, $key) {
        $key = \Myhelper::hextobin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = \Myhelper::hextobin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }

    public static  function hextobin($hexString) {
        $length = strlen($hexString);
        $binString = "";
        $count = 0;
        while ($count < $length) {
            $subString = substr($hexString, $count, 2);
            $packedString = pack("H*", $subString);
            if ($count == 0) {
                $binString = $packedString;
            } else {
                $binString .= $packedString;
            }
    
            $count += 2;
        }
        return $binString;
    }
}