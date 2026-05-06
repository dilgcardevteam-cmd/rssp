<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\Applications;
use App\Models\UploadedDocument;

$userId = 34;
$vacancyId = 'NS-049';

$application = Applications::where('user_id', $userId)->where('vacancy_id', $vacancyId)->first();
echo "Application status: " . $application->status . "\n";
echo "Application file_storage_path: " . ($application->file_storage_path ?? 'NULL') . "\n\n";

// Simulate loadUploadedDocumentsMap
$supportsVacancyScopedDocs = Schema::hasColumn('uploaded_documents', 'vacancy_id');
echo "Supports vacancy_id column: " . ($supportsVacancyScopedDocs ? 'yes' : 'no') . "\n";

$docsQuery = UploadedDocument::where('user_id', $userId);
if ($supportsVacancyScopedDocs && !empty($vacancyId)) {
    $docsQuery->where(function ($query) use ($vacancyId) {
        $query->where('vacancy_id', $vacancyId)->orWhereNull('vacancy_id');
    });
    $docsQuery->orderByRaw("CASE WHEN vacancy_id = ? THEN 0 ELSE 1 END", [$vacancyId]);
}
$docs = $docsQuery->orderByDesc('updated_at')->get();
$docsMap = $docs->unique('document_type')->keyBy('document_type');

echo "\nDocs found in map (" . $docsMap->count() . "):\n";
foreach ($docsMap as $type => $d) {
    echo "  $type | vac:" . ($d->vacancy_id ?? 'NULL') . " | path:" . (empty($d->storage_path) ? 'EMPTY' : 'HAS_FILE') . " | status:" . $d->status . "\n";
}

// Check what status signed_pds and passport_photo would get
foreach (['signed_pds', 'passport_photo'] as $docType) {
    $doc = $docsMap->get($docType);
    $hasFile = $doc && !empty($doc->storage_path) && $doc->storage_path !== 'NOINPUT';
    $docStatus = trim((string) ($doc?->status ?? ''));
    $isRevisionStatus = in_array(strtolower($docStatus), ['needs revision', 'disapproved with deficiency'], true);
    $status = $hasFile
        ? ($docStatus !== '' ? $doc->status : 'Pending')
        : ($isRevisionStatus ? $doc->status : 'Not Submitted');
    echo "\n$docType: hasFile=$hasFile, status=$status\n";
}
