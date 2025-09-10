@extends('layouts.app')
@section('title', "Complaints")
@section('pagetitle',  "Complaints")

@php
    $table = "yes";

    $product['data'] = array(
        'recharge' => 'Recharge',
        'billpay' => 'Billpay',
        'dmt' => 'Dmt',
        'aeps' => 'Aeps',
        'matm' => 'Matm',
        'utipancard' => 'Utipancard'
    );
    $product['type'] = "Service";

    $status['type'] = "Report";
    $status['data'] = [
        "resolved" => "Resolved",
        "pending" => "Pending",
    ];
@endphp

@section('content')
<div class="default-height">
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Complaints</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="display">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User Details</th>
                                        <th>Transaction Details</th>
                                        <th>Bank Utr</th>
                                        <th>Screenshot</th>
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

<div id="viewFullDataModal" class="modal fade right" role="dialog" data-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
                <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Transaction Details</h4>
            </div>
            <div class="modal-body p-0">
                <table class="table table-bordered table-striped ">
                    <tbody class="agentData">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="complaintEditModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title">Edit Report</h6>
            </div>
            <form id="complaintEditForm" action="{{route('complaintstore')}}" method="post">
                <div class="modal-body">
                    <input type="hidden" name="id">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control select">
                            <option value="">Select Status</option>
                            <option value="pending">Pending</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Amount</label>
                        <input type="text" name="amount" class="form-control" placeholder="Enter value" required="">
                    </div>

                    <div class="form-group">
                        <label>Solution</label>
                        <textarea name="solution" cols="30" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating">Update</button>
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
        var url = "{{url('statement/list/fetch')}}/complaints/0";
        var onDraw = function() {
        };
        var options = [
            { "data" : "name",
                render:function(data, type, full, meta){
                    return `<div>
                            <span class='text-inverse m-l-10'>SN : <b>`+full.id +`</b> </span>
                            <div class="clearfix"></div>
                        </div><span style='font-size:13px' class="pull=right">`+full.created_at+`</span>`;
                }
            },
            { "data" : "username"},
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return full.product+ ` ( `+full.transaction_id+` )`;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return full.subject;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return `<a href="`+full.description+`" target="_blank"><img src="`+full.description+`" width="100px" height="50px"></a>`;
                }
            },
            { "data" : "status",
                render:function(data, type, full, meta){
                    if(full.status == "resolved"){
                        var out = `<span class="label label-success">Resolved</span>`;
                    }else if(full.status == "rejected"){
                        var out = `<span class="label label-danger">Rejected</span>`;
                    }else{
                        var out = `<span class="label label-warning">Pending</span>`;
                    }

                    var menu = ``;
                    @if (Myhelper::can('complaint_edit'))
                    menu += `<li class="dropdown-header">Setting</li>
                            <li><a href="javascript:void(0)" onclick="editComplaint(`+full.id+`, '`+full.status+`', '`+full.solution+`')"><i class="icon-pencil5"></i> Edit</a></li>`;
                    @endif
                    

                    out +=  `<ul class="icons-list">
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                        <i class="icon-menu9"></i>
                                    </a>

                                    <ul class="dropdown-menu dropdown-menu-right">
                                        `+menu+`
                                    </ul>
                                </li>
                            </ul>`;

                    return out;
                }
            }
        ];

        datatableSetup(url, options, onDraw);

        $( "#complaintEditForm").validate({
            rules: {
                status: {
                    required: true,
                },
                solution: {
                    required: true,
                }
            },
            messages: {
                status: {
                    required: "Please select status",
                },
                solution: {
                    required: "Please enter your solution",
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
                var form = $('#complaintEditForm');
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button:submit').button('loading');
                    },
                    complete: function () {
                        form.find('button:submit').button('reset');
                    },
                    success:function(data){
                        if(data.status){
                            form[0].reset();
                            form.find('select').val(null).trigger('change');
                            form.closest('.modal').modal('hide');
                            $('#datatable').dataTable().api().ajax.reload();
                            notify("Complaint successfully updated", 'success');
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

    function viewData(id){
        $.ajax({
            url: `{{url('statement/list/fetch')}}/getstatement/`+id+`/single`,
            type: 'post',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType:'json',
            success:function(data) {
                var agentdata = "";
                $.each(data, function(index, values) {
                    agentdata += `<tr>
                        <td>`+index+`</td>
                        <td>`+values+`</td>
                    </tr>`;
                });
                $(".agentData").html(agentdata);
                $('#viewFullDataModal').modal();
            },
            error : function(errors) {
                notify('Oops', errors.status+'! '+errors.statusText, 'warning');
            }
        });
    }

    function editComplaint(id, status, solution){
        $('#complaintEditModal').find('[name="id"]').val(id);
        $('#complaintEditModal').find('[name="solution"]').val(solution);
        $('#complaintEditModal').find('[name="status"]').val(status).trigger('change');
        $('#complaintEditModal').modal('show');
    }
</script>
@endpush