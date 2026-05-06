<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Applicant Record Deletion Reminder</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;padding:32px 20px;">
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;">
            <div style="padding:28px 28px 20px;background:linear-gradient(135deg,#eff6ff 0%,#ffffff 100%);border-bottom:1px solid #e2e8f0;">
                <p style="margin:0 0 8px;font-size:12px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#64748b;">DILG-CAR Recruitment Portal</p>
                <h1 style="margin:0;font-size:26px;line-height:1.2;color:#0d2b70;">Applicant Record Deletion Reminder</h1>
            </div>
            <div style="padding:28px;">
                <p style="margin:0 0 16px;font-size:15px;line-height:1.7;">Dear {{ $applicantName }},</p>
                <p style="margin:0 0 16px;font-size:15px;line-height:1.7;">This is a reminder that your applicant record ({{ $applicantCode }}) is still scheduled for deletion.</p>
                <div style="margin:20px 0;padding:18px 20px;border:1px solid #fde68a;border-radius:16px;background:#fffbeb;">
                    <p style="margin:0 0 8px;font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#b45309;">Deletion Deadline</p>
                    <p style="margin:0;font-size:18px;font-weight:700;color:#92400e;">{{ $deadlineText }}</p>
                </div>
                <p style="margin:0;font-size:15px;line-height:1.7;">This reminder is being sent two days before the scheduled deletion date.</p>
            </div>
        </div>
    </div>
</body>
</html>
