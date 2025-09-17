@extends('layouts.app')
@section('title', 'Payment')
@section('content')
<div class="container-fluid mt-4">

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">PayIn - Merchant</h5>
                </div>
                <div class="card-body">
                    <form id="payForm" class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Amount (INR)</label>
                            <input type="number" name="amount" step="0.01" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="test@example.com" class="form-control">
                        </div>
                        <div class="col-12 d-flex gap-2 mt-3">
                            <button class="btn btn-primary" type="submit">Create Payment</button>
                            <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Result -->
        <div class="col-lg-6">
            <div id="result" class="card shadow-sm border-0 d-none">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">Payment Created</h6>
                </div>
                <div class="card-body">
                    <p><strong>TXNID:</strong> <span id="txnid" class="text-primary"></span></p>

                    <!-- UPI Link -->
                    <div class="mb-3 p-3 border rounded bg-light">
                        <strong>UPI Link:</strong>
                        <p id="upiLinkText" class="fw-bold text-dark"></p>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" type="button" onclick="copyLink()">Copy</button>
                            <button class="btn btn-sm btn-outline-success" type="button" onclick="downloadLink()">Download</button>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="mb-3">
                        <p class="mb-1">Or scan QR:</p>
                        <img id="qr" class="img-fluid border p-2 rounded" style="max-width:200px;" src="" alt="QR Code"/>
                        <br>
                        <button class="btn btn-sm btn-outline-dark mt-2" type="button" onclick="downloadQR()">Download QR</button>
                    </div>

                    <!-- Status -->
                    <div id="status" class="alert alert-secondary mb-0"><em>Waiting for status...</em></div>
                </div>
            </div>
        </div>
    </div>

</div>

 
<script>
    const form = document.getElementById('payForm');
    const result = document.getElementById('result');
    const txEl = document.getElementById('txnid');
    const qrImg = document.getElementById('qr');
    const statusEl = document.getElementById('status');
    const upiLinkText = document.getElementById('upiLinkText');
    let pollInterval;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        statusEl.innerHTML = '<em>Creating payment...</em>';

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        try {
            const res = await fetch('/payin/intent', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (!res.ok || data.error) {
                alert('Error creating payment: ' + (data.message || JSON.stringify(data)));
                statusEl.innerText = 'Error: ' + (data.message || JSON.stringify(data));
                return;
            }

            const intent = data.intent_url || data.upi_link;
            const txnid = data.txnid;

            if (!intent || !txnid) {
                alert('Invalid response from server. Check console.');
                console.log('Response:', data);
                return;
            }

            result.classList.remove('d-none');
            txEl.innerText = txnid;

            upiLinkText.innerText = intent;
            qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(intent);

            pollStatus(txnid);

        } catch (err) {
            console.error(err);
            alert('Request failed: ' + err);
        }
    });

    function copyLink() {
        const text = upiLinkText.innerText;
        navigator.clipboard.writeText(text).then(() => {
            alert("UPI Link copied to clipboard!");
        }).catch(err => {
            console.error("Copy failed", err);
        });
    }

    function downloadLink() {
        const text = upiLinkText.innerText;
        const blob = new Blob([text], { type: "text/plain" });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = "upi-payment-link.txt";
        link.click();
    }

    function downloadQR() {
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");
        const img = new Image();
        img.crossOrigin = "anonymous";
        img.src = qrImg.src;

        img.onload = function() {
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            const url = canvas.toDataURL("image/png");

            const a = document.createElement("a");
            a.href = url;
            a.download = "upi-payment-qr.png";
            a.click();
        };
    }

    async function pollStatus(txnid) {
        if (pollInterval) clearInterval(pollInterval);
        statusEl.innerHTML = '<em>Polling status...</em>';

        pollInterval = setInterval(async () => {
            try {
                const res = await fetch('/payin/status/' + encodeURIComponent(txnid));
                const json = await res.json();

                let details = json.payload?.details ?? json.payload ?? json.details ?? null;
                const state = details?.status ?? null;

                statusEl.innerText = 'Status: ' + (state ?? JSON.stringify(json));
                if (state && !['Initiated','Pending'].includes(state)) {
                    clearInterval(pollInterval);
                    statusEl.innerText = 'Final Status: ' + state;
                }

            } catch (err) {
                console.error('poll err', err);
            }
        }, 5000);
    }
</script>
 

@endsection
