@extends('layouts.app')
@section('title', 'Permissions To User')

@section('content')
<form action="{{ route('toolssetpermission') }}" method="post" id="setPermissions">
    {!! csrf_field() !!}
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
					<h5 class="panel-title">Permission List Of Users</h5>
				</div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>User</label>
                            <select class="form-control" name="user_id" onchange="getPermission()">
                                <option value="">Select User</option>
                                @foreach ($users as $user)
                                    <option value="{{$user->id}}">{{$user->name}} ({{$user->id}}) ({{$user->role->role_title}})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label style="width: 100%">&nbsp;</label>
                            <button class="btn btn-primary waves-effect pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Update Permissions</button>
                        </div>
                    </div>
                </div>
				<div class="panel-body p-l-0 p-r-0  table-responsive">
	                <table id="datatable" class="table table-hover table-bordered">
	                    <thead>
	                    <tr>
	                        <th width="170px;">Section Category</th>
	                        <th>
                                <span class="pull-left m-t-5 m-l-10">Permission's</span> 
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
                                            <input type="checkbox" class="select" id="{{ucfirst($key)}}">
                                            <label for="{{ucfirst($key)}}">{{ucfirst($key)}}</label>
                                        </div>
                                    </td>

                                    <td>
                                        @foreach ($value as $permission)
                                            <div class="md-checkbox" >
                                                <input type="checkbox" class="case" id="{{$permission->id}}" name="permissions[]" value="{{$permission->id}}">
                                                <label for="{{$permission->id}}">{{$permission->display_name}}</label>
                                            </div>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                            {{-- @foreach ($permissions as $permission)
                                <tr>
                                    <td>{{$permission->id}}</td>
                                    <td><label for="{{$permission->id}}">{{$permission->name}}</label></td>
                                    <td>
                                        <div class="md-checkbox" >
                                            <input type="checkbox" class="case" id="{{$permission->id}}" name="permissions[]" value="{{$permission->id}}">
                                            <label for="{{$permission->id}}"></label>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach --}}
	                    </tbody>
	                </table>
				</div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('style')
	 <link href="{{asset('')}}/assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .modal.left .modal-dialog, .modal.right .modal-dialog{
            width: 450px;
        }
        .ms-container .ms-list{
            height:350px;
        }

        .md-checkbox.mymd{
            margin: 7px 10px;
            min-width: 10px;
        }

        .md-checkbox{
            margin: 7px 10px;
            min-width: 260px;
        }
    </style>
@endpush

@push('script')
	<script src="{{asset('')}}/assets/plugins/select2/js/select2.min.js" type="text/javascript"></script>
	<script type="text/javascript">
    $(document).ready(function () {
        $('select[name="user_id"]').select2();

        $(window).load(function() {
            @if ($id != 0)
                $('select[name="user_id"]').val({{$id}});
                $('select[name="user_id"]').trigger('change');
            @endif
        });

    	$('#setPermissions').submit(function(){
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
                    notify('Transaction Successfull.', 'Success', 'success');
                },
                error: function(errors) {
                	notify('Transaction Failed.', 'Oops!', 'error');
                }
            });
            return false;
    	});

        $('#selectall').click(function(event) {
            if(this.checked) {
                $('.case').each(function() {
                   this.checked = true;       
                });
             }else{
                $('.case').each(function() {
                   this.checked = false;
                });      
            }
        });

        $('.select').click(function(event) {
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

    function getPermission() {
        var id = $('select[name="user_id"]').val();
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
                $('.case').each(function() {
                   this.checked = false;
                });
                $.each(data, function(index, val) {
                    $('input[value='+val.permission_id+']').prop('checked', true);
                });
            })
            .fail(function() {
                notify('Somthing went wrong', 'Oops', 'error');
            });
        }
    }
</script>
@endpush