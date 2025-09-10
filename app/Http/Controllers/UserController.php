<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Model\Circle;
use App\Model\Role;
use App\Model\Pindata;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function welcome()
    {
        $data['company'] = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();
        if ($data['company']) {
            $data['companydata'] = \App\Model\Companydata::where('company_id', $data['company']->id)->first();
        }
        return view('front')->with($data);
    }

    public function policy()
    {
        $data['company'] = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();
        if ($data['company']) {
            $data['companydata'] = \App\Model\Companydata::where('company_id', $data['company']->id)->first();
        }
        return view('policy')->with($data);
    }

    public function refund()
    {
        $data['company'] = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();
        if ($data['company']) {
            $data['companydata'] = \App\Model\Companydata::where('company_id', $data['company']->id)->first();
        }
        return view('refund')->with($data);
    }

    public function term()
    {
        $data['company'] = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();
        if ($data['company']) {
            $data['companydata'] = \App\Model\Companydata::where('company_id', $data['company']->id)->first();
        }
        return view('terms')->with($data);
    }

    public function routechache()
    {
        \Artisan::call("route:cache");
    }

    public function loginpage(Request $get)
    {
        //\Artisan::call("route:cache");
        $data = [];
        $data['company'] = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();
        if ($data['company']) {
            $data['slides'] = \App\Model\PortalSetting::where('code', 'slides')->where('company_id', $data['company']->id)->get();
        }
        $data['roles'] = Role::whereIn('slug', ['whitelable', 'md', 'distributor', 'retailer'])->get();
        return view('welcome')->with($data);
    }


    public function login1(Request $request)
    {
        $rules = ['password' => 'required', 'email' => 'required|email'];
        $validate = \Validator::make($request->all(), $rules);

        if ($validate->fails()) {
            return response()->json(['status' => 'ERR', 'message' => $validate->errors()->first()]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'ERR', 'message' => "You're not registered with us."]);
        }

        if (!\Auth::validate(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(['status' => 'ERR', 'message' => 'Username or password is incorrect']);
        }

        // Verify OTP
        if ($request->has('otp')) {
            // Verify the provided OTP without resending it
            return $this->verifyOTP($user, $request);
        }


        // Send OTP if required
        else if ($user->otpverify != 'no' || !$request->has('otp')) {
            $otp = rand(100000, 999999);
            $subject = "Your OTP Code";
            $message = "Dear {$user->name},\n\nYour OTP is: {$otp}\n\nThank you!";
            $headers = "From: NanakPay<no-reply@nanakpay.com>\r\n";

            if (mail($user->email, $subject, $message, $headers)) {
                $user->update(['otpverify' => $otp]);
                return response()->json(['status' => 'TXNOTP', 'message' => 'OTP sent to your email.']);
            } else {
                return response()->json(['status' => 'ERR', 'message' => 'Error sending OTP. Try again later.']);
            }
        }

        // Fallback error
        return response()->json(['status' => 'ERR', 'message' => 'Something went wrong.']);
    }

    private function verifyOTP($user, $request)
    {
        // Match OTP
        if (\Auth::attempt([
            'mobile' => $user->mobile,
            'password' => $request->password,
            'otpverify' => $request->otp,
            'status' => "active"
        ])) {
            return response()->json(['status' => 'TXN'], 200);
        } else {
            return response()->json(['status' => 'ERR', 'message' => 'Invalid OTP. Please try again.'], 400);
        }
    }



    public function login(Request $post)
    {
        $rules = array(
            'password' => 'required',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no") {
            return $validate;
        }
        $data = $post;
        $user = User::where('email', $data->email)->first();
        if (!$user) {
            return response()->json(['status' => 'ERR', 'message' => "Your aren't registred with us."]);
        }

        // if($user->company->website != $_SERVER['HTTP_HOST']){
        //     return response()->json(['status' => 'ERR', 'message' => "Your aren't registred with us." ]);
        // }

        $company = \App\Model\Company::where('id', $user->company_id)->first();
        $otprequired = \App\Model\PortalSetting::where('code', 'otplogin')->first();

        if (!\Auth::validate(['email' => $data->email, 'password' => $data->password])) {
            return response()->json(['status' => 'ERR', 'message' => 'Username or password is incorrect']);
        }

        if (!\Auth::validate(['email' => $data->email, 'password' => $data->password, 'status' => "active"])) {
            return response()->json(['status' => 'ERR', 'message' => 'Your account currently de-activated, please contact administrator']);
        }

        // if($otprequired->value == "yes"){
        //     $otp = rand(111111, 999999);
        //     $mydata['otp']    = $otp;
        //     $mydata['mobile'] = $data->mobile;
        //     $mydata['name']   = $user->name;
        //     $mydata['email']  = $user->email;

        //     if($post->has('otp') && $post->otp == "resend"){
        //         if($user->otpresend < 3){
        //             $send = \Myhelper::notification("otp", $mydata);
        //             if($send == 'success'){
        //                 User::where('mobile', $data->mobile)->update(['otpverify' => \Myhelper::encrypt($otp, "sakshi##254d65d6"), 'otpresend' => $user->otpresend+1]);
        //                 return response()->json(['status' => 'TXNOTP', "message" => "Otp Sent Successfully"], 200);
        //             }else{
        //                 return response()->json(['status' => 'ERR', 'message' => 'Please contact your service provider provider']);
        //             }
        //         }else{
        //             return response()->json(['status' => 'ERR', 'message' => 'Otp resend limit exceed, please contact your service provider']);
        //         }
        //     }

        //     if($user->otpverify == "yes"){
        //         $send = \Myhelper::notification("otp", $mydata);
        //         if($send == 'success'){
        //             User::where('mobile', $data->mobile)->update(['otpverify' => \Myhelper::encrypt($otp, "sakshi##254d65d6")]);
        //             return response()->json(['status' => 'TXNOTP', "message" => "Otp Sent Successfully"], 200);
        //         }else{
        //             return response()->json(['status' => 'ERR', 'message' => 'Please contact your service provider provider']);
        //         }
        //     }else{
        //         if(!$post->has('otp')){
        //             return response()->json(['status' => 'TXNOTP', "message" => "Please use previous otp"], 200);
        //         }
        //     }

        //     try {
        //         $otpData = \CJS::decrypt($post->otp, $post["_token"]);   
        //     } catch (\Exception $e) {
        //         return response()->json(['status' => 'ERR', 'message' => 'Please contact your service provider provider']);
        //     }


        //     if (\Auth::attempt(['mobile' => $data->mobile, 'password' =>$data->password, 'otpverify' => \Myhelper::encrypt($otpData->otp, "sakshi##254d65d6"), 'status'=>"active"])){
        //         return response()->json(['status' => 'TXN'], 200);
        //     }else{
        //         return response()->json(['status' => 'ERR', 'message' => 'Please provide correct otp'], 400);
        //     }

        // }else{
        if (\Auth::attempt(['email' => $data->email, 'password' => $data->password, 'status' => "active"])) {
            session(['loginid' => $user->id]);
            return response()->json(['status' => 'TXN'], 200);
        } else {
            return response()->json(['status' => 'ERR', 'message' => 'Something went wrong, please contact administrator'], 400);
        }
        // }
    }


    public function logout(Request $request)
    {
        \Auth::guard()->logout();
        $request->session()->invalidate();
        return redirect('/');
    }

    public function passwordReset(Request $post)
    {
        $rules = array(
            'type' => 'required',
            'mobile'  => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no") {
            return $validate;
        }

        if ($post->type == "request") {
            \LogActivity::addToLog('Web-PWD-Reset', $post);
            $user = \App\User::where('mobile', $post->mobile)->first();
            if ($user) {
                $company = \App\Model\Company::where('id', $user->company_id)->first();

                $otp = rand(111111, 999999);
                $mydata['otp']    = $otp;
                $mydata['mobile'] = $post->mobile;
                $mydata['name']   = $user->name;
                $mydata['email']  = $user->email;
                $send = \Myhelper::notification("password", $mydata);

                if ($send == "success") {
                    \DB::table('password_resets')->insert([
                        'mobile' => $post->mobile,
                        'token' => $otp,
                        'ip'    => $post->ip(),
                        'useragent' => $_SERVER['HTTP_USER_AGENT'],
                        "last_activity" => time()
                    ]);

                    return response()->json(['status' => 'TXN', 'message' => "Password reset token sent successfully"], 200);
                } else {
                    return response()->json(['status' => 'ERR', 'message' => "Something went wrong"], 400);
                }
            } else {
                return response()->json(['status' => 'ERR', 'message' => "You aren't registered with us"], 400);
            }
        } else {
            \LogActivity::addToLog('Web-PWD-Reset-Submit', $post);
            $user = \DB::table('password_resets')->where('mobile', $post->mobile)->where('token', $post->token)->first();
            if ($user) {
                $update = \App\User::where('mobile', $post->mobile)->update(['password' => bcrypt($post->password)]);
                if ($update) {
                    \DB::table('password_resets')->where('mobile', $post->mobile)->where('token', $post->token)->delete();
                    return response()->json(['status' => "TXN", 'message' => "Password reset successfully"], 200);
                } else {
                    return response()->json(['status' => 'ERR', 'message' => "Something went wrong"], 400);
                }
            } else {
                return response()->json(['status' => 'ERR', 'message' => "Please enter valid token"], 400);
            }
        }
    }

    // public function registration(Request $post)
    // {
    //     $rules = array(
    //         'name'       => 'required',
    //         'mobile'     => 'required|numeric|digits:10|unique:users,mobile',
    //         'email'      => 'required|email|unique:users,email',
    //         'shopname'   => 'required|unique:users,shopname',
    //         'pancard'    => 'required|unique:users,pancard',
    //         'aadharcard' => 'required|numeric|unique:users,aadharcard|digits:12',
    //         'state'      => 'required',
    //         'city'       => 'required',
    //         'address'    => 'required',
    //         'pincode'    => 'required|digits:6|numeric'
    //     );

    //     $validate = \Myhelper::FormValidator($rules, $post);
    //     if($validate != "no"){
    //         return $validate;
    //     }   

    //     $company = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();
    //     $admin = User::whereHas('role', function ($q){
    //         $q->where('slug', 'subadmin');
    //     })->where("company_id", $company->id)->first();

    //     if(!$admin){
    //         $admin = User::whereHas('role', function ($q){
    //             $q->where('slug', 'admin');
    //         })->first(['id', 'company_id']);
    //     }

    //     if($admin->role->slug == "subadmin"){
    //         if($admin->stock < 1){
    //             return response()->json(['status' => 'ERR', 'message' => "Please contact administrator"], 400);
    //         }
    //     }

    //     $role = Role::where('slug', 'apiuser')->first();
    //     $post['role_id']    = $role->id;
    //     $post['id']         = "new";
    //     $post['parent_id']  = $admin->id;
    //     $post['password']   = bcrypt($post->mobile);
    //     $post['company_id'] = $admin->company_id;
    //     $post['status']     = "block";
    //     $post['kyc']        = "pending";
    //     $post['type']       = "web";

    //     $maxid = User::max('id');
    //     $post['agentcode'] = "PAPI".($maxid+20200);

    //     $scheme = \DB::table('default_permissions')->where('type', 'scheme')->where('role_id', $role->id)->first();
    //     if($scheme){
    //         $post['scheme_id'] = $scheme->permission_id;
    //     }

    //     $response = User::updateOrCreate(['id'=> $post->id], $post->all());
    //     if($response){
    //         if($admin->role->slug == "subadmin"){
    //             \DB::table("users")->where("id", $admin->id)->decrement("stock");
    //         }

    //         $permissions = \DB::table('default_permissions')->where('type', 'permission')->where('role_id', $post->role_id)->get();
    //         if(sizeof($permissions) > 0){
    //             foreach ($permissions as $permission) {
    //                 $insert = array('user_id'=> $response->id , 'permission_id'=> $permission->permission_id);
    //                 $inserts[] = $insert;
    //             }
    //             \DB::table('user_permissions')->insert($inserts);
    //         }

    //         $mydata['mobile'] = $response->mobile;
    //         $mydata['name']   = $response->name;
    //         $mydata['email']  = $response->email;
    //         $send = \Myhelper::notification("id", $mydata);

    //         return response()->json(['status' => "TXN", 'message' => "Success"], 200);
    //     }else{
    //         return response()->json(['status' => 'ERR', 'message' => "Something went wrong, please try again"], 400);
    //     }
    // }

    public function getotp(Request $post)
    {
        $rules = array(
            'mobile'  => 'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no") {
            return $validate;
        }

        $user = \App\User::where('mobile', $post->mobile)->first();
        if ($user) {
            $otp = rand(111111, 999999);
            $mydata['otp']    = $otp;
            $mydata['mobile'] = $post->mobile;
            $mydata['name']   = $user->name;
            $mydata['email']  = $user->email;
            $send = \Myhelper::notification("tpin", $mydata);

            if ($send == "success") {
                $user = \DB::table('password_resets')->insert([
                    'mobile' => $post->mobile,
                    'token' => \Myhelper::encrypt($otp, "sakshi##254d65d6"),
                    'last_activity' => time()
                ]);

                return response()->json(['status' => 'TXN', 'message' => "Pin generate token sent successfully"], 200);
            } else {
                return response()->json(['status' => 'ERR', 'message' => "Something went wrong"], 400);
            }
        } else {
            return response()->json(['status' => 'ERR', 'message' => "You aren't registered with us"], 400);
        }
    }

    public function setpin(Request $post)
    {
        $rules = array(
            'id'  => 'required|numeric',
            'otp' => 'required|numeric',
            'pin' => [
                'required',
                'numeric',
                'digits:6',
                'confirmed',
                Rule::notIn(['123456']),
            ]
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if ($validate != "no") {
            return $validate;
        }

        $user = \DB::table('password_resets')->where('mobile', $post->mobile)->where('token', \Myhelper::encrypt($post->otp, "sakshi##254d65d6"))->first();
        if ($user) {
            try {
                Pindata::where('user_id', $post->id)->delete();
                $apptoken = Pindata::create([
                    'pin' => \Myhelper::encrypt($post->pin, "sakshi##254d65d6"),
                    'user_id'  => $post->id
                ]);
            } catch (\Exception $e) {
                return response()->json(['status' => 'ERR', 'message' => 'Try Again']);
            }

            if ($apptoken) {
                \DB::table('password_resets')->where('mobile', $post->mobile)->where('token', \Myhelper::encrypt($post->otp, "sakshi##254d65d6"))->delete();
                return response()->json(['status' => "success"], 200);
            } else {
                return response()->json(['status' => "Something went wrong"], 400);
            }
        } else {
            return response()->json(['status' => "Please enter valid otp"], 400);
        }
    }

    public function paytm(Request $post)
    {
        $orderid = "ORDS" . rand(10000, 99999999);
        $paytmParams = array();
        $paytmParams["body"] = array(
            "requestType" => "Payment",
            "mid"  => "ebStBv07499574784721",
            "websiteName"  => "WEB",
            "orderId"  => $orderid,
            "callbackUrl"  => "https://login.apisseva.com/callback/collection/paytm",
            "txnAmount"  => array(
                "value"  => "1.00",
                "currency" => "INR",
            ),
            "userInfo" => array(
                "custId" => "CUST12",
            )
        );
        $checksum = \Paytm::getChecksumFromString(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), "ywG8s9tgLgStYsQG");

        $paytmParams["head"] = array(
            "signature" => $checksum
        );

        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

        $url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=ebStBv07499574784721&orderId=" . $orderid;

        $result = \Myhelper::curl($url, 'POST', $post_data, array("Content-Type: application/json"), 'yes',  $post->orderid);

        dd($url, $paytmParams, $result);
        if ($result['response'] != '') {
            $response = json_decode($result['response']);

            if (isset($response->body->resultInfo->resultCode) && $response->body->resultInfo->resultCode == "0000") {
                $paramList['PAYTMURL'] = $api->url . "?mid=" . $api->username . "&orderId=" . $post->orderid;
                $paramList['data']["mid"] = $api->username;
                $paramList['data']["orderId"] = $post->orderid;
                $paramList['data']["txnToken"] = $response->body->txnToken;
                return response()->json(['status' => "success", 'message' => view('fund.paytm')->with($paramList)->render()]);
            } else {
                return response()->json(['status' => "failed", 'message' => "Something went wrong, please try again"]);
            }
        } else {
            return response()->json(['status' => "failed", 'message' => "Something went wrong, please try again"]);
        }
    }

    public function updateThemeMode(Request $request)
    {
        \Log::info('Request received:', $request->all()); // Logs the request data

        $request->validate([
            'theme_mode' => 'required|string|in:dark,light',
        ]);

        $user = auth()->user();
        if ($user) {
            $user->theme_mode = $request->theme_mode;
            $user->save();

            return response()->json(['message' => 'Theme mode updated successfully.']);
        }

        return response()->json(['message' => 'User not authenticated.'], 401);
    }
}
