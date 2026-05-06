<?php

namespace App\Http\Controllers;

use App\Support\PreviewUrl;
use Illuminate\Support\Facades\Auth;
use App\Models\UploadedDocument;

class JobStatusController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $documents = UploadedDocument::where('user_id', $user->id)->first();

        $documentsData = [
            [
                "id" => "app-letter",
                "name" => "application_letter",
                "text" => "Application Letter",
                "status" => $documents && $documents->application_letter ? "valid" : "invalid",
                "preview" => $documents && $documents->application_letter ? PreviewUrl::forPath($documents->application_letter) : "",
                "remarks" => $documents && $documents->application_letter ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "pds",
                "name" => "pds",
                "text" => "Fully Accomplished & Updated PDS",
                "status" => $documents && $documents->pds ? "valid" : "invalid",
                "preview" => $documents && $documents->pds ? PreviewUrl::forPath($documents->pds) : "",
                "remarks" => $documents && $documents->pds ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "pqe",
                "name" => "pqe_result",
                "text" => "Pre-Qualifying Exam (PQE) Result(if passed)",
                "status" => $documents && $documents->pqe_result ? "valid" : "invalid",
                "preview" => $documents && $documents->pqe_result ? PreviewUrl::forPath($documents->pqe_result) : "",
                "remarks" => $documents && $documents->pqe_result ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "certificate-eligibility",
                "name" => "certificate_eligibility",
                "text" => "Photocopy of Certificate of Eligibility / Board Rating",
                "status" => $documents && $documents->certificate_eligibility ? "valid" : "invalid",
                "preview" => $documents && $documents->certificate_eligibility ? PreviewUrl::forPath($documents->certificate_eligibility) : "",
                "remarks" => $documents && $documents->certificate_eligibility ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "certification-performance",
                "name" => "performance_rating",
                "text" => "Certification of Numerical Rating / Performance Rating / IPCR",
                "status" => $documents && $documents->performance_rating ? "valid" : "invalid",
                "preview" => $documents && $documents->performance_rating ? PreviewUrl::forPath($documents->performance_rating) : "",
                "remarks" => $documents && $documents->performance_rating ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "non-academic-awards",
                "name" => "non_academic_awards",
                "text" => "Non-Academic Awards Received",
                "status" => $documents && $documents->non_academic_awards ? "valid" : "invalid",
                "preview" => $documents && $documents->non_academic_awards ? PreviewUrl::forPath($documents->non_academic_awards) : "",
                "remarks" => $documents && $documents->non_academic_awards ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "certified-participation",
                "name" => "certificates_participation",
                "text" => "Certified/Authenticated Copy of Certificates of Training/Participation",
                "status" => $documents && $documents->certificates_participation ? "valid" : "invalid",
                "preview" => $documents && $documents->certificates_participation ? PreviewUrl::forPath($documents->certificates_participation) : "",
                "remarks" => $documents && $documents->certificates_participation ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "list-certified",
                "name" => "designation_orders",
                "text" => "List with Certified Photocopy of Duly Confirmed Designation Order/s",
                "status" => $documents && $documents->designation_orders ? "valid" : "invalid",
                "preview" => $documents && $documents->designation_orders ? PreviewUrl::forPath($documents->designation_orders) : "",
                "remarks" => $documents && $documents->designation_orders ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "transcript",
                "name" => "transcript",
                "text" => "Photocopy of Transcript of Records (Baccalaureate Degree)",
                "status" => $documents && $documents->transcript ? "valid" : "invalid",
                "preview" => $documents && $documents->transcript ? PreviewUrl::forPath($documents->transcript) : "",
                "remarks" => $documents && $documents->transcript ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "diploma",
                "name" => "diploma",
                "text" => "Photocopy of Diploma",
                "status" => $documents && $documents->diploma ? "valid" : "invalid",
                "preview" => $documents && $documents->diploma ? PreviewUrl::forPath($documents->diploma) : "",
                "remarks" => $documents && $documents->diploma ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "cert-grades",
                "name" => "certificate_grades",
                "text" => "Certified Photocopy of Certificate of Grades with Masteral/Doctorate Units Earned",
                "status" => $documents && $documents->certificate_grades ? "valid" : "invalid",
                "preview" => $documents && $documents->certificate_grades ? PreviewUrl::forPath($documents->certificate_grades) : "",
                "remarks" => $documents && $documents->certificate_grades ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "cert-tor",
                "name" => "certified_tor",
                "text" => "Certified Photocopy of TOR with Masteral/Doctorate Degree",
                "status" => $documents && $documents->certified_tor ? "valid" : "invalid",
                "preview" => $documents && $documents->certified_tor ? PreviewUrl::forPath($documents->certified_tor) : "",
                "remarks" => $documents && $documents->certified_tor ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "cert-employment",
                "name" => "certificate_employment",
                "text" => "Certificate of Employment (If Any)",
                "status" => $documents && $documents->certificate_employment ? "valid" : "invalid",
                "preview" => $documents && $documents->certificate_employment ? PreviewUrl::forPath($documents->certificate_employment) : "",
                "remarks" => $documents && $documents->certificate_employment ? "Uploaded" : "No document uploaded",
            ],
            [
                "id" => "other-documents",
                "name" => "other_documents",
                "text" => "Other Documents Submitted",
                "status" => $documents && $documents->other_documents ? "valid" : "invalid",
                "preview" => $documents && $documents->other_documents ? PreviewUrl::forPath($documents->other_documents) : "",
                "remarks" => $documents && $documents->other_documents ? "Uploaded" : "No document uploaded",
            ],
        ];

        return view('dashboard_user.application_status', [
            'user' => $user,
            'documents' => $documents,
            'documentsData' => $documentsData,
        ]);
    }
}
