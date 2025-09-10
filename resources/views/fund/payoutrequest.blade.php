@extends('layouts.app')
@section('title', "Transaction History")
@section('pagetitle',  "Transaction History")

@php
    $table  = "yes";
    $export = "payout";
    $status['type'] = "Report";
    $status['data'] = [
        "success"  => "Success",
        "pending"  => "Pending",
        "reversed" => "Reversed",
        "refunded" => "Refunded",
    ];
@endphp

@section('content')
<div class="content">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Payout History</h4>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="example4">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User Details</th>
                                <th>Transaction Details</th>
                                <th>Refrence Details</th>
                                <th>Amount</th>
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
@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    var DT;
    $(document).ready(function () {
        $('[name="dataType"]').val("payoutrequest");
        var url = "{{route("reportstatic")}}";
        var onDraw = function() {
        };
        var options = [
            { "data" : "name",
                render:function(data, type, full, meta){
                    return `<div>
                            <span class=''>`+full.apiname +`</span><br>
                            <span class='text-inverse m-l-10'>SN : <b>`+full.id +`</b> </span>
                            <div class="clearfix"></div>
                        </div><span style='font-size:13px' class="pull=right">`+full.created_at+`</span>`;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return full.username+"<br>"+full.usermobile+"<br>"+full.user_id;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.product == "bankpayout"){
                        return "Name : "+full.description+"<br>Account : "+full.number+"<br>Bank : "+full.option3+"<br>Ifsc: "+full.option2;
                    }else{
                        return "Txnid : "+full.txnid+"<br>Qr Code: "+full.type;
                    }
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.product == "bankpayout"){
                        return "Reference / Utr: "+full.refno+"<br>Txn Id : "+full.txnid;
                    }else{
                        return "Bank Utr: "+full.utr+"<br>Pay Id : "+full.refid;
                    }
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.product == "payout"){
                        return "Amount : "+full.amount+"<br>Charge : "+full.charge+"<br>Tds : "+full.tds+"<br>Gst : "+full.gst;
                    }else{
                        return "Amount : "+full.amount;
                    }
                }
            },
            { "data" : "status",
                render:function(data, type, full, meta){
                    if(full.status == "success"){
                        var out = `<span class="label label-success">Success</span>`;
                    }else if(full.status == "pending"){
                        var out = `<span class="label label-warning">Pending</span>`;
                    }else if(full.status == "reversed" || full.status == "refunded"){
                        var out = `<span class="label bg-slate">`+full.status+`</span>`;
                    }else{
                        var out = `<span class="label label-danger">`+full.status+`</span>`;
                    }
                    return out;
                }
            }
        ];

        DT = datatableSetup(url, options, onDraw);

        $( "#fundRequestForm").validate({
            rules: {
                account: {
                    required: true
                },
                ifsc: {
                    required: true
                },
                amount: {
                    required: true
                },
                type: {
                    required: true
                },
            },
            messages: {
                account: {
                    required: "Please enter bank account",
                },
                ifsc: {
                    required: "Please enter bank ifsc",
                },
                amount: {
                    required: "Please enter request amount",
                },
                type: {
                    required: "Please select request type",
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
                var form = $('#fundRequestForm');
                form.ajaxSubmit({
                    dataType:'json',
                    beforeSubmit:function(){
                        form.find('button:submit').button('loading');
                    },
                    complete: function () {
                        form.find('button:submit').button('reset');
                    },
                    success:function(data){
                        if(data.statuscode == "TXN"){
                            form.closest('.modal').modal('hide');
                            notify("Payout Request submitted Successfull", 'success');
                            $('#example4').dataTable().api().ajax.reload();
                        }else{
                            notify(data.message , 'warning');
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