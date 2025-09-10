@extends('layouts.app')
@section('title', 'Portal Settings')
@section('pagetitle',  'Portal Settings')
@php
    $search = "hide";
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-xl-6 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Login Otp Required</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                                {{ csrf_field() }}
                                <input type="hidden" name="actiontype" value="portalsetting">
                                <input type="hidden" name="code" value="otplogin">
                                <input type="hidden" name="name" value="Login required otp">
                                <div class="panel panel-default">
                                    <div class="panel-body p-b-0">
                                        <div class="form-group">
                                            <label>Login Type</label>
                                            <select name="value" required="" class="form-control select">
                                                <option value="">Select Type</option>
                                                <option value="yes" {{(isset($otplogin->value) && $otplogin->value == "yes") ? "selected=''" : ''}}>With Otp</option>
                                                <option value="no" {{(isset($otplogin->value) && $otplogin->value == "no") ? "selected=''" : ''}}>Without Otp</option>
                                            </select>
                                        </div>
                                        @if(Myhelper::hasRole('admin'))
                                            <div class="form-group">
                                                <label>Security Pin</label>
                                                <input type="password" name="mpin" autocomplete="off" class="form-control" required="">
                                            </div>
                                        @endif
                                    </div>
                                    <div class="panel-footer">
                                        <button class="btn btn-info btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
			</div>
            <div class="col-xl-6 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Payout Success</h4>
                    </div>
                    <div class="card-body">
                        <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="actiontype" value="portalsetting">
                            <input type="hidden" name="code" value="payoutsuccess">
                            <input type="hidden" name="name" value="Payout Success">
                            <div class="panel panel-default">
                                <div class="panel-body p-b-0">
                                    <div class="form-group">
                                        <label>Payout Success</label>
                                        <select name="value" required="" class="form-control select">
                                            <option value="">Select Type</option>
                                            <option value="real" {{(isset($payoutsuccess->value) && $payoutsuccess->value == "real") ? "selected=''" : ''}}>Real</option>
                                            <option value="success" {{(isset($payoutsuccess->value) && $payoutsuccess->value == "success") ? "selected=''" : ''}}>All Success</option>
                                        </select>
                                    </div>
                                    
                                    @if(Myhelper::hasRole('admin'))
                                        <div class="form-group">
                                            <label>Security Pin</label>
                                            <input type="password" name="mpin" autocomplete="off" class="form-control" required="">
                                        </div>
                                    @endif
                                </div>
                                <div class="panel-footer">
                                    <button class="btn btn-info btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
			</div>
			<div class="col-xl-6 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Transaction Id Code</h4>
                    </div>
                    <div class="card-body">
						<form class="actionForm" action="{{route('setupupdate')}}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="actiontype" value="portalsetting">
                            <input type="hidden" name="code" value="transactioncode">
                            <input type="hidden" name="name" value="Transaction Id Code">
                            <div class="panel panel-default">
                                <div class="panel-body p-b-0">
                                    <div class="form-group">
                                        <label>Code</label>
                                        <input type="text" name="value" value="{{$transactioncode->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                                    </div>
                                    @if(Myhelper::hasRole('admin'))
                                        <div class="form-group">
                                            <label>Security Pin</label>
                                            <input type="password" name="mpin" autocomplete="off" class="form-control" required="">
                                        </div>
                                    @endif
                                </div>
                                <div class="panel-footer">
                                    <button class="btn btn-info btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
			</div>
			<div class="col-xl-6 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Utr Code</h4>
                    </div>
                    <div class="card-body">
                        <form class="actionForm" action="{{route('setupupdate')}}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="actiontype" value="portalsetting">
                            <input type="hidden" name="code" value="utrcode">
                            <input type="hidden" name="name" value="Utr Code">
                            <div class="panel panel-default">
                                <div class="panel-body p-b-0">
                                    <div class="form-group">
                                        <label>Code</label>
                                        <input type="text" name="value" value="{{$utrcode->value ?? ''}}" class="form-control" required="" placeholder="Enter value">
                                    </div>
                                    @if(Myhelper::hasRole('admin'))
                                        <div class="form-group">
                                            <label>Security Pin</label>
                                            <input type="password" name="mpin" autocomplete="off" class="form-control" required="">
                                        </div>
                                    @endif
                                </div>
                                <div class="panel-footer">
                                    <button class="btn btn-info btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
			</div>
        </div>
	</div>
</div>
@endsection

@push('script')
    <script type="text/javascript">
    $(document).ready(function () {
        $('.actionForm').submit(function(event) {
            var form = $(this);
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
                            $('[name="api_id"]').select2().val(null).trigger('change');
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
            return false;
        });

        $("#setupModal").on('hidden.bs.modal', function () {
            $('#setupModal').find('.msg').text("Add");
            $('#setupModal').find('form')[0].reset();
        });

        $('')
    });
</script>
@endpush