@extends('layouts.app')
@section('title', 'Company Manager')
@section('pagetitle',  'Company Manager')
@php
    $table = "yes";
    $agentfilter = "hide";

    $status['type'] = "Company";
    $status['data'] = [
        "1" => "Active",
        "0" => "De-active"
    ];
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Company Manager</h4>
                        <div class="heading-elements">
                            <button type="submit" class="btn btn-info btn-xs btn-labeled legitRipple" onclick="addSetup()" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="flaticon-381-add-1" style="padding-right:5px;"></i></b> Add New</button></div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table-bordered nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Domain</th>
                                        <th>Status</th>
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

<div id="setupModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                <h6 class="modal-title"><span class="msg">Add</span> Company</h6>
            </div>
            <form id="setupManager" action="{{route('resourceupdate')}}" method="post">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="id">
                        <input type="hidden" name="actiontype" value="company">
                        {{ csrf_field() }}
                        <div class="form-group col-md-12">
                            <label>Name</label>
                            <input type="text" name="companyname" class="form-control" placeholder="Enter Bank Name" required="">
                        </div>
                        <div class="form-group col-md-12">
                            <label>Website</label>
                            <input type="text" name="website" class="form-control" placeholder="Enter Bank Name" required="">
                        </div>
                        <div class="form-group col-md-12">
                            <label>Senderid</label>
                            <input type="text" name="senderid" class="form-control" placeholder="Enter Sms Senderid">
                        </div>
                        <div class="form-group col-md-12">
                            <label>Smsuser</label>
                            <input type="text" name="smsuser" class="form-control" placeholder="Enter Sms Username">
                        </div>
                        <div class="form-group col-md-12">
                            <label>Smspwd</label>
                            <input type="text" name="smspwd" class="form-control" placeholder="Enter Sms Password">
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
@endsection

@push('script')
    <script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/list/fetch')}}/resource{{$type}}/0";

        var onDraw = function() {
            $('input#companyStatus').on('click', function(evt){
                evt.stopPropagation();
                var ele = $(this);
                var id = $(this).val();
                var status = "0";
                if($(this).prop('checked')){
                    status = "1";
                }
                
                $.ajax({
                    url: '{{ route('resourceupdate') }}',
                    type: 'post',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    data: {'id':id, 'status':status, "actiontype":"company"}
                })
                .done(function(data) {
                    if(data.status == "success"){
                        notify("Company Updated", 'success');
                        $('#datatable').dataTable().api().ajax.reload();
                    }else{
                        if(status == "1"){
                            ele.prop('checked', false);
                        }else{
                            ele.prop('checked', true);
                        }
                        notify("Something went wrong, Try again." ,'warning');
                    }
                })
                .fail(function(errors) {
                    if(status == "1"){
                        ele.prop('checked', false);
                    }else{
                        ele.prop('checked', true);
                    }
                    showError(errors, "withoutform");
                });
            });
        };

        var options = [
            { "data" : "id"},
            { "data" : "companyname"},
            { "data" : "website"},
            { "data" : "status",
                render:function(data, type, full, meta){
                    var check = "";
                    if(full.status == "1"){
                        check = "checked='checked'";
                    }

                    return `<label class="switch">
                                <input type="checkbox" id="companyStatus" `+check+` value="`+full.id+`" actionType="`+type+`"  data-toggle="toggle" data-style="ios">
                                <span class="slider round"></span>
                            </label>`;
                }
            },
           {
                "data": null,
                render: function (data, type, full, meta) {
                    return `
                        <button class="btn btn-info"
                            onclick="editSetup('${full.id}', '${full.companyname}', '${full.website}', '${full.senderid}', '${full.smsuser}', '${full.smspwd}')">
                            <i class="fas fa-pencil-alt"></i> Edit
                        </button>
                    `;
                }
            },

        ];
        datatableSetup(url, options, onDraw);

        $( "#setupManager" ).validate({
            rules: {
                name: {
                    required: true,
                }
            },
            messages: {
                name: {
                    required: "Please enter bank name",
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
                var form = $('#setupManager');
                var id = form.find('[name="id"]').val();
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button[type="submit"]').button('loading');
                    },
                    success:function(data){
                        if(data.status == "success"){
                            if(id == "new"){
                                form[0].reset();
                            }
                            form.find('button[type="submit"]').button('reset');
                            notify("Task Successfully Completed", 'success');
                            $('#datatable').dataTable().api().ajax.reload();
                        }else{
                            notify(data.status, 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form);
                    }
                });
            }
        });

        $("#setupModal").on('hidden.bs.modal', function () {
            $('#setupModal').find('.msg').text("Add");
            $('#setupModal').find('form')[0].reset();
        });
    
    });

    function addSetup(){
        $('#setupModal').find('.msg').text("Add");
        $('#setupModal').find('input[name="id"]').val("new");
        $('#setupModal').modal('show');
    }

    function editSetup(id, companyname, website, senderid, smsuser, smspwd){
        $('#setupModal').find('.msg').text("Edit");
        $('#setupModal').find('input[name="id"]').val(id);
        $('#setupModal').find('input[name="companyname"]').val(companyname);
        $('#setupModal').find('input[name="website"]').val(website);
        $('#setupModal').find('input[name="senderid"]').val(senderid);
        $('#setupModal').find('input[name="smsuser"]').val(smsuser);
        $('#setupModal').find('input[name="smspwd"]').val(smspwd);
        $('#setupModal').modal('show');
    }
</script>
@endpush