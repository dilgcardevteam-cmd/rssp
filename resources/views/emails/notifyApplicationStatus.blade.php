<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Application Status Update</title>
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

    .title-text h2 {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      color: #002c63;
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

    .status-box {
      margin: 20px 0;
      background-color: #f2f2f2;
      border: 2px dashed #cfd9e0;
      border-radius: 8px;
      padding: 15px;
    }

    .status-box h3 {
      margin-top: 0;
      color: #002c63;
      font-weight: 700;
      font-size: 16px;
    }

    .status-link {
      display: block;
      margin: 12px 0;
      text-align: center;
      text-decoration: none;
      padding: 12px;
      background-color: #002c76;
      color: #ffffff !important;
      font-weight: 600;
      border-radius: 8px;
      font-size: 15px;
    }

    .note {
      font-size: 13px;
      color: #4b5563;
      margin-top: 10px;
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

    <div class="banner">Application Status Update</div>

    <div class="content">
      <p>Hello {{ $applicant_name ?? 'Applicant' }},</p>

      <p>
        Your application for <strong>{{ $position_title ?? '[Position Title]' }}</strong> has been updated.
      </p>

      <div class="status-box">
        <h3>Summary of Changes</h3>
        <ul style="padding-left: 20px; margin: 0;">
          <li><strong>Date of Change:</strong> {{ $date ?? '[Date not provided]' }}</li>
          <li><strong>Status:</strong> {{ $status ?? '-' }}</li>
          <li><strong>Admin:</strong> {{ $admin_name ?? 'Admin' }}</li>
        </ul>

        @if(!empty($changes) && is_array($changes))
          <p style="margin: 12px 0 6px 0;"><strong>Field Updates:</strong></p>
          <ul style="padding-left: 20px; margin: 0;">
            @foreach ($changes as $field => $change)
              <li>
                <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}:</strong>
                {{ $change['old'] ?? 'N/A' }} -> {{ $change['new'] ?? 'N/A' }}
              </li>
            @endforeach
          </ul>
        @endif
      </div>

      <p>To view complete details, click below:</p>

      <a href="{{ route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id]) }}" class="status-link">View My Application</a>

      <p class="note">
        If the button does not work, copy and paste this link into your browser:
        <br>
        <span style="word-break: break-all;">{{ route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id]) }}</span>
      </p>

      <p>
        For concerns, contact:
        <a href="mailto:dilgcarcloud@gmail.com">dilgcarcloud@gmail.com</a> or
        <a href="mailto:dilgcar.hr@gmail.com">dilgcar.hr@gmail.com</a>.
      </p>

      <p><strong>- DILG-CAR</strong></p>
    </div>
  </div>
</body>
</html>
