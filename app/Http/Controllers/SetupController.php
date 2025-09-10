<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Fundbank;
use App\Model\Api;
use App\Model\Provider;
use App\Model\Apitoken;
use App\Model\PortalSetting;
use App\Model\Contact;
use App\Model\Complaintsubject;
use App\Model\ServiceManager;
use Illuminate\Validation\Rule;
use App\User;

class SetupController extends Controller
{
    public function index($type, $id=0)
    {
        $file = $type;
        $data['type'] = $type;

        switch ($type) {
            case 'api':
                $permission = "setup_api";
                break;

            case 'apitoken':
                $permission = "setup_apitoken";
                break;

            case 'payoutbank':
                $permission = "setup_payoutbank";
                break;

            case 'bank':
                $permission = "setup_bank";
                break;

            case 'operator':
                $permission = "setup_operator";
                $data['apis'] = Api::where('status', '1')->get(['id', 'product']);
                break;

            case 'servicemanage-payout':
            case 'servicemanage-collection':
                $permission = "service_manager";
                $file = "servicemanage";

                $split = explode("-", $type);
                $data['id']   = $id;
                $data['type'] = $split[1];
                $data['user'] = User::find($id);
                $data['services'] = ServiceManager::where("user_id", $id)->get();

                if($split[1] == "qr"){
                    $apitype = "collection";
                }else{
                    $apitype = $split[1];
                }
                $data['apis'] = Api::where("type", $apitype)->get(['id', 'product']);
                break;
            
            case 'complaintsub':
                $permission = "complaint_subject";
                break;

            case 'portalsetting':
                $data['otplogin'] = PortalSetting::where('code', 'otplogin')->first();
                $data['payoutsuccess'] = \App\Model\PortalSetting::where('code', 'payoutsuccess')->first();
                $data['utrcode']    = \App\Model\PortalSetting::where('code', 'utrcode')->first();
                $permission = "portal_setting";
                break;
            
            default:
                abort(404);
                break;
        }

        if (!\Myhelper::can($permission)) {
            abort(403);
        }
        return view("setup.".$file)->with($data);
    }

    public function update(Request $post)
    {
        switch ($post->actiontype) {
            case 'api':
                $permission = "setup_api";
                break;

            case 'apitoken':
                $permission = "setup_apitoken";
                break;

            case 'payoutbank':
                $permission = "setup_payoutbank";
                break;

            case 'bank':
                $permission = "setup_bank";
                break;

            case 'operator':
                $permission = "setup_operator";
                break;

            case 'complaintsub':
                $permission = "complaint_subject";
                break;

            case 'portalsetting':
                $permission = "portal_setting";
                break;

            case 'slides':
                $permission = "change_company_profile";
                break;
                
            case 'banklistupdate':
                $permission = "setup_banklistupdate";
                break;

            case 'operatorimport':
                $permission = "import";
                break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
        }

        switch ($post->actiontype) {
            case 'bank':
                $rules = array(
                    'name'    => 'sometimes|required',
                    'account'    => 'sometimes|required|numeric|unique:fundbanks,account'.($post->id != "new" ? ",".$post->id : ''),
                    'ifsc'    => 'sometimes|required',
                    'branch'    => 'sometimes|required'  
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                $post['user_id'] = \Auth::id();
                $action = Fundbank::updateOrCreate(['id'=> $post->id], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;
            
            case 'api':
                $rules = array(
                    'product'    => 'sometimes|required',
                    'name'    => 'sometimes|required',
                    'code'    => 'sometimes|required|unique:apis,code'.($post->id != "new" ? ",".$post->id : '')
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $action = Api::updateOrCreate(['id'=> $post->id], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'apitoken':
                $rules = array(
                    'status'    => 'required'
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $action = Apitoken::updateOrCreate(['id'=> $post->id], ['status' => $post->status]);
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'payoutbank':
                $rules = array(
                    'status'    => 'required'
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $action = Contact::updateOrCreate(['id'=> $post->id], ['status' => $post->status]);
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'operator':
                $rules = array(
                    'name' => 'sometimes|required',
                    'recharge1' => 'sometimes|required',
                    'recharge2' => 'sometimes|required',
                    'type' => ['sometimes', 'required'],
                    'api_id' => 'sometimes|required|numeric',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $action = Provider::updateOrCreate(['id'=> $post->id], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'servicemanage':
                $rules = array(
                    'api_id' => 'required',
                    'provider_id' => 'required',
                    'payee_id' => 'required',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                $post['user_id'] = $post->payee_id;
                $action = ServiceManager::updateOrCreate(['provider_id'=> $post->provider_id, "user_id" => $post->user_id], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'servicemanageall':
                $rules = array(
                    'type'    => 'required',
                    'api_id'  => 'required',
                    'payee_id' => 'required',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $post['user_id'] = $post->payee_id;
                $providers = Provider::where('type', $post->type)->get();

                foreach ($providers as $provider) {
                    ServiceManager::updateOrCreate(['provider_id'=> $provider->id, "user_id" => $post->user_id], $post->all());
                }
                return response()->json(['status' => "success"], 200);
                break;

            case 'complaintsub':
                $rules = array(
                    'subject'    => 'sometimes|required',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $action = Complaintsubject::updateOrCreate(['id'=> $post->id], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'portalsetting':
                $rules = array(
                    'value'    => 'required',
                    'name'     => 'required',
                    'code'     => 'required',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                if($post->hasFile('slides')){
                    $post['value'] = $post->file('slides')->store('slides');
                }
                $action = PortalSetting::updateOrCreate(['code'=> $post->code], $post->all());;
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;
                
            case 'slides':
                $rules = array(
                    'value'    => 'sometimes|required',
                    'name'     => 'required',
                    'code'     => 'required',
                );
                $post['company_id'] = \Auth::user()->company_id;
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                if($post->hasFile('slides')){
                    $post['value'] = $post->file('slides')->store('slides');
                }
                $post['name'] = "Login Slide ".date('ymdhis');
                $action = PortalSetting::updateOrCreate(['name'=> $post->name], $post->all());
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;
                
            case 'qrcode':
                $rules = array(
                    'value'    => 'sometimes|required',
                    'name'     => 'required',
                    'code'     => 'required',
                );
                $post['company_id'] = \Auth::user()->company_id;
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }
                // if($post->hasFile('file')){
                //     $post['value'] = $post->file('qrcodes')->store('file');
                // }
                $QRCodeReader = new \Libern\QRCodeReader\QRCodeReader();
                $qrcode_text = $QRCodeReader->decode($post->file('file'));

                $data   = explode("?", $qrcode_text);
                $qrdatas = explode("&", $data[1]);

                foreach ($qrdatas as $qrdata) {
                    if (str_contains($qrdata, 'pa')) { 
                        $qrString = explode("=",$qrdata);

                        return $qrString[1];
                    }
                }
                break;
                
            case 'banklistupdate':
                $rules = array(
                    'value'    => 'sometimes|required',
                    'name'     => 'required',
                    'code'     => 'required',
                );
                
                switch ($post->value) {
                    case 'aeps':
                        return $this->cwbankupdate();
                        break;

                    case 'aadharpay':
                        return $this->apbankupdate();
                        break;
                }
                break;

            case 'operatorimport':
                $action = \Excel::import(new \App\Imports\BillProvider, $post->file("import"));
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;

            case 'servicemanage':
                $rules = array(
                    'api_id' => 'required',
                    'provider_id' => 'required',
                    'payee_id' => 'required',
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
                }

                $post['user_id'] = $post->payee_id;

                if($post->api_id == 0){
                    $action = ServiceManager::where(['provider_id'=> $post->provider_id, "user_id" => $post->user_id])->delete();   
                }else{
                    $action = ServiceManager::updateOrCreate(['provider_id'=> $post->provider_id, "user_id" => $post->user_id], $post->all());
                }

                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
                break;
            
            default:
                # code...
                break;
        }
    }
}
