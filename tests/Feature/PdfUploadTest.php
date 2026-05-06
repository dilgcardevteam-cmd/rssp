<?php

namespace Tests\Feature;

use App\Models\DocumentGalleryItem;
use App\Models\UploadedDocument;
use App\Models\JobVacancy;
use App\Models\Applications;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfUploadTest extends TestCase
{
    use RefreshDatabase;

    private function createVacancy(string $vacancyId = 'VAC-001', string $vacancyType = 'COS'): JobVacancy
    {
        return JobVacancy::create([
            'vacancy_id' => $vacancyId,
            'position_title' => 'Test Vacancy',
            'vacancy_type' => $vacancyType,
            'monthly_salary' => 30000,
            'status' => 'OPEN',
            'closing_date' => now()->addWeek(),
            'qualification_education' => 'Bachelor',
            'qualification_training' => 'None',
            'qualification_experience' => '1 year',
            'qualification_eligibility' => 'None',
            'to_person' => 'HR Officer',
            'to_position' => 'HR',
            'to_office' => 'DILG',
            'to_office_address' => 'Baguio',
            'place_of_assignment' => 'Baguio',
        ]);
    }

    private function seedUploadedDocs(User $user, string $vacancyId, array $docTypes): void
    {
        foreach ($docTypes as $docType) {
            UploadedDocument::create([
                'user_id' => $user->id,
                'vacancy_id' => $vacancyId,
                'document_type' => $docType,
                'original_name' => $docType . '.pdf',
                'stored_name' => $docType . '.pdf',
                'storage_path' => 'uploads/pds-files/' . $docType . '.pdf',
                'mime_type' => 'application/pdf',
                'file_size_8b' => 1024,
                'status' => 'Pending',
                'remarks' => '',
            ]);
        }
    }

    public function test_upload_accepts_valid_pdf_and_stores_metadata(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $vacancy = $this->createVacancy();
        Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'New',
            'is_valid' => true,
        ]);
        UploadedDocument::create([
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
            'document_type' => 'pqe_result',
            'original_name' => 'old.pdf',
            'stored_name' => 'old.pdf',
            'storage_path' => 'uploads/pds-files/old.pdf',
            'mime_type' => 'application/pdf',
            'file_size_8b' => 100,
            'status' => 'Needs Revision',
            'remarks' => 'Please re-upload.',
        ]);
        $this->actingAs($user);

        $file = UploadedFile::fake()->createWithContent('doc.pdf', "%PDF-1.7\n%TEST\n");
        $response = $this->post(route('application_status.upload', [$user->id, $vacancy->vacancy_id]), [
            'cert_uploads' => [
                'pqe_result' => $file
            ]
        ]);

        $response->assertRedirect();

        $document = UploadedDocument::where('user_id', $user->id)
            ->where('document_type', 'pqe_result')
            ->first();

        $this->assertNotNull($document);
        $this->assertSame('', $document->remarks);
        Storage::disk('public')->assertExists($document->storage_path);
    }

    public function test_upload_rejects_invalid_pdf_header(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $vacancy = $this->createVacancy();
        Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'New',
            'is_valid' => true,
        ]);
        $this->actingAs($user);

        $file = UploadedFile::fake()->createWithContent('bad.pdf', 'NOT_A_PDF');
        $response = $this->post(route('application_status.upload', [$user->id, $vacancy->vacancy_id]), [
            'cert_uploads' => [
                'pqe_result' => $file
            ]
        ]);

        $response->assertSessionHasErrors(['cert_uploads.pqe_result']);
        $this->assertDatabaseMissing('uploaded_documents', [
            'user_id' => $user->id,
            'document_type' => 'pqe_result',
        ]);
    }

    public function test_application_status_upload_syncs_application_letter_to_document_gallery(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $vacancy = $this->createVacancy('VAC-AL-001');
        Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'Compliance',
            'is_valid' => true,
            'file_status' => 'Needs Revision',
            'file_storage_path' => 'uploads/application_letters/old_application_letter.pdf',
            'file_original_name' => 'old_application_letter.pdf',
            'file_stored_name' => 'old_application_letter.pdf',
            'file_size_8b' => 10,
        ]);

        Storage::disk('public')->put('uploads/application_letters/old_application_letter.pdf', 'old');
        $this->actingAs($user);

        $file = UploadedFile::fake()->createWithContent('application_letter.pdf', "%PDF-1.7\n%TEST\n");
        $response = $this->post(route('application_status.upload', [$user->id, $vacancy->vacancy_id]), [
            'cert_uploads' => [
                'application_letter' => $file,
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('document_gallery_items', [
            'user_id' => $user->id,
            'document_type' => 'application_letter',
        ]);
    }

    public function test_upload_rejects_oversized_pdf(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $vacancy = $this->createVacancy();
        Applications::create([
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
            'status' => 'New',
            'is_valid' => true,
        ]);
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('big.pdf', 10241, 'application/pdf');
        $response = $this->post(route('application_status.upload', [$user->id, $vacancy->vacancy_id]), [
            'cert_uploads' => [
                'pqe_result' => $file
            ]
        ]);

        $response->assertSessionHasErrors(['cert_uploads.pqe_result']);
    }

    public function test_finalize_pds_upload_succeeds(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $vacancy = $this->createVacancy('VAC-010');
        $this->actingAs($user);

        $response = $this->post(route('finalize_pds', ['go_to' => 'dashboard_user']), [
            'vacancy_id' => $vacancy->vacancy_id,
            'doc_track' => 'COS',
            'declaration' => '1',
            'consent' => '1',
            'confirmation' => '1',
            'cert_uploads' => [
                'passport_photo' => UploadedFile::fake()->createWithContent('passport_photo.pdf', "%PDF-1.7\n%TEST\n"),
                'signed_pds' => UploadedFile::fake()->createWithContent('signed_pds.pdf', "%PDF-1.7\n%TEST\n"),
                'signed_work_exp_sheet' => UploadedFile::fake()->createWithContent('signed_work_exp_sheet.pdf', "%PDF-1.7\n%TEST\n"),
                'photocopy_diploma' => UploadedFile::fake()->createWithContent('photocopy_diploma.pdf', "%PDF-1.7\n%TEST\n"),
                'application_letter' => UploadedFile::fake()->createWithContent('application_letter.pdf', "%PDF-1.7\n%TEST\n"),
                'cert_training' => UploadedFile::fake()->createWithContent('cert_training.pdf', "%PDF-1.7\n%TEST\n"),
            ]
        ]);

        $response->assertRedirect();
        $document = UploadedDocument::where('user_id', $user->id)
            ->where('document_type', 'application_letter')
            ->first();
        $this->assertNotNull($document);
        Storage::disk('public')->assertExists($document->storage_path);
        $this->assertDatabaseHas('document_gallery_items', [
            'user_id' => $user->id,
            'document_type' => 'application_letter',
        ]);
        $this->assertDatabaseHas('document_gallery_items', [
            'user_id' => $user->id,
            'document_type' => 'signed_pds',
        ]);
    }

    public function test_document_gallery_keeps_single_record_per_document_type_on_auto_sync(): void
    {
        $user = User::factory()->create();
        $this->createVacancy('VAC-001');
        $this->createVacancy('VAC-002');

        UploadedDocument::create([
            'user_id' => $user->id,
            'vacancy_id' => 'VAC-001',
            'document_type' => 'cert_training',
            'original_name' => 'old.pdf',
            'stored_name' => 'old.pdf',
            'storage_path' => 'uploads/pds-files/old.pdf',
            'mime_type' => 'application/pdf',
            'file_size_8b' => 100,
            'status' => 'Pending',
            'remarks' => '',
        ]);

        UploadedDocument::create([
            'user_id' => $user->id,
            'vacancy_id' => 'VAC-002',
            'document_type' => 'cert_training',
            'original_name' => 'new.pdf',
            'stored_name' => 'new.pdf',
            'storage_path' => 'uploads/pds-files/new.pdf',
            'mime_type' => 'application/pdf',
            'file_size_8b' => 200,
            'status' => 'Pending',
            'remarks' => '',
        ]);

        $this->assertSame(
            1,
            DocumentGalleryItem::where('user_id', $user->id)
                ->where('document_type', 'cert_training')
                ->count()
        );

        $this->assertDatabaseHas('document_gallery_items', [
            'user_id' => $user->id,
            'document_type' => 'cert_training',
            'storage_path' => 'uploads/pds-files/new.pdf',
        ]);
    }

    public function test_finalize_pds_upload_rolls_back_on_failure(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('finalize_pds', ['go_to' => 'dashboard_user']), [
            'doc_track' => 'COS',
            'declaration' => '1',
            'consent' => '1',
            'confirmation' => '1',
            'simulate_failure' => 1,
            'cert_uploads' => [
                'passport_photo' => UploadedFile::fake()->createWithContent('passport_photo.pdf', "%PDF-1.7\n%TEST\n"),
                'signed_pds' => UploadedFile::fake()->createWithContent('signed_pds.pdf', "%PDF-1.7\n%TEST\n"),
                'signed_work_exp_sheet' => UploadedFile::fake()->createWithContent('signed_work_exp_sheet.pdf', "%PDF-1.7\n%TEST\n"),
                'photocopy_diploma' => UploadedFile::fake()->createWithContent('photocopy_diploma.pdf', "%PDF-1.7\n%TEST\n"),
                'application_letter' => UploadedFile::fake()->createWithContent('application_letter.pdf', "%PDF-1.7\n%TEST\n"),
                'cert_training' => UploadedFile::fake()->createWithContent('cert_training.pdf', "%PDF-1.7\n%TEST\n"),
            ]
        ]);

        $response->assertSessionHasErrors('cert_uploads');
        $this->assertDatabaseMissing('uploaded_documents', [
            'user_id' => $user->id,
            'document_type' => 'application_letter',
        ]);
    }

    public function test_finalize_pds_allows_submission_with_existing_required_docs_only(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $vacancy = $this->createVacancy('VAC-COS-EXIST', 'COS');
        $this->seedUploadedDocs($user, $vacancy->vacancy_id, [
            'passport_photo',
            'signed_pds',
            'signed_work_exp_sheet',
            'photocopy_diploma',
            'application_letter',
            'cert_training',
        ]);
        $this->actingAs($user);

        $response = $this->post(route('finalize_pds', ['go_to' => 'job_description']), [
            'vacancy_id' => $vacancy->vacancy_id,
            'doc_track' => 'COS',
            'declaration' => '1',
            'consent' => '1',
            'confirmation' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
        ]);
    }

    public function test_finalize_pds_accepts_alias_key_for_required_cert_eligibility(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $vacancy = $this->createVacancy('VAC-PLA-ALIAS', 'Plantilla');
        $this->seedUploadedDocs($user, $vacancy->vacancy_id, [
            'application_letter',
            'signed_pds',
            'signed_work_exp_sheet',
            'ipcr',
            'non_academic',
            'cert_training',
            'designation_order',
            'transcript_records',
            'photocopy_diploma',
            'cert_employment',
            'passport_photo',
        ]);
        $this->actingAs($user);

        $response = $this->post(route('finalize_pds', ['go_to' => 'job_description']), [
            'vacancy_id' => $vacancy->vacancy_id,
            'doc_track' => 'Plantilla',
            'declaration' => '1',
            'consent' => '1',
            'confirmation' => '1',
            'cert_uploads' => [
                'cert_elegibility' => UploadedFile::fake()->createWithContent('cert_elegibility.pdf', "%PDF-1.7\n%TEST\n"),
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('uploaded_documents', [
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
            'document_type' => 'cert_eligibility',
        ]);
    }

    public function test_finalize_pds_allows_plantilla_submission_with_existing_required_docs_only(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $vacancy = $this->createVacancy('VAC-PLA-EXIST', 'Plantilla');
        $this->seedUploadedDocs($user, $vacancy->vacancy_id, [
            'application_letter',
            'signed_pds',
            'signed_work_exp_sheet',
            'cert_eligibility',
            'ipcr',
            'non_academic',
            'cert_training',
            'designation_order',
            'transcript_records',
            'photocopy_diploma',
            'cert_employment',
            'passport_photo',
        ]);
        $this->actingAs($user);

        $response = $this->post(route('finalize_pds', ['go_to' => 'job_description']), [
            'vacancy_id' => $vacancy->vacancy_id,
            'doc_track' => 'Plantilla',
            'declaration' => '1',
            'consent' => '1',
            'confirmation' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'vacancy_id' => $vacancy->vacancy_id,
        ]);
    }
}
