DILG-CAR Application Document Status Overview

Applicant: {{ $applicant_name ?? 'Applicant' }}
Position: {{ $position_title ?? 'N/A' }}
Place of Assignment: {{ $place_of_assignment ?? 'N/A' }}
Monthly Compensation: PHP {{ number_format($compensation ?? 0, 2) }}
Document Progress: {{ $progress_count ?? '0/0' }} verified ({{ $progress_percentage ?? 0 }}%)
Reviewed by: {{ $reviewer_name ?? 'N/A' }}

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

  $hasRevisions = $normalizedDocuments->contains(fn ($doc) => in_array($doc['status'], $revisionStatuses, true));
  $qsResultNormalized = strtolower(trim((string) ($qs_result ?? '')));
  $isQualified = $qsResultNormalized === 'qualified';
  $isNeedsRevisions = $qsResultNormalized === 'needs revisions';
  $noticeMode = strtolower(trim((string) ($compliance_notice_mode ?? 'default')));
  $isFinalWarning = $noticeMode === 'final_warning';
  $isFinalDisqualified = $noticeMode === 'disqualified_final';
  $isManualRejected = !$isFinalDisqualified && !$isQualified && !$isNeedsRevisions;
  $showActionRequirements = !$isFinalDisqualified && $isNeedsRevisions;
  $displayDeadline = !empty($deadline) && $deadline !== 'No deadline set' ? $deadline : null;
  $displayRemarks = trim((string) ($application_remarks ?? ''));

  $normalizeQs = function ($value) {
    $normalized = strtolower(trim((string) $value));
    if (in_array($normalized, ['yes', 'qualified', 'pass', 'passed'], true)) {
      return 'yes';
    }
    if (in_array($normalized, ['na', 'n/a', 'not applicable'], true)) {
      return 'na';
    }
    return 'no';
  };

  $qsLackings = [];
  if ($normalizeQs($qs_education ?? 'no') === 'no') {
    $qsLackings[] = 'Education';
  }
  if ($normalizeQs($qs_eligibility ?? 'no') === 'no') {
    $qsLackings[] = 'Eligibility';
  }
  if ($normalizeQs($qs_experience ?? 'no') === 'no') {
    $qsLackings[] = 'Experience';
  }
  if ($normalizeQs($qs_training ?? 'no') === 'no') {
    $qsLackings[] = 'Training';
  }
@endphp

Documents:
@forelse($normalizedDocuments as $doc)
- {{ $doc['name'] }} | Status: {{ $doc['status'] !== '' ? $doc['status'] : 'not submitted' }} | Remarks: {{ $doc['remarks'] !== '' ? $doc['remarks'] : '-' }}
@empty
- No document records available.
@endforelse

Qualification Standards:
- Education: {{ strtoupper((string) $qs_education) }}
- Eligibility: {{ strtoupper((string) $qs_eligibility) }}
- Experience: {{ strtoupper((string) $qs_experience) }}
- Training: {{ strtoupper((string) $qs_training) }}
- Overall Result: {{ strtoupper((string) $qs_result) }}

@if($isFinalDisqualified || $isManualRejected)
Action Required:
- You are not qualified for this position.
@elseif($showActionRequirements)
Action Required:
@if($displayDeadline)
- Please comply with all deficiencies noted above.
@if($isFinalWarning)
- This is your final opportunity to comply.
@endif
- Submission deadline: {{ $displayDeadline }}
@else
@if(!empty($qsLackings))
- This is the Qualifications you must attain inorder to apply for this job.
- Qualification standards not met: {{ implode(', ', $qsLackings) }}.
@endif
@endif
@else
Action Required:
- No further action required. Wait for further instruction on the next assessment phase.
@endif

@if($displayRemarks !== '')
Remarks:
{{ $displayRemarks }}
@endif

View full status:
{{ route('application_status', ['user' => $user_id, 'vacancy' => $vacancy_id]) }}

This email serves as an electronic acknowledgement receipt from DILG-CAR Human Resources.
For concerns, contact dilgcarcloud@gmail.com.
