@extends('layouts.app')
@section('title', "Account Statement")
@section('pagetitle',  "Account Statement")

@php
    $table = "yes";
    $agentfilter = "yes";
    $export = "wallet";
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Account Statement</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="display">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th width="150px">Refrence Details</th>
                                        <th>Product</th>
                                        <th>Provider</th>
                                        <th>Txnid</th>
                                        <th>Number</th>
                                        <th width="100px">ST Type</th>
                                        <th>Status</th>
                                        <th width="130px">Opening Bal.</th>
                                        <th width="130px">Amount</th>
                                        <th width="130px">Closing Bal.</th>
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
@endsection

@push('style')

@endpush

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
        $('[name="dataType"]').val("mainwallet");

        var url = "{{route("reportstatic")}}";
        var onDraw = function() {
            $('[data-popup="tooltip"]').tooltip();
            $('[data-popup="popover"]').popover({
                template: '<div class="popover border-teal-400"><div class="arrow"></div><h5 class="popover-title bg-teal-400"></h5><div class="popover-content"></div></div>'
            });
        };
        var options = [
            { "data" : "name",
                render:function(data, type, full, meta){
                    var out = "";
                    out += `</a><span style='font-size:13px' class="pull=right">`+full.created_at+`</span>`;
                    return out;
                }
            },
            { "data" : "full.username",
                render:function(data, type, full, meta){
                    var uid = "{{Auth::id()}}";
                    if(full.credited_by == uid){
                        var name = full.username + "<br>" + full.usermobile;
                    }else{
                        var name = full.sendername + "<br>" + full.sendermobile;
                    }
                    return name;
                }
            },
            { "data" : "product"},
            { "data" : "providername"},
            { "data" : "id"},
            { "data" : "number"},
            { "data" : "rtype"},
            { "data" : "status"},
            { "data" : "bank",
                render:function(data, type, full, meta){
                    return `<i class="fa fa-inr"></i> `+full.balance;
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.product == "aeps" || full.product == "matm"){
                        if(full.trans_type == "credit"){
                            return `<i class="text-success icon-plus22"></i> <i class="fa fa-inr"></i> `+ (full.amount + full.profit - full.gst - full.tds);
                        }else if(full.trans_type == "debit"){
                            return `<i class="text-danger icon-dash"></i> <i class="fa fa-inr"></i> `+ (full.amount + full.profit - full.gst - full.tds);
                        }else{
                            return `<i class="fa fa-inr"></i> `+ (full.amount + full.profit - full.gst - full.tds);
                        }
                    }else{
                        if(full.trans_type == "credit"){
                            return `<i class="text-success icon-plus22"></i> <i class="fa fa-inr"></i> `+ (full.amount + full.charge - full.profit - full.gst - full.tds);
                        }else if(full.trans_type == "debit"){
                            return `<i class="text-danger icon-dash"></i> <i class="fa fa-inr"></i> `+ (full.amount + full.charge - full.profit - full.gst - full.tds);
                        }else{
                            return `<i class="fa fa-inr"></i> `+ (full.amount + full.charge - full.profit - full.gst - full.tds);
                        }
                    }
                }
            },
            { "data" : "bank",
                render:function(data, type, full, meta){
                    if(full.status == "pending" || full.status == "success" || full.status == "reversed" || full.status == "refunded"){

                        if(full.product == "aeps" || full.product == "matm"){
                            if(full.trans_type == "credit"){
                                return `<i class="fa fa-inr"></i> `+ (full.balance + (full.amount + full.profit - full.gst - full.tds));
                            }else if(full.trans_type == "debit"){
                                return `<i class="fa fa-inr"></i> `+ (full.balance - (full.amount + full.profit - full.gst - full.tds));
                            }
                        }else{
                            if(full.trans_type == "credit"){
                                return `<i class="fa fa-inr"></i> `+ (full.balance + (full.amount + full.charge - full.profit - full.gst - full.tds));
                            }else if(full.trans_type == "debit"){
                                return `<i class="fa fa-inr"></i> `+ (full.balance - (full.amount + full.charge - full.profit - full.gst - full.tds));
                            }
                        }
                    }else{
                        return `<i class="fa fa-inr"></i> `+full.balance;
                    }
                }
            },
        ];

        datatableSetup(url, options, onDraw , '#datatable', {columnDefs: [{
                    orderable: false,
                    width: '80px',
                    targets: [0]
                }]});
    });
</script>
@endpush