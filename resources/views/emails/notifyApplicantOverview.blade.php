<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>DILG-CAR Acknowledgement Receipt</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background: #ffffff;
      font-family: "Times New Roman", Times, serif;
      color: #111111;
    }

    .page {
      width: 100%;
      max-width: none;
      margin: 0;
      background: #ffffff;
      border: 1px solid #444444;
      padding: 18px 18px 22px;
      box-sizing: border-box;
    }

    .header-table,
    .receipt-table,
    .qs-table,
    .action-table,
    .sign-table,
    .details-table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
    }

    .header-table td {
      vertical-align: middle;
    }

    .logo-col {
      width: 70px;
      padding-right: 10px;
    }

    .office-name {
      margin: 0;
      font-size: 12px;
      letter-spacing: 0.4px;
      text-transform: uppercase;
    }

    .receipt-title {
      margin: 2px 0 0;
      font-size: 24px;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      font-weight: 700;
    }

    .intro {
      margin: 12px 0 12px;
      font-size: 13px;
      line-height: 1.35;
    }

    .line-field {
      display: inline-block;
      border-bottom: 1px solid #333333;
      min-width: 185px;
      padding: 0 3px;
      font-weight: 700;
    }

    .receipt-table,
    .qs-table,
    .action-table {
      border: 1px solid #444444;
      margin-top: 8px;
    }

    .receipt-table th,
    .receipt-table td,
    .qs-table th,
    .qs-table td,
    .action-table th,
    .action-table td {
      border: 1px solid #444444;
      padding: 6px 7px;
      font-size: 12px;
      vertical-align: top;
      white-space: normal !important;
      word-break: break-word;
      overflow-wrap: anywhere;
      height: auto !important;
      max-height: none !important;
      overflow: visible !important;
    }

    .receipt-table th,
    .qs-table th,
    .action-table th {
      font-weight: 700;
      text-align: left;
      background: #f3f3f3;
    }

    .mark-col {
      width: 74px;
      text-align: center;
      font-weight: 700;
    }

    .remarks-col {
      width: 35%;
    }

    .center {
      text-align: center;
    }

    .section-label {
      margin: 12px 0 6px;
      font-size: 12px;
    }

    .status-label {
      text-align: center;
      font-weight: 700;
      font-size: 13px;
    }

    .action-text {
      margin: 0;
      line-height: 1.35;
      font-size: 12px;
    }

    .action-text + .action-text {
      margin-top: 6px;
    }

    .details-table {
      margin-top: 8px;
    }

    .details-table td {
      font-size: 12px;
      padding: 2px 0;
      vertical-align: top;
    }

    .details-label {
      width: 165px;
      font-weight: 700;
    }

    .sign-table {
      margin-top: 14px;
    }

    .sign-table td {
      width: 50%;
      font-size: 12px;
      vertical-align: top;
      padding: 3px 6px;
    }

    .sign-line {
      border-bottom: 1px solid #333333;
      min-height: 18px;
      margin-top: 12px;
      text-align: center;
    }

    .sign-note {
      margin-top: 3px;
      font-size: 11px;
      color: #333333;
    }

    .email-actions {
      margin-top: 14px;
      border-top: 1px solid #dddddd;
      padding-top: 10px;
      font-size: 12px;
      line-height: 1.4;
    }

    .email-actions .cta-label {
      margin: 0 0 8px;
      color: #333333;
      font-size: 12px;
    }

    .email-actions .cta-button {
      display: inline-block;
      background: #0b3b87;
      color: #ffffff !important;
      text-decoration: none;
      font-weight: 700;
      font-size: 13px;
      line-height: 1;
      padding: 11px 16px;
      border-radius: 6px;
    }

    .footnote {
      margin-top: 10px;
      font-size: 11px;
      color: #333333;
    }
  </style>
</head>

<body>
  @php
    $normalizedDocuments = collect($documents ?? [])->map(function ($doc) {
      return [
        'name' => $doc['name'] ?? $doc['text'] ?? $doc['id'] ?? 'N/A',
        'status' => strtolower(trim((string) ($doc['status'] ?? ''))),
        'remarks' => trim((string) ($doc['remarks'] ?? '')),
      ];
    });

    $verifiedStatuses = ['verified', 'okay/confirmed', 'confirmed', 'approved', 'ok', 'uni'];
    $revisionStatuses = ['needs revision', 'disapproved with deficiency', 'rejected', 'ggs'];

    $hasRevisions = $normalizedDocuments->contains(function ($doc) use ($revisionStatuses) {
      return in_array($doc['status'], $revisionStatuses, true);
    });

    $allDocumentsVerified = $normalizedDocuments->count() > 0 &&
      $normalizedDocuments->every(function ($doc) use ($verifiedStatuses) {
        return in_array($doc['status'], $verifiedStatuses, true);
      });

    $qsResultNormalized = strtolower(trim((string) ($qs_result ?? '')));
    $isQualified = $qsResultNormalized === 'qualified';
    $isNeedsRevisions = $qsResultNormalized === 'needs revisions';
    $noticeMode = strtolower(trim((string) ($compliance_notice_mode ?? 'default')));
    $isFinalWarning = $noticeMode === 'final_warning';
    $isFinalDisqualified = $noticeMode === 'disqualified_final';
    $isManualRejected = !$isFinalDisqualified && !$isQualified && !$isNeedsRevisions;
    $showActionRequirements = !$isFinalDisqualified && $isNeedsRevisions;
    $documentSubmissionStatus = ($isFinalDisqualified || $isManualRejected)
      ? 'NOT QUALIFIED'
      : ($isQualified ? 'COMPLETE' : ($showActionRequirements ? 'INCOMPLETE' : 'COMPLETE'));

    $formatQsValue = function ($value) {
      $normalized = strtolower(trim((string) $value));
      if (in_array($normalized, ['yes', 'qualified', 'pass', 'passed'], true)) {
        return 'YES';
      }
      if (in_array($normalized, ['na', 'n/a', 'not applicable'], true)) {
        return 'N/A';
      }
      return 'NO';
    };

    $qsEducationValue = $formatQsValue($qs_education ?? 'no');
    $qsEligibilityValue = $formatQsValue($qs_eligibility ?? 'no');
    $qsExperienceValue = $formatQsValue($qs_experience ?? 'no');
    $qsTrainingValue = $formatQsValue($qs_training ?? 'no');
    $overallQsMark = $isQualified ? 'YES (&#10003;)' : 'NO (&#10005;)';

    $qsLackingLabels = [];
    if ($qsEducationValue === 'NO') {
      $qsLackingLabels[] = 'Education';
    }
    if ($qsEligibilityValue === 'NO') {
      $qsLackingLabels[] = 'Eligibility';
    }
    if ($qsExperienceValue === 'NO') {
      $qsLackingLabels[] = 'Experience';
    }
    if ($qsTrainingValue === 'NO') {
      $qsLackingLabels[] = 'Training';
    }

    $displayDeadline = !empty($deadline) && $deadline !== 'No deadline set' ? $deadline : null;
    $displayRemarks = trim((string) ($application_remarks ?? ''));

    $sortedDocuments = $normalizedDocuments
      ->filter(function ($doc) {
        // Only include documents the applicant actually uploaded (exclude not submitted / empty)
        return $doc['status'] !== '' && $doc['status'] !== 'not submitted';
      })
      ->sortBy(function ($doc) {
        return strtolower($doc['name']);
      })->values();

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

  <div class="page">
    <table class="header-table" role="presentation">
      <tr>
        <td class="logo-col">
          <img
            src="{{ $logoSrc }}"
            alt="DILG Logo"
            style="width:62px;height:62px;display:block;border-radius:50%;border:3px solid #FCD116;background-color:#002C76;"
          >
        </td>
        <td>
          <p class="office-name">DILG - Cordillera Administrative Region</p>
          <p class="receipt-title">Acknowledgement Receipt</p>
        </td>
      </tr>
    </table>

    <p class="intro">
      This is to acknowledge receipt of the application documents of Mr./Ms.
      <span class="line-field">{{ $applicant_name ?? 'Applicant' }}</span>
      for the vacant
      <span class="line-field">{{ $position_title ?? 'N/A' }}</span>
      position.
    </p>

    <p class="section-label">
      Is the applicant qualified and has met the required Qualification Standard (QS) of position on:
    </p>

    <table class="qs-table" role="presentation">
      <thead>
        <tr>
          <th class="center">Education<br>(Yes/No)</th>
          <th class="center">Eligibility<br>(Yes/No)</th>
          <th class="center">Experience<br>(Yes/No)</th>
          <th class="center">Training<br>(Yes/No)</th>
          <th class="center">Result<br>(Qualified)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="center">{{ $qsEducationValue }}</td>
          <td class="center">{{ $qsEligibilityValue }}</td>
          <td class="center">{{ $qsExperienceValue }}</td>
          <td class="center">{{ $qsTrainingValue }}</td>
          <td class="center">{!! $overallQsMark !!}</td>
        </tr>
      </tbody>
    </table>

    <table class="receipt-table" role="presentation" style="margin-top:12px;">
      <thead>
        <tr>
          <th>Submitted Documents</th>
          <th class="mark-col">(&#10003; or &#10005;)</th>
          <th class="remarks-col">Remarks</th>
        </tr>
      </thead>
      <tbody>
        @if($sortedDocuments->isEmpty())
          <tr>
            <td colspan="3" class="center">No documents submitted.</td>
          </tr>
        @else
          @foreach($sortedDocuments as $doc)
            @php
              $isVerified = in_array($doc['status'], $verifiedStatuses, true);
              $needsRevision = in_array($doc['status'], $revisionStatuses, true);
              $mark = $isVerified ? '&#10003;' : '&#10005;';
              $markColor = $isVerified ? '#16a34a' : '#dc2626';
              $remarksText = $doc['remarks'] !== '' && strtolower($doc['remarks']) !== 'no remarks provided.'
                ? $doc['remarks']
                : '-';
            @endphp
            <tr>
              <td>{{ $doc['name'] }}</td>
              <td class="center"><strong style="color: {{ $markColor }};">{!! $mark !!}</strong></td>
              <td>{{ $remarksText }}</td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>

    <table class="action-table" role="presentation">
      <thead>
        <tr>
          <th style="width: 30%;">Documents Submitted</th>
          <th>Action Required from the Applicant</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="status-label">{{ $documentSubmissionStatus }}</td>
          <td>
            @if($isFinalDisqualified || $isManualRejected)
              <p class="action-text"><strong>I am sorry to inform you that, you are not qualified for this position.</strong></p>
              @if($displayRemarks !== '')
                <p class="action-text"><strong>Remarks:</strong> {{ $displayRemarks }}</p>
              @endif
            @elseif($showActionRequirements)
              @if($displayDeadline)
                <p class="action-text">Please comply with all deficiencies noted in the checklist above.</p>
                @if($isFinalWarning)
                  <p class="action-text"><strong>This is your final opportunity to comply. Once your document/s are marked as 'Needs Revision' again, you will be considered not qualified and will no longer have the opportunity to comply again.</strong></p>
                @endif
                <p class="action-text"><strong>Submission deadline:</strong> {{ $displayDeadline }}</p>
              @else
                @if(!empty($qsLackingLabels))
                  <p class="action-text"><strong>This is the Qualifications you must attain inorder to apply for this job.</strong></p>
                  <p class="action-text"><strong>Qualification standards not met:</strong> {{ implode(', ', $qsLackingLabels) }}.</p>
                @endif
              @endif
              @if($displayRemarks !== '')
                <p class="action-text"><strong>Remarks:</strong> {{ $displayRemarks }}</p>
              @endif
            @else
              <p class="action-text">No further action required. Wait for further instruction on the next assessment phase.</p>
            @endif
          </td>
        </tr>
      </tbody>
    </table>

    <table class="details-table" role="presentation">
      <tr>
        <td class="details-label">Job Applied:</td>
        <td>{{ $position_title ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td class="details-label">Place of Assignment:</td>
        <td>{{ $place_of_assignment ?? 'N/A' }}</td>
      </tr>
      <tr>
        <td class="details-label">Monthly Compensation:</td>
        <td>PHP {{ number_format($compensation ?? 0, 2) }}</td>
      </tr>
      <tr>
        <td class="details-label">Document Progress:</td>
        <td>{{ $progress_count ?? '0/0' }} verified ({{ $progress_percentage ?? 0 }}%)</td>
      </tr>
    </table>

    <table class="sign-table" role="presentation">
      <!-- <strong>{{ $reviewer_name ?? '' }}</strong> -->
      <tr>
        <td>Reviewed by: </td>
        <td>Received by or emailed to:</td>
      </tr>
      <tr>
        <td>
          <div class="flex justify-center items-center text-center sign-line">{{ $reviewer_name ?? '' }}</div>
          <div class="sign-note">Printed name and signature of HR personnel</div>
        </td>
        <td>
          <div class="flex justify-center items-center text-center sign-line">{{ $applicant_name ?? 'Applicant' }}</div>
          <div class="sign-note">Printed name and signature of applicant or email address</div>
        </td>
      </tr>
      <tr>
        <td>Date reviewed: {{ now()->format('F d, Y') }}</td>
        <td>Date received or emailed: {{ now()->format('F d, Y') }}</td>
      </tr>
    </table>

    <div class="email-actions">
      <p class="cta-label">View full status:</p>
      <a
        href="{{ route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id]) }}"
        class="cta-button"
        style="display:inline-block;background:#0b3b87;color:#ffffff !important;text-decoration:none;font-weight:700;font-size:13px;line-height:1;padding:11px 16px;border-radius:6px;"
      >
        View Application Status
      </a>
    </div>

    <p class="footnote">
      This email serves as an electronic acknowledgement receipt from DILG-CAR Human Resources. For concerns, contact
      <a href="mailto:dilgcarcloud@gmail.com">dilgcarcloud@gmail.com</a>.
    </p>
  </div>
</body>

</html>
