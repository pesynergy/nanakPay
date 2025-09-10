@extends('layouts.app')
@section('title', "UPI Intent Load Request")
@section('pagetitle',  "UPI Intent Load Request")

@php
    $table = "yes";
    $status['type'] = "Fund";
    $status['data'] = [
        "success" => "Success",
        "pending" => "Pending",
        "failed" => "Failed",
        "approved" => "Approved",
        "rejected" => "Rejected",
    ];

    $search = "hide";
@endphp

@section('content')
	<!-- row -->
	<div class="container-fluid">
		<div class="row">
            <div class="col-sm-12">
                <div class="card" style="height: auto;">
                    <div class="card-header">
                        <h4 class="card-title">UPI Intent Generation</h4>
                    </div>
                    <div class="card-body">
                        <input type="text" name="amount" id="amount" class="form-control" placeholder="Enter Amount" style="color:black">
                        <div class="heading-elements">
                            <button type="button" class="btn btn-primary mt-3" onclick="generateIntent()">Generate UPI Intent</button>
                        </div>
                    </div>
                </div>
                <div class="card" style="height: auto;">
                    <div class="card-header">
                        <h4 class="card-title text-center">Link for Payment</h4>
                    </div>
                    <div class="card-body">
                        <div class="upilink"></div>
                        <a href="#" class="btn btn-primary mt-3 code">Pay Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    function generateIntent() {
        var amount = $("#amount").val();

        if (amount) {
            $.ajax({
                url: '{{ route("fundtransaction") }}',
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    "type": "upiintent",
                    "amount": amount
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Please Wait!',
                        text: 'Generating UPI Intent URL...',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(data) {
                    $(".upilink").html(""); // Clear previous QR code
                    Swal.close();
                    if (data.statuscode === "TXN") {
                        console.log("UPI Intent Data:", data.code); // Debug: Check the data received
                        $(".upilink").text(data.code);
                        $(".code").attr("href", data.code); // Store the code for the Pay Now button
                    } else {
                        notify(data.message, 'warning');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    showError(xhr);
                }
            });
        } else {
            notify("Amount is required", 'warning');
        }
    }

    function notify(message, type) {
        // Your notification function (use whatever library you have)
        console.log(type.toUpperCase() + ": " + message);
    }

    function showError(xhr) {
        // Your error handling function (use whatever library you have)
        console.error("Error:", xhr.responseText);
    }
</script>
@endpush