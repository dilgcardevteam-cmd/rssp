<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>OTP Verification</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background-color: #f3f8ff;
    }

    .container {
      max-width: 600px;
      margin: 30px auto;
      background: #ffffff;
      border: 1px solid #cfd9e0;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .header {
      padding: 20px 30px 10px;
      display: flex;
      align-items: center;
    }

    .logo {
      width: 60px;
      height: 60px;
      margin-right: 15px;
    }

    .title-text {
      color: #002c63;
    }

    .title-text h2 {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      line-height: 1.3;
    }

    .banner {
      background-color: #002c76;
      color: #ffffff;
      padding: 15px 30px;
      margin: 15px 15px 0;
      font-size: 18px;
      font-weight: 700;
      border-radius: 16px;
    }

    .content {
      padding: 0 30px 20px 30px;
      color: #1a202c;
      font-size: 15px;
      line-height: 1.6;
    }

    .otp-box {
      margin: 20px 0;
      background-color: #f2f2f2;
      border: 2px dashed #002c63;
      text-align: center;
      font-size: 28px;
      font-weight: 700;
      padding: 20px;
      color: #002c63;
      letter-spacing: 4px;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  @php
    $logoPath = public_path('images/dilg_logo.png');
    $logoSrc = asset('images/dilg_logo.png');

    if (isset($message) && is_object($message) && method_exists($message, 'embed') && is_file($logoPath)) {
        try {
            $logoSrc = $message->embed($logoPath);
        } catch (\Throwable $e) {
            $logoSrc = asset('images/dilg_logo.png');
        }
    }
  @endphp

  <div class="container">
    <div class="header">
      <img class="logo" src="{{ $logoSrc }}" alt="DILG Logo" style="object-fit: contain;">
      <div class="title-text">
        <h2>DILG - CAR<br>Recruitment Selection and Placement Portal</h2>
      </div>
    </div>

    <div class="banner">One-Time Password (OTP)</div>

    <div class="content">
      <p>Hello!</p>
      <p>
        You are registering a new account on <strong>DILG - CAR Recruitment Selection and Placement Portal</strong>.
        To verify your account, use the OTP below:
      </p>

      <div class="otp-box">{{ $otp }}</div>

      <p>This code will expire in <strong>5 minutes</strong>.</p>
      <p>If you requested a resend, you may receive multiple messages. Use the most recent code from this registration attempt.</p>
      <p>Do not share this code with anyone.</p>
      <p>If you did not request this code, just ignore this email. Thank you.</p>
      <p><strong>- DILG-CAR</strong></p>
    </div>
  </div>
</body>
</html>
