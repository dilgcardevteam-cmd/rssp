<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Scanning for applicants who should be Qualified...\n";
    
    $applications = \App\Models\Applications::where('status', 'Pending')->get();
    
    foreach ($applications as $application) {
        $documents = getApplicantDocuments($application->user_id, $application);
        
        $hasNeedsRevision = false;
        $allVerified = true;
        $submittedCount = 0;
        
        foreach ($documents as $doc) {
            $status = $doc['status'];
            
            if ($status === 'Not Submitted') {
                continue;
            }
            
            $submittedCount++;
            
            if ($status === 'Needs Revision' || $status === 'Disapproved With Deficiency') {
                $hasNeedsRevision = true;
            }
            
            if ($status !== 'Verified' && $status !== 'Okay/Confirmed') {
                $allVerified = false;
            }
        }
        
        if ($hasNeedsRevision) {
            // Should be Compliance
            if ($application->status !== 'Compliance') {
                echo "App ID {$application->id}: Updating to Compliance\n";
                $application->status = 'Compliance';
                $application->save();
            }
        } elseif ($allVerified && $submittedCount > 0) {
            // Should be Qualified
            if ($application->status !== 'Qualified') {
                echo "App ID {$application->id} (User: " . ($application->user->name ?? 'Unknown') . "): Updating to Qualified\n";
                $application->status = 'Qualified';
                $application->save();
            }
        }
    }
    
    echo "Done.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Helper function mimicked from Controller
function getApplicantDocuments($userId, $application) {
    $uploadedDocuments = \App\Models\UploadedDocument::where('user_id', $userId)->get()->keyBy('document_type');
    $documents = [];
    
    // Mimic the list from UploadedDocument::DOCUMENTS or the controller
    // For simplicity, we fetch the keys from the uploaded docs + application_letter
    // Ideally we should use the exact same list as the controller.
    
    $docTypes = \App\Models\UploadedDocument::DOCUMENTS; 
    
    foreach ($docTypes as $docType) {
        if ($docType === 'isApproved') continue;
        
        $status = 'Not Submitted';
        $originalName = null;
        $id = $docType;
        
        if ($docType === 'application_letter') {
            $status = $application->file_status ?? 'Not Submitted';
        } else {
            $doc = $uploadedDocuments->get($docType);
            if ($doc) {
                $status = $doc->status;
                $originalName = $doc->original_name;
            }
        }
        
        $documents[] = [
            'id' => $id,
            'status' => $status,
            'original_name' => $originalName
        ];
    }
    return $documents;
}
