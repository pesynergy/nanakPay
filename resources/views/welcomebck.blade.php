<!DOCTYPE html>
<html lang="zxx">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Login {{$_SERVER['HTTP_HOST']}}</title>
        <link rel="shortcut icon" type="image/icon" href="images/favicon-16x16.png"/>
        <link rel="stylesheet" href="{{asset('')}}login/css/login3-style.css">
        <link href="{{asset('')}}assets/js/plugins/materialToast/mdtoast.min.css" rel="stylesheet" type="text/css">

        <style type="text/css">
            .navbar{
                border-radius: 0px;
            }
        </style>
    </head>
  
    <body>
        <nav class="navbar navbar-inverse">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="{{ route('mylogin') }}">{{$company->companyname}}</a>
                </div>
            </div>
        </nav>

        <div id="preload-block">
            <div class="square-block"></div>
        </div>
        
        <div class="container">
            <div class="row">
                <div class="authfy-container col-xs-12 col-sm-8 col-md-6 col-lg-6 col-sm-offset-2 col-md-offset-3 col-lg-offset-3">
                    <div class="col-md-12 authfy-panel-right">
                        <div class="authfy-login">
                            <div class="authfy-panel panel-login active">
                                <div class="authfy-heading text-center">
                                    <h3 class="auth-title">Login to your account</h3>
                                </div>
                                
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12">
                                        <form class="login-form" id="login" method="POST" action="{{route('authCheck')}}">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="gps_location">
                                            <div class="form-group">
                                                <label>Email Address</label>
                                                <input type="email" class="form-control" name="email" placeholder="Email Address">
                                            </div>
                                            
                                            <div class="form-group">
                                                <div class="pwdMask">
                                                    <label>Password</label>
                                                    <input type="password" class="form-control password" name="password" placeholder="Password">
                                                    <span class="fa fa-eye-slash pwd-toggle"></span>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
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

        <script src="{{asset('')}}login/js/jquery-2.2.4.min.js"></script>
        <script src="{{asset('')}}login/js/bootstrap.min.js"></script>
        <script src="{{asset('')}}login/js/custom.js"></script>
      
        <script type="text/javascript" src="{{asset('')}}assets/js/core/jquery.validate.min.js"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/core/jquery.form.min.js"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/plugins/loaders/blockui.min.js"></script>
        <script type="text/javascript" src="{{asset('')}}assets/js/plugins/materialToast/mdtoast.min.js"></script>

        <script type="text/javascript">
            window.alert = function(){};
            var defaultCSS = document.getElementById('bootstrap-css');
            function changeCSS(css){
                if(css) $('head > link').filter(':first').replaceWith('<link rel="stylesheet" href="'+ css +'" type="text/css" />'); 
                else $('head > link').filter(':first').replaceWith(defaultCSS); 
            }

            $(document).ready(function() {
                $.fn.extend({
                    myalert: function(value, type, time = 5000) {
                        var tag = $(this);
                        tag.find('.myalert').remove();
                        tag.append('<p id="" class="myalert text-' + type + '">' + value + '</p>');
                        tag.find('input').focus();
                        tag.find('select').focus();
                        setTimeout(function() {
                            tag.find('.myalert').remove();
                        }, time);
                        tag.find('input').change(function() {
                            if (tag.find('input').val() != '') {
                                tag.find('.myalert').remove();
                            }
                        });
                        tag.find('select').change(function() {
                            if (tag.find('select').val() != '') {
                                tag.find('.myalert').remove();
                            }
                        });
                    },

                    mynotify: function(value, type, time = 5000) {
                        var tag = $(this);
                        tag.find('.mynotify').remove();
                        tag.prepend(`<div class="mynotify alert alert-` + type + ` alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            ` + value + `
                        </div>`);
                        setTimeout(function() {
                            tag.find('.mynotify').remove();
                        }, time);
                    }
                });

                LOGINSYSTEM = {
                        DEFAULT: function() {
                            $('#login').submit(function() {
                                SYSTEM.FORMSUBMIT($('#login'), function(data) {
                                    if (!data.statusText) {
                                        if (data.status == "TXN") {
                                            SYSTEM.NOTIFY("success", "Successfully Logged In");
                                            setTimeout(function(){
                                                window.location.reload(); 
                                            }, 2000);
                                        }else {
                                            SYSTEM.SHOWERROR(data, $('#login'));
                                        }
                                    } else {
                                        SYSTEM.SHOWERROR(data, $('#login'));
                                    }
                                }, $('#login'));
                                return false;
                            });
                        }
                    },

                    SYSTEM = {
                        NOTIFY: function(type, message) {
                            switch(type){
                                case "success":
                                    mdtoast.success("Success : "+message, { position: "top center" });
                                break;

                                default:
                                    mdtoast.error("Oops! "+message, { position: "top center" });
                                    break;
                            }
                        },

                        FORMBLOCK:function (form) {
                            form.block({
                                message:'<div class="spinner-border text-white" role="status"></div>',
                                timeout:1e3,
                                css:{
                                    backgroundColor:"transparent",
                                    border:"0"
                                },
                                overlayCSS:{
                                    opacity:.5
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
                                    block.block({
                                        message:'<div class="spinner-border text-white" role="status"></div>',
                                        timeout:1e3,
                                        css:{
                                            backgroundColor:"transparent",
                                            border:"0"
                                        },
                                        overlayCSS:{
                                            opacity:.5
                                        }
                                    });
                                },
                                complete: function(){
                                    block.unblock();
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

                        SHOWERROR: function(errors, form, type = "inline") {
                            if (type == "inline") {
                                if (errors.statusText) {
                                    if (errors.status == 422) {
                                        form.find('p.error').remove();
                                        $.each(errors.responseJSON, function(index, value) {
                                            form.find('[name="' + index + '"]').closest('div.form-group').myalert(value, 'danger');
                                        });
                                    } else if (errors.status == 400) {
                                        form.mynotify(errors.responseJSON.message, 'danger');
                                    } else {
                                        form.mynotify(errors.statusText, 'danger');
                                    }
                                } else {
                                    form.mynotify(errors.message, 'danger');
                                }
                            } else {
                                if (errors.statusText) {
                                    if (errors.status == 400) {
                                        SYSTEM.NOTIFY('error', errors.responseJSON.message);
                                    } else {
                                        SYSTEM.NOTIFY('error', errors.statusText);
                                    }
                                } else {
                                    SYSTEM.NOTIFY('error', errors.message);
                                }
                            }
                        }
                    }

                LOGINSYSTEM.DEFAULT();
            });
        </script>
    </body>   
</html>
