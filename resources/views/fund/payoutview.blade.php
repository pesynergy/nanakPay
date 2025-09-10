@extends('layouts.app')

@section('title', "Transaction History")
@section('pagetitle', "Transaction History")

@php
    $table = "yes";
    $export = "payout";
    $status['type'] = "Report";
    $status['data'] = [
        "success" => "Success",
        "pending" => "Pending",
        "reversed" => "Reversed",
        "refunded" => "Refunded",
    ];
@endphp

@section('content')
<div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Payout History</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="example4" class="display" style="min-width: 845px">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User Details</th>
                                <th>Transaction Details</th>
                                <th>Reference Details</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        @if (Myhelper::hasRole('admin'))
        <!-- Transfer Modal -->
        <div id="transferModal" class="modal fade" role="dialog" aria-labelledby="transferModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="transferModalLabel">Fund Request From <span class="payeename text-capitalize"></span></h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
        
                    <form id="transferForm" method="post" action="{{route('statementUpdate')}}">
                        @csrf
                        <input type="hidden" name="id">
                        <input type="hidden" name="actiontype" value="bankpayout">
        
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="status">Action Type</label>
                                <select class="form-control select" name="status" id="status" required>
                                    <option value="">Select Action Type</option>
                                    <option value="pending">Pending</option>
                                    <option value="success">Success</option>
                                    <option value="complete">Complete</option>
                                    <option value="failed">Failed</option>
                                    <option value="reversed">Reversed</option>
                                    <option value="chargeback">Charge Back</option>
                                </select>
                            </div>
        
                            <div class="form-group">
                                <label for="txnid">Transaction ID</label>
                                <input type="text" name="txnid" class="form-control" id="txnid" required placeholder="Enter Transaction ID">
                            </div>
        
                            <div class="form-group">
                                <label for="payid">Pay ID</label>
                                <input type="text" name="payid" class="form-control" id="payid" required placeholder="Enter Pay ID">
                            </div>
        
                            <div class="form-group">
                                <label for="refno">Ref No</label>
                                <input type="text" name="refno" class="form-control" id="refno" required placeholder="Enter Reference Number">
                            </div>
                        </div>
        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger light" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('style')
<!-- Add any required CSS here -->
@endpush

@push('script')

<script type="text/javascript">
$(document).ready(function () {
    // Initialize DataTable
    $('#example4').DataTable().destroy();
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
                d.type = "payoutview";
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
              "render": function(data, type, full, meta) {
                return `<div>
                        <span class=''>${full.apiname}</span><br>
                        <span class='text-inverse m-l-10'>SN : <b>${full.id}</b></span>
                        <div class="clearfix"></div>
                    </div><span style='font-size:13px' class="pull=right">${full.created_at}</span>`;
              }
            },
            { "data": "bank",
              "render": function(data, type, full, meta) {
                return `${full.username}<br>${full.usermobile}<br>${full.user_id}`;
              }
            },
            { "data": "bank",
              "render": function(data, type, full, meta) {
                return `Name : ${full.description}<br>Account : ${full.number}<br>Bank : ${full.option3}<br>Ifsc: ${full.option2}`;
              }
            },
            { "data": "bank",
              "render": function(data, type, full, meta) {
                if (full.status == "success" || full.status == "pending") {
                    return `Reference / Utr: ${full.refno}<br>Txn Id : ${full.txnid}<br>Pay Id : ${full.payid}`;
                } else {
                    return `Remark: ${full.remark}<br>Txn Id : ${full.txnid}<br>Pay Id : ${full.payid}`;
                }
              }
            },
            { "data": "bank",
              "render": function(data, type, full, meta) {
                return `Amount : ${full.amount}<br>Charge : ${full.charge}<br>Gst : ${full.gst}`;
              }
            },
            { "data": "status",
              "render": function(data, type, full, meta) {
                var btn;
                if (full.status == "success" || full.status == 'accept') {
                    btn = `<span class="label label-success text-uppercase"><b>${full.status}</b></span>`;
                } else if (full.status == 'pending') {
                    btn = `<span class="label label-warning text-uppercase"><b>${full.status}</b></span>`;
                } else if (full.status == 'complete') {
                    btn = `<span class="label label-primary text-uppercase"><b>${full.status}</b></span>`;
                } else if (full.status == 'chargeback') {
                    btn = `<span class="label label-info text-uppercase"><b>${full.status}</b></span>`;
                } else {
                    btn = `<span class="label label-danger text-uppercase"><b>${full.status}</b></span>`;
                }
                @if(Myhelper::hasRole('admin'))
                    btn += `<li><a href="javascript:void(0)" onclick="editReport(${full.id}, '${full.refno}', '${full.txnid}', '${full.payid}', '${full.remark}', '${full.status}', '${full.product}')"><i class="icon-pencil5"></i> Edit</a></li>`;
                @endif
                return btn;
              }
            }
        ],
        "drawCallback": function() {
            // Additional callback logic if needed
        }
    });

    $('#transferForm').submit(function(e) {
         notify('Internale server error', 'danger');
        e.preventDefault();
        $.ajax({
            url: "{{route('statementUpdate')}}",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                 alert('iff');
                if (response.status === "success") {
                    notify('Fund request successfully updated', 'success');
                    $('#transferModal').modal('hide');
                    $('#example4').DataTable().ajax.reload();
                } else {
                    alert(response.message);
                    notify(response.status, 'danger');
                }
            },
            error: function(xhr) {
                alert('error');
                let errors = xhr.responseJSON.errors;
                 notify('Internale server error', 'danger');
                $.each(errors, function(key, value) {
                    notify(value[0], 'danger');
                });
            }
        });
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

// Function to populate and show the transfer modal
// function editReport(id, refno, txnid, payid, remark, status, product) {
//     $('#transferForm').find('input[name="id"]').val(id);
//     $('#transferForm').find('input[name="refno"]').val(refno);
//     $('#transferForm').find('input[name="txnid"]').val(txnid);
//     $('#transferForm').find('input[name="payid"]').val(payid);
//     $('#transferForm').find('select[name="status"]').val(status);
//     $('#transferModal').modal('show');
// }
</script>
@endpush
