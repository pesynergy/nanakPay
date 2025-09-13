@extends('layouts.app')
@section('title', 'Api Manager')
@section('pagetitle',  'Api Manager')
@php
    $table = "yes";
    $agentfilter = "hide";
    $status['type'] = "Api";
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
                        <h4 class="card-title">API Manager</h4>
                        <div class="heading-elements">
                            <button type="submit" class="btn btn-info btn-xs btn-labeled legitRipple" onclick="addSetup()" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="flaticon-381-add-1" style="padding-right:5px;"></i></b> Add New</button></div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table-bordered nowrap" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th>Display Name</th>
                                        <th>Api Code</th>
                                        <th>Credentials</th>
                                        <th>Gst/Tds</th>
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                <h6 class="modal-title"><span class="msg">Add</span> Api</h6>
            </div>
            <form id="setupManager" action="{{route('setupupdate')}}" method="post">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="id">
                        <input type="hidden" name="actiontype" value="api">
                        {{ csrf_field() }}
                        <div class="form-group col-md-4">
                            <label>Product Name</label>
                            <input type="text" name="product" class="form-control" placeholder="Enter value" required="">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Display Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter value" required="">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Url</label>
                            <input type="text" name="url" class="form-control" placeholder="Enter url">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Enter Value">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Password</label>
                            <input type="text" name="password" class="form-control" placeholder="Enter url">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Optional1</label>
                            <input type="text" name="optional1" class="form-control" placeholder="Enter Value">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Api Code</label>
                            <input type="text" name="code" class="form-control" placeholder="Enter url" required="">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Product Type</label>
                            <input type="text" name="type" class="form-control" placeholder="Enter value" required="">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Gst</label>
                            <input type="text" name="gst" class="form-control" placeholder="Enter value" required="">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Tds</label>
                            <input type="text" name="tds" class="form-control" placeholder="Enter value" required="">
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
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection

@push('script')
	<script type="text/javascript">
    $(document).ready(function () {
        var url = "{{url('statement/list/fetch')}}/setup{{$type}}/0";

        var onDraw = function() {
            $('[data-popup="popover"]').popover({
                template: '<div class="popover border-teal-400"><div class="arrow"></div><h5 class="popover-title bg-teal-400"></h5><div class="popover-content"></div></div>'
            });

            $('input#apiStatus').on('click', function(evt){
                evt.stopPropagation();
                var ele = $(this);
                var id = $(this).val();
                var status = "0";
                if($(this).prop('checked')){
                    status = "1";
                }
                
                $.ajax({
                    url: '{{ route('setupupdate') }}',
                    type: 'post',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    data: {'id':id, 'status':status, "actiontype":"api"}
                })
                .done(function(data) {
                    if(data.status == "success"){
                        notify("Api Status Updated", 'success');
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
            { "data" : "product"},
            { "data" : "name"},
            { "data" : "code"},
            { "data" : "id",
                render:function(data, type, full, meta){
                    return `<a href="javascript:void(0)" data-popup="popover" data-placement="top" title="" data-html="true" data-trigger="hover" data-content="Url - `+full.url+`<br>Username - `+full.username+`<br>Password - `+full.password+`<br>Optional - `+full.optional1+`" data-original-title="`+full.product+`">Api Credentials</a>`;
                }
            },
            { "data" : "id",
                render:function(data, type, full, meta){
                    return full.gst+"/"+full.tds;
                }
            },
            { "data" : "name",
                render:function(data, type, full, meta){
                    var check = "";
                    if(full.status == "1"){
                        check = "checked='checked'";
                    }

                    return `<label class="switch">
                                <input type="checkbox" id="apiStatus" `+check+` value="`+full.id+`" actionType="`+type+`">
                                <span class="slider round"></span>
                            </label>`;
                }
            },
            { "data" : "id",
                render:function(data, type, full, meta){
                    return `<button type="button" class="btn btn-info btn-raised legitRipple btn-xs" onclick="editSetup(`+full.id+`, \``+full.product+`\`, \``+full.name+`\`, \``+full.url+`\`, \``+full.username+`\`, \``+full.password+`\`, \``+full.optional1+`\`, \``+full.code+`\`, \``+full.type+`\`, \``+full.gst+`\`, \``+full.tds+`\`)"><i class="fas fa-pencil-alt" style="padding-right:5px;"></i> Edit</button>`;
                }
            },
        ];
        datatableSetup(url, options, onDraw);

        $( "#setupManager" ).validate({
            rules: {
                name: {
                    required: true,
                },
                product: {
                    required: true,
                },
                code: {
                    required: true,
                },
            },
            messages: {
                name: {
                    required: "Please enter display name",
                },
                product: {
                    required: "Please enter product name",
                },
                url: {
                    required: "Please enter api url",
                },
                code: {
                    required: "Please enter api code",
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

	function editSetup(id, product, name, url, username, password, optional1, code, type, gst, tds){
		$('#setupModal').find('.msg').text("Edit");
    	$('#setupModal').find('input[name="id"]').val(id);
    	$('#setupModal').find('input[name="product"]').val(product);
        $('#setupModal').find('input[name="name"]').val(name);
        $('#setupModal').find('input[name="url"]').val(url);
        $('#setupModal').find('input[name="username"]').val(username);
        $('#setupModal').find('input[name="password"]').val(password);
        $('#setupModal').find('input[name="optional1"]').val(optional1);
        $('#setupModal').find('input[name="code"]').val(code);
        $('#setupModal').find('input[name="gst"]').val(gst);
        $('#setupModal').find('input[name="tds"]').val(tds);
        $('#setupModal').find('input[name="type"]').val(type);
    	$('#setupModal').modal('show');
	}
</script>
@endpush