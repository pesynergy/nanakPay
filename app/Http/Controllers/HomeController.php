<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Circle;
use App\User;
use App\Model\Report;
use App\Model\Payoutreport;
use App\Model\Complaint;
use App\Model\Api;
use App\Model\Apitoken;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except(['getmysendip', 'setpermissions','checkcommission']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index()
    {
        if(\Myhelper::hasNotRole(['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'subadmin'])){
            $user = User::whereHas('role', function ($q){
                $q->where('slug', 'admin');
            })->first(['id']);

            session(['sessionUserId' => $user->id]);
        }else{
            session(['sessionUserId' => \Auth::id()]);
        }
        
        if(!session('parentData')){
            session()->put('parentData', \Myhelper::getParents(session('sessionUserId')));
        }

        $data['state'] = Circle::all();
        $roles = ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'other'];

        foreach ($roles as $role) {
            if($role == "other"){
                $data[$role] = User::whereHas('role', function($q){
                    $q->whereNotIn('slug', ['whitelable', 'md', 'distributor', 'retailer', 'apiuser', 'admin']);
                })->whereIn('id', session('parentData'))->whereIn('kyc', ['verified'])->count();
            }else{
                $data[$role] = User::whereHas('role', function($q) use($role){
                    $q->where('slug', $role);
                })->whereIn('id', session('parentData'))->whereIn('kyc', ['verified'])->count();
            }
        }

        if(\Myhelper::hasRole("admin")){
            $data['mainwallet'] = User::where('id', \Auth::id())->sum('mainwallet');
            $data['payoutwallet'] = User::where('id', \Auth::id())->sum('payoutwallet');
            $data['todaymainwallet'] = Report::where('id', \Auth::id())->sum('amount');
            $data['todaypayoutwallet'] = Payoutreport::where('user_id', '!=', \Auth::id())->where('status', 'success')->where('trans_type', 'debit')->whereDate('created_at', date('Y-m-d'))->sum('amount');
            $data['downlinemainwallet'] = round(Report::where('user_id', '!=', \Auth::id())->where('status', 'success')->where('product', 'collection')->sum('amount'), 2);
            $data['downlinepayoutwallet'] = round(Payoutreport::where('user_id', '!=', \Auth::id())->where('status', 'success')->where('trans_type', 'debit')->sum('amount'), 2);
            $data['totalpayoutcoll'] = Payoutreport::where('user_id', '!=', \Auth::id())->where('status', 'success')->where('trans_type', 'debit')->sum('amount');
            $data['downlinemainwallet1'] = round(User::where('id', '!=', \Auth::id())->sum('mainwallet'), 2);
            $data['downlinepayoutwallet1'] = round(User::where('id', '!=', \Auth::id())->sum('payoutwallet'), 2);
            $data['totalpayoutcoll1'] = Payoutreport::where('user_id', '!=', \Auth::id())->where('status', 'success')->where('trans_type', 'debit')->sum('amount');
            $data['payoutcount'] = Payoutreport::where('user_id', '!=', \Auth::id())->where('status', 'success')->where('trans_type', 'debit')->count();
            $data['user_agents'] = User::where('role_id', '!=', '1')->get();
            
            // Calculate sums for today
            $data['pi_today'] = Report::select('user_id', DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as success_count'), DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'), DB::raw('SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as total_success_amount'), DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending_amount'))->whereDate('created_at', today())->where('product', 'collection')->groupBy('user_id')->orderBy('total_success_amount', 'desc')->with('user')->get();
            $data['po_today'] = Payoutreport::select('user_id', DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as success_count'), DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'), DB::raw('SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as total_success_amount'), DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending_amount'))->whereDate('created_at', today())->whereIn('product', ['bankpayout', 'payout'])->groupBy('user_id')->orderBy('total_success_amount', 'desc')->with('user')->get();
            $data['cb_today'] = Report::select('user_id', DB::raw('SUM(CASE WHEN status = "chargeback" THEN amount ELSE 0 END) as chargeback_amount'), DB::raw('SUM(CASE WHEN status IN ("chargeback", "success") THEN amount ELSE 0 END) as total_amount'), DB::raw('COUNT(CASE WHEN status = "chargeback" THEN 1 END) as total_count'))->whereDate('created_at', today())->where('product', 'collection')->groupBy('user_id')->orderBy('chargeback_amount', 'desc')->with('user')->get()->map(function ($report) { $report->chargeback_percentage = $report->total_amount > 0 ? ($report->chargeback_amount / $report->total_amount) * 100 : 0; return $report;})->filter(function ($report) {return $report->chargeback_amount > 0;});
    
            // Calculate sums for the last 7 days
            $data['pi_thisweek'] = Report::select('user_id', DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as success_count'), DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'), DB::raw('SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as total_success_amount'), DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending_amount'))->whereBetween('created_at', [now()->subDays(7), now()])->where('product', 'collection')->groupBy('user_id')->orderBy('total_success_amount', 'desc')->with('user')->get();
            $data['po_thisweek'] = Payoutreport::select('user_id', DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as success_count'), DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'), DB::raw('SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as total_success_amount'), DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending_amount'))->whereBetween('created_at', [now()->subDays(7), now()])->whereIn('product', ['bankpayout', 'payout'])->groupBy('user_id')->orderBy('total_success_amount', 'desc')->with('user')->get();
            $data['cb_thisweek'] = Report::select('user_id', DB::raw('SUM(CASE WHEN status = "chargeback" THEN amount ELSE 0 END) as chargeback_amount'), DB::raw('SUM(CASE WHEN status IN ("chargeback", "success") THEN amount ELSE 0 END) as total_amount'), DB::raw('COUNT(CASE WHEN status = "chargeback" THEN 1 END) as total_count'))->whereBetween('created_at', [now()->subDays(7), now()])->where('product', 'collection')->groupBy('user_id')->orderBy('chargeback_amount', 'desc')->with('user')->get()->map(function ($report) { $report->chargeback_percentage = $report->total_amount > 0 ? ($report->chargeback_amount / $report->total_amount) * 100 : 0; return $report;})->filter(function ($report) {return $report->chargeback_amount > 0;});
    
            // Calculate sums for this month
            $data['pi_thismonth'] = Report::select('user_id', DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as success_count'), DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'), DB::raw('SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as total_success_amount'), DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending_amount'))->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('product', 'collection')->groupBy('user_id')->orderBy('total_success_amount', 'desc')->with('user')->get();
            $data['po_thismonth'] = Payoutreport::select('user_id', DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as success_count'), DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'), DB::raw('SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as total_success_amount'), DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending_amount'))->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->whereIn('product', ['bankpayout', 'payout'])->groupBy('user_id')->orderBy('total_success_amount', 'desc')->with('user')->get();
            $data['cb_thismonth'] = Report::select('user_id', DB::raw('SUM(CASE WHEN status = "chargeback" THEN amount ELSE 0 END) as chargeback_amount'), DB::raw('SUM(CASE WHEN status IN ("chargeback", "success") THEN amount ELSE 0 END) as total_amount'), DB::raw('COUNT(CASE WHEN status = "chargeback" THEN 1 END) as total_count'))->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('product', 'collection')->groupBy('user_id')->orderBy('chargeback_amount', 'desc')->with('user')->get()->map(function ($report) { $report->chargeback_percentage = $report->total_amount > 0 ? ($report->chargeback_amount / $report->total_amount) * 100 : 0; return $report;})->filter(function ($report) {return $report->chargeback_amount > 0;});
            
            // Calculate sums for this month
            $data['pi_all'] = Report::select('user_id', DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as success_count'), DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'), DB::raw('SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as total_success_amount'), DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending_amount'))->where('product', 'collection')->whereHas('user')->groupBy('user_id')->orderBy('total_success_amount', 'desc')->with('user')->get();
            $data['po_all'] = Payoutreport::select('user_id', DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as success_count'), DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'), DB::raw('SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as total_success_amount'), DB::raw('SUM(CASE WHEN status = "pending" THEN amount ELSE 0 END) as total_pending_amount'))->whereIn('product', ['bankpayout', 'payout'])->whereHas('user')->groupBy('user_id')->orderBy('total_success_amount', 'desc')->with('user')->get();
            $data['cb_all'] = Report::select('user_id', DB::raw('SUM(CASE WHEN status = "chargeback" THEN amount ELSE 0 END) as chargeback_amount'), DB::raw('SUM(CASE WHEN status IN ("chargeback", "success") THEN amount ELSE 0 END) as total_amount'), DB::raw('COUNT(CASE WHEN status = "chargeback" THEN 1 END) as total_count'))->where('product', 'collection')->groupBy('user_id')->orderBy('chargeback_amount', 'desc')->with('user')->get()->map(function ($report) { $report->chargeback_percentage = $report->total_amount > 0 ? ($report->chargeback_amount / $report->total_amount) * 100 : 0; return $report;})->filter(function ($report) {return $report->chargeback_amount > 0;});
        }else{
            $data['todaymainwallet'] = Report::where('id', \Auth::id())->sum('amount');
            $data['todaypayoutwallet'] = Payoutreport::where('user_id', \Auth::id())->where('status', 'success')->where('trans_type', 'debit')->whereDate('created_at', date('Y-m-d'))->sum('amount');
            $data['mainwallet'] = User::where('id', \Auth::id())->sum('mainwallet');
            $data['downlinemainwallet'] = round(User::whereIn('id', \Myhelper::getParents(session('sessionUserId')))->sum('mainwallet'), 2);
            $data['payoutwallet'] = User::where('id', \Auth::id())->sum('payoutwallet');
            $data['downlinepayoutwallet'] = round(User::whereIn('id', \Myhelper::getParents(session('sessionUserId')))->sum('payoutwallet'), 2);
            $data['payoutcount'] = Payoutreport::where('user_id', \Auth::id())->where('status', 'success')->where('trans_type', 'debit')->count();
            $data['user_agents'] = [];
            $data['pi_today'] = [];
            $data['pi_thisweek'] = [];
            $data['pi_thismonth'] = [];
            $data['pi_all'] = [];
            $data['po_today'] = [];
            $data['po_thisweek'] = [];
            $data['po_thismonth'] = [];
            $data['po_all'] = [];
            $data['cb_today'] = [];
            $data['cb_thisweek'] = [];
            $data['cb_thismonth'] = [];
            $data['cb_all'] = [];
        }

        $data['commssion'] = \DB::table("payoutreports")->where('id', \Auth::id())->where("status", "success")->where("rtype", "commission")->sum('amount');
        return view('home')->with($data);
    }
    
    public function pigetCustomData(Request $request)
    {
        $query = Report::select(
            'user_id',
            DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as success_count'),
            DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'),
            DB::raw('COALESCE(SUM(CASE WHEN status = "success" THEN amount ELSE 0 END), 0) as total_success_amount')
        )
        ->where('product', 'collection')
        ->whereHas('user')
        ->groupBy('user_id')
        ->orderBy('total_success_amount', 'desc')
        ->with('user');

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $data = $query->get()->map(function ($report, $index) {
            return [
                'sr_no' => $index + 1,
                'agent_name' => $report->user->name ?? 'N/A',
                'success_count' => $report->success_count,
                'pending_count' => $report->pending_count,
                'total_success_amount' => (float) $report->total_success_amount
            ];
        });

        return response()->json($data);
    }

    public function pogetCustomData(Request $request)
    {
        $query = Payoutreport::select(
            'user_id',
            DB::raw('COUNT(CASE WHEN status = "success" THEN 1 END) as success_count'),
            DB::raw('COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_count'),
            DB::raw('COALESCE(SUM(CASE WHEN status = "success" THEN amount ELSE 0 END), 0) as total_success_amount')
        )
        ->whereIn('product', ['bankpayout', 'payout'])
        ->whereHas('user')
        ->groupBy('user_id')
        ->orderBy('total_success_amount', 'desc')
        ->with('user');

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $data = $query->get()->map(function ($report, $index) {
            return [
                'sr_no' => $index + 1,
                'agent_name' => $report->user->name ?? 'N/A',
                'success_count' => $report->success_count,
                'pending_count' => $report->pending_count,
                'total_success_amount' => (float) $report->total_success_amount
            ];
        });

        return response()->json($data);
    }
    
    public function setpermissions()
    {
        $users = User::whereHas('role', function($q){ $q->whereIn('slug' ,['whitelable','md', 'distributor','retailer','retaillite']); })->get();
        \DB::table('user_permissions')->where('permission_id', 97)->delete();
        foreach ($users as $user) {
            \DB::table('user_permissions')->insert(['user_id'=> $user->id , 'permission_id'=> "97"]);
        }
    }

    public function setscheme()
    {
        $bcids = App\Model\Mahaagent::get(['phone1', 'id']);
        foreach ($bcids as $user) {
            $userdata = User::where('mobile', $user->phone1)->first(['id']);
            if($userdata){
                App\Model\Mahaagent::where('id', $user->id)->update(['user_id' => $userdata->id]);
            }
        }
    }

    public function mydata()
    {
        $data['fundrequest'] = \App\Model\Fundreport::where('credited_by', session("loginid"))->where('status', 'pending')->count();
        $data['member'] = \App\User::where('status', 'block')->where('kyc', 'pending')->count();
        $data['kycpending']      = User::where('kyc', 'pending')->whereHas('role', function ($q){
            $q->whereIn('slug', ['apiuser']);
        })->count();
        $data['kycsubmitted']    = User::where('kyc', 'submitted')->whereHas('role', function ($q){
            $q->whereIn('slug', ['apiuser']);
        })->count();
        $data['kycrejected']     = User::where('kyc', 'rejected')->whereHas('role', function ($q){
            $q->whereIn('slug', ['apiuser']);
        })->count();
        $data['complaint'] = Complaint::where('status', 'pending')->count();
        $data['apitoken']  = Apitoken::where('status', '0')->count();
        $data['pendingApprovals'] = $data['complaint'] + $data['apitoken'];
        $data['payin'] = \DB::table("reports")->where('user_id', session("loginid"))->whereDate("created_at", date("Y-m-d"))->where("status", "success")->where("rtype", "main")->where("product", "payin")->sum("amount");
        $data['payout'] = \DB::table("payoutreports")->where('user_id', session("loginid"))->whereDate("created_at", date("Y-m-d"))->where("status", "success")->where("rtype", "main")->where("product", "payout")->sum("amount");
        return response()->json($data);      
    }

    public function statics(Request $post)
    {
        if(\Myhelper::hasRole("apiuser")){
            $userid = \Auth::id();
        }else{
            $userid = $post->userid;
        }

        $product = [
            'payout',
            'collection',
            'qrcharge',
            'commission',
            'chargeback'
        ];

        $statuscount = [ 'success' => ['success']];

        foreach ($product as $value) {
            foreach ($statuscount as $keys => $values) {    
                $date = "created_at";
                switch ($value) {
                    case 'payout':
                        $query = \DB::table('payoutreports')->where("rtype", "main")->where("user_id", "!=" , 1);
                        if($userid != 0){
                            $query->where("user_id", $userid);
                        }else{
                            if(\Myhelper::hasRole(['subadmin'])){
                                $query->whereIn("user_id", \Myhelper::getParents(\Auth::id()));
                            }
                        }
                        $query->where(function($q) {
                            $q->where('product', 'payout')
                              ->orWhere('product', 'bankpayout');
                        });
                        break;
                        
                    case 'collection':
                        $query = \DB::table('reports')->where("rtype", "main")->where("user_id", "!=" , 1);
                        if($userid != 0){
                            $query->where("user_id", $userid);
                        }else{
                            if(\Myhelper::hasRole(['subadmin'])){
                                $query->whereIn("user_id", \Myhelper::getParents(\Auth::id()));
                            }
                        }
                        $query->where('product', 'collection');
                        break;
                        
                    case 'qrcharge':
                        $query = \DB::table('reports')->where("rtype", "main")->where("user_id", "!=" , 1);
                        if($userid != 0){
                            $query->where("user_id", $userid);
                        }else{
                            if(\Myhelper::hasRole(['subadmin'])){
                                $query->whereIn("user_id", \Myhelper::getParents(\Auth::id()));
                            }
                        }
                        $query->where('product', 'qrcharge');
                        $values = ["success", "pending"];
                        break;
                        
                    case 'chargeback':
                        $query = \DB::table('reports')->where("rtype", "main")->where("user_id", "!=" , 1);
                        if($userid != 0){
                            $query->where("user_id", $userid);
                        }else{
                            if(\Myhelper::hasRole(['subadmin'])){
                                $query->whereIn("user_id", \Myhelper::getParents(\Auth::id()));
                            }
                        }
                        $query->where('product', 'collection');
                        $date = "updated_at";
                        $values = ["chargeback"];
                        break;
                }
                

                if((isset($post->fromdate) && !empty($post->fromdate)) && (isset($post->todate) && !empty($post->todate))){
                    if($post->fromdate == $post->todate){
                        $query->whereDate($date,'=', Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'));
                    }else{
                        $query->whereBetween($date, [Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'), Carbon::createFromFormat('Y-m-d', $post->todate)->addDay(1)->format('Y-m-d')]);
                    }
                }elseif (isset($post->fromdate) && !empty($post->fromdate)) {
                    $query->whereDate($date,'=', Carbon::createFromFormat('Y-m-d', $post->fromdate)->format('Y-m-d'));
                }else{
                    $query->whereDate($date,'=', date('Y-m-d'));
                }
                
                $data[$value.$keys] = $query->whereIn('status', $values)->count();
                $amt = $query->whereIn('status', $values)->sum("amount");
                $data[$value.$keys."amt"] = $amt;
                $charges = round($query->whereIn('status', $values)->selectRaw('SUM(charge + gst) as total')->value('total') ?? 0, 2);
                $data[$value.$keys."com"] = $charges;
                $data[$value."settle"] = $amt-$charges;
            }
        }

        return response()->json($data);      
    }
}
