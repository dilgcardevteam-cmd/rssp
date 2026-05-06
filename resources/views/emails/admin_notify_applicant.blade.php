<div style="font-family: Montserrat, Arial, sans-serif; background:#f7fafc; padding:24px;">
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
  <div style="max-width:720px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; box-shadow:0 8px 20px rgba(0,0,0,0.06); overflow:hidden;">
    <div style="background:#0D2B70; color:#fff; padding:20px 24px; display:flex; align-items:center; gap:16px;">
      <img src="{{ $logoSrc }}" alt="DILG Logo" style="width:54px;height:54px;object-fit:contain;border-radius:4px;flex-shrink:0;background:#fff;padding:2px;">
      <div>
        <h1 style="margin:0; font-size:20px;">Admin Notification</h1>
        <p style="margin:4px 0 0 0; font-size:13px; opacity:.9;">Applicant Notification Triggered</p>
      </div>
    </div>
    <div style="padding:24px;">
      <h2 style="margin:0 0 10px 0; font-size:18px; color:#0D2B70;">Action Details</h2>
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <tr>
          <td style="padding:6px 0; font-weight:700; color:#374151; width:180px;">Timestamp</td>
          <td style="padding:6px 0; color:#111827;">{{ $timestamp }} ({{ $timezone }})</td>
        </tr>
        <tr>
          <td style="padding:6px 0; font-weight:700; color:#374151;">Initiated By</td>
          <td style="padding:6px 0; color:#111827;">{{ $actorName }}</td>
        </tr>
        <tr>
          <td style="padding:6px 0; font-weight:700; color:#374151;">Applicant Name</td>
          <td style="padding:6px 0; color:#111827;">{{ $applicantName }}</td>
        </tr>
        <tr>
          <td style="padding:6px 0; font-weight:700; color:#374151;">Vacancy</td>
          <td style="padding:6px 0; color:#111827;">{{ $positionTitle }} ({{ $vacancyId }})</td>
        </tr>
      </table>

      <h2 style="margin:18px 0 8px 0; font-size:18px; color:#0D2B70;">Verified Documents</h2>

      @if(empty($documents))
        <p style="margin:0; color:#6b7280;">No documents were marked Verified or Needs Revision.</p>
      @else
        @php
          // Check if all documents are verified
          $allVerified = true;
          foreach($documents as $doc) {
              $st = strtolower(trim($doc['status'] ?? ''));
              $isVerified = in_array($st, ['okay/confirmed','confirmed','approved','ok','uni'], true);
              if (!$isVerified) {
                  $allVerified = false;
                  break;
              }
          }
        @endphp

        @if($allVerified)
          <div style="margin-bottom:16px; padding:12px 16px; background:#dcfce7; border:1px solid #bbf7d0; border-radius:8px; color:#166534; font-size:14px;">
            <strong>Qualification Status:</strong> All documents have been verified. Applicant is now qualified.<br>
            <strong>Remarks:</strong> No further action required. Wait for further instruction on the next assessment phase.
          </div>
        @endif

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin-top:8px;">
          <thead>
            <tr style="background:#f3f4f6; color:#111827;">
              <th align="left" style="padding:10px; font-size:12px; text-transform:uppercase; letter-spacing:.03em;">Document Type</th>
              <th align="left" style="padding:10px; font-size:12px; text-transform:uppercase; letter-spacing:.03em;">Document ID</th>
              <th align="center" style="padding:10px; font-size:12px; text-transform:uppercase; letter-spacing:.03em; width:60px;">Status</th>
              <th align="left" style="padding:10px; font-size:12px; text-transform:uppercase; letter-spacing:.03em;">Remarks</th>
            </tr>
          </thead>
          <tbody>
            @php
              // Sort documents to prioritize 'Needs Revision' items
              $sortedDocuments = collect($documents)->sortBy(function($doc) {
                  $status = $doc['status'] ?? '';
                  if ($status == 'ggs' || $status == 'Disapproved With Deficiency') {
                      return 1;
                  } elseif ($status == 'dds' || $status == 'Pending') {
                      return 2;
                  } elseif ($status == 'uni' || $status == 'Okay/Confirmed') {
                      return 3;
                  }
                  return 4;
              });
            @endphp
            @foreach($sortedDocuments as $doc)
              <tr style="border-bottom:1px solid #e5e7eb;">
                <td style="padding:10px; color:#111827;">{{ $doc['name'] ?? $doc['text'] ?? $doc['id'] ?? 'N/A' }}</td>
                <td style="padding:10px; color:#111827;">{{ $doc['doc_id'] ?? 'N/A' }}</td>
                <td style="padding:10px; text-align:center;">
                  @php
                    $st = strtolower(trim($doc['status'] ?? ''));
                    $isVerified = in_array($st, ['okay/confirmed','confirmed','approved','ok','uni'], true);
                    $needsRevision = in_array($st, ['needs revision','disapproved with deficiency','rejected','ggs'], true);
                    $badgeBg = $isVerified ? '#dcfce7' : ($needsRevision ? '#fee2e2' : '#e5e7eb');
                    $badgeColor = $isVerified ? '#166534' : ($needsRevision ? '#991b1b' : '#374151');
                    $icon = $isVerified ? '✓' : ($needsRevision ? '✕' : '•');
                  @endphp
                  <span style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:50%; font-size:18px; font-weight:700; background:{{ $badgeBg }}; color:{{ $badgeColor }}; line-height:1;">
                    {{ $icon }}
                  </span>
                </td>
                <td style="padding:10px; color:#6b7280; font-size:13px;">{{ $doc['remarks'] ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif

      <div style="margin-top:24px; padding:16px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:12px; color:#374151; font-size:12px;">
        This notification was generated automatically by DILG-CAR. If you believe you received this in error, please contact the system administrator.
      </div>
    </div>
  </div>
</div>
