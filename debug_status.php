<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $application = \App\Models\Applications::with(['user', 'user.personalInformation'])
        ->whereHas('user.personalInformation', function($q) {
            $q->where('first_name', 'like', '%Plantilla9%');
        })->first();

    if ($application) {
        echo "Application ID: " . $application->id . "\n";
        echo "User: " . $application->user->name . "\n";
        echo "Current Status: " . $application->status . "\n";
        echo "Application Letter Status (in DB): " . ($application->file_status ?? 'NULL') . "\n";
        
        // Simulate getApplicantDocuments logic
        $uploadedDocuments = \App\Models\UploadedDocument::where('user_id', $application->user_id)->get()->keyBy('document_type');
        $docTypes = \App\Models\UploadedDocument::DOCUMENTS;
        
        $allVerified = true;
        $hasNeedsRevision = false;
        $verifiedCount = 0;
        $totalDocuments = 0;

        echo "\nChecking Documents Logic:\n";
        foreach ($docTypes as $docType) {
            if ($docType === 'isApproved') continue;
            
            $status = 'Not Submitted';
            if ($docType === 'application_letter') {
                $status = $application->file_status ?? 'Not Submitted';
            } else {
                $doc = $uploadedDocuments->get($docType);
                $status = $doc ? $doc->status : 'Not Submitted';
            }
            
            $totalDocuments++;
            echo "- $docType: $status";
            
            if ($status === 'Needs Revision' || $status === 'Disapproved With Deficiency') {
                $hasNeedsRevision = true;
                echo " [NEEDS REVISION]";
            }

            if ($status === 'Verified' || $status === 'Okay/Confirmed') {
                $verifiedCount++;
                echo " [VERIFIED]";
            } else {
                $allVerified = false;
                echo " [NOT VERIFIED]";
            }
            echo "\n";
        }
        
        echo "\n--- Summary ---\n";
        echo "Total Documents: $totalDocuments\n";
        echo "Verified Count: $verifiedCount\n";
        echo "All Verified: " . ($allVerified ? 'YES' : 'NO') . "\n";
        echo "Has Needs Revision: " . ($hasNeedsRevision ? 'YES' : 'NO') . "\n";
        
        if ($hasNeedsRevision) {
            echo "Expected Status: Compliance\n";
        } elseif ($allVerified && $totalDocuments > 0) {
            echo "Expected Status: Qualified\n";
        } else {
            echo "Expected Status: (No Change / Pending)\n";
        }
        
    } else {
        echo "Application not found.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
