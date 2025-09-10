<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Model\Permission;
use \App\Model\Role;
use \App\Model\Scheme;
use App\User;

class RoleController extends Controller
{
    public function index($type)
    {
			$data  = [];
			$types = array(
	            'Resource'    => 'resource',
	            'Setup Tools' => 'setup',
	            'Member'      => 'member',
	            'Wallet Fund'     => 'fund',
	            'Portal Services' => 'service',
	            'User Setting'  => 'setting',
	            'Transactions'  => 'report'
	        );

	        foreach ($types as $key => $value) {
	            $data['permissions'][$key] = Permission::where('type', $value)->orderBy('id', 'ASC')->get();
	        }
			$data['scheme'] = Scheme::where('user_id', \Auth::id())->get();
			return view('tools.'.$type)->with($data);
    }

    public function store(Request $post, $type)
    {
    	if($type == "roles"){
    	    if($post->id != "new"){
    	        $role = \App\Model\Role::where('id', $post->id)->whereIn('slug', ['admin', 'whitelable', 'md', 'distributor', 'retailer', 'retaillite'])->first();
    	        
    	        if($role){
    	            unset($post['slug']);
    	        }
    	    }
			$data = \App\Model\Role::query();
		}else{
			$data = \App\Model\Permission::query();
		}

    	$response = $data->updateOrCreate(['id'=> $post->id], $post->all());
    	if($response){
    		return response()->json(['status'=>'success'], 200);
    	}else{
    		return response()->json(['status'=>'fail'], 400);
    	}
    }

	public function assignPermissions(Request $post)
	{
		if (!\Myhelper::can('member_permission_change')){
			return response()->json(['status' => "Permission not allowed"],400);
		}

		if ($post->has('role_id')) {
			\DB::table('default_permissions')->where('type', $post->type)->where('role_id', $post->role_id)->delete();
		}else{
			\DB::table('user_permissions')->where('user_id', $post->payee_id)->delete();
		}
		
		if(!$post->has('permissions')){
			$post['permissions'] = array();
		}
		if(sizeOf($post->permissions)){
			foreach ($post->permissions as $value) {
				if ($post->has('role_id')) {
					$insert = array('role_id'=> $post->role_id , 'permission_id'=> $value, 'type'=> $post->type);
				}else{
					$insert = array('user_id'=> $post->payee_id , 'permission_id'=> $value);
				}
				$inserts[] = $insert;
			}

			if ($post->has('role_id')) {
				$response = \DB::table('default_permissions')->insert($inserts);
			}else{
				$response = \DB::table('user_permissions')->insert($inserts);
			}
			
			if($response){
	    		return response()->json(['status'=>'success'], 200);
	    	}else{
	    		return response()->json(['status'=>'fail'], 400);
	    	}
		}else{
			return response()->json(['status'=>'success'], 200);
		}
	}
	
	public function setPermissions(Request $post)
	{
	    //dd($post->all());
		if (!\Myhelper::can('admin')){
			return response()->json(['status' => "Permission not allowed"],400);
		}

		if(!$post->has('permissions')){
			$post['permissions'] = array();
		}
		   
		$role = Role::find($post->role_id);
		if(in_array($role->slug, ['whitelable', 'md', 'distributor', 'retailer', 'retaillite'])){
		    $post['permissions'] = array_diff($post->permissions, [12,13,17,18,21,22,23,28,34,35,36,49,62, 95,103,104,105,106,107,108]);
		}
		
		//dd($post->permissions);
		$users = User::where('role_id', $post->role_id)->get();
		$roles = [];
		foreach ($users as $user) {
		    $roles[] = $user->id;
		}
		
		if(sizeOf($post->permissions)){
		    if($post->action == "add"){
    			foreach ($post->permissions as $value) {
    			    foreach ($users as $user) {
        				$insert = array('user_id'=> $user->id , 'permission_id'=> $value);
        				$inserts[] = $insert;
    			    }
    			}
    			
    			//dd($inserts);
    			$response = \DB::table('user_permissions')->whereIn('permission_id', $post->permissions)->whereIn('user_id', $roles)->delete();
    			$response = \DB::table('user_permissions')->insert($inserts);
		    }else{
                $response = \DB::table('user_permissions')->whereIn('permission_id', $post->permissions)->whereIn('user_id', $roles)->delete();
		    }
			
			if($response){
	    		return response()->json(['status'=>'success'], 200);
	    	}else{
	    		return response()->json(['status'=>'fail'], 400);
	    	}
		}else{
			return response()->json(['status'=>'success'], 200);
		}
	}
	

	public function getpermissions($id)
	{
		return \DB::table('user_permissions')->where('user_id', $id)->get()->toJson();
	}

	public function getdefaultpermissions($id)
	{
		return \DB::table('default_permissions')->where('type', 'permission')->where('role_id', $id)->get()->toJson();
	}
}
