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

        // QR code
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

            let details = json.payload?.details ?? json.payload ?? json.json?.details ?? json.payload?.details;
            if (!details && json.details) details = json.details;

            const state = details?.status ?? (details ?? {}).status ?? (json?.payload?.details?.status ?? null);

            statusEl.innerText = 'Status: ' + (state ?? JSON.stringify(json));
            if (state && !['Initiated', 'Pending'].includes(state)) {
                clearInterval(pollInterval);
                statusEl.innerText = 'Final Status: ' + state;
            }

        } catch (err) {
            console.error('poll err', err);
        }
    }, 5000);
}
