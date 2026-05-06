<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Notification</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background-color: #0D2B70;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .email-body {
            padding: 30px;
            color: #333333;
            line-height: 1.6;
        }

        .email-body h2 {
            color: #0D2B70;
            font-size: 20px;
            margin-top: 0;
        }

        .exam-details {
            background-color: #f8f9fa;
            border-left: 4px solid #0D2B70;
            padding: 15px;
            margin: 20px 0;
        }

        .exam-details p {
            margin: 8px 0;
        }

        .exam-details strong {
            color: #0D2B70;
        }

        .cta-button {
            display: inline-block;
            background-color: #0D2B70;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }

        .cta-button:hover {
            background-color: #0a1f4d;
        }

        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }

        .divider {
            border-top: 1px solid #e0e0e0;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <?php
        $logoPath = public_path('images/dilg_logo.png');
        $logoSrc = asset('images/dilg_logo.png');

        if (isset($message) && is_object($message) && method_exists($message, 'embed') && is_file($logoPath)) {
            try {
                $logoSrc = $message->embed($logoPath);
            } catch (\Throwable $e) {
                $logoSrc = asset('images/dilg_logo.png');
            }
        }
    ?>
    <div class="email-container">
        <div class="email-header">            <img src="<?php echo e($logoSrc); ?>" alt="DILG Logo" style="width:64px;height:64px;object-fit:contain;display:block;margin:0 auto 12px auto;border-radius:4px;">            <h1>📝 Exam Notification</h1>
        </div>

        <div class="email-body">
            <h2>Dear <?php echo e($user->name ?? 'Applicant'); ?>,</h2>

            <p>We are pleased to inform you that the exam for the position of <strong><?php echo e($vacancy->position_title ?? 'Position'); ?></strong>
                (Vacancy ID: <?php echo e($vacancy->vacancy_id ?? 'N/A'); ?>) has been scheduled.</p>

            <div class="exam-details">
                <p><strong>📅 Date:</strong>
                    <?php echo e(isset($exam->date) && $exam->date ? \Carbon\Carbon::parse($exam->date)->format('F d, Y') : 'TBD'); ?>

                </p>
                <p><strong>🕐 Time:</strong>
                    <?php echo e(isset($exam->time) && $exam->time ? \Carbon\Carbon::parse($exam->time)->format('h:i A') : 'TBD'); ?>

                </p>
                <p><strong>📍 Venue:</strong> <?php echo e($exam->place ?? 'TBD'); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($exam->message) && $exam->message): ?>
                    <p><strong>✉️ Message:</strong> <?php echo e($exam->message); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <!-- <p><strong>⏱️ Duration:</strong> <?php echo e($exam->duration ?? 'TBD'); ?> minutes</p> -->
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($examLink)): ?>
            <p>Please click the button below to access the exam lobby when it's time to take your exam:</p>

            <!-- lalagay lang ito kapag exam day na -->
            <div style="text-align: center;">
                <a href="<?php echo e($examLink); ?>" class="cta-button">Go to Exam Lobby</a>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($attendancePromptLink)): ?>
                <p style="text-align: center; margin-top: 20px;">Arm whether you can attend the examination by
                    clicking the button below:</p>
                <span style="text-align: center; display: block; margin-bottom: 12px; font-size: 12px; color: #ff1414; font-weight: bold;">
                    NOTE: If you decide to change your attendance, you can return to this message and click the button again to update your response.
                </span>
                <div style="text-align: center;">
                    <a href="<?php echo e($attendancePromptLink); ?>" class="cta-button" style="background-color: #0D2B70; color: white;">Respond to
                        Attendance</a>
                </div>
                <p style="font-size: 12px; color: #666; text-align: center; margin-top: 12px;">
                    You will be asked to log in first if you are not yet signed in.
                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="divider"></div>

            <p><strong>Important Reminders:</strong></p>
            <ul>
                <li>Please arrive at the exam venue at least 20 minutes before the scheduled time.</li>
                <li>Bring a valid ID for verification purposes.</li>
                <li>Bring your own Laptop.</li>
                <li>The exam will be available only during the scheduled time.</li>
            </ul>

            <p>If you have any questions or concerns, please don't hesitate to contact us.</p>

            <p>Good luck with your exam!</p>

            <p><strong>Sent by:</strong> <?php echo e($senderName ?? 'DILG-CAR Recruitment Team'); ?></p>

            <p>Best regards,<br>
                <strong>DILG-CAR Recruitment Team</strong>
            </p>
        </div>

        <div class="email-footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; <?php echo e(date('Y')); ?> DILG-CAR. All rights reserved.</p>
        </div>
    </div>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\rhrmspb\resources\views/emails/exam_notification.blade.php ENDPATH**/ ?>