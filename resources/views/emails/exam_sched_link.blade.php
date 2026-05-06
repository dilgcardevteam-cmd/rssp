<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Examination Schedule</title>
  <!-- Google Fonts: Montserrat -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Montserrat', sans-serif;
      background-color: #F3F8FF;
    }
    .container {
      max-width: 600px;
      margin: 30px auto;
      background: #FFFFFF;
      border: 1px solid #cfd9e0;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
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
      color: #002C63;
      line-height: 1.3;
    }
    .banner {
      /* background-color: #002C76; */
      display:flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: #002C76;
      padding: 15px 30px;
      margin: 15px 15px 0px 15px;
      font-size: 18px;
      font-weight: 700;
      border-radius: 16px;
      display: flex;
      align-items: center;
    }
    .banner img {
      width: 20px;
      margin-right: 10px;
      filter: brightness(0) invert(1);
    }
    .content {
      padding: 0px 30px 15px 30px;
      color: #1a202c;
      font-size: 15px;
      text-align: justify;
      line-height: 1.6;
    }
    .exam-details {
      margin: 20px 0;
      background-color: #f2f2f2;
      border: 2px dashed #cfd9e0;
      border-radius: 8px;
      padding: 15px;
    }
    .exam-details h3 {
      margin-top: 0;
      color: #002C63;
      font-weight: 700;
      font-size: 16px;
    }
    .exam-details p {
      margin: 4px 0;
      line-height: 1.4;
    }

    .join-button {
      display: block;
      margin: 10px;
      text-align: center;
      text-decoration: none;
      padding: 12px;
      background-color: #002C76;
      color: white !important;
      font-weight: 600;
      border-radius: 8px;
      font-size: 15px;
    }

    .note {
      font-size: 13px;
      color: #718096;
      margin-top: 10px;
    }
    .footer {
      padding: 0 30px 30px;
      font-size: 13px;
      color: #2d3748;
    }
    .footer strong {
      font-weight: 700;
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
            <!-- Header -->
            <div class="header">
            <img class="logo" src="{{ $logoSrc }}" alt="DILG Logo" style="object-fit:contain;">
            <div class="title-text">
                <h2>DILG - CAR<br>Recruitment Selection and Placement Portal</h2>
            </div>
            </div>

            <!-- Banner -->
            <div class="banner">
            <!-- <img src="https://cdn-icons-png.flaticon.com/512/1827/1827392.png" alt="Schedule Icon" /> -->
                <h1>Examination Access Link</h1>
            </div>
            <!-- Content -->
            <div class="content">
            <p>Hello {{ $user->name ?? 'Applicant' }}!</p>
            <p>
                Thank you for your interest in joining our team. We are pleased to inform you that you are scheduled to take an examination as part of your application process. Please find the details below:
            </p>

            <div class="exam-details">
            <h3>{{ $vacancy->position_title ?? '[Position Title]' }}</h3>
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="font-size: 15px;">
                <tr>
                <td style="font-weight:700; color:#002C63; padding: 4px 0;">Date:</td>
                <td>{{ $exam->date ?? '[Month Day, Year]' }}</td>
                </tr>
                <tr>
                <td style="font-weight:700; color:#002C63; padding: 4px 0;">Time:</td>
                <td>{{ $exam->time ?? '[00:00 AM/PM]' }}</td>
                </tr>
                <tr>
                <td style="font-weight:700; color:#002C63; padding: 4px 0;">Venue:</td>
                <td>
                    {{ $exam->place ?? '[Office Name]' }}
                </td>
                </tr>
            </table>
            </div>
            <p>
                Please ensure that you arrive at the venue at least 15 minutes before the scheduled time. Bring a valid ID and any other required documents.
            </p>

            <p>
                The examination will be conducted in person, but you will also need to access it through the link below:
            </p>

            <a href="{{ $join_link ?? '#' }}" class="join-button">Access Exam Link</a>

            @php
              $expiryLabel = null;
              if (!empty($link_expires_at)) {
                try {
                  $expiryLabel = \Carbon\Carbon::parse($link_expires_at)->format('F d, Y h:i A');
                } catch (\Throwable $e) {
                  $expiryLabel = $link_expires_at;
                }
              }
            @endphp

            <p class="note" style="margin-top:10px;">
              Important: The link is single-use per device and remains valid until {{ $expiryLabel ?? 'the end of the scheduled exam window' }}.
            </p>

            <p class="note">
                If the button above does not work, please copy and paste this link into your browser:<br>
                <p class="note" style="word-break: break-all; margin-top: -10px;">{{ $join_link ?? '[exam_link_here]' }} </p>
            </p>

            <p>
                If you have any questions or concerns, please feel free to reply to this email.
            </p>
            <p>
                <strong>Sent by:</strong> {{ $senderName ?? 'DILG-CAR Recruitment Team' }}
            </p>
            <p>
                We look forward to seeing you. Thank you.<br>
                <strong>– DILG-CAR</strong>
            </p>
            </div>
        </div>
    </body>
</html>
