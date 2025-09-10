<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title') - {{$mydata['company']->companyname??null}}</title>
        
        <!-- Favicon icon -->
	    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('')}}new_assests/images/favicon-n.png">
        
        <!-- Global stylesheets -->
    	<link rel="stylesheet" href="{{asset('')}}new_assests/vendor/chartist/css/chartist.min.css">
    	<link href="{{asset('')}}assets/js/plugins/materialToast/mdtoast.min.css" rel="stylesheet" type="text/css">
    	
    	<!-- Vectormap -->
    	<link href="{{asset('')}}new_assests/vendor/jqvmap/css/jqvmap.min.css" rel="stylesheet">
    	<link href="{{asset('')}}new_assests/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    	<link href="{{asset('')}}new_assests/vendor/owl-carousel/owl.carousel.css" rel="stylesheet">
    	<link class="main-css" href="{{asset('')}}new_assests/css/style.css" rel="stylesheet">
    	
    	<!-- Datatable Stylesheet -->
    	<link href="{{asset('')}}new_assests/vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="{{asset('')}}new_assests/vendor/datatables/responsive/responsive.css" rel="stylesheet">
        <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
        <style>
            .deznav, .header, .nav-header {
                position:fixed;
            }
            .toggle.ios, .toggle-on.ios, .toggle-off.ios { border-radius: 20px; }
            .toggle.ios .toggle-handle { border-radius: 20px; }
            .nav-header .logo-abbr, .select2, .dataTables_filter, .hide {display:none;}
            .open .dropdown-menu {display:block;}
            .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {color:#fff !important;}
            #permissionForm, .tab-pane .table-responsive { overflow: hidden !important;}
            .table-responsive { overflow: visible !important;}
            .show, .menu-toggle .nav-header .logo-abbr {display:block;}
        </style>
        
        <!--Old Styles-->
    	<link href="{{asset('')}}new_assests/css/bootstrap-modal.css" rel="stylesheet" type="text/css">
        @stack('style')
        
        <!-- Required vendors -->
    	<script src="{{asset('')}}new_assests/vendor/global/global.min.js"></script>
    	<script src="{{asset('')}}new_assests/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
    	<script src="{{asset('')}}new_assests/vendor/chart-js/chart.bundle.min.js"></script>
    	<script src="{{asset('')}}new_assests/vendor/owl-carousel/owl.carousel.js"></script>
    
    	<!-- Chart piety plugin files -->
    	<script src="{{asset('')}}new_assests/vendor/peity/jquery.peity.min.js"></script>
    
    	<!-- Apex Chart -->
    	<script src="{{asset('')}}new_assests/vendor/apexchart/apexchart.js"></script>
    
    	<!-- Dashboard 1 -->
    	<script src="{{asset('')}}new_assests/js/dashboard/dashboard-1.js"></script>
    	<script src="{{asset('')}}new_assests/js/custom.min.js"></script>
    	<!--<script src="{{asset('')}}new_assests/js/deznav-init.js"></script>-->
    	<!--<script src="{{asset('')}}new_assests/js/demo.js"></script>-->
    	<script src="{{asset('')}}new_assests/js/styleSwitcher.js"></script>
    	
    	<!-- Datatable -->
	    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
        <script src="{{asset('')}}new_assests/vendor/datatables/js/jquery.dataTables.min.js"></script>
        <script src="{{asset('')}}new_assests/vendor/datatables/responsive/responsive.js"></script>
        <script src="{{asset('')}}new_assests/js/plugins-init/datatables.init.js"></script>
        
        <script>
    		$(function () {
    			$('[data-bs-toggle="popover"]').popover()
    		})
    	</script>
	
	    <!--Old Script-->
        <script type="text/javascript" src="{{asset('')}}assets/js/core/app.js"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/core/dropzone.js"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/plugins/materialToast/mdtoast.min.js"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/core/momentjs.js"></script>
    	<script type="text/javascript" src="{{asset('')}}assets/js/core/libraries/bootstrap.min.js"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/plugins/loaders/blockui.min.js"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/plugins/ui/ripple.min.js"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/core/jquery.validate.min.js"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/core/jquery.form.min.js"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/core/sweetalert2.min.js"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
        <script type="text/javascript" src="{{ asset('/assets/js/core/jQuery.print.js') }}"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/plugins/forms/selects/select2.min.js"></script>

        <script type="text/javascript">
            $(document).ready(function(){

                getbalance();

                $('.select').select2();
                
                $(".navbar-default a").each(function() {
                    if (this.href == window.location.href) {
                        $(this).addClass("active");
                        $(this).parent().addClass("active");
                        $(this).parent().parent().parent().addClass("active");
                        $(this).parent().parent().parent().parent().parent().parent().parent().addClass("active");
                    }
                });

                $('#reportExport').click(function(){
                    var type     = $('[name="dataType"]').val();
                    var fromdate = $('#searchForm').find('input[name="from_date"]').val();
                    var todate   = $('#searchForm').find('input[name="to_date"]').val();
                    var agent    = $('#searchForm').find('input[name="agent"]').val();
                    var status   = $('#searchForm').find('[name="status"]').val();

                    @if(isset($id))
                        agent = "{{$id}}";
                    @endif

                    window.location.href = "{{ url('export/report') }}/"+type+"?fromdate="+fromdate+"&todate="+todate+"&agent="+agent+"&status="+status;
                });

                $('.mydate').datepicker({
                    'autoclose':true,
                    'clearBtn':true,
                    'todayHighlight':true,
                    'format':'yyyy-mm-dd'
                });

                $('input[name="from_date"]').datepicker("setDate", new Date());
                $('input[name="to_date"]').datepicker('setStartDate', new Date());

                 $('input[name="to_date"]').focus(function(){
                    if($('input[name="from_date"]').val().length == 0){
                        $('input[name="to_date"]').datepicker('hide');
                        $('input[name="from_date"]').focus();
                    }
                });

                $('input[name="from_date"]').datepicker().on('changeDate', function(e) {
                    $('input[name="to_date"]').datepicker('setStartDate', $('input[name="from_date"]').val());
                    $('input[name="to_date"]').datepicker('setDate', $('input[name="from_date"]').val());
                });

                $('form#searchForm').submit(function(){
                    $('#searchForm').find('button:submit').button('loading');
                    var fromdate =  $(this).find('input[name="from_date"]').val();
                    var todate   =  $(this).find('input[name="to_date"]').val();
                    if(fromdate.length !=0 || todate.length !=0){
                        $('#datatable').dataTable().api().ajax.reload();
                    }

                    return false;
                });

                $('#formReset').click(function () {
                    $('form#searchForm')[0].reset();
                    $('form#searchForm').find('[name="from_date"]').datepicker().datepicker("setDate", new Date());
                    $('form#searchForm').find('[name="to_date"]').datepicker().datepicker("setDate", null);
                    $('form#searchForm').find('select').select2().val(null).trigger('change')
                    $('#formReset').button('loading');
                    $('#datatable').dataTable().api().ajax.reload();
                });
                
                $('select').change(function(event) {
                    var ele = $(this);
                    if(ele.val() != ''){
                        $(this).closest('div.form-group').find('p.error').remove();
                    }
                });

                $( "#editForm" ).validate({
                    rules: {
                        status: {
                            required: true,
                        },
                        txnid: {
                            required: true,
                        },
                        payid: {
                            required: true,
                        },
                        refno: {
                            required: true,
                        }
                    },
                    messages: {
                        name: {
                            required: "Please select status",
                        },
                        txnid: {
                            required: "Please enter txn id",
                        },
                        payid: {
                            required: "Please enter payid",
                        },
                        refno: {
                            required: "Please enter ref no",
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
                        var form = $('#editForm');
                        var id = form.find('[name="id"]').val();
                        form.ajaxSubmit({
                            dataType:'json',
                            beforeSubmit:function(){
                                form.find('button[type="submit"]').button('loading');
                            },
                            success:function(data){
                                if(data.status == "success"){
                                    form.find('button[type="submit"]').button('reset');
                                    notify("Task Successfully Completed", 'success');
                                    $('#datatable').dataTable().api().ajax.reload(null, false);
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

                $(".modal").on('hidden.bs.modal', function () {
                    if($(this).find('form').length){
                        $(this).find('form')[0].reset();
                    }
        
                    if($(this).find('.select').length){
                        $(this).find('.select').val(null).trigger('change');
                    }
                });

                $( "#walletLoadForm").validate({
                    rules: {
                        amount: {
                            required: true,
                        }
                    },
                    messages: {
                        amount: {
                            required: "Please enter amount",
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
                        var form = $('#walletLoadForm');
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
                                    getbalance();
                                    form.closest('.modal').modal('hide');
                                    notify("Wallet successfully loaded", 'success');
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

                $( "#complaintForm").validate({
                    rules: {
                        subject: {
                            required: true,
                        },
                        description: {
                            required: true,
                        }
                    },
                    messages: {
                        subject: {
                            required: "Please select subject",
                        },
                        description: {
                            required: "Please enter your description",
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
                        var form = $('#complaintForm');
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
                                    if(data.status == "success"){
                                        form[0].reset();
                                        form.closest('.modal').modal('hide');
                                        notify("Complaint successfully submitted", 'success');
                                    }else{
                                        notify(data.status , 'warning');
                                    }
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
                
                $( "#notifyForm").validate({
                    rules: {
                        amount: {
                            required: true,
                        }
                    },
                    messages: {
                        amount: {
                            required: "Please enter amount",
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
                        var form = $('#notifyForm');
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
                                    getbalance();
                                    form.closest('.modal').modal('hide');
                                    notify("Send successfully", 'success');
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

            function getbalance(){
                $.ajax({
                    url: "{{url('mydata')}}",
                    type: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    success: function(data){
                        $('.fundCount').text(data.fundrequest);
                        $(".payin").text(data.payin);
                        $(".payout").text(data.payout);
                    }
                });
            }

            @if (isset($table) && $table == "yes")
                function datatableSetup(urls, datas, onDraw=function () {}, ele="#datatable", element={}) {
                    var options = {
                        dom: '<"datatable-header"l><"datatable-scroll"t><"datatable-footer"ip>',
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ordering:   false,
                        stateSave:  true,
                        lengthMenu: [25, 50, 100],
                        language: {
                            paginate: { 'first': 'First', 'last': 'Last', 'next': '&rarr;', 'previous': '&larr;' }
                        },
                        drawCallback: function () {
                            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
                        },
                        preDrawCallback: function() {
                            $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
                        },    
                        ajax:{
                            url : urls,
                            type: "post",
                            data:function( d )
                                {
                                    d._token   = $('meta[name="csrf-token"]').attr('content');
                                    d.type     = $('[name="dataType"]').val();
                                    d.fromdate = $('#searchForm').find('[name="from_date"]').val();
                                    d.todate   = $('#searchForm').find('[name="to_date"]').val();
                                    d.searchtext = $('#searchForm').find('[name="searchtext"]').val();
                                    d.agent    = $('#searchForm').find('[name="agent"]').val();
                                    d.status   = $('#searchForm').find('[name="status"]').val();
                                    d.product  = $('#searchForm').find('[name="product"]').val();
                                },
                            beforeSend: function(){
                            },
                            complete: function(){
                                $('#searchForm').find('button:submit').button('reset');
                                $('#formReset').button('reset');
                            },
                            error:function(response) {
                            }
                        },
                        columns: datas
                    };

                    $.each(element, function(index, val) {
                        options[index] = val; 
                    });

                    var DT = $(ele).DataTable(options).on('draw.dt', onDraw);
                    return DT;
                }
            @endif

            function notify(msg, type="success", notitype="popup", element="none"){
                if(notitype == "popup"){
                    switch(type){
                        case "success":
                            mdtoast.success("Success : "+msg, { position: "top center" });
                        break;

                        default:
                            mdtoast.error("Oops! "+msg, { position: "top center" });
                            break;
                    }
                }else{
                    element.find('div.alert').remove();
                    element.prepend(`<div class="alert bg-`+type+` alert-styled-left">
                        <button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button> `+msg+`
                    </div>`);

                    setTimeout(function(){
                        element.find('div.alert').remove();
                    }, 10000);
                }
            }

            function showError(errors, form="withoutform"){
                if(form != "withoutform"){
                    form.find('button[type="submit"]').button('reset');
                    $('p.error').remove();
                    $('div.alert').remove();
                    if(errors.status == 422){
                        $.each(errors.responseJSON.errors, function (index, value) {
                            form.find('[name="'+index+'"]').closest('div.form-group').append('<p class="error">'+value+'</span>');
                        });
                        form.find('p.error').first().closest('.form-group').find('input').focus();
                        setTimeout(function () {
                            form.find('p.error').remove();
                        }, 5000);
                    }else if(errors.status == 400){
                        if(errors.responseJSON.message){
                            form.prepend(`<div class="alert bg-danger alert-styled-left">
                                <button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>
                                <span class="text-semibold">Oops !</span> `+errors.responseJSON.message+`
                            </div>`);
                        }else{
                            form.prepend(`<div class="alert bg-danger alert-styled-left">
                                <button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>
                                <span class="text-semibold">Oops !</span> `+errors.responseJSON.status+`
                            </div>`);
                        }

                        setTimeout(function () {
                            form.find('div.alert').remove();
                        }, 10000);
                    }else{
                        mdtoast.error("Oops! "+errors.statusText , { position: "top center" });
                    }
                }else{
                    if(errors.responseJSON.message){
                        mdtoast.error("Oops! "+errors.responseJSON.message, { position: "top center" });
                    }else{
                        mdtoast.error("Oops! "+errors.responseJSON.status, { position: "top center" });
                    }
                }
            }

            function sessionOut(){
                window.location.href = "{{route('logout')}}";
            }

            function status(id, type){
                $.ajax({
                    url: `{{route('statementStatus')}}`,
                    type: 'post',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType:'json',
                    beforeSend:function(){
                        swal({
                            title: 'Wait!',
                            text: 'Please wait, we are fetching transaction details',
                            onOpen: () => {
                                swal.showLoading()
                            },
                            allowOutsideClick: () => !swal.isLoading()
                        });
                    },
                    data:{'id':id, "type":type}
                })
                .done(function(data) {
                    if(data.status == "success"){
                        if(data.refno){
                            var refno = "Operator Refrence is "+data.refno
                        }else{
                            var refno = data.remark;
                        }
                        swal({
                            type: 'success',
                            title: data.status,
                            text : refno,
                            onClose: () => {
                                $('#datatable').dataTable().api().ajax.reload(null, false);
                            },
                        });
                    }else{
                        swal({
                            type: 'success',
                            title: data.status,
                            text : "Transaction status is "+data.status,
                            onClose: () => {
                                $('#datatable').dataTable().api().ajax.reload(null, false);
                            },
                        });
                    }
                })
                .fail(function(errors) {
                    swal.close();
                    showError(errors, "withoutform");
                });
            }

            function editReport(id, refno, txnid, payid, remark, status, actiontype){
                $('#editModal').find('[name="id"]').val(id);
                $('#editModal').find('[name="status"]').val(status).trigger('change');
                $('#editModal').find('[name="refno"]').val(refno);
                $('#editModal').find('[name="txnid"]').val(txnid);
                if(actiontype == "billpay"){
                    $('#editModal').find('[name="payid"]').closest('div.form-group').remove();
                }else{
                    $('#editModal').find('[name="payid"]').val(payid);
                }
                $('#editModal').find('[name="remark"]').val(remark);
                $('#editModal').find('[name="actiontype"]').val(actiontype);
                $('#editModal').modal('show');
            }

            function complaint(id, product){
                $('#complaintModal').find('[name="transaction_id"]').val(id);
                $('#complaintModal').find('[name="product"]').val(product);
                $('#complaintModal').modal('show');
            }
        </script>
        
        <script type="text/javascript">
            var ROOT = "{{url('')}}" , SYSTEM;

            $(document).ready(function () {
                SYSTEM = {
                    DEFAULT: function () {
                    },

                    FORMBLOCK:function (form) {
                        form.block({
                            message: '<span class="text-semibold"><i class="icon-spinner4 spinner position-left"></i>&nbsp; Working on request</span>',
                            overlayCSS: {
                                backgroundColor: '#fff',
                                opacity: 0.8,
                                cursor: 'wait'
                            },
                            css: {
                                border: 0,
                                padding: '10px 15px',
                                color: '#fff',
                                width: 'auto',
                                '-webkit-border-radius': 2,
                                '-moz-border-radius': 2,
                                backgroundColor: '#333'
                            }
                        });
                    },

                    FORMUNBLOCK: function (form) {
                        form.unblock();
                    },

                    FORMSUBMIT: function(form, callback, block="none"){
                        form.ajaxSubmit({
                            dataType:'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            beforeSubmit:function(){
                                form.find('button[type="submit"]').button('loading');
                                if(block == "none"){
                                    form.block({
                                        message: '<span class="text-semibold"><i class="icon-spinner4 spinner position-left"></i>&nbsp; Working on request</span>',
                                        overlayCSS: {
                                            backgroundColor: '#fff',
                                            opacity: 0.8,
                                            cursor: 'wait'
                                        },
                                        css: {
                                            border: 0,
                                            padding: '10px 15px',
                                            color: '#fff',
                                            width: 'auto',
                                            '-webkit-border-radius': 2,
                                            '-moz-border-radius': 2,
                                            backgroundColor: '#333'
                                        }
                                    });
                                }
                            },
                            complete: function(){
                                form.find('button[type="submit"]').button('reset');
                                if(block == "none"){
                                    form.unblock();
                                }
                            },
                            success:function(data){
                                callback(data);
                            },
                            error: function(errors) {
                                callback(errors);
                            }
                        });
                    },

                    AJAX: function(url, method, data, callback, loading="none", msg="Updating Data"){
                        $.ajax({
                            url: url,
                            type: method,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType:'json',
                            data: data,
                            beforeSend:function(){
                                if(loading != "none"){
                                    $(loading).block({
                                        message: '<span class="text-semibold"><i class="icon-spinner4 spinner position-left"></i> '+msg+'</span>',
                                        overlayCSS: {
                                            backgroundColor: '#fff',
                                            opacity: 0.8,
                                            cursor: 'wait'
                                        },
                                        css: {
                                            border: 0,
                                            padding: '10px 15px',
                                            color: '#fff',
                                            width: 'auto',
                                            '-webkit-border-radius': 2,
                                            '-moz-border-radius': 2,
                                            backgroundColor: '#333'
                                        }
                                    });
                                }
                            },
                            complete: function () {
                                $(loading).unblock();
                            },
                            success:function(data){
                                callback(data);
                            },
                            error: function(errors) {
                                callback(errors);
                            }
                        });
                    },

                    SHOWERROR: function(errors, form){
                        if(errors.status == 422){
                            $.each(errors.responseJSON.errors, function (index, value) {
                                form.find('[name="'+index+'"]').closest('div.form-group').append('<p class="error">'+value+'</span>');
                            });
                            form.find('p.error').first().closest('.form-group').find('input').focus();
                            setTimeout(function () {
                                form.find('p.error').remove();
                            }, 5000);
                        }else if(errors.status == 400){
                            mdtoast.error("Oops! "+errors.responseJSON.message, { position: "top center" });
                        }else{
                            if(errors.message){
                                mdtoast.error("Oops! "+errors.message, { position: "top center" });
                            }else{
                                mdtoast.error("Oops! "+errors.statusText, { position: "top center" });
                            }
                        }
                    },

                    NOTIFY: function(msg, type="success",element="none"){
                        if(element == "none"){
                            switch(type){
                                case "success":
                                    mdtoast.success("Success : "+msg, { position: "top center" });
                                break;

                                default:
                                    mdtoast.error("Oops! "+msg, { position: "top center" });
                                    break;
                            }
                        }else{
                            element.find('div.alert').remove();
                            element.prepend(`<div class="alert bg-`+type+` alert-styled-left">
                                <button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button> `+msg+`
                            </div>`);

                            setTimeout(function(){
                                element.find('div.alert').remove();
                            }, 10000);
                        }
                    }
                }
                SYSTEM.DEFAULT();
            });
            // Function to set a cookie
            function setCookie(name, value, days) {
                let expires = "";
                if (days) {
                    const date = new Date();
                    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
            }
            
            // Function to get a cookie
            function getCookie(name) {
                const nameEQ = name + "=";
                const cookies = document.cookie.split(';');
                for (let i = 0; i < cookies.length; i++) {
                    let c = cookies[i];
                    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }
            
            // Function to delete a cookie
            function eraseCookie(name) {
                document.cookie = name + "=; Max-Age=-99999999;";
            }
        </script>
        @stack('script')
    </head>

    <body class="navbar-top @yield('bodyClass')">
        <!--*******************
            Preloader start
        ********************-->
    	<div id="preloader">
    		<div class="sk-three-bounce">
    			<div class="sk-child sk-bounce1"></div>
    			<div class="sk-child sk-bounce2"></div>
    			<div class="sk-child sk-bounce3"></div>
    		</div>
    	</div>
    	<!--*******************
            Preloader end
        ********************-->
        <div id="main-wrapper">
            <input type="hidden" name="dataType" value="">
            @include('layouts.topbar')
    
            <div class="page-container">
                <div class="page-content">
                    {{-- @include('layouts.sidebar') --}}
    
                    <div class="content-wrapper">
                        @include('layouts.pageheader')
                        @yield('content')
                    </div>
                </div>
            </div>
            
            <div id="walletLoadModal" class="modal fade" data-backdrop="false" data-keyboard="false">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header bg-slate">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h6 class="modal-title">Wallet Load</h6>
                        </div>
                        <form id="walletLoadForm" action="{{route('fundtransaction')}}" method="post">
                            <div class="modal-body">
                                <div class="row">
                                    <input type="hidden" name="type" value="loadwallet">
                                    {{ csrf_field() }}
                                    <div class="form-group col-md-12">
                                        <label>Wallet Type</label>
                                        <select name="wallet" class="form-control select" id="select" required>
                                            <option value="">Select Wallet</option>
                                            <option value="mainwallet">Collection Wallet</option>
                                            <option value="payoutwallet">Payout Wallet</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label>Amount</label>
                                        <input type="number" name="amount" step="any" class="form-control" placeholder="Enter Amount" required="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label>Remark</label>
                                        <textarea name="remark" class="form-control" rows="3" placeholder="Enter Remark"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                                <button class="btn btn-info btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="notifyModal" class="modal fade" data-backdrop="false" data-keyboard="false">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header bg-slate">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h6 class="modal-title">App Notify</h6>
                        </div>
                        <form id="notifyForm" action="{{route('fundtransaction')}}" method="post">
                            <div class="modal-body">
                                <div class="row">
                                    <input type="hidden" name="type" value="appnotify">
                                    {{ csrf_field() }}
                                    <div class="form-group col-md-12">
                                        <label>Notification Type</label>
                                        <select name="sendtype" class="form-control select" required>
                                            <option value="">Select Type</option>
                                            <option value="alert">Alert</option>
                                            <option value="notify">Notify</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Title</label>
                                        <input type="text" name="title" class="form-control" placeholder="Enter Title" required="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label>Decstription</label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="Enter Decstription"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                                <button class="btn btn-info btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Submitting">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="editModal" class="modal fade" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true" data-backdrop="false" data-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-slate">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h6 class="modal-title">Edit Report</h6>
                        </div>
                        <form id="editForm" action="{{route('statementUpdate')}}" method="post">
                            <div class="modal-body">
                                <div class="row">
                                    <input type="hidden" name="id">
                                    <input type="hidden" name="actiontype" value="">
                                    {{ csrf_field() }}
                                    <div class="form-group col-md-6">
                                        <label>Status</label>
                                        <select name="status" class="form-control select" required>
                                            <option value="">Select Type</option>
                                            <option value="pending">Pending</option>
                                            <option value="success">Success</option>
                                            <option value="complete">Complete</option>
                                            <option value="failed">Failed</option>
                                            <option value="reversed">Reversed</option>
                                            <option value="chargeback">Charge Back</option>
                                        </select>
                                    </div>
            
                                    <div class="form-group col-md-6">
                                        <label>Ref No</label>
                                        <input type="text" name="refno" class="form-control" placeholder="Enter Vle id" required="">
                                    </div>
                                </div>
            
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Txn Id</label>
                                        <input type="text" name="txnid" class="form-control" placeholder="Enter Vle id" required="">
                                    </div>
            
                                    <div class="form-group col-md-6">
                                        <label>Pay Id</label>
                                        <input type="text" name="payid" class="form-control" placeholder="Enter Vle id" required="">
                                    </div>
                                </div>
            
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label>Remark</label>
                                        <textarea rows="3" name="remark" class="form-control" placeholder="Enter Remark"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger btn-raised legitRipple" data-dismiss="modal" aria-hidden="true">Close</button>
                                <button class="btn btn-info btn-raised legitRipple" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div id="complaintModal" class="modal fade" role="dialog" aria-labelledby="complaintModalLabel" aria-hidden="true" data-backdrop="false" data-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-slate">
                            <h6 class="modal-title" id="complaintModalLabel">Raise Complaint</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
            
                        <form id="complaintForm" action="{{route('complaintstore')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="new">
                            <input type="hidden" name="product">
                            <input type="hidden" name="transaction_id">
            
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="subject">Bank Utr</label>
                                    <input type="text" name="subject" class="form-control" id="subject" placeholder="Enter value" required>
                                </div>
                                <div class="form-group">
                                    <label for="descriptions">Screenshot</label>
                                    <input type="file" name="descriptions" class="form-control" id="descriptions" required>
                                </div>
                            </div>
            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger light" data-bs-dismiss="modal" aria-hidden="true">Close</button>
                                <button class="btn bg-slate" type="submit" data-loading-text="<i class='fa fa-spin fa-spinner'></i> Updating">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div id="payoutreceipt" class="modal fade" role="dialog" aria-labelledby="payoutReceiptLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-slate">
                            <h4 class="modal-title" id="payoutReceiptLabel">Receipt</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <legend>Payment Details</legend>
                            
                            <div class="form-group mb-10">
                                <label class="text-semibold">Transaction ID</label>
                                <span class="pull-right txnid"></span>
                            </div>
                            <hr class="mt-1 mb-1">
            
                            <div class="form-group mb-10">
                                <label class="text-semibold">RRN</label>
                                <span class="pull-right refno"></span>
                            </div>
                            <hr class="mt-1 mb-1">
            
                            <div class="form-group mb-10">
                                <label class="text-semibold">Beneficiary Name</label>
                                <span class="pull-right description"></span>
                            </div>
                            <hr class="mt-1 mb-1">
            
                            <div class="form-group mb-10">
                                <label class="text-semibold">Beneficiary Account No</label>
                                <span class="pull-right number"></span>
                            </div>
                            <hr class="mt-1 mb-1">
            
                            <div class="form-group mb-10">
                                <label class="text-semibold">Beneficiary IFSC</label>
                                <span class="pull-right option2"></span>
                            </div>
                            <hr class="mt-1 mb-1">
            
                            <div class="form-group mb-10">
                                <label class="text-semibold">Beneficiary Bank</label>
                                <span class="pull-right option3"></span>
                            </div>
                            <hr class="mt-1 mb-1">
            
                            <div class="form-group mb-10">
                                <label class="text-semibold">Transaction Date</label>
                                <span class="pull-right created_at"></span>
                            </div>
                            <hr class="mt-1 mb-1">
            
                            <div class="form-group mb-10">
                                <label class="text-semibold">Transaction Status</label>
                                <span class="pull-right status text-uppercase"></span>
                            </div>
                            <hr class="mt-1 mb-1">
            
                            <div class="form-group mb-10">
                                <label class="text-semibold">Transaction Amount</label>
                                <span class="pull-right amount"></span>
                            </div>
                        </div>
            
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger light" data-bs-dismiss="modal" aria-hidden="true">Close</button>
                            <button class="btn bg-slate" type="button" id="payoutprint">
                                <i class="fa fa-print"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="payinreceipt" class="modal fade" role="dialog" aria-labelledby="payinReceiptLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-slate">
                            <h4 class="modal-title" id="payoutReceiptLabel">Receipt</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <legend>Payment Details</legend>
                            
                            <div class="form-group mb-10">
                                <label class="text-semibold">Transaction ID</label>
                                <span class="pull-right txnid"></span>
                            </div>
                            <hr class="mt-1 mb-1">
            
                            <div class="form-group mb-10">
                                <label class="text-semibold">RRN</label>
                                <span class="pull-right refno"></span>
                            </div>
                            <hr class="mt-1 mb-1">
            
                            <div class="form-group mb-10">
                                <label class="text-semibold">Transaction Date</label>
                                <span class="pull-right created_at"></span>
                            </div>
                            <hr class="mt-1 mb-1">
            
                            <div class="form-group mb-10">
                                <label class="text-semibold">Transaction Status</label>
                                <span class="pull-right status text-uppercase"></span>
                            </div>
                            <hr class="mt-1 mb-1">
            
                            <div class="form-group mb-10">
                                <label class="text-semibold">Transaction Amount</label>
                                <span class="pull-right amount"></span>
                            </div>
                        </div>
            
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger light" data-bs-dismiss="modal" aria-hidden="true">Close</button>
                            <button class="btn bg-slate" type="button" id="payinprint">
                                <i class="fa fa-print"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="transactionModal" class="modal fade right" data-backdrop="false" data-keyboard="false">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-slate">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h6 class="modal-title">Transaction Details</h6>
                        </div>
                        <div class="modal-body ">
                            <legend>Reference Details</legend>
                            <div class="form-group">
                                <label class="text-semibold">Reference No.</label>
                                <span class="pull-right refno"></span>
                            </div>
            
                            <div class="form-group">
                                <label class="text-semibold">Tranaction Id</label>
                                <span class="pull-right txnid"></span>
                            </div>
            
                            <div class="form-group">
                                <label class="text-semibold">Aoi Reference Id</label>
                                <span class="pull-right apitxnid"></span>
                            </div>
            
                            <div class="form-group">
                                <label class="text-semibold">Transaction Date</label>
                                <span class="pull-right created_at"></span>
                            </div>
            
                            <div class="form-group no-margin-bottom">
                                <label class="text-semibold">Transaction Status</label>
                                <span class="pull-right status text-uppercase"></span>
                            </div>
            
                            <legend>Amount Details</legend>
                            <div class="amountData"></div>
            
                            <legend>Transaction Details</legend>
                            <div class="transactionData"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
