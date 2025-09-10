<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

		if(\Myhelper::hasRole("apiuser")){
			$parentid = \Auth::id();
		}else{
			$parentid = $this->admin->id;
		}

		switch ($type) {
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
				$request['table'] = '\App\Model\Apilog';
				$request['searchdata'] = ['txnid'];
				$request['select'] = 'all';
				$request['order'] = ['id','DSEC'];
				$request['parentData'] = 'all';
			break;

			case 'apiuser':
			case 'other':
			case 'tr' :
			case 'kycpending':
			case 'kycsubmitted':
			case 'kycrejected':
			case 'web':
				$request['table']= '\App\User';
				$request['searchdata'] = ['name', 'mobile','email'];
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
				
			case 'setupservicemanage':
				$request['table']= '\App\Model\ServiceManager';
				$request['searchdata'] = ['provider_id', 'api_id'];
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
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;
			
			case 'setuppayoutbank':
				$request['table']= '\App\Model\Contact';
				$request['searchdata'] = ['name', 'account'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = 'all';
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

			case 'resourcepackage':
				$request['table']= '\App\Model\Package';
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
			
			case 'utiidstatement':
				$request['table']= '\App\Model\Utiid';
				$request['searchdata'] = ['name','vleid', 'user_id', 'location', 'contact_person', 'pincode', 'email', 'id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser', 'retaillite'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
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

			case 'complaints':
				$request['table']= '\App\Model\Complaint';
				$request['searchdata'] = ['type', 'solution', 'description', 'user_id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['retailer', 'apiuser', 'retaillite'])){
							$request['parentData'] = [\Auth::id()];
						}elseif(\Myhelper::hasRole(['md', 'distributor','whitelable'])){
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
					$request['parentData'] = [\Auth::id()];
				}
				$request['whereIn'] = 'user_id';
				break;

			case 'paepsagentstatement':
				$request['table']= '\App\Model\Aepsuser';
				$request['searchdata'] = ['merchantPhoneNumber','merchantLoginId'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if (\Myhelper::hasRole(['admin'])){
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
				
			case 'faepsagentstatement':
				$request['table']= '\App\Model\Fingagent';
				$request['searchdata'] = ['id'];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				if ($id == 0 || $returntype == "all") {
					if($id == 0){
						if (\Myhelper::hasRole(['apiuser'])){
							$request['parentData'] = [\Auth::id()];
						}else{
							$request['parentData'] = 'all';
						}
					}else{
						if (\Myhelper::hasRole(['apiuser'])){
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

			case 'payoutaccount':
				$request['table']= '\App\Model\Contact';
				$request['searchdata'] = [];
				$request['select'] = 'all';
				$request['order'] = ['id','DESC'];
				$request['parentData'] = [\Auth::id()];
				$request['whereIn'] = 'user_id';
				break;

			case 'apiswitchamount':
			case 'apiswitchstate':
			case 'apiswitchuser':
				$request['table']= '\App\Model\Apiswitch';
				$request['searchdata'] = ['id'];
				$request['select'] = 'all';
				$request['order'] = ['id','desc'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;

			case 'apiswitchoperator':
				$request['table']= '\App\Model\IntegrationOperator';
				$request['searchdata'] = ['id'];
				$request['select'] = 'all';
				$request['order'] = ['id','desc'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;

			case 'apiswitchcircle':
				$request['table']= '\App\Model\IntegrationCircle';
				$request['searchdata'] = ['id'];
				$request['select'] = 'all';
				$request['order'] = ['id','desc'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;

			case 'rintegrationlist':
				$request['table']= '\App\Model\Integration';
				$request['searchdata'] = ['id'];
				$request['select'] = 'all';
				$request['order'] = ['id','desc'];
				$request['parentData'] = 'all';
				$request['whereIn'] = 'user_id';
				break;

			case 'eintegrationlist':
				$request['table']= '\App\Model\Integration';
				$request['searchdata'] = ['id'];
				$request['select'] = 'all';
				$request['order'] = ['id','desc'];
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
			$request->type != "resourcepackage" &&
			$request->type != "fundrequestview" &&
			$request->type != "payoutaccount" &&
 			$request->payoutaccount != "payoutaccount" &&
			!in_array($request->type , ['apiuser', 'other', 'tr', 'web', 'kycpending', 'kycsubmitted', 'kycrejected'])&&
			$request->where != 1
        ){
            if(!empty($request->fromdate)){
                $data->whereDate('created_at', $request->fromdate);
            }
	    }

        switch ($request->type) {
			case 'apiuser':
				$data->whereHas('role', function ($q) use($request){
					$q->where('slug', $request->type);
				});
			break;

			case 'web':
				$data->where('type' ,'web');
			break;

			case 'other':
				$data->whereHas('role', function ($q) use($request){
					$q->whereNotIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'admin']);
				});
			break;

			case 'tr':
				$data->whereHas('role', function ($q) use($request){
					$q->whereIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'apiuser']);
				})->where('kyc', 'verified');
			break;

			case 'kycpending':
				$data->whereHas('role', function ($q) use($request){
					$q->whereIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'apiuser']);
				})->whereIn('kyc', ['pending']);
			break;

			case 'kycsubmitted':
				$data->whereHas('role', function ($q) use($request){
					$q->whereIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'apiuser']);
				})->whereIn('kyc', ['submitted']);
			break;
				
			case 'kycrejected':
				$data->whereHas('role', function ($q) use($request){
					$q->whereIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'apiuser']);
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
					case 'tr':
					case 'kycpending':
					case 'kycsubmitted':
					case 'kycrejected':
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
                  		$q->orWhere($value,'like','%'.$request->searchtext.'%');
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
}
