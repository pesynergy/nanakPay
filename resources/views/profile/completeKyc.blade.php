@extends('layouts.app')
@section('title', "Complete Profile")
@section('pagetitle', "Complete Profile")
@section('content')

@php
    $search = "hide";
@endphp

	<!-- row -->
	<div class="container-fluid">
        <form class="memberForm" action="{{ route('profileUpdate') }}" method="post">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{Auth::id()}}">
            <input type="hidden" name="actiontype" value="kycdata">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Individual Documnents</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>Pancard Pic</label>
                                    <input type="file" name="pancardpics" class="form-control" value="" placeholder="Enter Value" required="">
                                </div>
    
                                <div class="form-group col-md-4">
                                    <label>Adhaarcard Pic Front</label>
                                    <input type="file" name="aadharcardpicfronts" class="form-control" value="" placeholder="Enter Value" required="">
                                </div>
    
                                <div class="form-group col-md-4">
                                    <label>Adhaarcard Pic Back</label>
                                    <input type="file" name="aadharcardpicbacks" class="form-control" value="" placeholder="Enter Value" required="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Company Documents</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>GST Doc</label>
                                    <input type="file" name="gstpics" class="form-control" value="" placeholder="Enter Value" required="">
                                </div>
    
                                <div class="form-group col-md-4">
                                    <label>MSME Doc</label>
                                    <input type="file" name="msmes" class="form-control" value="" placeholder="Enter Value" required="">
                                </div>
    
                                <div class="form-group col-md-4">
                                    <label>Chequebook Current Account</label>
                                    <input type="file" name="passbooks" class="form-control" value="" placeholder="Enter Value" required="">
                                </div>
                            </div>
    
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>Other Doc</label>
                                    <input type="file" name="otherdocs" class="form-control" value="" placeholder="Enter Value" required="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4 col-md-offset-4">
                    <button class="btn btn-info btn-raised legitRipple btn-lg btn-block" type="submit" data-loading-text="Please Wait...">Complete Your Profile</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
        $( ".memberForm" ).validate({
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
                },
                aadharcard: {
                    required: true,
                    minlength: 12,
                    number : true,
                    maxlength: 12
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
                },
                aadharcard: {
                    required: "Please enter aadharcard",
                    number: "Aadhar should be numeric",
                    minlength: "Your aadhar number must be 12 digit",
                    maxlength: "Your aadhar number must be 12 digit"
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
                var form = $('form.memberForm');
                form.find('span.text-danger').remove();
                $('form.memberForm').ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button:submit').button('loading');
                    },
                    complete: function () {
                        form.find('button:submit').button('reset');
                    },
                    success:function(data){
                        if(data.status == "success"){
                            form[0].reset();
                            $('select').val('');
                            $('select').trigger('change');
                            notify("Member Successfully Created" , 'success');
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
</script>
@endpush
