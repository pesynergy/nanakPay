<!DOCTYPE html>
<html>
<head>
    <title>Your OTP Code</title>
</head>
<body>
    <p>Hi {{ $data['name'] }},</p>
    <p>Your OTP code is: <strong>{{ $data['otp'] }}</strong></p>
    <p>Please use this code to complete your login.</p>
    <p>Thanks,<br>The Team</p>
</body>
</html>
