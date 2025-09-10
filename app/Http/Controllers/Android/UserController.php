<?php

namespace App\Http\Controllers\Android;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Model\Mahaagent;
use App\Model\Api;
use App\Model\Microatmreport;
use App\Model\Provider;
use App\Model\Fingagent;
use \App\Model\Securedata;
use App\Model\Aepsreport;
use App\Model\Companydata;
use App\Model\Pindata;
use App\Model\Upiload;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function login(Request $post)
    {
        $rules = array(
            'password' => 'required',
            'mobile'  =>'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = User::where('mobile', $post->mobile)->with(['role'])->first();
        if(!$user){
            return response()->json(['status' => 'ERR', 'message' => "Your aren't registred with us." ]);
        }
        
        if (!\Auth::validate(['mobile' => $post->mobile, 'password' => $post->password])) {
            return response()->json(['status' => 'ERR', 'message' => 'Username and password is incorrect']);
        }

        if (!\Auth::validate(['mobile' => $post->mobile, 'password' => $post->password, 'status' => "active"])) {
            return response()->json(['status' => 'ERR', 'message' => 'Your account currently de-activated, please contact administrator']);
        }

        $apptoken = Securedata::where('user_id', $user->id)->first();
        if(!$apptoken){
            do {
                $string = str_random(40);
            } while (Securedata::where("apptoken", "=", $string)->first() instanceof Securedata);

            try {
                $apptoken = Securedata::create([
                    'apptoken' => $string,
                    'ip'       => $post->ip(),
                    'user_id'  => $user->id,
                    'last_activity' => time()
                ]);
            } catch (\Exception $e) {
                return response()->json(['status' => 'ERR', 'message' => 'Already Logged In']);
            }
        }

        User::where('mobile', $post->mobile)->update(['device_id' => $post->device_id]);
        $user = \DB::table("users")->leftjoin('roles', "roles.id" , "users.role_id")->leftjoin('companies', "companies.id" , "users.company_id")->where('mobile', $post->mobile)->first(['users.id','users.name','users.email','users.mobile','users.company_id','users.address','users.shopname','users.city','users.state','users.pincode','users.pancard','users.aadharcard','users.kyc','users.resetpwd','users.account','users.bank','users.ifsc','users.mainwallet','roles.name as rolename','roles.slug as roleslug', 'companies.companyname']);
        $user->apptoken    = $apptoken->apptoken;
        $news  = Companydata::where('company_id', $user->company_id)->first();  
        
        if($news){
            $user->news = $news->news;
            $user->billnotice = $news->billnotice;
            $user->supportnumber = $news->number;
            $user->supportemail = $news->email;
        }
        $user->tokenamount = '107';
        $user->status = "TXN";
        
        $user->tpin = "no";
        if($this->pinbased() == "yes"){
            if(!\Myhelper::can('pin_check', $user->id)){
                $user->tpin  = "yes";
            }
        }
        return response()->json($user);
    }
    
    public function logout(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $delete = Securedata::where('user_id', $post->user_id)->where('apptoken', $post->apptoken)->delete();
        if($delete){
            return response()->json(['status' => 'TXN', 'message' => 'User Successfully Logout']);
        }else{
            return response()->json(['status' => 'ERR', 'message' => 'Something went wrong']);
        }
    }

    public function slide(Request $post)
    {
        $output['slides'] = \App\Model\PortalSetting::where('code', 'slides')->get();
        $output['company'] = \App\Model\Company::where('website', $_SERVER['HTTP_HOST'])->first();
        if($output['company']){
            $output['companydata'] = \App\Model\Companydata::where('company_id', $output['company']->id)->first();
        }
        return response()->json($output);
    }

    public function getbalance(Request $post)
    {
        $rules = array(
            'apptoken' => 'required',
            'user_id'  =>'required|numeric',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        $user = User::where('id', $post->user_id)->first(['mainwallet','payoutwallet','securewallet']);
        if($user){
            $output['status']  = "TXN";
            $output['message'] = "Balance Fetched Successfully";
            $output['slides']  = \App\Model\PortalSetting::where('code', 'slides')->get();
            $output['data']    = [ 
                "mainwallet" => $user->mainwallet,
                "payoutwallet" => $user->payoutwallet,
                "securewallet" => $user->securewallet,
            ];

            $output['virtual'] = Upiload::where("type", "payload")->where("user_id", $post->user_id)->first();
            if(!$output['virtual']){
                $url  = "https://api.verifyapi.in/api/v1/collection/virtual_account";
                $parameter = [
                    "mobile_number" => $user->mobile,
                    "email" => $user->email,
                    "name"  => $user->name,
                    "provider_id" => "174",
                ];
                
                $header = array(
                    'Content-Type: application/json',
                    'Token: 120|QVjIY3qg1FsoQa7QRu6Z56llA0d7HHMO75vYNpUV',
                    'Authorization: Bearer 120|QVjIY3qg1FsoQa7QRu6Z56llA0d7HHMO75vYNpUV'
                );
            
                $result   = \Myhelper::curl($url, 'POST', json_encode($parameter), $header, "yes", "Qr", $user->mobile); 
                $response = json_decode($result['response']);

                if(isset($response->status_id) && $response->status_id == "1"){
                    $post['refid'] = $response->virtual_account_number;
                    $post['upiid'] = $response->virtual_upi;
                    $post['user_id'] = \Auth::id();
                    $post['type'] = "payload";

                    if($post->type == "dynamic"){
                        $post['upi_string'] = "upi://pay?pn=Merchant&pa=".$post->upiid."@yesbankltd&cu=INR";
                    }else{
                        $post['upi_string'] = "upi://pay?pn=Merchant&pa=".$post->upiid."@yesbankltd&cu=INR";
                    }

                    Upiload::create($post->all());
                    $output['virtual'] = Upiload::where("type", "payload")->where("user_id", $post->user_id)->first();
                }
            }

            $product = [
                'payout',
                'collection'
            ];

            foreach ($product as $value) {
                switch ($value) {
                    case 'payout':
                        $query = \DB::table('payoutreports')->where("rtype", "main");
                        break;

                    case 'collection':
                        $query = \DB::table('reports')->where("rtype", "main");
                        break;
                }
                $query->where("user_id", $post->user_id);
                if((isset($post->fromdate) && !empty($post->fromdate)) && (isset($post->todate) && !empty($post->todate))){
                    if($post->fromdate == $post->todate){
                        $query->whereDate('created_at','=', Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'));
                    }else{
                        $query->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $post->todate)->addDay(1)->format('Y-m-d')]);
                    }
                }elseif (isset($post->fromdate) && !empty($post->fromdate)) {
                    $query->whereDate('created_at','=', Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'));
                }else{
                    $query->whereDate('created_at','=', date('Y-m-d'));
                }

                switch ($value) {
                    case 'payout':
                        $query->where('product', 'payout');
                        break;
                        
                    case 'collection':
                        $query->where('product', 'collection');
                        break;
                }
                $output[$value."amt"] = $query->where('status', "success")->sum("amount");
            }
        }else{
            $output['status'] = "ERR";
            $output['message'] = "User details not matched";
        }
        return response()->json($output);
    }

    public function getcommission(Request $post)
    {
        $user = User::where('id', $post->user_id)->first();
        $product = ['mobile', 'dth', 'electricity', 'pancard', 'dmt', 'aeps','matm', 'aadharpay'];
        foreach ($product as $key) {
            $commission = \App\Model\Commission::where('scheme_id', $user->scheme_id)->whereHas('provider', function ($q) use($key){
                $q->where('type' , $key);
            })->get();
            $mydata1 = [];
            foreach ($commission as $commissions){
                $mydata["value"] = $commissions[$user->role->slug];
                $mydata["name"] = $commissions->provider->name;
                $mydata["type"] = $commissions->type;
                
                $mydata1[] = $mydata;
            }
            
            $data[] = $mydata1;
        }
        return response()->json(['status' => "TXN", "key" => $product, "role" => $user->role->slug,"data" => $data]);
    }

    public function changePassword(Request $post)
    {
        $rules = array(
            'oldpassword' =>'required',
            'password'    =>'required|min:8|confirmed',
        );

        $validate = \Myhelper::FormValidator($rules, $post);
        if($validate != "no"){
            return $validate;
        }

        if(!\Myhelper::can('password_reset', $post->user_id)){
            return response()->json(['status' => "ERR" , "message" => "Permission Not Allowed"]);
        }

        $user = User::where('id', $post->user_id)->first();

        $credentials = [
            'mobile'   => $user->mobile,
            'password' => $post->oldpassword
        ];

        if(!\Auth::validate($credentials)){
            return response()->json(['status' => "ERR" , "message" =>  'Please enter corret old password']);
        }

        $post['password'] = bcrypt($post->password);
        $post['resetpwd'] = "changed";

        $user = User::updateOrCreate(['id'=> $post->user_id], $post->all());

        if($user){
            \LogActivity::addToLog("password-changed-app");
            $output['status']  = "TXN";
            $output['message'] = "Password Changed Successfully";
        }else{
            $output['status']  = "ERR";
            $output['message'] = "Something Went Wrong";
        }
        
        return response()->json($output);
    }
}
