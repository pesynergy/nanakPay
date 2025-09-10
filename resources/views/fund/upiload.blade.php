@extends('layouts.app')
@section('title', "QR Intent Load Request")
@section('pagetitle', "QR Intent Load Request")

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

<div class="">
    <!-- row -->
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">QR Intent Generation</h4>
            </div>
            <div class="card-body">
                <input type="text" name="amount" id="amount" class="form-control" placeholder="Enter Amount" style="color:black">
                <div class="heading-elements">
                    <button type="button" class="btn btn-primary mt-3" onclick="generateQr()">Generate QR Intent</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title text-center">Scan & Pay</h4>
            </div>
            <div class="card-body text-center">
                <div class="qrimage"></div>
                <button type="button" class="btn btn-primary mt-3" onclick="payNow()">Pay Now</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('style')
@endpush

@push('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $(".paynow").click(function() {
            var code = $(".code").text();
            if (code) {
                window.location.href = code;
            } else {
                notify("No QR code available to pay", 'warning');
            }
        });
    });

    function generateQr() {
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
                    "type": "qrcode",
                    "amount": amount
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Please Wait!',
                        text: 'Generating QR Code...',
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(data) {
                    $(".qrimage").html(""); // Clear previous QR code
                    Swal.close();
                    if (data.statuscode === "TXN") {
                        console.log("QR Code Data:", data.code); // Debug: Check the data received
                        $(".qrimage").qrcode({
                            width: 250,
                            height: 250,
                            text: data.code
                        });
                        $(".code").text(data.code); // Store the code for the Pay Now button
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

    function payNow() {
        var code = $(".code").text();
        if (code) {
            window.location.href = code;
        } else {
            notify("No QR code available to pay", 'warning');
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
