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

@if (isset($permissions) && $permissions)
<div id="permissionModal" class="modal fade right" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title">Member Permission</h6>
            </div>
            <form id="permissionForm" action="{{route('toolsupdatepermission')}}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="role_id">
                <input type="hidden" name="type" value="permission">
                <input type="hidden" name="action" value="">
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
                    <button type="button" class="btn btn-default btn-raised legitRipple" style="display:none" type="submit">Submit</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="button" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting" id="addpermission">Add Permission</button>
                    <button class="btn bg-slate btn-raised legitRipple" type="button" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting" id="removepermission">Remove Permission</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
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
        var url = "{{url('statement/fetch/roles/0')}}";
        var onDraw = function() {};
        var options = [
            { "data" : "id"},
            { "data" : "slug"},
            { "data" : "name"},
            { "data" : "updated_at"},
            { "data" : "action",
                render:function(data, type, full, meta){
                    var out =  `<a href="javascript:void(0)" onclick="getPermission(`+full.id+`)" class="btn btn-primary"><i class="icon-cogs"></i> Permission</a>`;

                    return out;
                }
            },
        ];
        datatableSetup(url, options, onDraw);
    
        $('#addpermission').click(function(){
    		$('form#permissionForm').find("[name='action']").val('add');
    		$('form#permissionForm').submit();
    	});
    	
    	$('#removepermission').click(function(){
    		$('form#permissionForm').find("[name='action']").val('remove');
    		$('form#permissionForm').submit();
    	});
    	
        $('form#permissionForm').submit(function(){
    		var form= $(this);
            $(this).ajaxSubmit({
                dataType:'json',
                beforeSubmit:function(){
                    swal({
                        title: 'Wait!',
                        text: 'Please wait, we are working on yoor request',
                        onOpen: () => {
                            swal.showLoading()
                        },
                        allowOutsideClick: () => !swal.isLoading()
                    });
                },
                complete: function(){
                    swal.close();
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
    });
    
    function getPermission(id) {
        $('#permissionForm').find('[name="role_id"]').val(id);
        $('#permissionModal').modal();
    }
</script>
@endpush