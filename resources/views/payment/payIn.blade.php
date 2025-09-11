<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>PayIn</title>
  <link rel="stylesheet" href="{{ asset('css/payin.css') }}">
</head>
<body>
  <h1>PayIn-Merchant</h1>

  <form id="payForm">
    <label>Customer name
      <input type="text" name="customer_name" required>
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

  <script src="{{ asset('js/payment/payin.js') }}"></script>
</body>
</html>
