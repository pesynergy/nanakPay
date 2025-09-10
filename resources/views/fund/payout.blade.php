@extends('layouts.app')
@section('title', "Transaction History")
@section('pagetitle', "Transaction History")

@php
$table = "yes";
$export = "payout";
$search = "hide";
$status['type'] = "Report";
$status['data'] = [
    "success" => "Success",
    "pending" => "Pending",
    "reversed" => "Reversed",
    "refunded" => "Refunded",
];
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-sm-12">
                <div class="card" style="height: auto;">
                    <div class="card-header">
                        <h4 class="card-title">Payout Statement</h4>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <!--    @csrf-->
                            <form id="fundRequestForm" action="{{route('fundtransaction')}}" method="post">
                                {{ csrf_field() }}
                                <input type="hidden" name="type" value="bank">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="mb-3">
                                            <label>Account Name</label>
                                            <input type="text" class="form-control" name="accountname" placeholder="Enter Value" value="{{Auth::user()->accountname}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mb-3">
                                            <label>Account Number</label>
                                            <input type="text" class="form-control" name="account" placeholder="Enter Value" value="{{Auth::user()->account}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mb-3">
                                            <label>Ifsc Code</label>
                                            <input type="text" class="form-control" name="ifsc" placeholder="Enter Value" value="{{Auth::user()->ifsc}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="mb-3">
                                            <label>Bank</label>
                                            <input type="text" class="form-control" name="bank" placeholder="Enter Value" value="{{Auth::user()->bank}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mb-3">
                                            <label>Amount</label>
                                            <input type="number" class="form-control" name="amount" placeholder="Enter Value" required="">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card mb-2" style="height: auto;">
                    <div class="card-header">
                        <h4 class="card-title">Search</h4>
                        <form id="searchForm">
                            <button type="submit" class="btn bg-slate btn-xs btn-labeled legitRipple btn-primary" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Searching"><b><i class="icon-search4"></i></b> Search</button>
                            <button type="button" class="btn btn-warning btn-xs btn-labeled legitRipple" id="formReset" data-loading-text="<b><i class='fa fa-spin fa-spinner'></i></b> Refreshing"><b><i class="icon-rotate-ccw3"></i></b> Refresh</button>
                            <button type="button" class="btn btn-primary btn-xs btn-labeled legitRipple {{ isset($export) ? '' : 'hide' }}" product="{{ $export ?? '' }}" id="reportExport"><b><i class="icon-cloud-download2"></i></b> Export</button>
                    </div>
                    <div class="card-body">
                        @if(isset($mystatus))
                            <input type="hidden" name="status" value="{{$mystatus}}">
                        @endif
                        <div class="row">
                            <div class="form-group col-md-2 m-b-10">
                                <input type="text" name="from_date" class="form-control mydate" placeholder="From Date">
                                </div>
                                <div class="form-group col-md-2 m-b-10">
                                    <input type="text" name="to_date" class="form-control mydate" placeholder="To Date">
                                </div>
                                <div class="form-group col-md-2 m-b-10">
                                    <input type="text" name="searchtext" class="form-control" placeholder="Search Value">
                                </div>
                                @if (Myhelper::hasRole(['admin']))
                                    <div class="form-group col-md-2 m-b-10 {{ isset($agentfilter) ? $agentfilter : ''}}">
                                        <input type="text" name="agent" class="form-control" placeholder="Agent Id / Parent Id">
                                    </div>
                                @endif
        
                                @if(isset($status))
                                <div class="form-group col-md-2">
                                    <select name="status" class="form-control select">
                                        <option value="">Select {{$status['type'] ?? ''}} Status</option>
                                        @if (isset($status['data']) && sizeOf($status['data']) > 0)
                                            @foreach ($status['data'] as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @endif
        
                                @if(isset($product))
                                <div class="form-group col-md-2">
                                    <select name="product" class="form-control select">
                                        <option value="">Select {{$product['type'] ?? ''}}</option>
                                        @if (isset($product['data']) && sizeOf($product['data']) > 0)
                                            @foreach ($product['data'] as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @endif
                            </div>
        
                        </form>
                    </div>
                </div>
                <div class="card" style="height: auto;">
                    <div class="card-header">
                        <h4 class="card-title">Payout History</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="example4" class="display" style="min-width: 845px">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Transaction Details</th>
                                        <th>Reference Details</th>
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
    </div>
</div>

<div id="confirmModal" class="modal fade" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h6 class="modal-title">Payout Confirmation</h6>
            </div>

            <div class="modal-body p-0">
                <div class="panel no-margin-bottom">
                    <div class="panel-body">
                        <div class="row text-center">
                            <div class="col-xs-4">
                                <h5 class="text-semibold no-margin count"></h5>
                                <span class="text-size-small">No Of Payout</span>
                            </div>

                            <div class="col-xs-4">
                                <h5 class="text-semibold no-margin amount"></h5>
                                <span class="text-size-small">Payout Amount</span>
                            </div>

                            <div class="col-xs-4">
                                <h5 class="text-semibold no-margin charge"></h5>
                                <span class="text-size-small">Payout Charge</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel no-margin-bottom">
                    <div class="panel-body no-padding" style="height: 450px; overflow: auto;">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Account</th>
                                    <th>Ifsc</th>
                                    <th>Mode</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody class="payoutdata">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="panel panel-default no-margin-bottom">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="md-checkbox no-margin">
                                    <input type="checkbox" name="consent" id="consent" required="">
                                    <label for="consent">Accept Consent</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer mt-20">
                <button type="button" class="btn btn-sm" data-dismiss="modal" aria-hidden="true">Close</button>
                <button type="button" class="btn  bg-slate legitRipple" id="payoutSubmit">Proceed</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script type="text/javascript">
    $(document).ready(function () {
        $('#payoutprint').click(function() {
            $('#payoutreceipt').find('.modal-body').print();
        });
        
        if ($.fn.DataTable.isDataTable('#example4')) {
            $('#example4').DataTable().destroy(); // Destroy existing DataTable
        }
    
        // Get current URL type
        const currentUrl = new URL(window.location.href);
        const type = 'payoutview';
        // const type = currentUrl.pathname.split('/').pop();

        // Initialize DataTable
        var DT = $('#example4').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('reportstatic') }}",
                "type": "POST",
                "headers": {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                "data": function(d) {
                    console.log('type',type);
                    d.type = type;
                    d.fromdate = $('[name="from_date"]').val();
                    d.todate = $('[name="to_date"]').val();
                    d.searchtext = $('[name="searchtext"]').val();
                    d.status = $('[name="status"]').val();
                    d.product = $('[name="product"]').val();
                    d.agent = $('[name="agent"]').val();
                    console.log('AJAX Data Sent:', d);
                },
                "error": function(xhr, status, error) {
                    console.log("Error:", error);
                    console.log("XHR:", xhr);
                    console.log("Status:", status);
                }
            },
            "columns": [
                { "data": "name",
                    render: function(data, type, full, meta) {
                        return `<div>
                                    <span class=''>${full.apiname}</span><br>
                                    <span class='text-inverse m-l-10'>SN : <b>${full.id}</b></span>
                                    <div class="clearfix"></div>
                                </div><span style='font-size:13px' class="pull=right">${full.created_at}</span>`;
                    }
                },
                { "data": "bank",
                    render: function(data, type, full, meta) {
                        return `Name : ${full.description}<br>Account : ${full.number}<br>Bank : ${full.option3}<br>Ifsc: ${full.option2}`;
                    }
                },
                { "data": "bank",
                    render: function(data, type, full, meta) {
                        if (full.status === "success" || full.status === "pending") {
                            return `Reference / Utr: ${full.refno}<br>Txn Id : ${full.apitxnid}<br>Pay Id : ${full.txnid}`;
                        } else {
                            return `Remark: ${full.remark}<br>Txn Id : ${full.txnid}`;
                        }
                    }
                },
                { "data": "bank",
                    render: function(data, type, full, meta) {
                        return `Amount : ${full.amount}<br>Charge : ${full.charge}<br>Gst : ${full.gst}`;
                    }
                },
                { "data": "status",
                    render: function(data, type, full, meta) {
                        let statusLabel;
                        if (full.status === "success" || full.status === "Success") {
                            statusLabel = `<span class="label label-success">${full.status}</span>`;
                        } else if (full.status === "pending") {
                            statusLabel = `<span class="label label-warning">Pending</span>`;
                        } else if (full.status === "reversed" || full.status === "refunded") {
                            statusLabel = `<span class="label bg-slate">${full.status}</span>`;
                        } else {
                            statusLabel = `<span class="label label-danger">${full.status}</span>`;
                        }
    
                        const menu = `<li class="dropdown-header">Action</li>
                                      <li><a href="javascript:void(0)" class="print"><i class="icon-info22"></i>Print Invoice</a></li>`;
                        
                        return `${statusLabel}<ul class="icons-list">
                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                            <i class="icon-menu9"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            ${menu}
                                        </ul>
                                    </li>
                                </ul>`;
                    }
                }
            ],
            "drawCallback": function() {
                // Print logic (same as in Code 1)
                $('.print').click(function(event) {
                    var data = DT.row($(this).closest('tr')).data(); // Adjusted to find the row correctly
                    console.log('Print Data:', data); // Debugging output
                    if (data) {
                        $.each(data, function(index, value) {
                            $("." + index).text(value);
                        });
                        
                        $('#payoutreceipt').modal('show');
                    } else {
                        console.error('No data found for printing.'); // Error output
                    }
                });
            }
        });
        
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
                        if(data.statuscode == "TXNS"){
                            notify("Payout Request submitted Successfully", 'success');
                            getbalance();
                            form.closest('.modal').modal('hide');
                            $('#example4').dataTable().api().ajax.reload();
                        }else{
                            notify(data.message , 'TXN Message');
                        }
                    },
                    error: function(errors) {
                        notify(errors);
                    }
                });
            }
        });
        // Reload DataTable on form submit
        $('form#searchForm').on('submit', function(e) {
            e.preventDefault();
            console.log('Form Submitted:', {
                from_date: $('[name="from_date"]').val(),
                to_date: $('[name="to_date"]').val(),
                searchtext: $('[name="searchtext"]').val(),
                status: $('[name="status"]').val(),
                product: $('[name="product"]').val(),
                agent: $('[name="agent"]').val(),
            });
            DT.ajax.reload();
        });
        
        // Automatic DataTable reload when form fields change
        $('[name="from_date"], [name="to_date"], [name="searchtext"], [name="status"], [name="product"], [name="agent"]').on('change', function() {
            DT.ajax.reload();
        });

        // Export report based on selected filters
        $('#reportExport').click(function(){
            var type = "{{$type}}";
            var fromdate = $('[name="from_date"]').val();
            var todate = $('[name="to_date"]').val();
            var agent = $('[name="agent"]').val();
            var status = $('[name="status"]').val();

            @if(isset($id) && $id != 0)
                agent = "{{$id}}";
            @endif

            window.location.href = `{{ url('export/report') }}/${type}?fromdate=${fromdate}&todate=${todate}&agent=${agent}&status=${status}`;
        });
    });
</script>
@endpush