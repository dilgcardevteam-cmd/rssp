<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\UploadedDocument;

class C5Controller extends Controller
{
    public function store(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        // ✅ Validate uploaded PDF files (all are optional)
        $request->validate([
            'cert_uploads.application_letter' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.pqe_result' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_eligibility' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.ipcr' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.non_academic' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_training' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.designation_order' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.transcript_records' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.photocopy_diploma' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.grade_masteraldoctorate' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.tor_masteraldoctorate' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_employment' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.other_documents' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.signed_pds' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.signed_work_exp_sheet' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.cert_lgoo_induction' => 'nullable|file|mimes:pdf|max:10240',
            'cert_uploads.passport_photo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $data = [];

        $fields = [
            'application_letter',
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
            'other_documents',
            'signed_pds',
            'signed_work_exp_sheet',
            'cert_lgoo_induction',
            'passport_photo',
        ];

        foreach ($fields as $field) {
            if ($request->hasFile("cert_uploads.$field")) {
                $data[$field] = $request->file("cert_uploads.$field")->store("documents/{$user->id}", 'public');
            } else {
                $data[$field] = null; // ensure nullable fields are handled
            }
        }

        $data['user_id'] = $user->id;

        UploadedDocument::updateOrCreate(
            ['user_id' => $user->id],  // 🔍 Find by user_id
            $data                       // 📝 Then update or insert these fields
        );


        // Redirect to the main submit form after upload
        return redirect()->route('submit');
    }

    public function editC5()
    {
    $user = \Illuminate\Support\Facades\Auth::user();
    $documents = UploadedDocument::where('user_id', $user->id)->first();

    return view('c5_update', compact('documents'));
    }

    public function c5UpdateForm(Request $request)
{
    //dd('You are in editC5()', $);

    $user = \Illuminate\Support\Facades\Auth::user();

    $request->validate([
        'cert_uploads.application_letter' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.pqe_result' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.cert_eligibility' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.ipcr' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.non_academic' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.cert_training' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.designation_order' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.transcript_records' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.photocopy_diploma' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.grade_masteraldoctorate' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.tor_masteraldoctorate' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.cert_employment' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.other_documents' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.signed_pds' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.signed_work_exp_sheet' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.cert_lgoo_induction' => 'nullable|file|mimes:pdf|max:10240',
        'cert_uploads.passport_photo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
    ]);

    $fields = [
        'application_letter',
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
        'other_documents',
        'signed_pds',
        'signed_work_exp_sheet',
        'cert_lgoo_induction',
        'passport_photo',
    ];

    $document = UploadedDocument::firstOrNew(['user_id' => $user->id]);

    foreach ($fields as $field) {
        if ($request->hasFile("cert_uploads.$field")) {
            // Optionally delete old file
            if ($document->$field) {
                Storage::disk('public')->delete($document->$field);
            }

            // Store new file
            $document->$field = $request->file("cert_uploads.$field")->store("documents/{$user->id}", 'public');
        }
        // else, keep the old file or null as is
    }

    foreach ($fields as $field) {
        $shouldRemove = $request->input("remove_files.$field") === 'on';

        if ($shouldRemove && $document->$field) {
            Storage::disk('public')->delete($document->$field);
            $document->$field = null;
        }

        if ($request->hasFile("cert_uploads.$field")) {
            if ($document->$field) {
                Storage::disk('public')->delete($document->$field);
            }

            $document->$field = $request->file("cert_uploads.$field")->store("documents/{$user->id}", 'public');
        }
    }


    $document->user_id = $user->id;
    $document->save();



    return redirect()->route('submit')->with('success', 'Documents updated successfully!');
}

    public function c5ShowForm(){
            $data = session('form_data', []);
            return view('pds.c5', compact('data'));
        }


}
