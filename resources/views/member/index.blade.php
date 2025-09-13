@extends('layouts.app')
@section('title', ucwords($type).' List')
@section('pagetitle',  ucwords($type).' List')

@php
    $table = "yes";
    $export = $type;

    $table = "yes";
    switch($type){
        case 'kycpending':
        case 'kycsubmitted':
        case 'kycrejected':
            $status['type'] = "Kyc";
            $status['data'] = [
                "pending" => "Pending",
                "submitted" => "Submitted",
                "verified" => "Verified",
                "rejected" => "Rejected",
            ];
        break;

        default:
            $status['type'] = "member";
            $status['data'] = [
                "active" => "Active",
                "block" => "Block"
            ];
        break;
    }
@endphp

@section('content')
<div class="default-height">
	<!-- row -->
	<div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="panel-title">{{isset($role->name) ? $role->name : $type}} List</h4>
                        @if (Myhelper::hasRole('admin') || ($role || sizeOf($roles) > 0))
                            <div class="heading-elements">
                                <a href="{{route('member', ['type' => $type, 'action' => 'create'])}}"><button type="button" class="btn btn-sm btn-info btn-raised heading-btn legitRipple">
                                    <i class="flaticon-381-add-1" style="padding-right:5px;"></i> Add New
                                </button></a>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table-bordered nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Partner Code</th>
                                        <th>Status</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Main Wallet</th>
                                        <th>Payout Wallet</th>
                                        <th>Hold Balance</th>
                                        <th>Reports</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="transferModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                <h6 class="modal-title">Fund Transfer / Return</h6>
            </div>
            <form id="transferForm" action="{{route('fundtransaction')}}" method="post">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="payee_id">
                        {{ csrf_field() }}
                        <div class="form-group col-md-6">
                            <label>Wallet Type</label>
                            <select name="wallet" class="form-control select" id="select" required>
                                <option value="">Select Wallet</option>
                                <option value="mainwallet">Collection Wallet</option>
                                <option value="payoutwallet">Payout Wallet</option>
                                <option value="collectionpayoutwallet">Collection To Payout Wallet</option>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Fund Action</label>
                            <select name="type" class="form-control select" id="select" required>
                                <option value="">Select Action</option>
                                @if (Myhelper::can('fund_transfer'))
                                <option value="transfer">Transfer</option>
                                @endif
                                @if (Myhelper::can('fund_return'))
                                <option value="return">Return</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Amount</label>
                            <input type="number" name="amount" step="any" class="form-control" placeholder="Enter Amount" required="">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Ref No</label>
                            <input type="text" name="refno" step="any" class="form-control" placeholder="Enter value" required="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label>Remark</label>
                            <textarea name="remark" class="form-control" rows="3" placeholder="Enter Remark"></textarea>
                        </div>
                    </div>
                    @if(Myhelper::hasRole('admin'))
                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Security Pin</label>
                                <input type="password" name="mpin" autocomplete="off" class="form-control" required="">
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                   <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-info btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if (isset($permissions) && $permissions && Myhelper::can('member_permission_change'))
    <div id="permissionModal" class="modal fade right" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-slate">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h6 class="modal-title">Member Permission</h6>
                </div>
                <form id="permissionForm" action="{{route('toolssetpermission')}}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="payee_id">
                    <div class="modal-body p-0">
                        <table id="datatable" class="table table-hover table-bordered">
    	                    <thead>
    	                    <tr>
    	                        <th width="170px;">Section Category</th>
    	                        <th>
                                    <span class="pull-left m-t-5 m-l-10">Permissions</span> 
                                    <div class="md-checkbox pull-right">
                                        <input type="checkbox" id="selectall">
                                        <label for="selectall">Select All</label>
                                    </div>
                                </th>
    	                    </tr>
    	                    </thead>
    	                    <tbody>
                                @foreach ($permissions as $key => $value)
                                    <tr>
                                        <td>
                                            <div class="md-checkbox mymd">
                                                <input type="checkbox" class="selectall" id="{{ucfirst($key)}}">
                                                <label for="{{ucfirst($key)}}">{{ucfirst($key)}}</label>
                                            </div>
                                        </td>

                                        <td class="row">
                                            @foreach ($value as $permission)
                                                <div class="md-checkbox col-md-4 p-0" >
                                                    <input type="checkbox" class="case" id="{{$permission->id}}" name="permissions[]" value="{{$permission->id}}">
                                                    <label for="{{$permission->id}}">{{$permission->name}}</label>
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
    	                    </tbody>
    	                </table>
                    </div>
                    <div class="modal-footer">
                       <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-info btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<div id="commissionModal" class="modal fade right" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
                <div class="modal-header bg-slate">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Scheme Manager</h4>
            </div>
            <form id="schemeForm" method="post" action="{{ route('profileUpdate') }}">
                <div class="modal-body">
                    {!! csrf_field() !!}
                    <input type="hidden" name="id">
                    <input type="hidden" name="actiontype" value="scheme">
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Scheme</label>
                            <select class="form-control select" name="scheme_id" required="" onchange="viewCommission(this)">
                                <option value="">Select Scheme</option>
                                @foreach ($scheme as $element)
                                    <option value="{{$element->id}}">{{$element->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(Myhelper::hasRole('admin'))
                            <div class="form-group col-md-4">
                                <label>Security Pin</label>
                                <input type="password" name="mpin" autocomplete="off" class="form-control" required="">
                            </div>
                        @endif
                        <div class="form-group col-md-4">
                            <label style="width:100%">&nbsp;</label>
                            <button class="btn btn-info btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                           
                        </div>
                    </div>
                </div>
            </form>

            <div class="modal-body no-padding commissioData">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@if (Myhelper::can('member_stock_manager'))
    <div id="stockModal" class="modal fade" role="dialog" data-backdrop="false">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                    <div class="modal-header bg-slate">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Add Stock</h4>
                </div>
                <form id="stockForm" method="post" action="{{ route('profileUpdate') }}">
                    <div class="modal-body">
                        {!! csrf_field() !!}
                        <input type="hidden" name="id">
                        <input type="hidden" name="actiontype" value="stock">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>User Stock</label>
                                <input type="number" step="any" name="stock" class="form-control" required="">
                            </div>
                        </div>

                        @if(Myhelper::hasRole('admin'))
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Security Pin</label>
                                    <input type="password" name="mpin" autocomplete="off" class="form-control" required="">
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                       <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-info btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if (Myhelper::can('member_kyc_update'))
    <div id="kycUpdateModal" class="modal fade" data-backdrop="false" data-keyboard="false">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-slate">
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                    <h6 class="modal-title">Kyc Manager</h6>
                </div>
                <form id="kycUpdateForm" action="{{route('profileUpdate')}}" method="post">
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="id">
                            <input type="hidden" name="actiontype" value="kyc_change">
                            
                            {{ csrf_field() }}
                            <div class="form-group col-md-12">
                                <label>Kyc Status</label>
                                <select name="kyc" class="form-control select" id="select" required>
                                    <option value="">Select Action</option>
                                    <option value="pending">Pending</option>
                                    <option value="submitted">Submitted</option>
                                    <option value="verified">Verified</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Remark</label>
                                <textarea name="remark" class="form-control" rows="3" placeholder="Enter Remark"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                       <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-info btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if (Myhelper::can('locked_amount'))
    <div id="lockedAmountModal" class="modal fade" role="dialog" data-backdrop="false">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                    <div class="modal-header bg-slate">
                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Locked Amount</h4>
                </div>
                <form id="lockedAmountForm" method="post" action="{{ route('profileUpdate') }}">
                    <div class="modal-body">
                        {!! csrf_field() !!}
                        <input type="hidden" name="id">
                        <input type="hidden" name="actiontype" value="locakedAmount">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label>Amount</label>
                                <input type="number" step="any" name="lockedamount" class="form-control" required="">
                            </div>
                        </div>

                        @if(Myhelper::hasRole('admin'))
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Security Pin</label>
                                    <input type="password" name="mpin" autocomplete="off" class="form-control" required="">
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                       <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-info btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@endsection

@push('style')
<style>
    .md-checkbox {
        margin: 5px 0px;
    }
</style>
@endpush

@push('script')

<script type="text/javascript">
    $(document).ready(function () {
        $('[name="dataType"]').val("{{$type}}");

        var url = "{{url('statement/list/fetch')}}/{{$type}}/0";
        var onDraw = function() {
            $('input#membarStatus').on('click', function(evt){
                evt.stopPropagation();
                var ele = $(this);
                var id = $(this).val();
                var status = "block";
                if($(this).prop('checked')){
                    status = "active";
                }
                
                $.ajax({
                    url: '{{ route('profileUpdate') }}',
                    type: 'post',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    data: {'id':id, 'status':status, 'actiontype' : 'profile'}
                })
                .done(function(data) {
                    if(data.status == "success"){
                        notify("Member Updated", 'success');
                        $('#datatable').dataTable().api().ajax.reload();
                    }else{
                        if(status == "active"){
                            ele.prop('checked', false);
                        }else{
                            ele.prop('checked', true);
                        }
                        notify("Something went wrong, Try again." ,'warning');
                    }
                })
                .fail(function(errors) {
                    if(status == "active"){
                        ele.prop('checked', false);
                    }else{
                        ele.prop('checked', true);
                    }
                    showError(errors, "withoutform");
                });
            });
        };

        var options = [
            { "data" : "agentcode"},
            { "data" : "agentcode",
                render:function(data, type, full, meta){
                    var check = "";
                    var type  = "";
                    if(full.status == "active"){
                        check = "checked='checked'";
                    }

                    return `<div>
                            <label class="switch">
                                <input type="checkbox" id="membarStatus" `+check+` value="`+full.id+`" actionType="`+type+`">
                                <span class="slider round"></span>
                            </label>
                        </div>`;
                }
            },
            { "data" : "agentcode",
                render:function(data, type, full, meta){
                    return `<a href="{{url('profile/view')}}/`+full.id+`" target="_blank">`+full.name+`</a>`;
                }
            },
            { "data" : "mobile"},
            { "data" : "mainwallet"},
            { "data" : "payoutwallet"},
            { "data" : "lockedamount"},
            { "data" : "agentcode",
                render:function(data, type, full, meta){
                    var out  = '';
                    var menu = ``;

                    menu += `<li style="padding:15px 15px 5px;text-transform:capitalize;"><a href="{{url('statement/report/chargeback/')}}/`+full.id+`" target="_blank"><i class="icon-paragraph-justify3"></i> Charge Back</a></li>`;
                    menu += `<li style="padding:5px 15px;text-transform:capitalize;"><a href="{{url('statement/report/payin/')}}/`+full.id+`" target="_blank"><i class="icon-paragraph-justify3"></i> Pay-In</a></li>`;
                    menu += `<li style="padding:5px 15px;text-transform:capitalize;"><a href="{{url('statement/report/payout/')}}/`+full.id+`" target="_blank"><i class="icon-paragraph-justify3"></i> Pay-Out Back</a></li>`;
                    menu += `<li style="padding:5px 15px;text-transform:capitalize;"><a href="{{url('statement/report/upiintent/')}}/`+full.id+`" target="_blank"><i class="icon-paragraph-justify3"></i> Upi Intent</a></li>`;
                    menu += `<li style="padding:5px 15px;text-transform:capitalize;"><a href="{{url('statement/report/mainwallet/')}}/`+full.id+`" target="_blank"><i class="icon-paragraph-justify3"></i> Main Wallet Ladger</a></li>`;
                    menu += `<li style="padding:5px 15px;text-transform:capitalize;"><a href="{{url('statement/report/payoutwallet/')}}/`+full.id+`" target="_blank"><i class="icon-paragraph-justify3"></i> Payout Wallet Ladger</a></li>`;

                    out +=  `<ul class="icons-list">
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle mt-10 btn btn-info btn-xs btn-labeled legitRipple" data-toggle="dropdown">
                                        <span class="label bg-slate"><i class="flaticon-381-list-1" style="padding-right:5px;"></i> Reports</span>
                                    </a>

                                    <ul class="dropdown-menu height-250">
                                        `+menu+`
                                    </ul>
                                </li>
                            </ul>`;
                            
                    return out;
                }
            },
            { "data" : "agentcode",
                render:function(data, type, full, meta){
                    var out = '';
                    var menu = ``;
                    
                    @if (Myhelper::can('service_manager'))
                        menu += `<li style="padding:15px 15px 5px;text-transform:capitalize;"><a href="{{url('setup/servicemanage-collection')}}/`+full.id+`" target="_blank"><i class="icon-arrow-right5"></i>Service Manager</a></li>`;
                    @endif

                    @if (Myhelper::can(['fund_transfer', 'fund_return']))
                        menu += `<li style="padding:5px 15px;text-transform:capitalize;"><a href="javascript:void(0)" onclick="transfer(`+full.id+`)"><i class="icon-arrow-right5"></i>  Wallet Transfer</a></li>`;
                    @endif
                    
                    @if (Myhelper::can('member_scheme_update'))
                        menu += `<li style="padding:5px 15px;text-transform:capitalize;"><a href="javascript:void(0)" onclick="scheme(`+full.id+`, '`+full.scheme_id+`')"><i class="icon-arrow-right5"></i> Scheme</a></li>`;
                    @endif
                    
                    @if (Myhelper::can("locked_amount"))
                        menu += `<li style="padding:5px 15px;text-transform:capitalize;"><a href="javascript:void(0)" onclick="lockedAmount('`+full.id+`', '`+full.lockedamount+`')"><i class="icon-arrow-right5"></i> Locked Amount</a></li>`;
                    @endif

                    @if (Myhelper::can('member_permission_change'))
                        menu += `<li style="padding:5px 15px;text-transform:capitalize;"><a href="javascript:void(0)" onclick="getPermission(`+full.id+`)"><i class="icon-arrow-right5"></i> Permission</a></li>`;
                    @endif

                    @if (Myhelper::can('member_kyc_update'))
                        menu += `<li style="padding:5px 15px;text-transform:capitalize;"><a href="javascript:void(0)" onclick="kycManage(`+full.id+`, '`+full.kyc+`', '`+full.remark+`')"><i class="icon-arrow-right5"></i> Kyc Manager</a></li>`;
                    @endif

                    out +=  `<ul class="icons-list">
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle mt-10 btn btn-info btn-xs btn-labeled legitRipple" data-toggle="dropdown">
                                        <span class="label"><i class="flaticon-381-controls-3" style="padding-right:5px;"></i> Action</span>
                                    </a>

                                    <ul class="dropdown-menu height-250">
                                        `+menu+`
                                    </ul>
                                </li>
                            </ul>`;
                    return out;
                }
            },
            
        ];

        datatableSetup(url, options, onDraw);

        $( "#transferForm").validate({
            rules: {
                type: {
                    required: true
                },
                amount: {
                    required: true,
                    min : 1
                }
            },
            messages: {
                type: {
                    required: "Please select transfer action",
                },
                amount: {
                    required: "Please enter amount",
                    min : "Amount value should be greater than 0"
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
                var form = $('#transferForm');
                var type = $('#transferForm').find('[name="type"]').val();
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button:submit').button('loading');
                    },
                    complete: function () {
                        form.find('button:submit').button('reset');
                    },
                    success:function(data){
                        if(data.status == "TXN"){
                            getbalance();
                            form.closest('.modal').modal('hide');
                            notify("Fund "+type+" Successfull", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
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
        
        $( "#kycUpdateForm").validate({
            rules: {
                kyc: {
                    required: true
                }
            },
            messages: {
                kyc: {
                    required: "Please select kyc status",
                }
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
                var form = $('#kycUpdateForm');
                var type = $('#kycUpdateForm').find('[name="type"]').val();
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
                            getbalance();
                            form.closest('.modal').modal('hide');
                            notify("Member Kyc Updated Successfull", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });

        $( "#schemeForm").validate({
            rules: {
                scheme_id: {
                    required: true
                }
            },
            messages: {
                scheme_id: {
                    required: "Please select scheme",
                }
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
                var form = $('#schemeForm');
                var type = $('#schemeForm').find('[name="type"]').val();
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
                            getbalance();
                            form.closest('.modal').modal('hide');
                            notify("Member Scheme Updated Successfull", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });

        $('form#permissionForm').submit(function(){
    		var form= $(this);
            $(this).ajaxSubmit({
                dataType:'json',
                beforeSubmit:function(){
                    form.find('button[type="submit"]').button('loading');
                },
                complete: function(){
                    form.find('button[type="submit"]').button('reset');
                },
                success:function(data){
                    if(data.status == "success"){
                        notify('Permission Set Successfully', 'success');
                    }else{
                        notify('Transaction Failed', 'warning');
                    }
                },
                error: function(errors) {
                	showError(errors, form);
                }
            });
            return false;
    	});

        $('#selectall').click(function(event) {
            if(this.checked) {
                $('.case').each(function() {
                   this.checked = true;       
                });
                $('.selectall').each(function() {
                    this.checked = true;       
                });
             }else{
                $('.case').each(function() {
                   this.checked = false;
                });   
                $('.selectall').each(function() {
                    this.checked = false;       
                });
            }
        });

        $('.selectall').click(function(event) {
            if(this.checked) {
                $(this).closest('tr').find('.case').each(function() {
                   this.checked = true;       
                });
             }else{
                $(this).closest('tr').find('.case').each(function() {
                   this.checked = false;
                });      
            }
        });

        $( "#lockedAmountForm").validate({
            rules: {
                amount: {
                    required: true
                }
            },
            messages: {
                amount: {
                    required: "Please enter value",
                }
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
                var form = $('#lockedAmountForm');
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
                            form.closest('.modal').modal('hide');
                            notify("Request Successfull Completed", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });

        $( "#stockForm").validate({
            rules: {
                stock: {
                    required: true
                }
            },
            messages: {
                stock: {
                    required: "Please enter value",
                }
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
                var form = $('#stockForm');
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
                            form.closest('.modal').modal('hide');
                            notify("Request Successfull Completed", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });
    });

    function transfer(id){
        $('#transferForm').find('[name="payee_id"]').val(id);
        $('#transferModal').modal('show');
    }

    function getPermission(id) {
        if(id.length != ''){
            $.ajax({
                url: '{{url('tools/get/permission')}}/'+id,
                type: 'post',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            })
            .done(function(data) {
                $('#permissionForm').find('[name="payee_id"]').val(id);
                $('.case').each(function() {
                   this.checked = false;
                });
                $.each(data, function(index, val) {
                    $('#permissionForm').find('input[value='+val.permission_id+']').prop('checked', true);
                });
                $('#permissionModal').modal('show');
            })
            .fail(function() {
                notify('Somthing went wrong', 'warning');
            });
        }
    }

    function kycManage(id, kyc, remark){
        $('#kycUpdateForm').find('[name="id"]').val(id);
        $('#kycUpdateForm').find('[name="kyc"]').select2().val(kyc).trigger('change');
        $('#kycUpdateForm').find('[name="remark"]').val(remark);
        $('#kycUpdateModal').modal('show');
    }

    function scheme(id, scheme){
        $('#schemeForm').find('[name="id"]').val(id);
        if(scheme != '' && scheme != null && scheme != 'null'){
            $('#schemeForm').find('[name="scheme_id"]').select2().val(scheme).trigger('change');
        }
        $('#commissionModal').modal('show');
    }

    function addStock(id) {
        $('#idModal').find('input[name="id"]').val(id);
        $('#idModal').modal('show');
    }
    
    function rmupdate(id, reference) {
        $('#rmModal').find('input[name="id"]').val(id);
        $('#rmModal').find('input[name="reference"]').val(reference);
        $('#rmModal').modal('show');
    }

    function viewCommission(element) {
        var scheme_id = $(element).val();
    
        if (scheme_id && scheme_id != 0) {
            // Show a loader or disable the element for user feedback
            $('#loader').show(); // Assuming you have a loader element with ID 'loader'
    
            $.ajax({
                url: '{{route("getMemberCommission")}}',
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: { "scheme_id": scheme_id },
                beforeSend: function () {
                    console.log('Fetching commission details...');
                },
                success: function (data) {
                    // Hide the loader
                    $('#loader').hide();
    
                    // Update modal with the received data
                    $('#commissionModal').find('.commissioData').html(data);
    
                    // Show the modal
                    $('#commissionModal').modal('show');
                },
                error: function (xhr, status, error) {
                    // Hide the loader
                    $('#loader').hide();
    
                    console.error('Error fetching commission details:', error);
                    notify('Something went wrong while fetching commission details', 'warning');
                }
            });
        } else {
            notify('Please select a valid scheme', 'warning');
        }
    }


    function lockedAmount(id, amount) {
        $('#lockedAmountModal').find('input[name="id"]').val(id);
        $('#lockedAmountModal').find('input[name="lockedamount"]').val(amount);
        $('#lockedAmountModal').modal('show');
    }

    function stockmanager(id, stock) {
        $('#stockModal').find('input[name="id"]').val(id);
        $('#stockModal').modal('show');
    }
</script>
@endpush