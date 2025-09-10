<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Exports\ReportExport;
use App\User;

class ExportController extends Controller
{
    public $admin;
    public function __construct()
    {
        $this->admin = User::whereHas('role', function ($q){
            $q->where('slug', 'admin');
        })->first();
    }

    public function export(Request $request, $type)
    {
        $userid = $request->user_id;
        $data = [];
        $parentData = \Myhelper::getParents($userid);
        
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '-1');
        $data = [];

        switch ($type) {
            case 'payin':
            case 'upiintent':
            case 'oldpayout':
                $tables = "reports";
                $query  = \DB::table($tables)->leftJoin('users', 'users.id', '=', $tables.'.user_id')
                ->leftJoin('apis', 'apis.id', '=', $tables.'.api_id')
                ->leftJoin('providers', 'providers.id', '=', $tables.'.provider_id')
                ->orderBy($tables.'.id', 'desc')
                ->where($tables.'.rtype', "main");

                if($type == "oldpayout"){
                    $query->where($tables.'.product', "payout");
                }else{
                    $query->where($tables.'.status', "success")
                    ->where($tables.'.product', $type);
                }

                if(!empty($request->agent) && \Myhelper::hasRole("admin")){
                    $query->where($tables.'.user_id', $request->agent);
                }else{
                    if(\Myhelper::hasNotRole("admin")){
                        $query->whereIn($tables.'.user_id', $parentData);
                    }
                }

                $dateFilter = 1;
                if(!empty($request->searchtext)){
                    $serachDatas = ['number', 'txnid', 'payid', 'refno', 'id'];
                    $query->where( function($q) use($request, $serachDatas, $tables){
                        foreach ($serachDatas as $value) {
                            $q->orWhere($tables.".".$value , 'like', '%'.$request->searchtext.'%');
                        }
                    });
                    $dateFilter = 0;
                }

                if(isset($request->product) && !empty($request->product)){
                    $query->where($tables.'.api_id', $request->product);
                    $dateFilter = 0;
                }
                
                if(isset($request->status) && $request->status != '' && $request->status != null){
                    $query->where($tables.'.status', $request->status);
                    $dateFilter = 0;
                }

                if((isset($request->fromdate) && !empty($request->fromdate)) && (isset($request->todate) && !empty($request->todate))){
                    if($request->fromdate == $request->todate){
                        $query->whereDate($tables.'.created_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
                    }else{
                        $query->whereBetween($tables.'.created_at', [Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->addDay(1)->format('Y-m-d')]);
                    }
                }elseif($dateFilter && isset($request->fromdate) && !empty($request->fromdate)){
                    $query->whereDate($tables.'.created_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
                }

                $selects = [
                    $tables.'.id', 
                    'providers.name as providername',
                    $tables.'.mobile', 
                    $tables.'.option4', 
                    $tables.'.number', 
                    $tables.'.apitxnid', 
                    $tables.'.txnid' , 
                    $tables.'.apitxnid' , 
                    $tables.'.refno' , 
                    $tables.'.amount',
                    $tables.'.charge', 
                    $tables.'.profit', 
                    $tables.'.tds'   , 
                    $tables.'.status', 
                    $tables.'.trans_type', 
                    $tables.'.user_id', 
                    'users.name as username', 
                    'users.mobile as usermobile', 
                    $tables.'.remark', 
                    $tables.'.created_at'
                ];

                $titles = [
                    'Id', 
                    'Provider',
                    'Upi Name',
                    'Upi Id',
                    'Mobile', 
                    'Api Txnid', 
                    'Txnid', 
                    'Api Txnid', 
                    'Refno', 
                    'Amount', 
                    'Charge',
                    'Profit', 
                    'Tds', 
                    'Status',  
                    'Type',
                    'Agent Id', 
                    'Agent Name', 
                    'Agent Mobile' , 
                    'Remark',
                    'Craete Time',
                ];
                $exportData = $query->select($selects)->get()->toArray();
                break;            
                
            case 'chargeback':
                $tables = "reports";
                $query  = \DB::table($tables)->leftJoin('users', 'users.id', '=', $tables.'.user_id')
                ->leftJoin('apis', 'apis.id', '=', $tables.'.api_id')
                ->leftJoin('providers', 'providers.id', '=', $tables.'.provider_id')
                ->orderBy($tables.'.id', 'desc')
                ->where($tables.'.rtype', "main")
                ->where($tables.'.status', "chargeback")
                ->where($tables.'.product', 'payin');

                if(!empty($request->agent) && \Myhelper::hasRole("admin")){
                    $query->where($tables.'.user_id', $request->agent);
                }else{
                    if(\Myhelper::hasNotRole("admin")){
                        $query->whereIn($tables.'.user_id', $parentData);
                    }
                }

                $dateFilter = 1;
                if(!empty($request->searchtext)){
                    $serachDatas = ['number', 'txnid', 'payid', 'refno', 'id'];
                    $query->where( function($q) use($request, $serachDatas, $tables){
                        foreach ($serachDatas as $value) {
                            $q->orWhere($tables.".".$value , 'like', '%'.$request->searchtext.'%');
                        }
                    });
                    $dateFilter = 0;
                }

                if(isset($request->product) && !empty($request->product)){
                    $query->where($tables.'.api_id', $request->product);
                    $dateFilter = 0;
                }
                
                if(isset($request->status) && $request->status != '' && $request->status != null){
                    $query->where($tables.'.status', $request->status);
                    $dateFilter = 0;
                }

                if((isset($request->fromdate) && !empty($request->fromdate)) && (isset($request->todate) && !empty($request->todate))){
                    if($request->fromdate == $request->todate){
                        $query->whereDate($tables.'.updated_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
                    }else{
                        $query->whereBetween($tables.'.updated_at', [Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->addDay(1)->format('Y-m-d')]);
                    }
                }elseif($dateFilter && isset($request->fromdate) && !empty($request->fromdate)){
                    $query->whereDate($tables.'.updated_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
                }

                $selects = [
                    $tables.'.id', 
                    'providers.name as providername',
                    $tables.'.mobile', 
                    $tables.'.option4', 
                    $tables.'.number', 
                    $tables.'.apitxnid', 
                    $tables.'.txnid' , 
                    $tables.'.refno' , 
                    $tables.'.amount',
                    $tables.'.charge', 
                    $tables.'.profit', 
                    $tables.'.tds'   , 
                    $tables.'.status', 
                    $tables.'.trans_type', 
                    $tables.'.user_id', 
                    'users.name as username', 
                    'users.mobile as usermobile', 
                    $tables.'.remark', 
                    $tables.'.updated_at'
                ];


                $titles = [
                    'Id', 
                    'Provider',
                    'Upi Name',
                    'Upi Id',
                    'Mobile', 
                    'Api Txnid', 
                    'Txnid', 
                    'Refno', 
                    'Amount', 
                    'Charge',
                    'Profit', 
                    'Tds', 
                    'Status',  
                    'Type',
                    'Agent Id', 
                    'Agent Name', 
                    'Agent Mobile' , 
                    'Remark',
                    'Craete Time',
                ];
                $exportData = $query->select($selects)->get()->toArray();
                break;
            
            case 'payout':
                $tables = "payoutreports";
                $query  = \DB::table($tables)->leftJoin('users', 'users.id', '=', $tables.'.user_id')
                        ->leftJoin('apis', 'apis.id', '=', $tables.'.api_id')
                        ->leftJoin('providers', 'providers.id', '=', $tables.'.provider_id')
                        ->orderBy($tables.'.id', 'desc')
                        ->where($tables.'.rtype', "main");

                switch ($request->type) {
                    case 'payout':
                        $query->where($tables.'.product', 'payout');
                        break;
                }

                if(\Myhelper::hasRole("apiuser")){
                    $query->where($tables.'.user_id', \Auth::id());
                }elseif(\Myhelper::hasRole("subadmin")){
                    $query->where($tables.'.user_id', \Myhelper::getParents($userid));
                }else{
                    if(!empty($request->agent) && (\Myhelper::hasRole("admin", $userid) || in_array($request->agent, \Myhelper::getParents($userid)))){
                        $query->where($tables.'.user_id', $request->agent);
                    }
                }

                $dateFilter = 1;
                if(!empty($request->searchtext)){
                    $serachDatas = ['number', 'txnid', 'payid', 'refno', 'id'];
                    $query->where( function($q) use($request, $serachDatas, $tables){
                        foreach ($serachDatas as $value) {
                            $q->orWhere($tables.".".$value , 'like', '%'.$request->searchtext.'%');
                        }
                    });
                    $dateFilter = 0;
                }

                if(isset($request->product) && !empty($request->product)){
                    $query->where($tables.'.api_id', $request->product);
                    $dateFilter = 0;
                }
                
                if(isset($request->status) && $request->status != '' && $request->status != null){
                    $query->where($tables.'.status', $request->status);
                    $dateFilter = 0;
                }

                if((isset($request->fromdate) && !empty($request->fromdate)) && (isset($request->todate) && !empty($request->todate))){
                    if($request->fromdate == $request->todate){
                        $query->whereDate($tables.'.created_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
                    }else{
                        $query->whereBetween($tables.'.created_at', [Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->addDay(1)->format('Y-m-d')]);
                    }
                }elseif($dateFilter && isset($request->fromdate) && !empty($request->fromdate)){
                    $query->whereDate($tables.'.created_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
                }

                $selects = [
                    $tables.'.id', 
                    'providers.name as providername',
                    $tables.'.mobile', 
                    $tables.'.number', 
                    $tables.'.txnid' , 
                    $tables.'.payid' , 
                    $tables.'.refno' , 
                    $tables.'.amount',
                    $tables.'.charge', 
                    $tables.'.profit', 
                    $tables.'.tds'   , 
                    $tables.'.status', 
                    $tables.'.trans_type', 
                    $tables.'.option1', 
                    $tables.'.user_id', 
                    'users.name as username', 
                    'users.mobile as usermobile', 
                    $tables.'.remark', 
                    $tables.'.created_at'
                ];

                $titles = [
                    'Id', 
                    'Provider',
                    'Number',
                    'Mobile', 
                    'Txnid', 
                    'Payid', 
                    'Refno', 
                    'Amount', 
                    'Charge',
                    'Profit', 
                    'Tds', 
                    'Status',  
                    'Type',
                    'Product',
                    'Agent Id', 
                    'Agent Name', 
                    'Agent Mobile' , 
                    'Remark',
                    'Craete Time',
                ];
                $exportData = $query->select($selects)->get()->toArray();
                break;

            case 'mainwallet':
                $tables = "reports";
                $query = \DB::table($tables)->leftJoin('users', 'users.id', '=', $tables.'.credit_by')
                        ->leftJoin('providers', 'providers.id', '=', $tables.'.provider_id')
                        ->orderBy($tables.'.id', 'desc');

                if(!empty($request->agent) && \Myhelper::hasRole("admin", $userid)){
                    $query->where($tables.'.user_id', $request->agent);
                }
                $dateFilter = 1;
                
                if(!empty($request->searchtext)){
                    $serachDatas = ['number', 'txnid', 'payid', 'refno', 'id'];
                    $query->where( function($q) use($request, $serachDatas, $tables){
                        foreach ($serachDatas as $value) {
                            $q->orWhere($tables.".".$value , 'like', '%'.$request->searchtext.'%');
                        }
                    });
                    $dateFilter = 0;
                }

                if((isset($request->fromdate) && !empty($request->fromdate)) && (isset($request->todate) && !empty($request->todate))){
                    if($request->fromdate == $request->todate){
                        $query->whereDate($tables.'.created_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
                    }else{
                        $query->whereBetween($tables.'.created_at', [Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->addDay(1)->format('Y-m-d')]);
                    }
                }elseif($dateFilter && isset($request->fromdate) && !empty($request->fromdate)){
                    $query->whereDate($tables.'.created_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
                }

                $selects = ['id','mobile' ,'number', 'txnid', 'api_id', 'amount', 'profit', 'charge','tds', 'gst', 'payid', 'refno', 'balance', 'status', 'rtype', 'trans_type', 'user_id', 'credit_by', 'created_at', 'product', 'remark','option1', 'option3', 'option2', 'option5', 'closing'];

                foreach ($selects as $select) {
                    $selectData[] = $tables.".".$select;
                }

                $selectData[] = 'users.name as username';
                $selectData[] = 'users.mobile as usermobile';
                $datas = $query->select($selectData)->get();

                $titles = ['Date', 'User Details', 'Transaction Details', 'Transaction Type', 'Status', "Product", "Amount", "Commission", "Charge", "Tds", 'Opening Balance', 'Credit', 'Debit', "Closing"];

                $exportData = [];
                
                foreach ($datas as $record) {
                    $data['created_at']  = $record->created_at;
                    $data['userdetails'] = $record->username." (".$record->user_id.")";
                    $data['txn']     = $record->number."/".$record->mobile."/".$record->refno;
                    $data['number']  = $record->trans_type;
                    $data['status']  = $record->status;
                    $data['product'] = $record->product;
                    $data['amount']  = " ".round($record->amount, 2);
                    $data['profit']  = " ".round($record->profit, 2);
                    $data['charge']  = " ".round($record->charge, 2);
                    $data['tds']     = " ".round($record->tds, 2);
                    $data['balance'] = " ".round($record->balance, 2);

                    $debit = $record->balance - $record->closing;

                    if($debit < 0){
                        $debit = -1 * $debit;
                    }

                    if($record->trans_type == "credit"){
                        $data['credit'] = $debit;
                        $data['debit']  = '';
                    }elseif($record->trans_type == "debit"){
                        $data['credit'] = '';
                        $data['debit']  = $debit;
                    }
                    $data['closing'] = " ".round($record->closing, 2);
                    array_push($exportData, $data);
                }
                break;
            
            case 'payoutwallet':
                $tables = "payoutreports";
                $query = \DB::table($tables)->leftJoin('users', 'users.id', '=', $tables.'.credit_by')
                        ->leftJoin('providers', 'providers.id', '=', $tables.'.provider_id')
                        ->orderBy($tables.'.id', 'desc');

                if(!empty($request->agent) && \Myhelper::hasRole("admin", $userid)){
                    $query->where($tables.'.user_id', $request->agent);
                }
                $dateFilter = 1;
                
                if(!empty($request->searchtext)){
                    $serachDatas = ['number', 'txnid', 'payid', 'refno', 'id'];
                    $query->where( function($q) use($request, $serachDatas, $tables){
                        foreach ($serachDatas as $value) {
                            $q->orWhere($tables.".".$value , 'like', '%'.$request->searchtext.'%');
                        }
                    });
                    $dateFilter = 0;
                }

                if((isset($request->fromdate) && !empty($request->fromdate)) && (isset($request->todate) && !empty($request->todate))){
                    if($request->fromdate == $request->todate){
                        $query->whereDate($tables.'.created_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
                    }else{
                        $query->whereBetween($tables.'.created_at', [Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->addDay(1)->format('Y-m-d')]);
                    }
                }elseif($dateFilter && isset($request->fromdate) && !empty($request->fromdate)){
                    $query->whereDate($tables.'.created_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
                }

                $selects = ['id','mobile' ,'number', 'txnid', 'api_id', 'amount', 'profit', 'charge','tds', 'gst', 'payid', 'refno', 'balance', 'status', 'rtype', 'trans_type', 'user_id', 'credit_by', 'created_at', 'product', 'remark','option1', 'option3', 'option2', 'option5', 'closing'];

                foreach ($selects as $select) {
                    $selectData[] = $tables.".".$select;
                }

                $selectData[] = 'users.name as username';
                $selectData[] = 'users.mobile as usermobile';
                $selectData[] = 'apis.type as apitype';

                $datas = $query->select($selectData)->get();

                $titles = ['Date', 'User Details', 'Transaction Details', 'Transaction Type', 'Status', "Product", "Amount", "Commission", "Charge", "Tds", 'Opening Balance', 'Credit', 'Debit', "Closing"];

                $exportData = [];
                
                foreach ($datas as $record) {
                    $data['created_at']  = $record->created_at;
                    $data['userdetails'] = $record->username." (".$record->user_id.")";
                    $data['txn']     = $record->number."/".$record->mobile."/".$record->refno;
                    $data['number']  = $record->trans_type;
                    $data['status']  = $record->status;
                    $data['product'] = $record->product;
                    $data['amount']  = " ".round($record->amount, 2);
                    $data['profit']  = " ".round($record->profit, 2);
                    $data['charge']  = " ".round($record->charge, 2);
                    $data['tds']     = " ".round($record->tds, 2);
                    $data['balance'] = " ".round($record->balance, 2);

                    $debit = $record->balance - $record->closing;
                    if($debit < 0){
                        $debit = -1 * $debit;
                    }

                    if($record->trans_type == "credit"){
                        $data['credit'] = $debit;
                        $data['debit']  = '';
                    }elseif($record->trans_type == "debit"){
                        $data['credit'] = '';
                        $data['debit']  = $debit;
                    }

                    $data['closing'] = " ".round($record->balance, 2);
                    array_push($exportData, $data);
                }
                break;
            
            case 'fundrequest':
                $table = "fundreports";
                $query = \DB::table($table)
                        ->leftJoin('users as user', 'user.id', '=', $table.'.user_id')
                        ->leftJoin('users as sender', 'sender.id', '=', $table.'.credited_by')
                        ->leftJoin('fundbanks as fundbank', 'fundbank.id', '=', $table.'.fundbank_id')
                        ->orderBy($table.'.id', 'desc');
                $dateFilter = 1;

                if(!empty($request->searchtext)){
                    $serachDatas = ['ref_no', 'amount', 'id'];
                    $query->where( function($q) use($request, $serachDatas, $table){
                        foreach ($serachDatas as $value) {
                            $q->orWhere($table.".".$value , $request->searchtext);
                        }
                    });
                    $dateFilter = 0;
                }
                if((isset($request->fromdate) && !empty($request->fromdate)) && (isset($request->todate) && !empty($request->todate))){
                    if($request->fromdate == $request->todate){
                        $query->whereDate($table.'.created_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
                    }else{
                        $query->whereBetween($table.'.created_at', [Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $request->todate)->addDay(1)->format('Y-m-d')]);
                    }
                }elseif($dateFilter && isset($request->fromdate) && !empty($request->fromdate)){
                    $query->whereDate($table.'.created_at','=', Carbon::createFromFormat('Y-m-d', $request->fromdate)->format('Y-m-d'));
                }

                $selects = ['created_at', 'type', 'ref_no', 'paydate', 'remark', 'status', 'paymode', 'amount'];

                foreach ($selects as $select) {
                    $selectData[] = $table.".".$select;
                }

                $selectData[] = 'fundbank.account as bankaccount';
                $selectData[] = 'fundbank.branch as bankbranch';
                $selectData[] = 'fundbank.name as bankname';
                $selectData[] = 'user.name as username';
                $selectData[] = 'user.mobile as usermobile';
                $selectData[] = 'sender.name as sendername';

                $exportData = $query->select($selectData)->get();
                $titles = ['Date', 'Type', "Ref No", 'Pay Date', 'Remark', 'Status', "Paymode", "Amount", "Deposite Account", 'Deposite Branch', "Deposite Bank", "Agent Name", "Agent Mobile", "Approved By"];
                break;

            case 'apiuser':
            case 'web':
            case 'kycpending':
            case 'kycsubmitted':
            case 'kycrejected':
            case 'subadmin':
                $table = "users";
                $query = \DB::table('users');
                $query->leftJoin('companies', 'companies.id', '=', 'users.company_id');
                $query->leftJoin('roles', 'roles.id', '=', 'users.role_id');
                $query->leftJoin('users as parents', 'parents.id', '=', 'users.parent_id');

                switch ($request->type) {
                    case 'statehead':
                    case 'asm':
                    case 'whitelable':
                    case 'md':
                    case 'distributor':
                    case 'retailer':
                        $query->where('roles.slug', $type);
                    break;

                    case 'web':
                        $query->where($table.'.type', "web");
                    break;

                    case 'other':
                        $query->whereNotIn($table.'.slug', ['statehead', 'asm', 'whitelable', 'md', 'distributor', 'retailer', 'admin']);
                    break;

                    case 'kycpending':
                        $query->whereIn($table.'.kyc', ['pending']);
                    break;

                    case 'kycsubmitted':
                        $query->whereIn($table.'.kyc', ['submitted']);
                    break;
                        
                    case 'kycrejected':
                        $$query->whereIn($table.'.kyc', ['rejected']);
                    break;
                }

                $titles = [
                    'Id', 
                    'Date' ,
                    'Name', 
                    'Email', 
                    'Mobile', 
                    'Role', 
                    'Main Balance', 
                    'Parent Name', 
                    'Parent Mobile', 
                    'Company', 
                    'Status' ,
                    'address', 
                    'City', 
                    'State',
                    'Pincode',
                    'Shopname',
                    'Pancard',
                    'Aadhar Card'
                ];

                $selects = [
                    $table.'.id', 
                    $table.'.created_at', 
                    $table.'.name', 
                    $table.'.email', 
                    $table.'.mobile', 
                    'roles.name as rolename', 
                    $table.'.mainwallet', 
                    'parents.name   as parentname', 
                    'parents.mobile as parentmobile', 
                    'companies.companyname' , 
                    $table.'.status' , 
                    $table.'.address',
                    $table.'.city', 
                    $table.'.state', 
                    $table.'.pincode'   , 
                    $table.'.shopname', 
                    $table.'.pancard', 
                    $table.'.aadharcard'
                ];
                $exportData = $query->select($selects)->get()->toArray();
            break;
        }

        $excelData[] = $titles;
        $excelData[] = json_decode(json_encode($exportData), true);
        
        $export = new ReportExport($excelData);
        return \Excel::download($export, $type.'.csv');
    }
}
