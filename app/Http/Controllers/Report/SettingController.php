<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Model\Circle;
use App\Model\Role;

class SettingController extends Controller
{
    public function index($id=0)
    {
        if(\Myhelper::hasNotRole('admin')){
            if((\Auth::id() != $id) && !in_array($id, \Myhelper::getParents(\Auth::id()))){
                //abort(401);
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
                $q->where('slug', '!=', 'retailer');
            })->get(['id', 'name', 'role_id', 'mobile']);

            $data['roles']   = Role::where('slug' , '!=' , 'admin')->get();
        }else{
            $data['parents'] = [];
            $data['roles']   = [];
        }

        $data['state'] = Circle::all(['state']);
        return view('profile.index')->with($data);
    }

    public function profileUpdate(\App\Http\Requests\Member $post)
    {
        if(\Myhelper::hasNotRole(['admin'])){
            unset($post['mobile']);
            unset($post['alternate_mobile']);
            unset($post['mainwallet']);
        }

        \LogActivity::addToLog('Profile', $post);
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

            case 'mstock' :
            case 'dstock' :
            case 'rstock' :
                if(!\Myhelper::can('member_stock_manager')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                if(\Myhelper::hasNotRole(['admin'])){
                    if($post->mstock > 0 && \Auth::user()->mstock < $post->mstock){
                        return response()->json(['status'=>'Low id stock'], 400);
                    }

                    if($post->dstock > 0 && \Auth::user()->dstock < $post->dstock){
                        return response()->json(['status'=>'Low id stock'], 400);
                    }
        
                    if($post->rstock > 0 && \Auth::user()->rstock < $post->rstock){
                        return response()->json(['status'=>'Low id stock'], 400);
                    }
                }

                if($post->mstock != ''){
                    User::where('id', \Auth::id())->decrement('mstock', $post->mstock);
                    $response = User::where('id', $post->id)->increment('mstock', $post->mstock);
                }

                if($post->dstock != ''){
                    User::where('id', \Auth::id())->decrement('dstock', $post->dstock);
                    $response = User::where('id', $post->id)->increment('dstock', $post->dstock);
                }

                if($post->rstock != ''){
                    User::where('id', \Auth::id())->decrement('rstock', $post->rstock);
                    $response = User::where('id', $post->id)->increment('rstock', $post->rstock);
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
                    case 'retailer':
                        $roles = Role::where('id', $parent->role_id)->whereIn('slug', ['admin','distributor', 'md', 'whitelable'])->count();
                        break;

                    case 'distributor':
                        $roles = Role::where('id', $parent->role_id)->whereIn('slug', ['admin','md', 'whitelable'])->count();
                        break;
                    
                    case 'md':
                        $roles = Role::where('id', $parent->role_id)->whereIn('slug', ['admin','whitelable'])->count();
                        break;

                    case 'whitelable':
                        return response()->json(['status' => "Invalid mapping member"], 400);
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
                    case 'retaillite':
                        $roles = Role::where('id', $post->role_id)->whereIn('slug', ['distributor', 'md', 'whitelable','retailer'])->count();
                        break;
                        
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
                if($this->schememanager() == "admin" && \Myhelper::hasNotRole('admin')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                if($this->schememanager() == "all" && \Myhelper::hasRole('retailer')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                if($this->schememanager() == "admin"){
                    $users = \Myhelper::getParents($post->id);
                    User::whereIn('id', $users)->where('id', '!=', $post->id)->update(['scheme_id' => $post->scheme_id]);
                }

                $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], ['scheme_id' => $post->scheme_id]);
                break;

            case 'locakedAmount':
                if(!\Myhelper::can('locked_amount')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }

                $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], ['locakedAmount' => $post->lockedamount]);
                break;

            case 'kyc_change':
                if(!\Myhelper::can('member_kyc_update')){
                    return response()->json(['status' => "Permission Not Allowed"], 400);
                }
                $response = User::where('id', $post->id)->updateOrCreate(['id'=> $post->id], ['kyc' => $post->kyc]);
                break;
        }
        if($response){
            return response()->json(['status'=>'success'], 200);
        }else{
            return response()->json(['status'=>'fail'], 400);
        }
    }
}
