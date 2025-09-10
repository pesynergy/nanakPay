@extends('layouts.app')
@section('title', "Fund Request")
@section('pagetitle',  "Fund Request")

@php
    $agentfilter = "hide";
    $table = "yes";
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Fund Request</h4>
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

@if(Myhelper::can("fund_requestview_update"))
<div id="transferModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title">Fund Request Update</h6>
            </div>
            <form id="transferForm" action="{{route('fundtransaction')}}" method="post">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="id">
                        <input type="hidden" name="type" value="requestview">
                        {{ csrf_field() }}
                        <div class="form-group col-md-12">
                            <label>Select Action</label>
                            <select name="status" class="form-control select" id="select" required>
                                <option value="">Select Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
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
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endif

@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    $(document).ready(function () {

        var url = "{{url('statement/list/fetch')}}/fundrequestview/0";
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

                    var menu = ``;

                    @if (Myhelper::can(['fund_requestview_update']))
                        menu += `<li class="dropdown-header">Action</li><li><a href="javascript:void(0)" onclick="transfer(`+full.id+`, '`+full.remark+`', '`+full.status+`')"><i class="icon-wallet"></i> Update Request</a></li>`;
                    @endif

                    out +=  `<ul class="icons-list">
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <i class="icon-menu9"></i>
                                    </a>

                                    <ul class="dropdown-menu dropdown-menu-left">
                                        `+menu+`
                                    </ul>
                                </li>
                            </ul>`;

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

        $( "#transferForm").validate({
            rules: {
                status: {
                    required: true
                },
            },
            messages: {
                fundbank_id: {
                    required: "Please select request status",
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
                var form = $('#transferForm');
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
                            form.closest('.modal').modal('hide');
                            notify("Fund Request Approved Successfull", 'success');
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

    function transfer(id, remark, status){
        $('#transferForm').find('[name="id"]').val(id);
        $('#transferForm').find('[name="status"]').select2().val(status).trigger('change');
        $('#transferForm').find('[name="remark"]').val(remark);
        $('#transferModal').modal();
    }
</script>
@endpush