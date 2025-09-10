<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Apitoken;
use App\Model\Permission;
use App\User;

class ApiController extends Controller
{
    public function index($type)
    {
        $data['type']  = $type;
        $data['services'] = Permission::where('type', "service")->orderBy('id', 'ASC')->get();
        $data['user_permissions'] = \DB::table("user_permissions")->where("user_id", \Auth::id())->get();
        return view("apitools.".$type)->with($data);
    }

    public function update(Request $post)
    {
        if (\Myhelper::hasNotRole('apiuser')) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
        }
        $rules = array(
            'ip'  => 'required'
        );
        
        $validator = \Validator::make($post->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        do {
            $post['token'] = bin2hex(random_bytes(15));
        } while (Apitoken::where("token", "=", $post->token)->first() instanceof Apitoken);

        $post['user_id'] = \Auth::id();
        $action = Apitoken::updateOrCreate(['id'=> $post->id], $post->all());
        if ($action) {
            return response()->json(['status' => "success"], 200);
        }else{
            return response()->json(['status' => "Task Failed, please try again"], 200);
        }
    }
}
