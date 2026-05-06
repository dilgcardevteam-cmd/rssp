<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>DILG-CAR Admin Notification</title>
    <style>
        body { font-family: Arial, sans-serif; color: #0D2B70; }
        .container { max-width: 640px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 12px; }
        .title { font-weight: 700; font-size: 18px; }
        .muted { color: #6b7280; font-size: 12px; }
        .btn { display: inline-block; padding: 8px 14px; background: #0D2B70; color: #fff; border-radius: 9999px; text-decoration: none; font-weight: 700; }
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
        <div style="display:flex;align-items:center;gap:12px;padding-bottom:12px;border-bottom:1px solid #e5e7eb;margin-bottom:12px;">
            <img src="{{ $logoSrc }}" alt="DILG Logo" style="width:50px;height:50px;object-fit:contain;">
            <div>
                <div style="font-weight:700;font-size:15px;color:#0D2B70;">DILG - CAR</div>
                <div style="font-size:11px;color:#6b7280;">Recruitment Selection and Placement Portal</div>
            </div>
        </div>
        <p class="muted">Timestamp: {{ \Carbon\Carbon::parse($occurredAt)->format('M d, Y h:i A') }}</p>
        <p class="title">{{ $title }}</p>
        <p>{{ $body }}</p>
        @if($positionTitle)
        <p><strong>Position:</strong> {{ $positionTitle }} ({{ $vacancyId }})</p>
        @endif
        @if($applicantName)
        <p><strong>Applicant:</strong> {{ $applicantName }}</p>
        @endif
        <p class="muted">By: {{ $actorName }}</p>
        @if($link)
        <p><a class="btn" href="{{ $link }}">View Details</a></p>
        @endif
        <p class="muted">This is an automated email from DILG-CAR RHRMSPB.</p>
    </div>
</body>
</html>
