<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Model\Utiid;

class PortalController extends Controller
{
    public function index($type)
    {
        $data['type'] = $type;
        switch ($type) {
            case 'uti':
                $permission = 'utiid_request';
                break;

            default:
                abort(404);
                break;
        }

        if (!\Myhelper::can($permission)) {
            abort(403);
        }
        $data['users'] = User::whereIn('id', session('parentData'))->get(['id', 'name', 'mobile']);
        return view('portal.'.$type)->with($data);
    }

    public function logs($type)
    {
        return view('portal.'.$type.'logs');
    }

    public function create(Request $post)
    {
        $rules = array(
            'vleid'    => 'required|unique:utiids,vleid',
            'vlepassword'    => 'sometimes|required',
            'name'    => 'required',
            'location'    => 'required',
            'contact_person'    => 'required',
            'pincode'    => 'required|numeric|digits:6',
            'state'    => 'required',
            'email'    => 'required',
            'mobile'    => 'required|numeric|digits_between:10,11',
        );
        
        $validator = \Validator::make($post->all(), $rules);
        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $key => $value) {
                $error = $value[0];
            }
            return response()->json(['status' => $error], 400);
        }
        $post['sender_id'] = \Auth::id();

        $action = Utiid::updateOrCreate(['id'=> $post->id], $post->all());
        if ($action) {
            return response()->json(['status' => "success"], 200);
        }else{
            return response()->json(['status' => "Task Failed, please try again"], 200);
        }
    }
}
