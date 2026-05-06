<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadedDocument extends Model
{
    use HasFactory;

    public const DOCUMENTS = [
        'application_letter',
        'signed_pds',
        'signed_work_exp_sheet',
        'pqe_result',
        'cert_eligibility',
        'ipcr',
        'non_academic',
        'cert_training',
        'designation_order',
        'transcript_records',
        'photocopy_diploma',
        'grade_masteraldoctorate',
        'tor_masteraldoctorate',
        'cert_employment',
        'cert_lgoo_induction',
        'passport_photo',
        'other_documents',
        'isApproved',
    ];

    protected $fillable = [
        'user_id',
        'vacancy_id',
        'document_type',
        'original_name',
        'stored_name',
        'storage_path',
        'mime_type',
        'file_size_8b',
        'status',
        'revision_requested_count',
        'revision_requested_at',
        'revision_submitted_at',
        'remarks',
        'last_modified_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
