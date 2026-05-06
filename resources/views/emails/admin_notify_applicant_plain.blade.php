Admin Notification: Applicant Notified

Timestamp: {{ $timestamp }} ({{ $timezone }})
Initiated By: {{ $actorName }}
Applicant Name: {{ $applicantName }}
Vacancy: {{ $positionTitle }} ({{ $vacancyId }})

Verified Documents
@if(empty($documents))
- None marked Verified or Needs Revision
@else
@foreach($documents as $doc)
- Type: {{ $doc['name'] ?? $doc['text'] ?? $doc['id'] ?? 'N/A' }}
  ID: {{ $doc['doc_id'] ?? 'N/A' }}
  Status: {{ $doc['status'] ?? 'N/A' }}
  Remarks: {{ $doc['remarks'] ?? '' }}
@endforeach
@endif

This email was generated automatically by DILG-CAR.
