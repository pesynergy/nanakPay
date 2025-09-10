@extends('layouts.app')
@section('title', "Transaction History")
@section('pagetitle',  "Transaction History")

@php
    $table = "yes";
    $agentfilter = "yes";
    $export = "recharge";

    $billers = App\Model\Provider::whereIn('type', ['mobile', 'dth'])->get(['id', 'name']);
    foreach ($billers as $item){
        $product['data'][$item->id] = $item->name;
    }
    $product['type'] = "Operator";

    $status['type'] = "Report";
    $status['data'] = [
        "success"  => "Success",
        "pending"  => "Pending",
        "reversed" => "Reversed",
        "refunded" => "Refunded",
        "chargeback"  => "Chargeback"
    ];
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Transaction History</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="display">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Partner</th>
                                        <th>Txn Id</th>
                                        <th>Merchant Id</th>
                                        <th>Reference</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Charge</th>
                                        <th>Gst</th>
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
    </div>
@endsection

@push('script')
    <script type="text/javascript">
        var DT;
        $(document).ready(function () {
            $('[name="dataType"]').val("{{$type}}");
            $('#print').click(function(){
                $('#receipt').find('.modal-body').print();
            });
            
            @if(isset($id) && $id != 0)
                $('form#searchForm').find('[name="agent"]').val("{{$id}}");
            @endif

            var url = "{{route("reportstatic")}}";
            var onDraw = function() {
                $('.print').click(function(event) {
                    var data = DT.row($(this).parent().parent().parent().parent().parent()).data();
                    $.each(data, function(index, values) {
                        $("."+index).text(values);
                    });

                    if(data['product'] == "dmt"){
                        $('address.dmt').show();
                        $('address.notdmt').hide();
                    }else{
                        $('address.notdmt').show();
                        $('address.dmt').hide();
                    }
                    $('#receipt').modal();
                });
            };
            var options = [

                { "data" : "created_at"},
                { "data" : "username",
                    render:function(data, type, full, meta){
                        return full.username+" ("+full.user_id+")";
                    }
                },
                { "data" : "txnid"},
                { "data" : "id",
                    render:function(data, type, full, meta){
                        if(full.product == "payout"){
                            return full.apitxnid;
                        }else{
                            return full.option1;
                        }
                    }
                },
                { "data" : "refno"},
                { "data" : "trans_type"},
                { "data" : "amount"},
                { "data" : "charge"},
                { "data" : "gst"},
                { "data" : "status",
                    render:function(data, type, full, meta){
                        if(full.status == "success"){
                            var out = `<span class="label label-success">Success</span>`;
                        }else if(full.status == "pending"){
                            var out = `<span class="label label-warning">Pending</span>`;
                        }else if(full.status == "reversed"){
                            var out = `<span class="label bg-slate">Failed</span>`;
                        }else if(full.status == "processing"){
                            var out = `<span class="label label-primary">Processing</span>`;
                        }else{
                            var out = `<span class="label label-danger">`+full.status+`</span>`;
                        }
                        
                        var menu = `<li class="dropdown-header">Action</li>`;
                        @if(\Myhelper::hasRole("admin") && $type == "payout")
                            if(full.option7 == "failed"){
                                menu += `<li><a href="javascript:void(0)" onclick="trasferTopay(`+full.id+`)"><i class="icon-cogs"></i> Transfer To Api</a></li>`;
                            }
                        @endif

                        @if(in_array($type , ["payin", "payout"]) && Myhelper::can(["payout_statement_edit", "upi_statement_edit"]))
                            if(full.status == "success" || full.status == "pending" || full.status == "failed" || full.status == "initiated" || full.status == "accept" || full.status == "processing"){
                                menu += `<li><a href="javascript:void(0)" onclick="editReport(`+full.id+`,'`+full.refno+`','`+full.txnid+`','`+full.payid+`','`+full.remark+`', '`+full.status+`', '`+full.product+`')"><i class="fas fa-pencil-alt" style="padding-right:5px;"></i> Edit</a></li>`;
                            }
                        @endif
                        
                        out +=  `<ul class="icons-list">
                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>

                                        <ul class="dropdown-menu dropdown-menu-right">
                                            `+menu+`
                                        </ul>
                                    </li>
                                </ul>`;

                        return out;
                    }
                }
            ];

            DT = datatableSetup(url, options, onDraw);
        });

        function trasferTopay(id) {
            if(id != ''){
                $.ajax({
                    url: '{{route("fundtransaction")}}',
                    type: 'post',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data : {"type" : "apitransfer", "id" : id},
                    beforeSend : function(){
                        swal({
                            title: 'Wait!',
                            text: 'Please wait, we are working on your request',
                            onOpen: () => {
                                swal.showLoading()
                            },
                            allowOutsideClick: () => !swal.isLoading()
                        });
                    }
                })
                .success(function(data) {
                    swal.close();
                    if(data.status == "success"){
                        $('#datatable').dataTable().api().ajax.reload(null, false);
                        notify("Fund Transfer Successfully" , 'success');
                    }else{
                        notify(data.status , 'warning');
                    }
                })
                .fail(function() {
                    swal.close();
                    notify('Somthing went wrong', 'warning');
                });
            }
        }
    </script>
@endpush