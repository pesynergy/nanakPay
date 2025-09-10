<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Model\Role;
use App\Model\Circle;
use App\Model\Scheme;
use App\Model\Company;
use App\Model\Provider;
use App\Model\Permission;
use App\User;
use App\Model\Commission;

class MemberController extends Controller
{
    public function index($type , $action="view")
    {
        if($action != 'view' && $action != 'create'){
            abort(404);
        }

        $data['role'] = Role::where('slug', $type)->first();
        $data['roles'] = [];
        if(!$data['role'] && !in_array($type, ['other', 'web', 'kycpending', 'kycsubmitted', 'kycrejected'])){
            abort(404);
        }
        
        if($action == "view" && !\Myhelper::can('view_'.$type)){
            abort(401);
        }elseif($action == "create" && !\Myhelper::can('create_'.$type) && !in_array($type, ['kycpending', 'kycsubmitted', 'kycrejected'])){
            abort(401);
        }

        if($action == "create"){
            $roles = Role::whereIn('slug', ["apiuser", "subadmin", "mis"])->get();

            foreach ($roles as $role) {
                if(\Myhelper::can('create_'.$role->slug)){
                    $data['roles'][] = $role;
                }
            }
        }
        
        if ($action == "create" && (!$data['role'] && sizeOf($data['roles']) == 0)){
            abort(404);
        }
        
        $data['type'] = $type;
        $data['company'] = Company::all();
        $data['scheme']  = Scheme::where('user_id', \Auth::id())->get();
        $types = array(
            'Resource'    => 'resource',
            'Setup Tools' => 'setup',
            'Member'      => 'member',
            'Wallet Fund'   => 'fund',
            'Portal Services' => 'service',
            'User Setting'  => 'setting',
            'Transactions'  => 'report'
        );
        
        foreach ($types as $key => $value) {
            if(\Myhelper::hasRole("admin")){
                $data['permissions'][$key] = Permission::where('type', $value)->orderBy('id', 'ASC')->get();
            }else{    
                $data['permissions'][$key] = Permission::where('type', $value)->whereNotIn("id", [12,13,17,18,21,22,23,28,34,35,36,49,62,95,103,104,105,106,107,108])->orderBy('id', 'ASC')->get();
            }
        }

        if($action == "view"){
            return view('member.index')->with($data);
        }else{
            return view('member.create')->with($data);
        }
    }

    public function completeProfile()
    {
        return view("profile.completeKyc");
    }

    public function create(\App\Http\Requests\Member $post)
    {
        \LogActivity::addToLog('Member-create', $post);
        
        $role = Role::where('id', $post->role_id)->first();
        if(!\Myhelper::can('create_'.$role->slug)){
            return response()->json(['status' => "Permission not allowed"],200);
        }

        $post['id'] = "new";
        $post['parent_id'] = \Auth::id();
        $post['kyc'] = "pending";
        $post['password'] = bcrypt($post->mobile);

        $maxid = User::max('id');
        $post['agentcode'] = "PAPI".($maxid+20200);

        if(!$post->has("company_id")){
            $post['company_id'] = \Auth::user()->company_id;
        }
        
        $response = User::updateOrCreate(['id'=> $post->id], $post->all());
        if($response){
            $permissions = \DB::table('default_permissions')->where('type', 'permission')->where('role_id', $post->role_id)->get();
            if(sizeof($permissions) > 0){
                foreach ($permissions as $permission) {
                    $insert = array('user_id'=> $response->id , 'permission_id'=> $permission->permission_id);
                    $inserts[] = $insert;
                }
                \DB::table('user_permissions')->insert($inserts);
                $mydata['mobile'] = $response->mobile;
                $mydata['name']   = $response->name;
                $mydata['email']  = $response->email;
                $send = \Myhelper::notification("id", $mydata);
            }
            return response()->json(['status'=>'success'], 200);
        }else{
            return response()->json(['status'=>'fail'], 400);
        }
    }

    public function getCommission(Request $post)
    {
        $product = [
            "Payout"     => ['payout'],
            "Collection" => ['collection'],
            "Paytm Collection" => ['paytmcollection']
        ];

        foreach ($product as $key => $value) {
            $data['commission'][$key] = Commission::where('scheme_id', $post->scheme_id)->whereHas('provider', function ($q) use($value){
                $q->whereIn('type' , $value);
            })->get();
        }

        return response()->json(view('member.commission')->with($data)->render());
    }
}
