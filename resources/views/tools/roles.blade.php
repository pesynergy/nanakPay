@extends('layouts.app')
@section('title', 'Roles')
@section('pagetitle',  'Role List')
@php
    $table = "yes";
@endphp

@section('content')
<div class="content-body default-height">
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Role List</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="display">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Display Name</th>
                                        <th>Last Update</th>
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

<div id="roleModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title"><span class="msg">Add</span> Role</h6>
            </div>
            <form id="rolemanager" action="{{route('toolsstore', ['type'=>'roles'])}}" method="post">
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="id">
                        {{ csrf_field() }}
                        <div class="form-group col-md-6">
                            <label>Role Name</label>
                            <input type="text" name="slug" class="form-control" placeholder="Enter Role Name" required="">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Display Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter Display Name" required="">
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
</div><!-- /.modal -->

@if (isset($permissions) && $permissions)
<div id="permissionModal" class="modal fade right" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title">Member Permission</h6>
            </div>
            <form id="permissionForm" action="{{route('toolssetpermission')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="role_id">
                <input type="hidden" name="type" value="permission">
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
                    <button type="button" class="btn btn-default btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endif

<div id="schemeModal" class="modal fade" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
                <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Scheme Manager</h4>
            </div>
            <form id="schemeForm" method="post" action="{{ route('toolssetpermission') }}">
                <div class="modal-body">
                    {!! csrf_field() !!}
                    <input type="hidden" name="role_id">
                    <input type="hidden" name="type" value="scheme">
                    <div class="row">
                        <div class="form-group">
                            <label>Scheme</label>
                            <select class="form-control select" name="permissions[]" required="">
                                <option value="">Select Scheme</option>
                                @foreach ($scheme as $element)
                                    <option value="{{$element->id}}">{{$element->name}}</option>
                                @endforeach
                            </select>
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
</div><!-- /.modal -->
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
        var url = "{{url('statement/list/fetch/roles/0')}}";
        var onDraw = function() {};
        var options = [
            { "data" : "id"},
            { "data" : "slug"},
            { "data" : "name"},
            { "data" : "updated_at"},
            { "data" : "action",
                render:function(data, type, full, meta){
                    var menu = ``;

                    @if (Myhelper::can('member_permission_change'))
                        menu += `<li><a href="javascript:void(0)" onclick="getPermission(`+full.id+`)"><i class="icon-cogs"></i> Permission</a></li>`;
                    @endif

                    @if (Myhelper::can('member_scheme_change'))
                        menu += `<li><a href="javascript:void(0)" onclick="scheme(`+full.id+`, '`+full.scheme+`')"><i class="icon-wallet"></i> Scheme</a></li>`;
                    @endif

                    out =  `<ul class="icons-list">
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
            },
        ];
        datatableSetup(url, options, onDraw);

        $( "#rolemanager" ).validate({
            rules: {
                slug: {
                    required: true,
                },
                name: {
                    required: true,
                },
            },
            messages: {
                mobile: {
                    required: "Please enter role slug",
                },
                name: {
                    required: "Please enter role name",
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
                var form = $('#rolemanager');
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

    	$("#roleModal").on('hidden.bs.modal', function () {
            $('#roleModal').find('.msg').text("Add");
            $('#roleModal').find('form')[0].reset();
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
                            notify("Role Scheme Updated Successfull", 'success');
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

    function addrole(){
    	$('#roleModal').find('.panel-title').text("Add New Role");
    	$('#roleModal').find('input[name="id"]').val("new");
    	$('#roleModal').modal('show');
	}

	function editRole(ele){
		var id = $(ele).closest('tr').find('td').eq(0).text();
		var slug = $(ele).closest('tr').find('td').eq(1).text();
		var name = $(ele).closest('tr').find('td').eq(2).text();

		$('#roleModal').find('.msg').text("Edit");
    	$('#roleModal').find('input[name="id"]').val(id);
    	$('#roleModal').find('input[name="slug"]').val(slug);
    	$('#roleModal').find('input[name="name"]').val(name);
    	$('#roleModal').modal('show');
    }
    
    function getPermission(id) {
        if(id.length != ''){
            $.ajax({
                url: '{{url('tools/getdefault/permission')}}/'+id,
                type: 'post',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            })
            .done(function(data) {
                $('#permissionForm').find('[name="role_id"]').val(id);
                $('.case').each(function() {
                   this.checked = false;
                });
                $.each(data, function(index, val) {
                    $('#permissionForm').find('input[value='+val.permission_id+']').prop('checked', true);
                });
                $('#permissionModal').modal();
            })
            .fail(function() {
                notify('Somthing went wrong', 'warning');
            });
        }
    }

    function scheme(id, scheme){
        $('#schemeForm').find('[name="role_id"]').val(id);
        if(scheme != '' && scheme != null && scheme != 'null'){
            $('#schemeForm').find('[name="permissions[]"]').select2().val(scheme).trigger('change');
        }
        $('#schemeModal').modal();
    }
</script>
@endpush