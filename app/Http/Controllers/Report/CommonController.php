<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Model\Report;
use App\User;
use App\Model\Provider;
use App\Model\Api;
use App\Model\PortalSetting;

class CommonController extends Controller
{
    protected $api, $admin;
    public function __construct()
    {
        $this->api = Api::where('code', 'dmt2')->first();
        $this->admin = User::whereHas('role', function ($q){
            $q->where('slug', 'admin');
        })->first();
    }
    
    public function fetchData(Request $request, $type, $id=0, $returntype="all")
	{
		$request['return'] = 'all';
		$request['returntype'] = $returntype;

		if(\Myhelper::hasRole(["apiuser", "subadmin"])){
			$parentid = \Auth::id();
		}else{
			$parentid = $this->admin->id;
		}

		switch ($type) {
			case 'loginsessions':
				$request['table'] = '\App\Model\Loginsession';
				$request['searchdata'] = ['user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DSEC'];
				$request['parentData'] = 'all';
			break;

		    case 'websession':
				$request['table']= '\App\Model\Session';
				$request['searchdata'] = ['user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;

			case 'appsession':
				$request['table']= '\App\Model\Securedata';
				$request['searchdata'] = ['user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;
				
			case 'permissions':
				$request['table']= '\App\Model\Permission';
				$request['searchdata'] = ['name', 'slug'];
				$request['select'] = 'all';
				$request['order'] = ['id','DSEC'];
				$request['parentData'] = 'all';
			break;

			case 'roles':
				$request['table']= '\App\Model\Role';
				$request['searchdata'] = ['name', 'slug'];
				$request['select'] = 'all';
				$request['order'] = ['id','DSEC'];
				$request['parentData'] = 'all';
			break;

			case 'apilogs':
				$request['table'] = 'apilogs';
				$request['dbtype']= 'apilogs';
				$request['searchdata'] = ['txnid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DSEC'];
				$request['parentData'] = 'all';
			break;

			case 'subadmin':
			case 'apiuser':
			case 'mis':
			case 'tr' :
			case 'kycpending':
			case 'kycsubmitted':
			case 'kycrejected':
			case 'web':
				$request['table']= '\App\User';
				$request['searchdata'] = ['name', 'mobile', 'email', 'id', 'agentcode'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if (\Myhelper::hasRole(['admin'])){
					$request['parentData'] = 'all';
				}else{
					$request['parentData'] = [\Auth::id()];
				}
				$request['whereIn'] = 'parent_id';
			break;

			case 'fundrequest':
				$request['table']= '\App\Model\Fundreport';
				$request['searchdata'] = ['amount','ref_no', 'remark','paymode', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [\Auth::id()];
				$request['whereIn'] = 'user_id';
				break;
			
			case 'fundrequestview':
			case 'fundrequestviewall':
				$request['table']= '\App\Model\Fundreport';
				$request['searchdata'] = ['amount','ref_no', 'remark','paymode', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$parentid];
				$request['whereIn'] = 'credited_by';
				break;

			case 'fundstatement':
				$request['table']= '\App\Model\Payoutreport';
				$request['searchdata'] = ['amount','number', 'mobile','credit_by', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [\Auth::id()];
				$request['whereIn'] = 'user_id';
				break;

			case 'allfund':
				$request['table']= '\App\Model\Payoutreport';
				$request['searchdata'] = ['amount','number', 'mobile','credit_by', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [\Auth::id()];
				$request['whereIn'] = 'user_id';
				break;
			
			case 'setupbank':
				$request['table']= '\App\Model\Fundbank';
				$request['searchdata'] = ['name','account', 'ifsc','branch'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				
				$request['parentData'] = [\Auth::id()];
				$request['whereIn'] = 'user_id';
				break;
			
			case 'setupapi':
				$request['table']= '\App\Model\Api';
				$request['searchdata'] = ['name','account', 'ifsc','branch'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;
				
			case 'setupoperator':
				$request['table']= '\App\Model\Provider';
				$request['searchdata'] = ['name','recharge1', 'recharge2','type'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;
				
			case 'setupcomplaintsub':
				$request['table']= '\App\Model\Complaintsubject';
				$request['searchdata'] = ['name'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;
			
			case 'setupapitoken':
				$request['table']= '\App\Model\Apitoken';
				$request['searchdata'] = ['name'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if (\Myhelper::hasRole(['subadmin'])){
					$request['parentData'] = \Myhelper::getParents($parentid);
				}else{
					$request['parentData'] = 'all';
				}
				$request['whereIn'] = 'user_id';
				break;
	
			case 'loginslide':
				$request['table']= '\App\Model\PortalSetting';
				$request['searchdata'] = ['name'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = ['slides'];
				$request['whereIn'] = 'code';
				break;
				
			case 'appslide':
				$request['table']= '\App\Model\PortalSetting';
				$request['searchdata'] = ['name'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = ['appslides'];
				$request['whereIn'] = 'code';
				break;

			case 'resourcescheme':
				$request['table']= '\App\Model\Scheme';
				$request['searchdata'] = ['name', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [\Auth::id()];
				$request['whereIn'] = 'user_id';
				break;

			case 'resourcecompany':
				$request['table']= '\App\Model\Company';
				$request['searchdata'] = ['companyname'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;
			
			case 'complaints':
				$request['table']= '\App\Model\Complaint';
				$request['searchdata'] = ['type', 'solution', 'description', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['subadmin'])){
							$request['parentData'] = \Myhelper::getParents(\Auth::id());
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if(in_array($id, \Myhelper::getParents(\Auth::id()))){
							$request['parentData'] = [$id];
						}else{
							$request['parentData'] = [\Auth::id()];
						}
					}
					$request['whereIn'] = 'user_id';
				}else{
					$request['parentData'] = [$id];
					$request['whereIn'] = 'id';
					$request['return'] = 'single';
				}
				break;

			case 'apitoken':
				$request['table']= '\App\Model\Apitoken';
				$request['searchdata'] = ['ip'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if (\Myhelper::hasRole('admin')){
					$request['parentData'] = 'all';
				}else{
					$request['parentData'] = [\Myhelper::getParents(\Auth::id())];
				}
				$request['whereIn'] = 'user_id';
				break;

			case 'getstatement':
				$request['table']= '\App\Model\Report';
				$request['searchdata'] = ['id'];
				$request['select'] = ['number','mobile','provider_id','api_id','amount','charge','profit','apitxnid','txnid','payid','refno','description','remark','option1','option2','option3','option4','status','user_id','product','create_time'];
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [$id];
				$request['whereIn'] = 'id';
				$request['return']  = 'single';
				break;
			
			case 'setupqr':
			case 'setupcollection':
			case 'setuppayout':
				$request['table']= '\App\Model\Provider';
				$request['searchdata'] = ['name','recharge1', 'recharge2','type'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;
			
			default:
				# code...
				break;
        }
        
		$request['where']=0;
		$request['type']= $type;
        
		try {
			$totalData = $this->getData($request, 'count');
		} catch (\Exception $e) {
			$totalData = 0;
		}

		if ((isset($request->searchtext) && !empty($request->searchtext)) ||
           	(isset($request->todate) && !empty($request->todate))       ||
           	(isset($request->product) && !empty($request->product))       ||
           	(isset($request->status) && $request->status != '')		  ||
           	(isset($request->agent) && !empty($request->agent))
         ){
	        $request['where'] = 1;
	    }

		try {
			$totalFiltered = $this->getData($request, 'count');
		} catch (\Exception $e) {
			$totalFiltered = 0;
		}
		//return $data = $this->getData($request, 'data');
		try {
			$data = $this->getData($request, 'data');
		} catch (\Exception $e) {
			$data = [];
		}
		
		if ($request->return == "all" || $returntype =="all") {
			$json_data = array(
				"draw"            => intval( $request['draw'] ),
				"recordsTotal"    => intval( $totalData ),
				"recordsFiltered" => intval( $totalFiltered ),
				"data"            => $data
			);
			echo json_encode($json_data);
		}else{
			return response()->json($data);
		}
	}

	public function getData($request, $returntype)
	{ 
		$table = $request->table;
		if($request->has("dbtype")){
			$data = \DB::table($table);
		}else{
			$data = $table::query();
		}
		$data->orderBy($request->order[0], $request->order[1]);

		if($request->parentData != 'all'){
			if(!is_array($request->whereIn)){
				$data->whereIn($request->whereIn, $request->parentData);
			}else{
				$data->where(function ($query) use($request){
					$query->where($request->whereIn[0] , $request->parentData)
					->orWhere($request->whereIn[1] , $request->parentData);
				});
			}
		}

		if( $request->type != "roles" &&
			$request->type != "permissions" &&
			$request->type != "fundrequestview" &&
			$request->type != "fundrequest" &&
			$request->type != "setupbank" &&
			$request->type != "setupapi" &&
			$request->type != "setuppayoutbank" &&
			$request->type != "setupoperator" &&
			$request->type != "setupapitoken" &&
			$request->type != "resourcescheme" &&
			$request->type != "resourcecompany" &&
			$request->type != "fundrequestview" &&
			$request->type != "websession" &&
			$request->type != "appsession" &&
			$request->type != "setupcomplaintsub" &&
			$request->type != "setupqr" &&
			$request->type != "setupcollection" &&
			$request->type != "setuppayout" &&
 			$request->payoutaccount != "payoutaccount" &&
			!in_array($request->type , ['apiuser', 'subadmin', 'mis', 'tr', 'retaillite', 'web', 'kycpending', 'kycsubmitted', 'kycrejected'])&&
			$request->where != 1
        ){
            if(!empty($request->fromdate)){
                $data->whereDate('created_at', $request->fromdate);
            }
	    }

        switch ($request->type) {
			case 'apiuser':
			case 'subadmin':
			case 'mis':
				$data->whereHas('role', function ($q) use($request){
					$q->where('slug', $request->type);
				});
			break;

			case 'web':
				$data->where('type' ,'web');
			break;

			case 'tr':
				$data->whereHas('role', function ($q) use($request){
					$q->whereIn('slug', ['apiuser', 'apiuser']);
				})->where('kyc', 'verified');
			break;

			case 'kycpending':
				$data->whereHas('role', function ($q) use($request){
					$q->whereIn('slug', ['apiuser', 'apiuser']);
				})->whereIn('kyc', ['pending']);
			break;

			case 'kycsubmitted':
				$data->whereHas('role', function ($q) use($request){
					$q->whereIn('slug', ['apiuser', 'apiuser']);
				})->whereIn('kyc', ['submitted']);
			break;
				
			case 'kycrejected':
				$data->whereHas('role', function ($q) use($request){
					$q->whereIn('slug', ['apiuser', 'apiuser']);
				})->whereIn('kyc', ['rejected']);
			break;

			case 'fundrequest':
				$data->where('type', 'request');
				break;

			case 'fundrequestview':
				$data->where('status', 'pending')->where('type', 'request');
				break;
			
			case 'fundrequestviewall':
				$data->where('type', 'request');
				break;
			
			case 'allfund':
				$data->whereIn('product', ["fund return	", "fund transfer"]);
				break;

			case 'setupcollection':
				$data->where('type', "collection");
			break;

			case 'setuppayout':
				$data->where('type', "payout");
			break;
        }

		if ($request->where) {
	        if((isset($request->fromdate) && !empty($request->fromdate)) 
	        	&& (isset($request->todate) && !empty($request->todate))){
	        	    
	        	if(!in_array($request->type, ['websession', 'appsession'])){
	            if($request->fromdate == $request->todate){
	                $data->whereDate('created_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
	            }else{
	                $data->whereBetween('created_at', [Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->addDay(1)->format('Y-m-d')]);
	            }
	        	}
	        }

	        if(isset($request->product) && !empty($request->product)){
	            switch ($request->type) {
					case 'setupoperator':
	            		$data->where('type', $request->product);
					break;

					case 'complaints':
	            		$data->where('product', $request->product);
					break;
					case 'fundstatement':
	            		$data->where('type', $request->product);
					break;
				}
			}
			
	        if(isset($request->status) && $request->status != '' && $request->status != null){
	        	switch ($request->type) {	
					case 'kycpending':
					case 'kycsubmitted':
					case 'kycrejected':
						$data->where('kyc', $request->status);
					break;

					default:
	            		$data->where('status', $request->status);
					break;
				}
			}
			
			if(isset($request->agent) && !empty($request->agent)){
	        	switch ($request->type) {					
					case 'apiuser':
					case 'other':
					case 'tr' :
					case 'kycpending':
					case 'kycsubmitted':
					case 'kycrejected':
					    case 'retaillite':
						$data->whereIn('id', $this->agentFilter($request));
					break;

					default:
						$data->whereIn('user_id', [$request->agent]);
					break;
				}
	        }

	        if(!empty($request->searchtext)){
	            $data->where( function($q) use($request){
	            	foreach ($request->searchdata as $value) {
	            		$q->orWhere($value, 'like',$request->searchtext.'%');
                  		$q->orWhere($value, 'like','%'.$request->searchtext.'%');
                  		$q->orWhere($value, 'like','%'.$request->searchtext);
	            	}
				});
	        } 
      	}
		
		if ($request->return == "all" || $request->returntype == "all") {
			if($returntype == "count"){
				return $data->count();
			}else{
				if($request['length'] != -1){
					$data->skip($request['start'])->take($request['length']);
				}

				if($request->select == "all"){
					return $data->get();
				}else{
					return $data->select($request->select)->get();
				}
			}
		}else{
			if($request->select == "all"){
				return $data->first();
			}else{
				return $data->select($request->select)->first();
			}
		}
	}

	public function agentFilter($post)
	{
		if (\Myhelper::hasRole('admin') || in_array($post->agent, session('parentData'))) {
			return \Myhelper::getParents($post->agent);
		}else{
			return [];
		}
	}

	public function deleteData(Request $post, $type, $id=0, $returntype="all")
    {
        if (\Myhelper::hasNotRole('admin')) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
        }

        switch ($type) {
            case 'utiidstatement':
                $action = Utiid::where('id', $id)->delete();
                if ($action) {
                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
				break;
        }
	}

	public function update(Request $post)
    {
        switch ($post->actiontype) {
			case 'payout':
                $permission = "payout_statement_edit";
				break;
        }

        if (isset($permission) && !\Myhelper::can($permission)) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
        }

        switch ($post->actiontype) {
			case 'payout':
                $rules = array(
					'id'    => 'required',
                    'status'    => 'required',
                    'txnid'    => 'required',
					'refno'    => 'required',
                    'payid'    => 'required'
                );
                
                $validator = \Validator::make($post->all(), $rules);
                if ($validator->fails()) {
                    return response()->json(['errors'=>$validator->errors()], 422);
				}

				$report = Report::where('id', $post->id)->first();
				if(!$report || !in_array($report->status , ['pending', 'success'])){
					return response()->json(['status' => "Transaction Editing Not Allowed"], 400);
				}

                $action = Report::where('id', $post->id)->update($post->except(['id', '_token', 'actiontype']));
                if ($action) {
					if($post->status == "reversed"){
						\Myhelper::transactionRefund($post->id);
					}

					if($post->actiontype == "payout"){
						$newreport = Report::where('id', $post->id)->first();
						if( ($report->refno != $newreport->refno) || ($report->status != $newreport->status) ){
							if($report->user->role->slug == "apiuser" && $report->user->callbackurl != null && $report->user->callbackurl != ""){
		                        \Myhelper::callback($report->id);
		                    }
						}
					}

                    return response()->json(['status' => "success"], 200);
                }else{
                    return response()->json(['status' => "Task Failed, please try again"], 200);
                }
				break;
        }
	}
	
	public function status(Request $post)
    {
		if (!\Myhelper::can($post->type."_status")) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
		}
		
		switch ($post->type) {
			case 'recharge':
			case 'billpayment':
			case 'utipancard':
			case 'money':
				$report = Report::where('id', $post->id)->first();
				break;

			case 'utiid':
				$report = Utiid::where('id', $post->id)->first();
				break;
				
			case 'aeps':
				$report = Aepsreport::where('id', $post->id)->first();
				break;
				
			case 'matm':
				$report = Microatmreport::where('id', $post->id)->first();
				
				if(Carbon::parse($report->created_at)->timestamp > Carbon::now()->subMinutes(5)->timestamp){
				    return response()->json(['status' => "Permission Not Allowed"], 400);
				}
				break;

			case 'payout':
				$report = Aepsfundrequest::where('id', $post->id)->first();
				break;

			case 'mpayout':
				$report = Microatmfundrequest::where('id', $post->id)->first();
				break;

			default:
				return response()->json(['status' => "Status Not Allowed"], 400);
				break;
		}

		if(!$report || !in_array($report->status , ['pending', 'success', 'approved', 'accepted', 'initiated'])){
			return response()->json(['status' => "Recharge Status Not Allowed"], 400);
		}

		if($post->type == "aeps" && (!$report || !in_array($report->status , ['pending', 'success']))){
			return response()->json(['status' => "Aeps Status Not Allowed"], 400);
		}
		
		if($post->type == "matm" && (!$report || !in_array($report->status , ['pending', 'initiated']))){
			return response()->json(['status' => "Matm Status Not Allowed"], 400);
		}

		switch ($post->type) {
			case 'recharge':
				switch ($report->api->code) {
					case 'recharge1':
        				$url = $report->api->url."recharge/status";
        				$method = "POST";
        				$parameter = json_encode(array(
        					'referenceid' => $report->txnid,
        				));
        				
                        $payload =  [
                            "timestamp" => time(),
                            "partnerId" => $report->api->username,
                            "reqid"     => $report->user_id.Carbon::now()->timestamp
                        ];
                        
                        $key = $report->api->password;
                        $signer = new HS256($key);
                        $generator = new JwtGenerator($signer);
                        $header = array(
                            "Cache-Control: no-cache",
                            "Content-Type: application/json",
                            "Token: ".$generator->generate($payload),
                            "Authorisedkey: ".$report->api->optional1
                        );
        				break;
						
					default:
						return response()->json(['status' => "Recharge Status Not Allowed"], 400);
						break;
				}
				break;

			case 'billpayment':
				switch ($report->api->code) {
					case 'billpay1':
						if($report->provider->type == "electricity"){
							$url = $report->api->url."bill-payment/bill/status";
						}elseif($report->provider->type == "fastag"){
							$url = $report->api->url."fastag/Fastag/recharge";
						}

        				$method = "POST";
        				$parameter = json_encode(array(
        					'referenceid' => $report->txnid,
        				));
        				
                        $payload =  [
                            "timestamp" => time(),
                            "partnerId" => $report->api->username,
                            "reqid"     => $report->user_id.Carbon::now()->timestamp
                        ];
                        
                        $key 	= $report->api->password;
                        $signer = new HS256($key);
                        $generator = new JwtGenerator($signer);
                        $header = array(
                            "Cache-Control: no-cache",
                            "Content-Type: application/json",
                            "Token: ".$generator->generate($payload),
                            "Authorisedkey: ".$report->api->optional1
                        );
        				break;
					
					default:
						return response()->json(['status' => "Recharge Status Not Allowed"], 400);
						break;
				}
				break;

			case 'utipancard':
				$url = $report->api->url.'coupon_status.php';
				$method = "POST";
				$parameter['api_key'] = $report->api->username;
                $parameter['order_id'] = $report->payid;
                
				$header = array();
				break;
			
			case 'utiid':
				$url = $report->api->url.'/status?token='.$report->api->username.'&vleid='.$report->vleid;
				$method = "GET";
				$parameter = "";
				$header = [];
				break;

			case 'money':
				$url = $report->api->url."transact/transact/querytransact";
				$method = "POST";
				$parameter = json_encode(array(
					'referenceid' => $report->txnid,
				));
				
                $payload =  [
                    "timestamp" => time(),
                    "partnerId" => $report->api->username,
                    "reqid"     => $report->user_id.Carbon::now()->timestamp
                ];
                
                $key = $report->api->password;
                $signer = new HS256($key);
                $generator = new JwtGenerator($signer);
                $header = array(
                    "Cache-Control: no-cache",
                    "Content-Type: application/json",
                    "Token: ".$generator->generate($payload),
                    "Authorisedkey: ".$report->api->optional1
                );
				break;
			
			case 'aeps':
			    switch ($report->api->code) {
					case 'raeps':
						$url = $report->api->url."aeps/aepsquery/query";
        				$method = "POST";
        				$parameters = array(
        					'reference' => $report->txnid
        				);
        				
        				$key = $report->api->optional2;
                        $iv  = $report->api->optional3;
                        $cipher   = openssl_encrypt(json_encode($parameters,true), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                        $request  = base64_encode($cipher);
                        $parameter  = http_build_query(array('body' => $request));
                        
                        $gpsdata = geoip($post->ip());
                        $token = $this->getToken($report->user_id.Carbon::now()->timestamp, $report->api->username, $report->api->password);
        
        				$header = array(
                            "Cache-Control: no-cache",
                            "Content-Type: application/x-www-form-urlencoded",
                            "Token: ".$token['token'],
                            "Authorisedkey: ".$report->api->optional1
                        );
						break;
					
					default:
						return response()->json(['status' => "Transaction Status Not Allowed"], 400);
						break;
				}
				break;
				
			case 'matm':
				switch ($report->api->code) {
					case 'microatm':
						$url = "http://uat.dhansewa.com/MICROATM/GetMATMtxnStatus";
        				$method = "POST";
        				$parameter = json_encode(array(
        					'secretekey' => $report->api->password,
        					'saltkey' => $report->api->username,
        					'referenceid' => $report->txnid
        				));
        
        				$header = array(
        					"Accept: application/json",
        					"Cache-Control: no-cache",
        					"Content-Type: application/json"
        				);
						break;

					case 'fmicroatm':
					    $agent = Fingagent::where('user_id', $report->user->id)->first();
						$url = "https://fpma.tapits.in/fpcardwebservice/api/ma/statuscheck/cw";
        				$method = "POST";
        				$parameter = json_encode(array(
        					'merchantLoginId' => $agent->merchantLoginId,
        					'merchantPassword' => md5($agent->merchantLoginPin),
        					'superMerchantId' => $report->api->optional1,
        					'superMerchantPassword' => md5($report->api->password),
        					'merchantTranId' => $report->txnid,
        					'hash' => base64_encode(hash("sha256", $report->txnid.strtolower($agent->merchantLoginId).$report->api->optional1, True))
        				));
        
        				$header = array(
        					"Accept: application/json",
        					"Cache-Control: no-cache",
        					"Content-Type: application/json"
        				);
        				break;
					
					default:
						return response()->json(['status' => "Transaction Status Not Allowed"], 400);
						break;
				}
				break;

			case 'payout':
			case 'mpayout':
				$url = $report->api->url."status";
				$method = "POST";
				$parameter = json_encode(array(
					'refid' => $report->payoutid,
					'ackno' => $report->payoutref
				));
				
                $payload =  [
                    "timestamp" => time(),
                    "partnerId" => $report->api->username,
                    "reqid"     => $report->user_id.Carbon::now()->timestamp
                ];
                
                $key = $report->api->password;
                $signer = new HS256($key);
                $generator = new JwtGenerator($signer);
                $header = array(
                    "Cache-Control: no-cache",
                    "Content-Type: application/json",
                    "Token: ".$generator->generate($payload),
                    "Authorisedkey: ".$report->api->optional1
                );
                break;
			
			default:
				# code...
				break;
		}

		$result = \Myhelper::curl($url, $method, $parameter, $header);
	
		//dd([$url, $parameter, $result, $header]);
		if($result['response'] != ''){
			switch ($post->type) {
				case 'recharge':
					switch ($report->api->code) {
						case 'recharge1':
						    \DB::table('rp_log')->insert([
                                'ServiceName' => "Recharge-Status",
                                'header' => json_encode($header),
                                'body' => json_encode($parameter),
                                'response' => $result['response'],
                                'url' => $url,
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                            
							$doc = json_decode($result['response']);
							if(isset($doc->data->status) && strtolower($doc->data->status) == "1"){
								$update['refno'] = $doc->data->operatorid;
								$update['status'] = "success";
							}elseif(isset($doc->data->status) && strtolower($doc->data->status) == "0"){
								$update['status'] = "reversed";
								$update['refno'] = (isset($doc->message)) ? $doc->message : "failed";
							}else{
								$update['status'] = "Unknown";
								$update['refno'] = (isset($doc->message)) ? $doc->message : "Unknown";
							}
							break;
					}
					$product = "recharge";
					break;

				case 'billpayment':
					$doc = json_decode($result['response']);
					switch ($report->api->code) {
    					case 'billpay1':
						    \DB::table('rp_log')->insert([
                                'ServiceName' => "Billpay-Status",
                                'header' => json_encode($header),
                                'body' => json_encode($parameter),
                                'response' => $result['response'],
                                'url' => $url,
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                            
							$doc = json_decode($result['response']);
							if(isset($doc->data->status) && strtolower($doc->data->status) == "1"){
								$update['refno'] = $doc->data->operatorid;
								$update['status'] = "success";
							}elseif(isset($doc->data->status) && strtolower($doc->data->status) == "0"){
								$update['status'] = "reversed";
								$update['refno'] = (isset($doc->message)) ? $doc->message : "failed";
							}else{
								$update['status'] = "Unknown";
								$update['refno'] = (isset($doc->message)) ? $doc->message : "Unknown";
							}
							break;
    				}
    				$product = "billpay";
					break;

				case 'utipancard':
					$data =json_decode($result['response']);
                    if(isset($data->status) && $data->status == "SUCCEESS"){
                        $update['status'] = "success";
                    }elseif(isset($data->status) && ($data->status == "FAILED" || $data->status == "FAILURE")){
                        $update['status'] = "reversed";
                    }else{
                        $update['status'] = "Unknown";
                    }
					$product = "utipancard";
					break;

				case 'money':
				    \DB::table('rp_log')->insert([
                        'ServiceName' => "Status",
                        'header' => json_encode($header),
                        'body' => json_encode($parameter),
                        'response' => $result['response'],
                        'url' => $url,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
        
					$data = json_decode($result['response']);
                    if(isset($data->txn_status) && in_array($data->txn_status, ["5"])){
                        $update['status'] = "refund";
                        $update['refno']  = $data->message;
                    }elseif(isset($data->txn_status) && in_array($data->txn_status, ["1"])){
                        $update['status'] = "success";
                        $update['refno'] = isset($data->utr) ? $data->utr : "success";
                        $update['payid'] = isset($data->ackno) ? $data->ackno : "success";
                    }elseif(isset($data->txn_status) && in_array($data->txn_status, ["0"])){
                        $update['status'] = "reversed";
                        $update['refno']  = $data->message;
                    }else{
                        $update['status'] = "Unknown";
                    }

					$product = "money";
					break;

				case 'utiid':
					$doc = json_decode($result['response']);
					//dd($doc);
					if(isset($doc->statuscode) && $doc->statuscode == "TXN"){
						$update['status'] = "success";
						$update['remark'] = $doc->message;
					}elseif(isset($doc->statuscode) && $doc->statuscode == "TXF"){
						$update['status'] = "reversed";
						$update['remark'] = $doc->message;
					}elseif(isset($doc->statuscode) && $doc->statuscode == "TUP"){
						$update['status'] = "pending";
						$update['remark'] = $doc->message;
					}else{
						$update['status'] = "Unknown";
					}
					$product = "utiid";
					break;
					
				case 'aeps':
					$doc = json_decode($result['response']);
					//dd($doc);
					
					\DB::table('rp_log')->insert([
                        'ServiceName' => "Aeps-Status",
                        'header' => json_encode($header),
                        'body' => json_encode($parameter),
                        'response' => $result['response'],
                        'url' => $url,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
					switch ($report->api->code) {
    					case 'aeps':
    						if(isset($doc->statuscode) && $doc->statuscode == "000"){
        					    if(isset($doc->Data[0]) && isset($doc->Data[0]->status)){
        					       if($doc->Data[0]->status == "SUCCESS"){
            						    $update['status'] = "complete";
            						    $update['refno'] = $doc->Data[0]->rrn;
            						    $update['remark'] = isset($doc->Data[0]->bankmessage) ? $doc->Data[0]->bankmessage : "Success";
        					       }elseif($doc->Data[0]->status == "FAILURE"){
        					            $update['status'] = "failed";
        					            $update['refno'] = isset($doc->Data[0]->bankmessage) ? $doc->Data[0]->bankmessage : "Failed";
            						    $update['remark'] = isset($doc->Data[0]->bankmessage) ? $doc->Data[0]->bankmessage : "Failed";
        					       }elseif($doc->Data[0]->status == "PENDING"){
        					            $update['status'] = "pending";
            						    $update['remark'] = isset($doc->Data[0]->bankmessage) ? $doc->Data[0]->bankmessage : "pending";
        					       }else{
            						    $update['status'] = "Unknown";
                				   }
        					    }else{
            						$update['status'] = "Unknown";
            					}
        					}else{
        						$update['status'] = "Unknown";
        					}
    						break;
    
    					case 'ifaeps':
    					    //dd([$url, $parameter, $result, $header, $report->txnid."+".strtolower($agent->merchantLoginId)."+".$report->api->username]);
    					    if(isset($doc->data[0]->transactionStatusCode)){
        				       if($doc->data[0]->transactionStatusCode == "00"){
        						    $update['status'] = "complete";
        						    $update['refno']  = $doc->data[0]->bankRRN;
        						    $update['payid']  = $doc->data[0]->fingpayTransactionId;
        				       }elseif($doc->data[0]->transactionStatusCode == "91"){
        				            $update['status'] = "failed";
        				            $update['refno']  = $doc->data[0]->bankRRN;
        				            $update['payid']  = $doc->data[0]->fingpayTransactionId;
        						    $update['remark'] = $doc->data[0]->transactionStatusMessage;
        				       }
        				       //elseif(strtolower($doc->Data[0]->status) == "pending"){
        				       //     $update['status'] = "pending";
        				       //     $update['amount'] = $doc->Data[0]->amount;
        				       //     $update['payid']  = isset($doc->Data[0]->stanno) ? $doc->Data[0]->stanno : "Failed";
        				       //     $update['refno']  = isset($doc->Data[0]->rrn) ? $doc->Data[0]->rrn : "Failed";
        						  //  $update['remark'] = isset($doc->Data[0]->bankmessage) ? $doc->Data[0]->bankmessage : "pending";
        				       //}
        				       else{
        						    $update['status'] = "Unknown";
            				   }
        					}else{
        						$update['status'] = $doc->apiStatusMessage;
        					}
        					
            				break;
    					
    					default:
    						return response()->json(['status' => "Transaction Status Not Allowed"], 400);
    						break;
    				}
					$product = "aeps";
					break;

				case 'matm':
					$doc = json_decode($result['response']);
					
					switch ($report->api->code) {
    					case 'microatm':
    						if(isset($doc->statuscode) && $doc->statuscode == "000"){
        					    if(isset($doc->Data[0]) && isset($doc->Data[0]->status)){
        					       if(strtolower($doc->Data[0]->status) == "success"){
            						    $update['status'] = "complete";
            						    $update['amount'] = $doc->Data[0]->amount;
            						    $update['refno']  = $doc->Data[0]->rrn;
            						    $update['aadhar'] = $doc->Data[0]->cardno;
            						    $update['payid']  = isset($doc->Data[0]->stanno) ? $doc->Data[0]->stanno : "Failed";
            						    $update['remark'] = isset($doc->Data[0]->bankmessage) ? $doc->Data[0]->bankmessage : "Success";
        					       }elseif(strtolower($doc->Data[0]->status) == "failed"){
        					            $update['status'] = "failed";
        					            $update['amount'] = $doc->Data[0]->amount;
        					            $update['refno']  = isset($doc->Data[0]->bankmessage) ? $doc->Data[0]->bankmessage : "Failed";
        					            $update['payid']  = isset($doc->Data[0]->stanno) ? $doc->Data[0]->stanno : "Failed";
            						    $update['aadhar'] = $doc->Data[0]->cardno;
            						    $update['remark'] = isset($doc->Data[0]->bankmessage) ? $doc->Data[0]->bankmessage : "Failed";
        					       }elseif(strtolower($doc->Data[0]->status) == "pending"){
        					            $update['status'] = "pending";
        					            $update['amount'] = $doc->Data[0]->amount;
        					            $update['payid']  = isset($doc->Data[0]->stanno) ? $doc->Data[0]->stanno : "Failed";
        					            $update['refno']  = isset($doc->Data[0]->rrn) ? $doc->Data[0]->rrn : "Failed";
            						    $update['remark'] = isset($doc->Data[0]->bankmessage) ? $doc->Data[0]->bankmessage : "pending";
        					       }else{
            						    $update['status'] = "Unknown";
                				   }
        					    }else{
            						$update['status'] = "Unknown";
            					}
        					}elseif(isset($doc->statuscode) && $doc->statuscode == "002"){
        					    $update['status'] = "failed";
        					}else{
        						$update['status'] = "Unknown";
        					}
    						break;
    
    					case 'fmicroatm':
    					    //dd([$url, $parameter, $result, $header, $report->txnid.strtolower($agent->merchantLoginId).$report->api->optional1]);
    					    if(isset($doc->status) && $doc->status == "true"){
        				       if($doc->data[0]->transactionStatusCode == "00"){
        						    $update['status'] = "complete";
        						    $update['amount'] = $doc->data[0]->transactionAmount;
        						    $update['refno']  = $doc->data[0]->bankRRN;
        						    $update['aadhar'] = $doc->data[0]->cardNumber;
        						    $update['payid']  = $doc->data[0]->fingpayTransactionId;
        						    $update['bank']   = $doc->data[0]->bankName;
        						    $update['remark'] = $doc->data[0]->stan;
        				       }elseif(isset($doc->data[0]->transactionStatusCode) && $doc->data[0]->transactionStatusCode != "00"){
        						    $update['status'] = "failed";
        						    $update['amount'] = isset($doc->data[0]->transactionAmount)?$doc->data[0]->transactionAmount: 0;
        						    $update['refno']  = isset($doc->data[0]->bankRRN)?$doc->data[0]->bankRRN: "failed";
        						    $update['aadhar'] = isset($doc->data[0]->cardNumber)?$doc->data[0]->cardNumber: "failed";
        						    $update['payid']  = isset($doc->data[0]->fingpayTransactionId)?$doc->data[0]->fingpayTransactionId: "failed";
        						    $update['bank']   = isset($doc->data[0]->bankName)?$doc->data[0]->bankName: "failed";
        						    $update['remark'] = isset($doc->data[0]->transactionStatusMessage)?$doc->data[0]->transactionStatusMessage: "failed";
        				       }
        				       //elseif(strtolower($doc->Data[0]->status) == "failed"){
        				       //     $update['status'] = "failed";
        				       //     $update['amount'] = $doc->Data[0]->amount;
        				       //     $update['refno']  = isset($doc->Data[0]->rrn) ? $doc->Data[0]->rrn : "Failed";
        				       //     $update['payid']  = isset($doc->Data[0]->stanno) ? $doc->Data[0]->stanno : "Failed";
        						  //  $update['aadhar'] = $doc->Data[0]->cardno;
        						  //  $update['remark'] = isset($doc->Data[0]->bankmessage) ? $doc->Data[0]->bankmessage : "Failed";
        				       //}elseif(strtolower($doc->Data[0]->status) == "pending"){
        				       //     $update['status'] = "pending";
        				       //     $update['amount'] = $doc->Data[0]->amount;
        				       //     $update['payid']  = isset($doc->Data[0]->stanno) ? $doc->Data[0]->stanno : "Failed";
        				       //     $update['refno']  = isset($doc->Data[0]->rrn) ? $doc->Data[0]->rrn : "Failed";
        						  //  $update['remark'] = isset($doc->Data[0]->bankmessage) ? $doc->Data[0]->bankmessage : "pending";
        				       //}
        				       else{
        						    $update['status'] = "Unknown";
            				   }
        					}elseif(isset($doc->status) && $doc->status === false && $doc->message == "No data found"){
        			            $update['status'] = "failed";
        			            $update['refno']  = "No data found";
        				    }else{
        						$update['status'] = "Unknown";
        					}
            				break;
    					
    					default:
    						return response()->json(['status' => "Transaction Status Not Allowed"], 400);
    						break;
    				}
					$product = "matm";
					break;

				case 'payout':
				case 'mpayout':
					\DB::table('rp_log')->insert([
                        'ServiceName' => "Payout-Status",
                        'header' => json_encode($header),
                        'body' => json_encode($parameter),
                        'response' => $result['response'],
                        'url' => $url,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

					$data = json_decode($result['response']);
                    if(strtolower($data->status) && strtolower($data->status) == "success"){
                        $update['status'] = "approved";
                        $update['payoutref'] = $data->result->rrn;
                    }elseif (strtolower($data->status) && strtolower($data->status) == "failure") {
                        $update['status'] = "rejected";
                        if($data->statusMessage == "Account balance is low. Please add funds and try again."){
                            $update['payoutref'] = "Service Down For Sometime";
                        }else{
                            $update['payoutref'] = $data->statusMessage;
                        }
                    }else{
                        $update['status'] = "Unknown";
                    }
                    break;
			}

			if (in_array($update['status'], ["success", "complete", "failed", "reversed", "pending", 'approved','rejected', 'refund'])) {
				switch ($post->type) {
					case 'recharge':
					case 'billpayment':
					case 'utipancard':
					case 'money':
						$reportupdate = Report::where('id', $post->id)->update($update);
						if ($reportupdate && $update['status'] == "reversed") {
							\Myhelper::transactionRefund($post->id);
						}

						if($post->type == "recharge"){
							$newreport = Report::where('id', $post->id)->first();
							if( ($report->refno != $newreport->refno) || ($report->status != $newreport->status) ){
								if($report->user->role->slug == "apiuser" && $report->user->callbackurl != null && $report->user->callbackurl != ""){
			                        \Myhelper::callback($report->id, 'recharge');
			                    }
							}
						}

						break;

					case 'payout':
						$reportupdate = Aepsfundrequest::where('id', $post->id)->update($update);

						if ($reportupdate && $update['status'] == "rejected") {
						    Aepsreport::where('txnid', $report->payoutid)->update(['status' => "reversed"]);
							$aepsreport = Aepsreport::where('txnid', $report->payoutid)->first();
			                $aepsreports['api_id'] = $aepsreport->api_id;
			                $aepsreports['payid']  = $aepsreport->payid;
			                $aepsreports['mobile'] = $aepsreport->mobile;
			                $aepsreports['refno']  = $aepsreport->refno;
			                $aepsreports['aadhar'] = $aepsreport->aadhar;
			                $aepsreports['amount'] = $aepsreport->amount;
			                $aepsreports['charge'] = $aepsreport->charge;
			                $aepsreports['bank']   = $aepsreport->bank;
			                $aepsreports['txnid']  = $aepsreport->id;
			                $aepsreports['user_id']= $aepsreport->user_id;
			                $aepsreports['credited_by'] = $aepsreport->credited_by;
			                $aepsreports['balance']     = $aepsreport->user->aepsbalance;
			                $aepsreports['type']        = "credit";
			                $aepsreports['transtype']   = 'fund';
			                $aepsreports['status'] = 'refunded';
			                $aepsreports['remark'] = "Bank Settlement Refunded";

			                User::where('id', $aepsreports['user_id'])->increment('aepsbalance', $aepsreports['amount'] + $aepsreports['charge']);
			                Aepsreport::where('id', $aepsreport->id)->update(['status' => "reversed"]);
			                Aepsreport::create($aepsreports);
						}
						break;

					case 'mpayout':
						$reportupdate = Microatmfundrequest::where('id', $post->id)->update($update);

						if ($reportupdate && $update['status'] == "rejected") {
						    Microatmreport::where('txnid', $report->payoutid)->update(['status' => "reversed"]);
							$aepsreport = Microatmreport::where('txnid', $report->payoutid)->first();
			                $aepsreports['api_id'] = $aepsreport->api_id;
			                $aepsreports['payid']  = $aepsreport->payid;
			                $aepsreports['mobile'] = $aepsreport->mobile;
			                $aepsreports['refno']  = $aepsreport->refno;
			                $aepsreports['aadhar'] = $aepsreport->aadhar;
			                $aepsreports['amount'] = $aepsreport->amount;
			                $aepsreports['charge'] = $aepsreport->charge;
			                $aepsreports['bank']   = $aepsreport->bank;
			                $aepsreports['txnid']  = $aepsreport->id;
			                $aepsreports['user_id']= $aepsreport->user_id;
			                $aepsreports['credited_by'] = $aepsreport->credited_by;
			                $aepsreports['balance']     = $aepsreport->user->microatmbalance;
			                $aepsreports['type']        = "credit";
			                $aepsreports['transtype']   = 'fund';
			                $aepsreports['status'] = 'refunded';
			                $aepsreports['remark'] = "Bank Settlement Refunded";

			                User::where('id', $aepsreports['user_id'])->increment('microatmbalance', $aepsreports['amount'] + $aepsreports['charge']);
			                Microatmreport::where('id', $aepsreport->id)->update(['status' => "reversed"]);
			                Microatmreport::create($aepsreports);
						}
						break;
                    
                    case 'aeps':
						$reportupdate = Aepsreport::where('id', $post->id)->update($update);
						
						if($report->status == "pending" && $update['status'] == "complete"){
						    $user = User::where('id', $report->user_id)->first();
						    $insert = [
                                "mobile" => $report->mobile,
                                "aadhar" => $report->aadhar,
                                "api_id" => $report->api_id,
                                "txnid"  => $report->txnid,
                                "refno"  => "Txnid - ".$report->id. " Cleared",
                                "amount" => $report->amount,
                                "bank"   => $report->bank,
                                "user_id"=> $report->user_id,
                                "balance" => $user->aepsbalance,
                                'aepstype'=> $report->aepstype,
                                'status'  => 'success',
                                'authcode'=> $report->authcode,
                                'payid'=> $report->payid,
                                'mytxnid'=> $report->mytxnid,
                                'terminalid'=> $report->terminalid,
                                'TxnMedium'=> $report->TxnMedium,
                                'credited_by' => $report->credited_by,
                                'type' => 'credit',
                                'product' => "aeps"
                            ];
                            if($report->aepstype == "CW"){
                                if($report->amount > 99 && $report->amount <= 999){
                                    $provider = Provider::where('recharge1', 'aeps1')->first();
                                }elseif($report->amount>999 && $report->amount<=1499){
                                    $provider = Provider::where('recharge1', 'aeps2')->first();
                                }elseif($report->amount>1499 && $report->amount<=1999){
                                    $provider = Provider::where('recharge1', 'aeps3')->first();
                                }elseif($report->amount>1999 && $report->amount<=2499){
                                    $provider = Provider::where('recharge1', 'aeps4')->first();
                                }elseif($report->amount>2499 && $report->amount<=2999){
                                    $provider = Provider::where('recharge1', 'aeps5')->first();
                                }elseif($report->amount>2999 && $report->amount<=7999){
                                    $provider = Provider::where('recharge1', 'aeps6')->first();
                                }elseif($report->amount>7999 && $report->amount<=10000){
                                    $provider = Provider::where('recharge1', 'aeps7')->first();
                                }
                            
                                $post['provider_id'] = $provider->id;
                                if($report->amount > 500){
                                    $usercommission = \Myhelper::getCommission($report->amount, $user->scheme_id, $post->provider_id,$user->role->slug);
                                }else{
                                    $usercommission = 0;
                                }
                            }elseif($report->aepstype == "M"){
                                $provider = Provider::where('recharge1', 'pay1')->first();
                                $post['provider_id'] = $provider->id;
                                if($report->amount > 500){
                                    $usercommission = \Myhelper::getCommission($report->amount, $user->scheme_id, $post->provider_id,$user->role->slug);
                                }else{
                                    $usercommission = 0;
                                }
                            }else{
                                $usercommission = 0;
                            }
                            
                            $insert['charge'] = $usercommission;
                            $insert['provider_id'] = $provider->id;
                            
                            if($report->aepstype == "CW"){
                                $action = User::where('id', $report->user_id)->increment('aepsbalance', $report->amount+$usercommission);
                            }elseif($report->aepstype == "M"){
                                $action = User::where('id', $report->user_id)->increment('aepsbalance', $report->amount-$usercommission);
                            }else{
                                $action = false;
                            }
                            
                            if($action){
                                $aeps = Aepsreport::create($insert);
                                if($report->amount > 500 && $report->aepstype == "CW"){
                                    \Myhelper::commission(Aepsreport::find($aeps->id));
                                }
                                
                                if($report->amount > 99 && $report->aepstype == "M"){
                                    \Myhelper::commission(Aepsreport::find($aeps->id));
                                }
                                
                            }
						}
						break;

					case 'matm':
					    $update['TxnMedium'] = "status".date('Y-m-d H:i:s');
						$reportupdate = Microatmreport::where('id', $post->id)->update($update);
						
						if(in_array($report->status, ["pending", 'initiated']) && $update['status'] == "complete"){
						    $user     = User::where('id', $report->user_id)->first();
						    $myreport = Microatmreport::where('id', $post->id)->first();

						    $insert = [
                                "mobile"  => $myreport->mobile,
                                "aadhar"  => $myreport->aadhar,
                                "api_id"  => $myreport->api_id,
                                "txnid"   => $myreport->txnid,
                                "refno"   => "Txnid - ".$myreport->id. " Cleared",
                                "amount"  => $myreport->amount,
                                "bank"    => $myreport->bank,
                                "user_id" => $myreport->user_id,
                                "balance" => $user->microatmbalance,
                                'aepstype'=> $myreport->aepstype,
                                'status'  => 'success',
                                'authcode'=> $myreport->authcode,
                                'payid'	  => $myreport->payid,
                                'mytxnid' => $myreport->mytxnid,
                                'terminalid' => $myreport->terminalid,
                                'TxnMedium'  => $myreport->TxnMedium,
                                'credited_by'=> $myreport->credited_by,
                                'type' 	  => 'credit'
                            ];

                            if($myreport->amount > 0){
	                            if($myreport->amount > 99 && $myreport->amount <= 999){
	                                $provider = Provider::where('recharge1', 'matm1')->first();
	                            }elseif($myreport->amount>999 && $myreport->amount<=1499){
	                                $provider = Provider::where('recharge1', 'matm2')->first();
	                            }elseif($myreport->amount>1499 && $myreport->amount<=1999){
	                                $provider = Provider::where('recharge1', 'matm3')->first();
	                            }elseif($myreport->amount>1999 && $myreport->amount<=2499){
	                                $provider = Provider::where('recharge1', 'matm4')->first();
	                            }elseif($myreport->amount>2499 && $myreport->amount<=2999){
	                                $provider = Provider::where('recharge1', 'matm5')->first();
	                            }elseif($myreport->amount>2999 && $myreport->amount<=3499){
	                                $provider = Provider::where('recharge1', 'matm6')->first();
	                            }elseif($myreport->amount>3499){
	                                $provider = Provider::where('recharge1', 'matm7')->first();
	                            }
	                            
	                            $insert['provider_id'] = $provider->id;
                                if($report->amount > 500){
                                    $insert['charge'] = \Myhelper::getCommission($myreport->amount, $user->scheme_id, $insert['provider_id'], $user->role->slug);
                                }else{
                                	$insert['charge'] = 0;
                                }
	                        }else{
	                        	$insert['provider_id'] = 0;
	                        	$insert['charge'] = 0;
	                        }
                            
                            $action = User::where('id',$report->user_id)->increment('microatmbalance',$myreport->amount + $insert['charge']);
                            if($action){
                                $matm = Microatmreport::create($insert);

                                if($report->amount > 500){
                                    \Myhelper::commission(Microatmreport::find($matm->id));
                                }
                            }
						}
						break;
						
					case 'utiid':
						$reportupdate = Utiid::where('id', $post->id)->update($update);
						break;
				}
			}
			return response()->json($update, 200);
		}else{
			return response()->json(['status' => "Status Not Fetched , Try Again."], 400);
		}
	}
	
	public function delete(Request $post)
    {
    	if (\Myhelper::hasNotRole(['admin', 'whitelable'])) {
            return response()->json(['status' => "Permission Not Allowed"], 400);
		}
		
		switch ($post->type) {
			case 'slide':
				try {
					\Storage::delete($post->slide);
				} catch (\Exception $e) {}
                $action = true;
				if ($action) {
		            PortalSetting::where('value', $post->slide)->delete();
		            return response()->json(['status' => "success"], 200);
		        }else{
		            return response()->json(['status' => "Task Failed, please try again"], 200);
		        }
				break;

			case 'appsession':
                $action = \DB::table('securedatas')->where('id', $post->id)->delete();
				if ($action) {
		            return response()->json(['status' => "success"], 200);
		        }else{
		            return response()->json(['status' => "Task Failed, please try again"], 200);
		        }
				break;

			case 'websession':
                $action = \DB::table('sessions')->where('tid', $post->id)->delete();
				if ($action) {
		            return response()->json(['status' => "success"], 200);
		        }else{
		            return response()->json(['status' => "Task Failed, please try again"], 200);
		        }
				break;

			default:
				return response()->json(['status' => "Permission Not Allowed"], 400);
				break;
		}        
    }
    
    public function getPasswordHashEncode($data){

        $enc_data = hash("sha256",$data,true);
        
        for($i=0;$i<5;$i++){
            $enc_data = hash("sha256",$enc_data,true);
        } 
        return base64_encode($enc_data);
    }
    
    public function getAccessToken($post)
    {
        $url = "https://api.rapipay.com/auth";
        $header = array(
            'Content-Type: application/json',
            "authorization: Basic ".$this->api->optional1
        ); 
        
        $parameter = [
            "serviceType"     => "ValidCredentialService",
            "agentId"         => $this->api->username,
            "password"        => $this->getPasswordHashEncode($this->api->password),
            "clientRequestIP" => $post->ip(),
            "requestType"     => "handset_CHannel",
            "nodeAgentId"   => $this->api->username,
            "imeiNo"        => md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . $this->api->username),
            "domainName"    => "e-banker.in",
            "txnRefId"      => \Auth::id().date('ymdhis'),
            "typeMobileWeb" => "WEB"
        ];
        
        $enc_data = hash_hmac('sha512', json_encode($parameter), $parameter['imeiNo']);
        
        $parameter['checkSum'] = $enc_data;
        
        $aeps = Aepstransaction::where('user_id', \Auth::id())->first();

        $output = "error";
        if( !$aeps || 
            ((Carbon::now()->timestamp - Carbon::createFromFormat('Y-m-d H:i:s', $aeps->created_at)->timestamp) >= $aeps->expires_in))
        {
            $result = \Myhelper::curl($url, 'POST', json_encode($parameter), $header, 'yes' , 'DMT2', $parameter['txnRefId']);  
            
            //dd([json_encode($parameter), $enc_data, $parameter, $result]);
            if($result['response'] != ''){
                $data = json_decode($result['response']);
                if(isset($data->responseCode) && $data->responseCode == "200"){
                    $output = Aepstransaction::create([
                        'user_id' => \Auth::id(),
                        'access_token'=> $data->sessionKey,
                        'token_type'=> $data->sessionRefNo,
                        'expires_in'=> time()
                    ]);
                }
            }
        }else{
            $output = $aeps;
        }
        return $output;
    }

    public function checkSumGenerate($data)
    {
        $str = json_encode($data);
        $enc_data = hash_hmac('sha512', $str, $this->api->username);
        return $enc_data;
    }
    
    public function getToken($uniqueid, $partnerId, $key)
    {
        $payload =  [
            "timestamp" => time(),
            "partnerId" => $partnerId,
            "reqid"     => $uniqueid
        ];
        
        $key = $key;
        $signer = new HS256($key);
        $generator = new JwtGenerator($signer);
        return ['token' => $generator->generate($payload), 'payload' => $payload];
    }
}
