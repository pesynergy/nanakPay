<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Model\Circle;
use App\Model\Role;
use App\Model\Company;

class SettingController extends Controller
{
    public function index($id=0)
    {
        if(\Myhelper::hasNotRole('admin')){
            if($id != 0 && (\Auth::id() != $id) && !in_array($id, \Myhelper::getParents(\Auth::id()))){
                abort(401);
            }
        }
        
        $data = [];
        if($id != 0){
            $data['user'] = User::find($id);
        }else{
            $data['user'] = \Auth::user();
        }

        if(\Myhelper::hasRole('admin')){
            $data['parents'] = User::whereHas('role', function ($q){
                $q->where('slug', '!=', 'apiuser');
            })->get(['id', 'name', 'role_id', 'mobile']);

            $data['roles']   = Role::where('slug' , '!=' , 'admin')->get();
        }else{
            $data['parents'] = [];
            $data['roles']   = [];
        }

        $data['company'] = Company::all();
        return view('profile.index')->with($data);
    }

    public function profileUpdate(\App\Http\Requests\Member $post)
    {
        if(\Myhelper::hasNotRole(['admin'])){
            unset($post['mobile']);
            unset($post['alternate_mobile']);
            unset($post['mainwallet']);
        }

        if(\Myhelper::hasRole(['whitelable', 'md', 'distributor', 'retailer', 'retaillite'])){
            if((\Auth::id() != $post->id) && !in_array($post->id, \Myhelper::getParents(\Auth::id()))){
                return response()->json(['status' => "Permission Not Alloweds"], 400);
            }
        }

        switch ($post->actiontype) {
            case 'kycdata':
                $user = User::where('id', $post->id)->first();

                if($user->kyc != "verified"){
                    try {
                        unlink(public_path('kyc/useronboard'.\Auth::id()));
                    } catch (\Exception $e) {
                    }

                    if($post->hasFile('aadharcardpicfronts')){
                        $post['aadharcardpicfront'] = $post->file('aadharcardpicfronts')->store('kyc/useronboard'.\Auth::id());
                    }
                    if($post->hasFile('aadharcardpicbacks')){
                        $post['aadharcardpicback'] = $post->file('aadharcardpicbacks')->store('kyc/useronboard'.\Auth::id());
                    }
                    if($post->hasFile('gstpics')){
                        $post['gstpic'] = $post->file('gstpics')->store('kyc/useronboard'.\Auth::id());
                    }
                    if($post->hasFile('pancardpics')){
                        $post['pancardpic'] = $post->file('pancardpics')->store('kyc/useronboard'.\Auth::id());
                    }
                    if($post->hasFile('passbooks')){
                        $post['passbook'] = $post->file('passbooks')->store('kyc/useronboard'.\Auth::id());
                    }
                    if($post->hasFile('msmes')){
                        $post['msme'] = $post->file('msmes')->store('kyc/useronboard'.\Auth::id());
                    }
                    if($post->hasFile('otherdocs')){
                        $post['otherdoc'] = $post->file('otherdocs')->store('kyc/useronboard'.\Auth::id());
                    }

                    $response    = User::updateOrCreate(['id'=> $post->id], [
                        'aadharcardpicfront' => $post->aadharcardpicfront,
                        'aadharcardpicback' => $post->aadharcardpicback,
                        'gstpic'     => $post->gstpic,
                        'pancardpic' => $post->pancardpic,
                        'passbook'   => $post->passbook,
                        'msme' => $post->msme,
                        'otherdoc'   => $post->otherdoc,
                        "kyc"  => "submitted"
                    ]);
                }else{
                    $response = false;
                }
                break;

            case 'gateway':
                $response = User::where("id", \Auth::id())->update([
                    'merchant_key' => $post->merchant_key,
                    'merchant_id'  => $post->merchant_id,
                    'merchant_upi' => $post->merchant_upi
                ]);
                break;

            case 'password':
                if(($post->id != \Auth::id()) && !\Myhelper::can('member_password_reset')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                if(($post->id == \Auth::id()) && !\Myhelper::can('password_reset')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                if(\Myhelper::hasNotRole('admin')){
                    $credentials = [
                        'mobile'   => \Auth::user()->mobile,
                        'password' => $post->oldpassword
                    ];
            
                    if(!\Auth::validate($credentials)){
                        return response()->json(['errors' =>  ['oldpassword'=>'Please enter corret old password']], 422);
                    }
                }

                $post['password'] = bcrypt($post->password);
                $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], ['password' => $post->password, "resetpwd" => "changed"]);
                break;
            
            case 'profile':
                if(($post->id != \Auth::id()) && !\Myhelper::can('member_profile_edit')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                if(($post->id == \Auth::id()) && !\Myhelper::can('profile_edit')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }
                $post['kyc'] = "verified";
                $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], $post->all());
                break;

            case 'stock' :
                if(!\Myhelper::can('member_stock_manager')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                if($post->stock != ''){
                    $response = User::where('id', $post->id)->increment('stock', $post->stock);
                }
                $response = true;
                break;

            case 'mapping':
                if(\Myhelper::hasNotRole('admin')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }
                $user = User::find($post->id);
                $parent = User::find($post->parent_id);

                if($parent->role->slug == "retailer"){
                    return response()->json(['status' => "Invalid mapping member"], 400);
                }

                switch ($user->role->slug) {
                    case 'apiuser':
                        $roles = Role::where('id', $parent->role_id)->whereIn('slug', ['admin','subadmin'])->count();
                        break;
                }

                if(!$roles){
                    return response()->json(['status' => "Invalid mapping member"], 400);
                }
                $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], ['parent_id' => $post->parent_id]);
                break;

            case 'rolemanager':
                if(\Myhelper::hasNotRole('admin')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                $roles = Role::where('id', $post->role_id)->whereIn('slug', ['admin'])->count();
                if($roles){
                    return response()->json(['status' => "Invalid member role"], 400);
                }

                $user = User::find($post->id);
                switch ($user->role->slug) {
                    case 'retailer':
                        $roles = Role::where('id', $post->role_id)->whereIn('slug', ['distributor', 'md', 'whitelable', 'retaillite'])->count();
                        break;

                    case 'distributor':
                        $roles = Role::where('id', $post->role_id)->whereIn('slug', ['md', 'whitelable','retailer', 'retaillite'])->count();
                        break;
                    
                    case 'md':
                        $roles = Role::where('id', $post->role_id)->whereIn('slug', ['whitelable','distributor','retailer', 'retaillite'])->count();
                        break;

                    case 'whitelable':
                        $roles = Role::where('id', $post->role_id)->whereIn('slug', ['distributor','retailer','md', 'retaillite'])->count();
                        break;
                }

                if(!$roles){
                    return response()->json(['status' => "Invalid member role"], 400);
                }
                $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], ['role_id' => $post->role_id]);
                break;

            case 'scheme':
                if($this->schememanager() == "admin" && !\Myhelper::can('member_scheme_update')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], ['scheme_id' => $post->scheme_id]);
                break;

            case 'locakedAmount':
                if(!\Myhelper::can('locked_amount')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], ['lockedamount' => $post->lockedamount]);
                break;

            case 'bankdata':
                if(!\Myhelper::can('member_bank_change')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }
                $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], ['account' => $post->account, 'bank' => $post->bank, 'ifsc' => $post->ifsc]);
                break;

            case 'kyc_change':
                if(!\Myhelper::can('member_kyc_update')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }
                $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], ['kyc' => $post->kyc]);
                break;

            case 'callbackurl':
                $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], ['callbackurl' => $post->callbackurl, 'payout_callback' => $post->payout_callback]);
                break;
        }
        if($response){
            return response()->json(['status'=>'success'], 200);
        }else{
            return response()->json(['status'=>'fail'], 400);
        }
    }
}
