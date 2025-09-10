@extends('layouts.app')
@section('title', "Company Profile")
@section('pagetitle', "Company Profile")
@section('bodyClass', "has-detached-left")
@php
    $table = "yes";
    $agentfilter = "hide";
@endphp

@php
    $search = "hide";
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-12">
                <div class="card" style="background-color:transparent;box-shadow:none">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-3 col-lg-4" style="background-color:var(--card)">
                                <div class="p-5">
									<div class="author-profile">
									    <img src="{{asset('')}}new_assests/images/logo.png" alt="" width="100%">
										<!--<div class="author-media">-->
										<!--	<img src="{{asset('')}}new_assests/images/tab/1.jpg" alt="">-->
										<!--	<div class="upload-link" title="" data-toggle="tooltip"-->
										<!--		data-placement="right" data-original-title="update">-->
										<!--		<input type="file" class="update-flie">-->
										<!--		<i class="fa fa-camera"></i>-->
										<!--	</div>-->
										<!--</div>-->
										<!--<div class="author-info">-->
										<!--	<h6 class="title">NanakPay</h6>-->
										<!--</div>-->
									</div>
								</div>
                                <div class="nav flex-column nav-pills mb-3">
                                    <a href="#v-pills-home" data-bs-toggle="pill" class="nav-link active show">Company Details</a>
                                    <!--<a href="#v-pills-profile" data-bs-toggle="pill" class="nav-link">Company Logo</a>-->
                                    <a href="#v-pills-messages" data-bs-toggle="pill" class="nav-link">Company News</a>
                                    <a href="#v-pills-settings" data-bs-toggle="pill" class="nav-link">Company Support Details</a>
                                </div>
                            </div>
                            <div class="col-xl-9 col-lg-8">
                                <div class="tab-content" style="background-color:var(--card); padding-bottom:50px;">
                                    <div id="v-pills-home" class="tab-pane fade active show">
                                        <div class="card-header">
                                            <h4 class="card-title">Company Information</h4>
                                        </div>
                                        <form id="profileForm" action="{{route('resourceupdate')}}" method="post" style="padding:20px">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="id" value="{{$company->id}}">
                                            <input type="hidden" name="actiontype" value="company">
                                            <div class="panel panel-default">
                                                <div class="panel-body p-b-0">
                                                    <div class="row">
                                                        <div class="form-group col-md-4">
                                                            <label>Company Name</label>
                                                            <input type="text" name="companyname" class="form-control" value="{{$company->companyname}}" required="" placeholder="Enter Value">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel-footer">
                                                    <button class="btn btn-info btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div id="v-pills-profile" class="tab-pane fade">
                                        <div class="card-header">
                                            <h4 class="card-title">Company Logo</h4>
                                        </div>
                                        <form class="dropzone" id="logoupload" action="{{route('resourceupdate')}}" method="post" enctype="multipart/form-data" style="padding:20px">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="actiontype" value="company">
                                            <input type="hidden" name="id" value="{{$company->id}}">
                                        </form>
                                        <p style="padding:20px">Note : Prefered image size is 260px * 56px</p>
                                    </div>
                                    <div id="v-pills-messages" class="tab-pane fade">
                                        <div class="card-header">
                                            <h4 class="card-title">Company News</h4>
                                        </div>
                                        <form id="newsForm" action="{{route('resourceupdate')}}" method="post" style="padding:20px">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="id" value="{{$companydata->id ?? 'new'}}">
                                            <input type="hidden" name="company_id" value="{{$company->id}}">
                                            <input type="hidden" name="actiontype" value="companydata">
                                            <div class="panel panel-default">
                                                <div class="panel-body p-b-0">
                                                    <div class="row">
                                                        <div class="form-group col-md-6">
                                                            <label>News</label>
                                                            <textarea name="news" class="form-control" cols="30" rows="3" placeholder="Enter News">{{$companydata->news ?? ""}}</textarea>
                                                        </div>
                    
                                                        <div class="form-group col-md-6">
                                                            <label>Bill Notice</label>
                                                            <textarea name="billnotice" class="form-control" cols="30" rows="3" placeholder="Enter News">{{$companydata->billnotice ?? ""}}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="panel-footer">
                                                    <button class="btn btn-info btn-raised legitRipple pull-right" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating...">Update Info</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div id="v-pills-settings" class="tab-pane fade">
                                        <div class="card-header">
                                            <h4 class="card-title">Company Information</h4>
                                        </div>
                                         <form id="supportForm" action="{{route('resourceupdate')}}" method="post" style="padding:20px">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="company_id" value="{{$company->id}}">
                                            <input type="hidden" name="actiontype" value="companydata">
                                            <div class="panel panel-default">
                                                <div class="panel-body p-b-0">
                                                    <div class="row">
                                                        <div class="form-group col-md-6">
                                                            <label>Contact Number</label>
                                                            <textarea name="number" class="form-control" cols="30" rows="3" placeholder="Enter Value" required="">{{$companydata->number ?? ""}}</textarea>
                                                        </div>
                    
                                                        <div class="form-group col-md-6">
                                                            <label>Contact Email</label>
                                                            <textarea name="email" class="form-control" cols="30" rows="3" placeholder="Enter Value" required="">{{$companydata->email ?? ""}}</textarea>
                                                        </div>
                                                    </div>
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
            </div>
        </div>
	</div>
</div>

<div id="frontslideModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <h6 class="modal-title">Slide Upload</h6>
            </div>
            <div class="modal-body">
                <form class="dropzone" id="slideupload" action="{{route('setupupdate')}}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="actiontype" value="slides">
                    <input type="hidden" name="name" value="Login Slider">
                    <input type="hidden" name="code" value="slides">
                </form>

                <p>Info - Image size should be 800*600 for better view.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="appslideModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-slate">
                <h6 class="modal-title">App Slide Upload</h6>
            </div>
            <div class="modal-body">
                <form class="dropzone" id="appslideupload" action="{{route('setupupdate')}}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <input type="hidden" name="actiontype" value="slides">
                    <input type="hidden" name="name" value="App Banner">
                    <input type="hidden" name="code" value="appslides">
                </form>

                <p>Info - Image size should be 800*600 for better view.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('style')
    <style>
        .dropzone {
            min-height: 127px;
        }
        .dropzone .dz-default.dz-message:before{
            font-size: 50px;
            top: 60px;
        }
        .dropzone .dz-default.dz-message span{
            font-size: 18px;
            margin-top: 100px;
        }
    </style>
@endpush

@push('script')

<script type="text/javascript" src="{{asset('')}}assets/js/plugins/editors/summernote/summernote.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $( "#profileForm" ).validate({
            rules: {
                companyname: {
                    required: true,
                }
            },
            messages: {
                companyname: {
                    required: "Please enter name",
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
                            notify("Company Profile Successfully Updated" , 'success');
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

        $( "#newsForm" ).validate({
            rules: {
                company_id: {
                    required: true,
                }
            },
            messages: {
                company_id: {
                    required: "Please enter id",
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
                var form = $('form#newsForm');
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
                            notify("Company News Successfully Updated" , 'success');
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

        $( "#supportForm" ).validate({
            rules: {
                number: {
                    required: true,
                },
                email: {
                    required: true,
                }
            },
            messages: {
                number: {
                    required: "Number value is required",
                },
                email: {
                    required: "Email value is required",
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
                var form = $('form#supportForm');
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
                            notify("Company Support Details Successfully Updated" , 'success');
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

        $( "#noticeForm" ).validate({
            rules: {
                news: {
                    required: true,
                }
            },
            messages: {
                news: {
                    required: "Please enter name",
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
                var form = $('form#noticeForm');
                $('input[name="notice"]').val($('.note-editable').html());
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
                            notify("Company Notice Successfully Updated" , 'success');
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

        $('.summernote').summernote({
            height: 350,                 // set editor height
            minHeight: null,             // set minimum height of editor
            maxHeight: null,             // set maximum height of editor
            focus: false                 // set focus to editable area after initializing summernote
        });

        var url = "{{url('statement/list/fetch')}}/loginslide/0";

        var onDraw = function() {
        };

        var options = [
            { "data" : "id"},
            { "data" : "name"},
            { "data" : "action",
              "className" : "text-center",
                render:function(data, type, full, meta){
                    return `<a href="{{asset('public')}}/`+full.value+`" target="_blank"><img src="{{asset('public')}}/`+full.value+`" width="100px" height="50px"></a>`;
                }
            },
            { "data" : "action",
                render:function(data, type, full, meta){
                    return `<button type="button" class="btn bg-slate btn-raised legitRipple btn-xs" onclick="deleteSlide('`+full.value+`')"> Delete</button>`;
                }
            }
        ];
        datatableSetup(url, options, onDraw);

        var url = "{{url('statement/list/fetch')}}/appslide/0";

        datatableSetup(url, options, onDraw, '#mydatatable');

        Dropzone.options.slideupload = {
            paramName: "slides", // The name that will be used to transfer the file
            maxFilesize: 1, // MB
            complete: function(file) {
                this.removeFile(file);
            },
            success : function(file, data){
                console.log(file);
                if(data.status == "success"){
                    $('#datatable').dataTable().api().ajax.reload();
                    notify("Slide Successfully Uploaded", 'success');
                }else{
                    notify("Something went wrong, please try again.", 'warning');
                }
            }
        };

        Dropzone.options.appslideupload = {
            paramName: "slides", // The name that will be used to transfer the file
            maxFilesize: 1, // MB
            complete: function(file) {
                this.removeFile(file);
            },
            success : function(file, data){
                console.log(file);
                if(data.status == "success"){
                    $('#datatable').dataTable().api().ajax.reload();
                    notify("Slide Successfully Uploaded", 'success');
                }else{
                    notify("Something went wrong, please try again.", 'warning');
                }
            }
        };
    });
    
    function deleteSlide(id) {
        $.ajax({
            url: '{{route("statementDelete")}}',
            type: 'post',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data : {"slide" : id, 'type' : 'slide'},
            beforeSend : function(){
                swal({
                    title: 'Wait!',
                    text: 'Please wait, we are deleting slides',
                    onOpen: () => {
                        swal.showLoading()
                    },
                    allowOutsideClick: () => !swal.isLoading()
                });
            }
        })
        .success(function(data) {
            swal.close();
            $('#datatable').dataTable().api().ajax.reload();
            notify("Slide Successfully Deleted", 'success');
        })
        .fail(function() {
            swal.close();
            notify('Somthing went wrong', 'warning');
        });
    }
</script>
@endpush
