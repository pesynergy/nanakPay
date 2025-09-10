@extends('layouts.app')
@section('title', "Wallet Load Request")
@section('pagetitle',  "Wallet Load Request")

@php
    $table = "yes";

    $status['type'] = "Fund";
    $status['data'] = [
        "success" => "Success",
        "pending" => "Pending",
        "failed" => "Failed",
        "approved" => "Approved",
        "rejected" => "Rejected",
    ];
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            @if ($banks)
                @foreach ($banks as $bank)
                <a href="javascript:void(0)" onclick="fundRequest({{$bank->id}})">
                    <div class="col-sm-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h6 class="text-semibold no-margin-top">{{$bank->name}}</h6>
                                        <ul class="list list-unstyled">
                                            <li><span class="text-semibold">{{$bank->ifsc}}</span></li>
                                        </ul>
                                    </div>
    
                                    <div class="col-sm-6">
                                        <h6 class="text-semibold text-right no-margin-top">{{$bank->account}}</h6>
                                        <ul class="list list-unstyled text-right">
                                            <li><span class="text-semibold">{{$bank->branch}}</span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            @endif
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Wallet Load Request</h4>
                            <div class="heading-elements">
                                <button type="button" data-toggle="modal" data-target="#fundRequestModal" class="btn btn-info btn-sm btn-labeled legitRipple" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-plus2"></i></b> New Request</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatable" class="display">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Action</th>
                                            <th>Deposit Bank</th>
                                            <th>Bank Utr</th>
                                            <th>Pay Date</th>
                                            <th>Amount</th>
                                            <th>Remark</th>
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

<div id="fundRequestModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title">Wallet Fund Request</h6>
            </div>
            <form id="fundRequestForm" action="{{route('fundtransaction')}}" method="post">
                <div class="modal-body">
                    <input type="hidden" name="user_id">
                    <input type="hidden" name="type" value="request">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Deposit Bank</label>
                            <select name="fundbank_id" class="form-control select" id="select" required>
                                <option value="">Select Bank</option>
                                @foreach ($banks as $bank)
                                <option value="{{$bank->id}}">{{$bank->name}} ( {{$bank->account}} )</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Amount</label>
                            <input type="number" name="amount" step="any" class="form-control" placeholder="Enter Amount" required="">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Payment Mode</label>
                            <select name="paymode" class="form-control select" id="select" required>
                                <option value="">Select Paymode</option>
                                @foreach ($paymodes as $paymode)
                                <option value="{{$paymode->name}}">{{$paymode->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Pay Date</label>
                            <input type="text" name="paydate" class="form-control mydate" placeholder="Select date">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Ref No.</label>
                            <input type="text" name="ref_no" class="form-control" placeholder="Enter Refrence Number" required="">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Pay Slip (Optional)</label>
                            <input type="file" name="payslips" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label>Remark</label>
                            <textarea name="remark" class="form-control" rows="2" placeholder="Enter Remark"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>
@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/list/fetch')}}/fundrequest/0";
        var onDraw = function() {};
        var options = [
            { "data" : "id"},
            { "data" : "id",
                render:function(data, type, full, meta){
                    var out = '';
                    if(full.status == "approved"){
                        out += `<label class="label label-success">Approved</label>`;
                    }else if(full.status == "pending"){
                        out += `<label class="label label-warning">Pending</label>`;
                    }else if(full.status == "rejected"){
                        out += `<label class="label label-danger">Rejected</label>`;
                    }

                    return out;
                }
            },
            { "data" : "id",
                render:function(data, type, full, meta){
                    return full.fundbank.account+` (`+full.fundbank.name+')';
                }
            },
            { "data" : "ref_no"},
            { "data" : "paydate"},
            { "data" : "amount"},
            { "data" : "remark"}
        ];

        datatableSetup(url, options, onDraw);

        $( "#fundRequestForm").validate({
            rules: {
                fundbank_id: {
                    required: true
                },
                amount: {
                    required: true
                },
                paymode: {
                    required: true
                },
                ref_no: {
                    required: true
                },
            },
            messages: {
                fundbank_id: {
                    required: "Please select deposit bank",
                },
                amount: {
                    required: "Please enter request amount",
                },
                paymode: {
                    required: "Please select payment mode",
                },
                ref_no: {
                    required: "Please enter transaction refrence number",
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
                var form = $('#fundRequestForm');
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
                            notify("Fund Request submitted Successfull", 'success');
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

    function fundRequest(id = "none"){
        if(id != "none"){
            $('#fundRequestForm').find('[name="fundbank_id"]').select2().val(id).trigger('change');
        }
        $('#fundRequestModal').modal('show');
    }
</script>
@endpush