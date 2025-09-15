@extends('layouts.app')

@section('title', ucwords($user->name) . " Profile")
@section('bodyClass', "has-detached-left")
@section('pagetitle', ucwords($user->name) . " Profile")

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center bg-primary text-white rounded-top" 
                     style="background-image: url('http://demo.interface.club/limitless/assets/images/bg.png'); background-size: cover;">
                    <h5 class="fw-bold mb-1">{{ ucfirst($user->name) }}</h5>
                    <span class="d-block">{{ $user->role->name }}</span>
                    <span class="d-block">Partner Code : {{ $user->agentcode }}</span>
                </div>
                <div class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action active" data-bs-toggle="tab" href="#profile">Profile Details</a>
                    <a class="list-group-item list-group-item-action" data-bs-toggle="tab" href="#kycdata">KYC Details</a>
                    @if ((Auth::id() == $user->id && Myhelper::can('password_reset')) || Myhelper::can('member_password_reset'))
                        <a class="list-group-item list-group-item-action" data-bs-toggle="tab" href="#settings">Password Manager</a>
                    @endif
                    @if (\Myhelper::hasRole('admin'))
                        <a class="list-group-item list-group-item-action" data-bs-toggle="tab" href="#rolemanager">Role Manager</a>
                        <a class="list-group-item list-group-item-action" data-bs-toggle="tab" href="#mapping">Mapping Manager</a>
                        <a class="list-group-item list-group-item-action" data-bs-toggle="tab" href="#bankdata">Bank Details</a>
                    @endif
                    <a href="{{ route('logout') }}" class="list-group-item list-group-item-action text-danger">Logout</a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <div class="tab-content">

                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile">
                    <form id="profileForm" action="{{ route('profileUpdate') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        <input type="hidden" name="actiontype" value="profile">

                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Personal Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Mobile</label>
                                        <input type="number" {{ Myhelper::hasNotRole('admin') ? 'disabled' : 'name=mobile' }}
                                            class="form-control" value="{{ $user->mobile }}" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-4">
                                        <label class="form-label">State</label>
                                        <input type="text" name="state" class="form-control" value="{{ $user->state }}" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" class="form-control" value="{{ $user->city }}" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">District</label>
                                        <input type="text" name="district" class="form-control" value="{{ $user->district }}" required>
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-12">
                                        <label class="form-label">Address</label>
                                        <textarea name="address" rows="3" class="form-control" required>{{ $user->address }}</textarea>
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Pincode</label>
                                        <input type="number" name="pincode" class="form-control" value="{{ $user->pincode }}" required>
                                    </div>

                                    @if(Myhelper::hasRole('admin'))
                                        <div class="col-md-4">
                                            <label class="form-label">Company</label>
                                            <select name="company_id" class="form-select" required>
                                                <option value="">Select Company</option>
                                                @foreach ($company as $c)
                                                    <option value="{{ $c->id }}" {{ $c->id == $user->company_id ? 'selected' : '' }}>
                                                        {{ $c->companyname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Security Pin</label>
                                            <input type="password" name="mpin" class="form-control" required>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if ((Auth::id() == $user->id && Myhelper::can('profile_edit')) || Myhelper::can('member_profile_edit'))
                                <div class="card-footer text-end">
                                    <button class="btn btn-primary" type="submit">Update Profile</button>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
    <div class="tab-pane fade" id="kycdata">
                    <form id="kycForm" action="{{route('profileUpdate')}}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{$user->id}}">
                        <input type="hidden" name="actiontype" value="profile">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h5 class="panel-title">Kyc Data</h5>
                            </div>
                            <div class="panel-body p-b-0">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Shop Name</label>
                                        <input type="text" name="shopname" class="form-control" value="{{$user->shopname}}" required="" placeholder="Enter Value">
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Pancard Number</label>
                                        <input type="text" name="pancard" class="form-control" value="{{$user->pancard}}" required="" placeholder="Enter Value" 
                                        @if (Myhelper::hasNotRole('admin') && $user->kyc == "verified")
                                            disabled=""
                                        @endif
                                        >
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Adhaarcard Number</label>
                                        <input type="text" name="aadharcard" class="form-control" value="{{$user->aadharcard}}" required="" placeholder="Enter Value" maxlength="12" minlength="12"
                                        @if (Myhelper::hasNotRole('admin') && $user->kyc == "verified")
                                            disabled=""
                                        @endif
                                        >
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
                            @if ((Auth::id() == $user->id && Myhelper::can('profile_edit')) || Myhelper::can('member_profile_edit'))
                                <div class="panel-footer">
                                    <button   class="btn btn-primary" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Kyc</button>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="pinChange">
                    <form id="pinForm" action="{{route('setpin')}}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{$user->id}}">
                        <input type="hidden" name="mobile" value="{{$user->mobile}}">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4 class="panel-title">Pin Reset</h4>
                            </div>
                            <div class="panel-body p-b-0">
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>New Pin</label>
                                        <input type="password" minlength="6" maxlength="6" name="pin" id="pin" class="form-control" required="" placeholder="Enter Value">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Confirmed Pin</label>
                                        <input type="password" minlength="6" maxlength="6" name="pin_confirmation" class="form-control" required="" placeholder="Enter Value">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label>Otp</label>
                                        <input type="password" name="otp" class="form-control" Placeholder="Otp" required>
                                    </div>
                                    <a href="javascript:void(0)" onclick="OTPRESEND()" class="text-primary pull-right">Get Otp</a>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <button class="" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Resetting...">Password Reset</button>
                            </div>
                        </div>
                    </form>
                </div>

                @if ((Auth::id() == $user->id && Myhelper::can('password_reset')) || Myhelper::can('member_password_reset'))
                <div class="tab-pane fade" id="settings">
                    <form id="passwordForm" action="{{route('profileUpdate')}}" method="post" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{$user->id}}">
                        <input type="hidden" name="actiontype" value="password">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h5 class="panel-title pull-left">Password Reset</h5>
                                @if(Myhelper::hasRole('admin'))
                                <p class="pull-right">Current Password - {{$user->passwordold}}</p>
                                @endif
                                <div class="clearfix"></div>
                            </div>
                            <div class="panel-body p-b-0">
                                <div class="row">
                                    @if (Auth::id() == $user->id || (Myhelper::hasNotRole('admin') && !Myhelper::can('member_password_reset')))
                                        <div class="form-group col-md-4">
                                            <label>Old Password</label>
                                            <input type="password" name="oldpassword" class="form-control" required="" placeholder="Enter Value">
                                        </div>
                                    @endif
        
                                    <div class="form-group col-md-4">
                                        <label>New Password</label>
                                        <input type="password" name="password" id="password" class="form-control" required="" placeholder="Enter Value">
                                    </div>
                                    @if (Auth::id() == $user->id || (Myhelper::hasNotRole('admin') && !Myhelper::can('member_password_reset')))
                                        <div class="form-group col-md-4">
                                            <label>Confirmed Password</label>
                                            <input type="password" name="password_confirmation" class="form-control" required="" placeholder="Enter Value">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Otp</label>
                                            <input type="password" name="otp" class="form-control" Placeholder="Otp" required>
                                        </div>
                                        <a href="javascript:void(0)" onclick="OTPRESEND()" class="text-primary pull-right">Get Otp</a>

                                    @endif
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
                            <div class="panel-footer">
                                <button  class="btn btn-primary" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Resetting...">Password Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif

                @if (\Myhelper::hasRole('admin'))
                    <div class="tab-pane fade" id="bankdata">
                        <form id="bankForm" action="{{route('profileUpdate')}}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="id" value="{{$user->id}}">
                            <input type="hidden" name="actiontype" value="bankdata">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">Bank Details</h5>
                                </div>
                                <div class="panel-body p-b-0">
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label>Account Number 1</label>
                                            <input type="text" name="account" class="form-control" value="{{$user->account}}" placeholder="Enter Value">
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>Bank Name 1</label>
                                            <input type="text" name="bank" class="form-control" value="{{$user->bank}}" placeholder="Enter Value">
                                        </div>
            
                                        <div class="form-group col-md-4">
                                            <label>Ifsc Code 1</label>
                                            <input type="text" name="ifsc" class="form-control" value="{{$user->ifsc}}" placeholder="Enter Value">
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
                                <div class="panel-footer">
                                    <button   class="btn btn-primary" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Changing...">Change</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="rolemanager">
                        <form id="roleForm" action="{{route('profileUpdate')}}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="id" value="{{$user->id}}">
                            <input type="hidden" name="actiontype" value="rolemanager">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">Role Manager</h5>
                                </div>
                                <div class="panel-body p-b-0">
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label>Member Role</label>
                                            <select name="role_id" class="form-control select" required="">
                                                <option value="">Select Role</option>
                                                @foreach ($roles as $role)
                                                    <option value="{{$role->id}}">{{$role->name}}</option>
                                                @endforeach
                                            </select>
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
                                <div class="panel-footer">
                                    <button  class="btn btn-primary" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Changing...">Change</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="mapping">
                        <form id="memberForm" action="{{route('profileUpdate')}}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="id" value="{{$user->id}}">
                            <input type="hidden" name="actiontype" value="mapping">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h5 class="panel-title">Change Mapping</h5>
                                </div>
                                <div class="panel-body p-b-0">
                                    <div class="row">
                                        <div class="form-group col-md-4">
                                            <label>Parent Member</label>
                                            <select name="parent_id" class="form-control select" required="">
                                                <option value="">Select Member</option>
                                                @foreach ($parents as $parent)
                                                    <option value="{{$parent->id}}">{{$parent->name}} ({{$parent->mobile}}) ({{$parent->role->name}})</option>
                                                @endforeach
                                            </select>
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
                                <div class="panel-footer">
                                    <button   class="btn btn-primary" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Changing...">Change</button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            
            </div>
        </div>
    </div>
</div>
@endsection
@push('script')

<script type="text/javascript">
    $(document).ready(function () {
        $('[name="state"]').select2().val('{{$user->state}}').trigger('change');
        @if (\Myhelper::hasRole('admin'))
            $('[name="parent_id"]').select2().val('{{$user->parent_id}}').trigger('change');
            $('[name="role_id"]').select2().val('{{$user->role_id}}').trigger('change');
            $('[name="company_id"]').select2().val('{{$user->company_id}}').trigger('change');
        @endif

        $( "#profileForm" ).validate({
            rules: {
                name: {
                    required: true,
                },
                mobile: {
                    required: true,
                    minlength: 10,
                    number : true,
                    maxlength: 10
                },
                email: {
                    required: true,
                    email : true
                },
                state: {
                    required: true,
                },
                city: {
                    required: true,
                },
                pincode: {
                    required: true,
                    minlength: 6,
                    number : true,
                    maxlength: 6
                },
                address: {
                    required: true,
                }
            },
            messages: {
                name: {
                    required: "Please enter name",
                },
                mobile: {
                    required: "Please enter mobile",
                    number: "Mobile number should be numeric",
                    minlength: "Your mobile number must be 10 digit",
                    maxlength: "Your mobile number must be 10 digit"
                },
                email: {
                    required: "Please enter email",
                    email: "Please enter valid email address",
                },
                state: {
                    required: "Please select state",
                },
                city: {
                    required: "Please enter city",
                },
                pincode: {
                    required: "Please enter pincode",
                    number: "Mobile number should be numeric",
                    minlength: "Your mobile number must be 6 digit",
                    maxlength: "Your mobile number must be 6 digit"
                },
                address: {
                    required: "Please enter address",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#profileForm');
                form.find('span.text-danger').remove();
                $('form#profileForm').ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button:submit').button('loading');
                    },
                    complete: function () {
                        form.find('button:submit').button('reset');
                    },
                    success:function(data){
                        if(data.status == "success"){
                            notify("Profile Successfully Updated" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.panel-body'));
                    }
                });
            }
        });

        $( "#kycForm" ).validate({
            rules: {
                aadharcard: {
                    required: true,
                    minlength: 12,
                    number : true,
                    maxlength: 12
                },
                pancard: {
                    required: true,
                },
                shopname: {
                    required: true,
                }
            },
            messages: {
                aadharcard: {
                    required: "Please enter aadharcard",
                    number: "Mobile number should be numeric",
                    minlength: "Your mobile number must be 12 digit",
                    maxlength: "Your mobile number must be 12 digit"
                },
                pancard: {
                    required: "Please enter pancard",
                },
                shopname: {
                    required: "Please enter shop name",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#kycForm');
                form.find('span.text-danger').remove();
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
                            notify("Profile Successfully Updated" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.panel-body'));
                    }
                });
            }
        });

        $( "#passwordForm").validate({
            rules: {
                @if (Auth::id() == $user->id || (Myhelper::hasNotRole('admin') && !Myhelper::can('member_password_reset')))
                oldpassword: {
                    required: true,
                },
                password_confirmation: {
                    required: true,
                    minlength: 8,
                    equalTo : "#password"
                },
                @endif
                password: {
                    required: true,
                    minlength: 8,
                }
            },
            messages: {
                @if (Auth::id() == $user->id || (Myhelper::hasNotRole('admin') && !Myhelper::can('member_password_reset')))
                oldpassword: {
                    required: "Please enter old password",
                },
                password_confirmation: {
                    required: "Please enter confirmed password",
                    minlength: "Your password length should be atleast 8 character",
                    equalTo : "New password and confirmed password should be equal"
                },
                @endif
                password: {
                    required: "Please enter new password",
                    minlength: "Your password length should be atleast 8 character",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#passwordForm');
                form.find('span.text-danger').remove();
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
                            notify("Password Successfully Changed" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.panel-body'));
                    }
                });
            }
        });

        $( "#memberForm" ).validate({
            rules: {
                parent_id: {
                    required: true
                }
            },
            messages: {
                parent_id: {
                    required: "Please select parent member"
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#memberForm');
                form.find('span.text-danger').remove();
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
                            notify("Mapping Successfully Changed" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors);
                    }
                });
            }
        });

        $( "#roleForm" ).validate({
            rules: {
                role_id: {
                    required: true
                }
            },
            messages: {
                role_id: {
                    required: "Please select member role"
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#roleForm');
                form.find('span.text-danger').remove();
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
                            notify("Role Successfully Changed" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors);
                    }
                });
            }
        });

        $( "#bankForm" ).validate({
            rules: {
                account: {
                    required: true
                },
                bank: {
                    required: true
                },
                ifsc: {
                    required: true
                }
            },
            messages: {
                account: {
                    required: "Please enter member account"
                },
                bank: {
                    required: "Please enter member bank"
                },
                ifsc: {
                    required: "Please enter bank ifsc"
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#bankForm');
                form.find('span.text-danger').remove();
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
                            notify("Bank Details Successfully Changed" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.panel-body'));
                    }
                });
            }
        });

        $( "#pinForm").validate({
            rules: {
                oldpin: {
                    required: true,
                },
                pin_confirmation: {
                    required: true,
                    minlength: 6,
                    maxlength: 6,
                    equalTo : "#pin"
                },
                pin: {
                    required: true,
                    minlength: 6,
                    maxlength: 6,
                }
            },
            messages: {
                oldpin: {
                    required: "Please enter old pin",
                },
                pin_confirmation: {
                    required: "Please enter confirmed pin",
                    minlength: "Your pin length should be 6 character",
                    maxlength: "Your pin length should be 6 character",
                    equalTo : "New pin and confirmed pin should be equal"
                },
                pin: {
                    required: "Please enter new pin",
                    minlength: "Your pin length should be 6 character",
                }
            },
            errorElement: "p",
            errorPlacement: function ( error, element ) {
                if ( element.prop("tagName").toLowerCase().toLowerCase() === "select" ) {
                    error.insertAfter( element.closest( ".form-group" ).find(".select2") );
                } else {
                    error.insertAfter( element );
                }
            },
            submitHandler: function () {
                var form = $('form#pinForm');
                form.find('span.text-danger').remove();
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
                            form[0].reset();
                            notify("Pin Successfully Changed" , 'success');
                        }else{
                            notify(data.status , 'warning');
                        }
                    },
                    error: function(errors) {
                        showError(errors, form.find('.panel-body'));
                    }
                });
            }
        });
    });

    function OTPRESEND() {
        var mobile = "{{Auth::user()->mobile}}";
        if(mobile.length > 0){
            $.ajax({
                url: '{{ route("getotp") }}',
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data :  {'mobile' : mobile},
                beforeSend:function(){
                    swal({
                        title: 'Wait!',
                        text: 'Please wait, we are working on your request',
                        onOpen: () => {
                            swal.showLoading()
                        }
                    });
                },
                complete: function(){
                    swal.close();
                }
            })
            .done(function(data) {
                if(data.status == "TXN"){
                    notify("Otp sent successfully" , 'success');
                }else{
                    notify(data.message , 'warning');
                }
            })
            .fail(function() {
                notify("Something went wrong, try again", 'warning');
            });
        }else{
            notify("Enter your registered mobile number", 'warning');
        }
    }
</script>
@endpush