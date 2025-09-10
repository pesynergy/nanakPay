
@extends('layouts.app')
@section('title', "Account Ladger")
@section('pagetitle',  "Ladger")

@php
    $table  = "yes";
    $export = "yes";
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"><span class="titleName"></span> Statement</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="display">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Partner</th>
                                        <th>Product</th>
                                        <th>Txnid</th>
                                        <th>Type</th>
                                        <th>Opening Bal.</th>
                                        <th>Amount</th>
                                        <th>Closing Bal.</th>
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

@push('script')
<script src="{{ asset('/assets/js/core/jQuery.print.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('[name="dataType"]').val("{{$type}}");
        @if(isset($id) && $id != 0)
            $('form#searchForm').find('[name="agent"]').val("{{$id}}");
        @endif

        $('#print').click(function(){
            $('#receptTable').print();
        });
        
        var url = "{{route('reportstatic')}}";
        var onDraw = function() {
        };

        var options = [
            { "data" : "created_at"},
            { "data" : "created_at",
                render:function(data, type, full, meta){
                    return full.username+" ("+full.credit_by+")";
                }
            },
            { "data" : "product"},
            { "data" : "txnid"},
            { "data" : "trans_type"},
            { "data" : "balance"},
            { "data" : "created_at",
                render:function(data, type, full, meta){
                    var amount = 0;

                    if(full.product != "qrcode"){
                        amount += full.amount;
                    }

                    if(full.trans_type == "credit"){
                        if(full.product == "payout"){
                            amount += full.charge + full.gst;
                        }else{
                            amount -= full.charge + full.gst;
                        }
                    }else{
                        if(full.product == "payout"){
                            amount += full.charge + full.gst;
                        }else{
                            amount -= full.charge + full.gst;
                        }
                    }
                    
                    return amount.toFixed(2);
                }
            },
            { "data" : "closing"}
        ];

        var DT = datatableSetup(url, options, onDraw);
    });

    function SETTITLE(type) {
        $('[name="dataType"]').val(type);
        $(".titleName").text(type);
        $('#datatable').dataTable().api().ajax.reload(null, false);
    }
</script>
@endpush