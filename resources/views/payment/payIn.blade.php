<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PayIn</title>
  <style>
    body{ font-family: system-ui, Arial; max-width:720px; margin:24px auto; padding:12px; }
    label{ display:block; margin-top:10px; }
    input{ width:100%; padding:8px; margin-top:6px; }
    .btn{ display:inline-block; padding:10px 14px; margin-top:12px; cursor:pointer; background:#2563eb; color:#fff; border-radius:6px; text-decoration:none; }
    .card{ margin-top:18px; padding:12px; border:1px solid #eee; border-radius:8px; }
    img.qr{ max-width:300px; height:auto; display:block; margin-top:10px; }
  </style>
</head>
<body>
  <h1>PayIn-Merchant</h1>

  <form id="payForm">
    <label>Customer name
      <input type="text" name="customer_name"  required>
    </label>

    <label>Amount (INR)
      <input type="number" name="amount" step="0.01" required>
    </label>

    <label>Mobile
      <input type="text" name="mobile">
    </label>

    <label>Email
      <input type="email" name="email" value="test@example.com">
    </label>

    <button class="btn" type="submit">Create Payment</button>
   <button type="button" onclick="history.back()" class="btn">Back</button>

  </form>

  <div id="result" class="card" style="display:none;">
    <h3>Payment Created</h3>
    <p><strong>TXNID:</strong> <span id="txnid"></span></p>
    <p><a id="openLink" class="btn" target="_blank">Open in UPI app (mobile)</a></p>
    <p>Or scan QR from your phone:</p>
    <img id="qr" class="qr" src="" alt="qr"/>
    <div id="status" style="margin-top:12px"><em>Waiting for status...</em></div>
  </div>

  <script>
    const form = document.getElementById('payForm');
    const result = document.getElementById('result');
    const txEl = document.getElementById('txnid');
    const openLink = document.getElementById('openLink');
    const qrImg = document.getElementById('qr');
    const statusEl = document.getElementById('status');
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

        const intent = data.details?.intent_url;
        const txnid = data.details?.txnid;
        if (!intent || !txnid) {
          alert('Invalid response from PayIn. Check console.');
        
          return;
        }

        // show UI
        result.style.display = 'block';
        txEl.innerText = txnid;
        openLink.href = intent;
        openLink.innerText = 'Open in UPI app (mobile)';

        // QR code (use a public QR service for quick testing)
        qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(intent);

        // start polling status
        pollStatus(txnid);

      } catch (err) {
        console.error(err);
        alert('Request failed: ' + err);
      }
    });

    async function pollStatus(txnid) {
      if (pollInterval) clearInterval(pollInterval);
      statusEl.innerHTML = '<em>Polling status...</em>';

      pollInterval = setInterval(async () => {
        try {
          const res = await fetch('/payin/status/' + encodeURIComponent(txnid));
          const json = await res.json();

          // payin status usually in json.payload.details.status or json.payload.details
          let details = json.payload?.details ?? json.payload ?? json.json?.details ?? json.payload?.details;
          // fallback: if the API returns direct structure
          if (!details && json.details) details = json.details;

          const state = details?.status ?? (details ?? {}).status ?? (json?.payload?.details?.status ?? null);

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
</body>
</html>
