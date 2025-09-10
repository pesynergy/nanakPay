@extends('layouts.app')
@section('title', 'Dashboard')
@section('pagetitle', 'Dashboard')

@php
    $search = "hide";
    $header = "hide";
@endphp

@section('content')
			<!-- row -->
			<div class="container-fluid">
				<div class="form-head mb-4">
					<h2 class="text-black font-w600 mb-0">Dashboard</h2>
				</div>
				@if(Myhelper::hasRole(["admin"]))
				    <div class="row">
    					<div class="col-xl-4">
    						<div class="row">
    							<div class="col-xl-12 col-lg-6 col-md-7 col-sm-8">
    								<div class="card-bx stacked">
    									<img src="{{asset('')}}new_assests/images/card/card.png" alt="" class="mw-100">
    									<div class="card-info text-white">
    										<p class="mb-1 text-white">Overall Collection Balance</p>
    										<h2 class="fs-36 text-white mb-sm-4 mb-3">₹ {{Auth::user()->mainwallet}}</h2>
    										
    										<p class="mb-1 text-white">Overall Payout Balance</p>
    										<h2 class="fs-36 text-white mb-sm-4 mb-3">₹ {{Auth::user()->payoutwallet}}</h2>
    									</div>
    									<a href="#"><i class="fa fa-caret-down" aria-hidden="true"></i></a>
    								</div>
    							</div>
    						</div>
    					</div>
    					<div class="col-xl-8">
    						<div class="row">
    							<div class="col-xl-6 col-sm-6">
    								<div class="card">
    									<div class="card-header flex-wrap border-0 pb-0">
    										<div class="me-3 mb-2">
    											<p class="fs-14 mb-1">Today Collection Balance</p>
    											<span class="fs-24 text-black font-w600">₹ <b class="payin"></b></span>
    										</div>
    									</div>
    									<div class="card-body p-0">
    										<canvas id="widgetChart1" height="80"></canvas>
    									</div>
    								</div>
    							</div>
    							<div class="col-xl-6 col-sm-6">
    								<div class="card">
    									<div class="card-header flex-wrap border-0 pb-0">
    										<div class="me-3 mb-2">
    											<p class="fs-14 mb-1">Today Payout Withdrawal</p>
    											<span class="fs-24 text-black font-w600">₹ <b class="payout"></b></span>
    										</div>
    									</div>
    									<div class="card-body p-0">
    										<canvas id="widgetChart2" height="80"></canvas>
    									</div>
    								</div>
    							</div>
    							<div class="col-xl-12">
    							    <div class="row">
    							        <div class="col-xl-4">
    							            <div class="card">
            									<div class="card-header flex-wrap border-0 pb-0">
            										<div class="me-3 mb-2">
            											<p class="fs-14 mb-3">Payin Overview</p>
                                                        <h4>
                                                            <span class="text-black">Txn Amount: ₹</span><span class="text-black collectionsuccessamt">0</span>
                                                        </h4>
                                                        <h4>
                                                            <span class="text-black">Txn Count: </span><span class="text-black collectionsuccess">0</span>
                                                        </h4>
                                                        <h4>
                                                            <span class="text-black">Buy Rate + GST: </span><span class="text-black collectionsuccesscom">0</span>
                                                        </h4>
                                                        <h4>
                                                            <span class="text-black">Settlement Amt: </span><span class="text-black collectionsettle">0</span>
                                                        </h4>
                                                        <br>
            										</div>
            									</div>
            								</div>
    							        </div>
    							        <div class="col-xl-4">
    							            <div class="card">
            									<div class="card-header flex-wrap border-0 pb-0">
            										<div class="me-3 mb-2">
            											<p class="fs-14 mb-3">Payout Overview</p>
            											<h4>
                                                            <span class="text-black">Txn Amount: ₹</span><span class="text-black payoutsuccessamt">0</span>
                                                        </h4>
                                                        <h4>
                                                            <span class="text-black">Txn Count: </span><span class="text-black payoutsuccess">0</span>
                                                        </h4>
                                                        <h4>
                                                            <span class="text-black">Buy Rate + GST: </span><span class="text-black payoutsuccesscom">0</span>
                                                        </h4>
                                                        <br>
            										</div>
            									</div>
            								</div>
    							        </div>
    							        <div class="col-xl-4">
    							            <div class="card">
            									<div class="card-header flex-wrap border-0 pb-0">
            										<div class="me-3 mb-2">
            											<p class="fs-14 mb-3">Chargeback Overview</p>
            											<h4>
                                                            <span class="text-black">Txn Amount: ₹</span><span class="text-black chargebacksuccessamt">0</span>
                                                        </h4>
                                                        <h4>
                                                            <span class="text-black">Txn Count: </span><span class="text-black chargebacksuccess">0</span>
                                                        </h4>
                                                        <br>
            										</div>
            									</div>
            								</div>
    							        </div>
    							    </div>
    								
    							</div>
    						</div>
    					</div>
    				</div>
    				<div class="row">
                        <div class="col-xl-12 col-xxl-12 col-sm-12">
                            <div class="card">
                                <div class="card-body">
                                    <form id="filterForm">
                                        <div class="panel panel-default no-margin" style="border-radius: 10px;">
                                            <div class="panel-body p-tb-10">
                                                <div class="row">
                                                    <div class="form-group col-md-3 m-b-10">
                                                        <input type="text" name="fromdate" class="form-control mydate" placeholder="From Date">
                                                    </div>
                                                    <div class="form-group col-md-3 m-b-10">
                                                        <input type="text" name="todate" class="form-control mydate" placeholder="To Date">
                                                    </div>
                            
                                                    @if (\Myhelper::hasRole("admin"))
                                                        <!--<div class="form-group col-md-3 m-b-10">-->
                                                        <!--    <input type="text" name="userid" class="form-control" placeholder="Agent Id">-->
                                                        <!--</div>-->
                                                        <div class="form-group col-md-3 m-b-10">
                                                            <select id="userid" name="userid" class="default-select form-control wide">
                                                                <option selected>Select Agent</option>
                                                                @foreach($user_agents as $user)
                                                                    <!--<option value="{{ $user->id }}">{{ $user->name }} ({{ $user->id }}) ({{ $user->agentcode }})</option>-->
                                                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->agentcode }})</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endif
                                                    <div class="form-group col-md-3 m-b-10 pull-right">
                                                        <button type="submit" class="btn btn-primary btn-labeled legitRipple btn-lg mt-10" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-search4"></i></b> Search</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
				    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Payin Summary</h4>
                                </div>
                                <div class="card-body">
                                    <div class="custom-tab-1">
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="tab" href="#pitoday">Today</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#pithisweek">Last 7 Days</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#pithismonth">This Month</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#piall">Overall</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#picustom">Custom</a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="pitoday" role="tabpanel">
                                                <div class="pt-4">
                                                    @if($pi_today->isEmpty())
                                                        <p>No data found for today.</p>
                                                    @else
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr. No.</th>
                                                                    <th>Agent Name</th>
                                                                    <th>Success Count</th>
                                                                    <th>Pending Count</th>
                                                                    <th>Total Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($pi_today as $index => $report)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $report->user->name }} </td>
                                                                        <td>{{ $report->success_count }}</td>
                                                                        <td>{{ $report->pending_count }}</td>
                                                                        <td>{{ $report->total_success_amount }}</td>
                                                                        <!--<td>{{ $report->total_pending_amount }}</td>-->
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="pithisweek">
                                                <div class="pt-4">
                                                    @if($pi_thisweek->isEmpty())
                                                        <p>No data found for the last 7 days.</p>
                                                    @else
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr. No.</th>
                                                                    <th>Agent Name</th>
                                                                    <th>Success Count</th>
                                                                    <th>Pending Count</th>
                                                                    <th>Total Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($pi_thisweek as $index => $report)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $report->user->name }} </td>
                                                                        <td>{{ $report->success_count }}</td>
                                                                        <td>{{ $report->pending_count }}</td>
                                                                        <td>{{ $report->total_success_amount }}</td>
                                                                        <!--<td>{{ $report->total_pending_amount }}</td>-->
                                                                    </tr>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="pithismonth">
                                                <div class="pt-4">
                                                    @if($pi_thismonth->isEmpty())
                                                        <p>No data found for this month.</p>
                                                    @else
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr. No.</th>
                                                                    <th>Agent Name</th>
                                                                    <th>Success Count</th>
                                                                    <th>Pending Count</th>
                                                                    <th>Total Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($pi_thismonth as $index => $report)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $report->user->name }} </td>
                                                                        <td>{{ $report->success_count }}</td>
                                                                        <td>{{ $report->pending_count }}</td>
                                                                        <td>{{ $report->total_success_amount }}</td>
                                                                        <!--<td>{{ $report->total_pending_amount }}</td>-->
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="piall">
                                                <div class="pt-4">
                                                    @if($pi_all->isEmpty())
                                                        <p>No data found.</p>
                                                    @else
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr. No.</th>
                                                                    <th>Agent Name</th>
                                                                    <th>Success Count</th>
                                                                    <th>Pending Count</th>
                                                                    <th>Total Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($pi_all as $index => $report)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $report->user->name }} </td>
                                                                        <td>{{ $report->success_count }}</td>
                                                                        <td>{{ $report->pending_count }}</td>
                                                                        <td>{{ $report->total_success_amount }}</td>
                                                                        <!--<td>{{ $report->total_pending_amount }}</td>-->
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                            <!-- Custom Tab -->
                                            <div class="tab-pane fade" id="picustom">
                                                <div class="pt-4">
                                                    <label for="picustom_date">Select Date:</label>
                                                    <input type="date" id="picustom_date" class="form-control" style="width: 200px;" onchange="piloadCustomData()">
                                
                                                    <!-- Table -->
                                                    <table id="picustomDataTable" class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Sr. No.</th>
                                                                <th>Agent Name</th>
                                                                <th>Success Count</th>
                                                                <th>Pending Count</th>
                                                                <th>Total Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Payout Summary</h4>
                                </div>
                                <div class="card-body">
                                      
                                    <div class="custom-tab-1">
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="tab" href="#potoday">Today</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#pothisweek">Last 7 Days</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#pothismonth">This Month</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#poall">Overall</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#pocustom">Custom</a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="potoday" role="tabpanel">
                                                <div class="pt-4">
                                                    @if($po_today->isEmpty())
                                                        <p>No data found for today.</p>
                                                    @else
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr. No.</th>
                                                                    <th>Agent Name</th>
                                                                    <th>Success Count</th>
                                                                    <th>Pending Count</th>
                                                                    <th>Total Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($po_today as $index => $report)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $report->user->name }} </td>
                                                                        <td>{{ $report->success_count }}</td>
                                                                        <td>{{ $report->pending_count }}</td>
                                                                        <td>{{ $report->total_success_amount }}</td>
                                                                        <!--<td>{{ $report->total_pending_amount }}</td>-->
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="pothisweek">
                                                <div class="pt-4">
                                                    @if($po_thisweek->isEmpty())
                                                        <p>No data found for the last 7 days.</p>
                                                    @else
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr. No.</th>
                                                                    <th>Agent Name</th>
                                                                    <th>Success Count</th>
                                                                    <th>Pending Count</th>
                                                                    <th>Total Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($po_thisweek as $index => $report)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $report->user->name }} </td>
                                                                        <td>{{ $report->success_count }}</td>
                                                                        <td>{{ $report->pending_count }}</td>
                                                                        <td>{{ $report->total_success_amount }}</td>
                                                                        <!--<td>{{ $report->total_pending_amount }}</td>-->
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="pothismonth">
                                                <div class="pt-4">
                                                    @if($po_thismonth->isEmpty())
                                                        <p>No data found for this month.</p>
                                                    @else
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr. No.</th>
                                                                    <th>Agent Name</th>
                                                                    <th>Success Count</th>
                                                                    <th>Pending Count</th>
                                                                    <th>Total Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($po_thismonth as $index => $report)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $report->user->name }} </td>
                                                                        <td>{{ $report->success_count }}</td>
                                                                        <td>{{ $report->pending_count }}</td>
                                                                        <td>{{ $report->total_success_amount }}</td>
                                                                        <!--<td>{{ $report->total_pending_amount }}</td>-->
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="poall">
                                                <div class="pt-4">
                                                    @if($po_all->isEmpty())
                                                        <p>No data found.</p>
                                                    @else
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr. No.</th>
                                                                    <th>Agent Name</th>
                                                                    <th>Success Count</th>
                                                                    <th>Pending Count</th>
                                                                    <th>Total Amount</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($po_all as $index => $report)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $report->user->name }} </td>
                                                                        <td>{{ $report->success_count }}</td>
                                                                        <td>{{ $report->pending_count }}</td>
                                                                        <td>{{ $report->total_success_amount }}</td>
                                                                        <!--<td>{{ $report->total_pending_amount }}</td>-->
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                            <!-- Custom Tab -->
                                            <div class="tab-pane fade" id="pocustom">
                                                <div class="pt-4">
                                                    <label for="pocustom_date">Select Date:</label>
                                                    <input type="date" id="pocustom_date" class="form-control" style="width: 200px;" onchange="poloadCustomData()">
                                
                                                    <!-- Table -->
                                                    <table id="pocustomDataTable" class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Sr. No.</th>
                                                                <th>Agent Name</th>
                                                                <th>Success Count</th>
                                                                <th>Pending Count</th>
                                                                <th>Total Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Chargeback Summary</h4>
                                </div>
                                <div class="card-body">
                                      
                                    <div class="custom-tab-1">
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="tab" href="#cbtoday">Today</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#cbthisweek">Last 7 Days</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#cbthismonth">This Month</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#cball">Overall</a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="cbtoday" role="tabpanel">
                                                <div class="pt-4">
                                                    @if($cb_today->isEmpty())
                                                        <p>No data found for today.</p>
                                                    @else
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr. No.</th>
                                                                    <th>Agent Name</th>
                                                                    <th>Txn Count</th>
                                                                    <th>Total Amount</th>
                                                                    <th>Chargeback %</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($cb_today as $index => $report)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $report->user->name }} </td>
                                                                        <td>{{ $report->total_count }}</td>
                                                                        <td>{{ $report->chargeback_amount }}</td>
                                                                        <td>{{ number_format($report->chargeback_percentage, 2) }}%</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="cbthisweek">
                                                <div class="pt-4">
                                                    @if($cb_thisweek->isEmpty())
                                                        <p>No data found for the last 7 days.</p>
                                                    @else
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr. No.</th>
                                                                    <th>Agent Name</th>
                                                                    <th>Txn Count</th>
                                                                    <th>Total Amount</th>
                                                                    <th>Chargeback %</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($cb_thisweek as $index => $report)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $report->user->name }} </td>
                                                                        <td>{{ $report->total_count }}</td>
                                                                        <td>{{ $report->chargeback_amount }}</td>
                                                                        <td>{{ number_format($report->chargeback_percentage, 2) }}%</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="cbthismonth">
                                                <div class="pt-4">
                                                    @if($cb_thismonth->isEmpty())
                                                        <p>No data found for this month.</p>
                                                    @else
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr. No.</th>
                                                                    <th>Agent Name</th>
                                                                    <th>Txn Count</th>
                                                                    <th>Total Amount</th>
                                                                    <th>Chargeback %</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($cb_thismonth as $index => $report)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $report->user->name }} </td>
                                                                        <td>{{ $report->total_count }}</td>
                                                                        <td>{{ $report->chargeback_amount }}</td>
                                                                        <td>{{ number_format($report->chargeback_percentage, 2) }}%</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="cball">
                                                <div class="pt-4">
                                                    @if($cb_all->isEmpty())
                                                        <p>No data found for this month.</p>
                                                    @else
                                                        <table class="table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Sr. No.</th>
                                                                    <th>Agent Name</th>
                                                                    <th>Txn Count</th>
                                                                    <th>Total Amount</th>
                                                                    <th>Chargeback %</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($cb_all as $index => $report)
                                                                    <tr>
                                                                        <td>{{ $index + 1 }}</td>
                                                                        <td>{{ $report->user->name }} </td>
                                                                        <td>{{ $report->total_count }}</td>
                                                                        <td>{{ $report->chargeback_amount }}</td>
                                                                        <td>{{ number_format($report->chargeback_percentage, 2) }}%</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
				@else
				    <div class="row">
    					<div class="col-xl-4">
    						<div class="row">
    							<div class="col-xl-12 col-lg-6 col-md-7 col-sm-8">
    								<div class="card-bx stacked">
    									<img src="{{asset('')}}new_assests/images/card/card.png" alt="" class="mw-100">
    									<div class="card-info text-white">
    										<p class="mb-1 text-white">Overall Collection Balance</p>
    										<h2 class="fs-36 text-white mb-sm-4 mb-3">₹ {{Auth::user()->mainwallet}}</h2>
    										
    										<p class="mb-1 text-white">Overall Payout Balance</p>
    										<h2 class="fs-36 text-white mb-sm-4 mb-3">₹ {{Auth::user()->payoutwallet}}</h2>
    									</div>
    									<a href="#"><i class="fa fa-caret-down" aria-hidden="true"></i></a>
    								</div>
    							</div>
    						</div>
    					</div>
    					<div class="col-xl-8">
    						<div class="row">
    							<div class="col-xl-6 col-sm-6">
    								<div class="card">
    									<div class="card-header flex-wrap border-0 pb-0">
    										<div class="me-3 mb-2">
    											<p class="fs-14 mb-1">Today Collection Balance</p>
    											<span class="fs-24 text-black font-w600">₹ <h5 class="payin text-semibold"></h5></span>
    										</div>
    									</div>
    									<div class="card-body p-0">
    										<canvas id="widgetChart1" height="80"></canvas>
    									</div>
    								</div>
    							</div>
    							<div class="col-xl-6 col-sm-6">
    								<div class="card">
    									<div class="card-header flex-wrap border-0 pb-0">
    										<div class="me-3 mb-2">
    											<p class="fs-14 mb-1">Today Payout Withdrawal</p>
    											<span class="fs-24 text-black font-w600">₹ <h5 class="payout text-semibold"></h5></span>
    										</div>
    									</div>
    									<div class="card-body p-0">
    										<canvas id="widgetChart2" height="80"></canvas>
    									</div>
    								</div>
    							</div>
    							<div class="col-xl-12">
    							    <div class="row">
    							        <div class="col-xl-4">
    							            <div class="card">
            									<div class="card-header flex-wrap border-0 pb-0">
            										<div class="me-3 mb-2">
            											<p class="fs-14 mb-3">Payin Overview</p>
                                                        <h4>
                                                            <span class="text-black">Txn Amount: ₹</span><span class="text-black collectionsuccessamt">0</span>
                                                        </h4>
                                                        <h4>
                                                            <span class="text-black">Txn Count: </span><span class="text-black collectionsuccess">0</span>
                                                        </h4>
                                                        <br>
            										</div>
            									</div>
            								</div>
    							        </div>
    							        <div class="col-xl-4">
    							            <div class="card">
            									<div class="card-header flex-wrap border-0 pb-0">
            										<div class="me-3 mb-2">
            											<p class="fs-14 mb-3">Payout Overview</p>
            											<h4>
                                                            <span class="text-black">Txn Amount: ₹</span><span class="text-black payoutsuccessamt">0</span>
                                                        </h4>
                                                        <h4>
                                                            <span class="text-black">Txn Count: </span><span class="text-black payoutsuccess">0</span>
                                                        </h4>
                                                        <br>
            										</div>
            									</div>
            								</div>
    							        </div>
    							        <div class="col-xl-4">
    							            <div class="card">
            									<div class="card-header flex-wrap border-0 pb-0">
            										<div class="me-3 mb-2">
            											<p class="fs-14 mb-3">Chargeback Overview</p>
            											<h4>
                                                            <span class="text-black">Txn Amount: ₹</span><span class="text-black chargebacksuccessamt">0</span>
                                                        </h4>
                                                        <h4>
                                                            <span class="text-black">Txn Count: </span><span class="text-black chargebacksuccess">0</span>
                                                        </h4>
                                                        <br>
            										</div>
            									</div>
            								</div>
    							        </div>
    							    </div>
    							</div>
    						</div>
    					</div>
    				</div>
				@endif
			</div>
		</div>

    @if (Myhelper::hasNotRole('admin'))
        @if (Auth::user()->resetpwd == "default")
            <div id="pwdModal" class="modal fade" data-backdrop="false" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-slate">
                            <h6 class="modal-title">Change Password </h6>
                        </div>
                        <form id="passwordForm" action="{{route('profileUpdate')}}" method="post">
                            <div class="modal-body">
                                <input type="hidden" name="id" value="{{Auth::id()}}">
                                <input type="hidden" name="actiontype" value="password">
                                {{ csrf_field() }}
                                @if (Myhelper::can('password_reset'))
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label>Old Password</label>
                                            <input type="password" name="oldpassword" class="form-control" required="" placeholder="Enter Value">
                                        </div>
                                        <div class="form-group col-md-6  ">
                                            <label>New Password</label>
                                            <input type="password" name="password" id="password" class="form-control" required="" placeholder="Enter Value">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="form-group col-md-6  ">
                                            <label>Confirmed Password</label>
                                            <input type="password" name="password_confirmation" class="form-control" required="" placeholder="Enter Value">
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div id="noticeModal" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-slate">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Necessary Notice ( आवश्यक सूचना )</h4>
                </div>
                <div class="modal-body">
                    {!! nl2br($mydata['notice']) !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="addMoneyModal" class="modal fade" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-slate">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h6 class="modal-title">Add Money</h6>
                </div>
                <form id="addMoneyForm" action="{{route('fundtransaction')}}" method="post">
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="type" value="addmoney">
                            {{ csrf_field() }}
                            <div class="form-group col-md-12">
                                <label>Amount</label>
                                <input type="number" name="amount" step="any" class="form-control" placeholder="Enter Amount" required="">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-12 text-center">
                                <img class="" id="image_qr" style="width:200px;" src="#" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                        <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="getMoneyModal" class="modal fade" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-slate">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h6 class="modal-title">Get Money</h6>
                </div>
                <form id="getMoneyForm" action="{{route('fundtransaction')}}" method="post">
                    <input type="hidden" name="type" value="getmoney">
                    {{ csrf_field() }}
                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Account Holder Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter value" required="">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Account Number</label>
                                <input type="text" name="account_number" class="form-control" placeholder="Enter value" required="">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>IFSC Code</label>
                                <input type="text" name="ifsc_code" class="form-control" placeholder="Enter value" required="">
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label>Bank Name</label>
                                <input type="text" name="bank_name" step="any" class="form-control" placeholder="Enter value" required="">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Amount</label>
                                <input type="number" name="amount" step="any" class="form-control" placeholder="Enter Amount" required="">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                        <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style')
    
@endpush

@push('script')
    <script type="text/javascript" src="{{asset('')}}assets/js/plugins/forms/selects/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#userid').select2({
                placeholder: "Select an Agent",
                allowClear: true
            });
        });
    </script>
    <script>
        var salesdata = {
            dates : [],
            qrcodesales : [],
            payoutsales : [],
            payinsales  : []
        };

        $(document).ready(function(){
            $('select').select2();
            @if (Myhelper::hasNotRole('admin') && Auth::user()->resetpwd == "default")
                $('#pwdModal').modal();
            @endif

            @if ($mydata['notice'] != null && $mydata['notice'] != '')
                $('#noticeModal').modal();
            @endif

            $( "#passwordForm" ).validate({
                rules: {
                    @if (!Myhelper::can('member_password_reset'))
                    oldpassword: {
                        required: true,
                        minlength: 6,
                    },
                    password_confirmation: {
                        required: true,
                        minlength: 8,
                        equalTo : "#password"
                    },
                    @endif
                    password: {
                        required: true,
                        minlength: 8
                    }
                },
                messages: {
                    @if (!Myhelper::can('member_password_reset'))
                    oldpassword: {
                        required: "Please enter old password",
                        minlength: "Your password lenght should be atleast 6 character",
                    },
                    password_confirmation: {
                        required: "Please enter confirmed password",
                        minlength: "Your password lenght should be atleast 8 character",
                        equalTo : "New password and confirmed password should be equal"
                    },
                    @endif
                    password: {
                        required: "Please enter new password",
                        minlength: "Your password lenght should be atleast 8 character"
                    }
                },
                errorElement: "p",
                errorPlacement: function ( error, element ) {
                    if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                        error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                    } else {
                        error.insertAfter( element );
                    }
                },
                submitHandler: function () {
                    var form = $('form#passwordForm');
                    form.find('span.text-danger').remove();
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button:submit').button('loading');
                        },
                        complete: function () {
                            form.find('button:submit').button('reset');
                        },
                        success:function(data){
                            if(data.status == "success"){
                                form[0].reset();
                                form.closest('.modal').modal('hide');
                                notify("Password Successfully Changed" , 'success');
                            }else{
                                notify(data.status , 'warning');
                            }
                        },
                        error: function(errors) {
                            showError(errors, form.find('.modal-body'));
                        }
                    });
                }
            });

            $('form#filterForm').submit(function(){
                $.ajax({
                    url: "{{url('mystatics')}}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    data:{"fromdate" : $('form#filterForm').find("[name='fromdate']").val(), "todate" : $('form#filterForm').find("[name='todate']").val(), "userid" : $('form#filterForm').find("[name='userid']").val()},
                    success: function(data){
                        $.each(data, function (index, value) {
                            $('.'+index).text(value);
                        });
                    }
                });
                return false;
            });

            $.ajax({
                url: "{{url('mystatics')}}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType:'json',
                data:{"fromdate" : "{{date("Y-m-d")}}", "todate" : "{{date("Y-m-d")}}", "userid" : 0},
                success: function(data){
                    console.log(data);
                    $.each(data, function (index, value) {
                        $('.'+index).text(value);
                    });
                }
            });

            $( "#addMoneyForm").validate({
                rules: {
                    amount: {
                        required: true,
                    }
                },
                messages: {
                    amount: {
                        required: "Please enter amount",
                    },
                },
                errorElement: "p",
                errorPlacement: function ( error, element ) {
                    if ( element.prop("tagName").toLowerCase() === "select" ) {
                        error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                    } else {
                        error.insertAfter( element );
                    }
                },
                submitHandler: function () {
                    var form = $('#addMoneyForm');
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button:submit').button('loading');
                        },
                        complete: function () {
                            form.find('button:submit').button('reset');
                        },
                        success:function(data){
                            if(data.status == "success"){
                                form[0].reset();
                                $('#image_qr').attr('src', data.qr_link);
                            }else{
                                notify(data.message , 'warning');
                            }
                        },
                        error: function(errors) {
                            showError(errors, form);
                        }
                    });
                }
            });

            $( "#getMoneyForm").validate({
                rules: {
                    amount: {
                        required: true,
                    }
                },
                messages: {
                    amount: {
                        required: "Please enter amount",
                    },
                },
                errorElement: "p",
                errorPlacement: function ( error, element ) {
                    if ( element.prop("tagName").toLowerCase() === "select" ) {
                        error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                    } else {
                        error.insertAfter( element );
                    }
                },
                submitHandler: function () {
                    var form = $('#getMoneyForm');
                    form.ajaxSubmit({
                        dataType:'json',
                        beforeSubmit:function(){
                            form.find('button:submit').button('loading');
                        },
                        complete: function () {
                            form.find('button:submit').button('reset');
                        },
                        success:function(data){
                            if(data.status == "success"){
                                form[0].reset();
                                notify("Successfully Transfer", 'success');
                            }else{
                                notify(data.message , 'warning');
                            }
                        },
                        error: function(errors) {
                            showError(errors, form);
                        }
                    });
                }
            });
        });
    </script>
    <!-- AJAX Script for Data Loading -->
    <script>
        function piloadCustomData() {
            let selectedDate = document.getElementById('picustom_date').value;
            if (!selectedDate) return;
        
            fetch("{{ route('piget_custom_data') }}?date=" + selectedDate)
            .then(response => response.json())
            .then(data => {
                let tableBody = document.querySelector("#picustomDataTable tbody");
                tableBody.innerHTML = ""; // Clear existing data
        
                if (data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center">No data found</td></tr>`;
                    return;
                }
        
                data.forEach((item, index) => {
                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.agent_name}</td>
                            <td>${item.success_count}</td>
                            <td>${item.pending_count}</td>
                            <td>${parseFloat(item.total_success_amount).toFixed(2)}</td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            })
            .catch(error => console.error('Error fetching custom data:', error));
        }
        function poloadCustomData() {
            let selectedDate = document.getElementById('pocustom_date').value;
            if (!selectedDate) return;
        
            fetch("{{ route('poget_custom_data') }}?date=" + selectedDate)
            .then(response => response.json())
            .then(data => {
                let tableBody = document.querySelector("#pocustomDataTable tbody");
                tableBody.innerHTML = ""; // Clear existing data
        
                if (data.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center">No data found</td></tr>`;
                    return;
                }
        
                data.forEach((item, index) => {
                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.agent_name}</td>
                            <td>${item.success_count}</td>
                            <td>${item.pending_count}</td>
                            <td>${parseFloat(item.total_success_amount).toFixed(2)}</td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            })
            .catch(error => console.error('Error fetching custom data:', error));
        }
    </script>
@endpush
