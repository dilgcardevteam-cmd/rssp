<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Models\Applications;
use App\Models\JobVacancy;
use App\Models\PersonalInformation;
use App\Models\EducationalBackground;
use App\Models\WorkExperience;
use App\Models\LearningAndDevelopment;
use App\Models\CivilServiceEligibility;
use App\Models\MiscInfos;
use App\Models\Signatory;
use App\Enums\ApplicationStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class ApplicantListExportController extends Controller
{
    public function previewFinalSelection($vacancy_id)
    {
        return view('admin.reports.preview', [
            'title' => 'Final Selection Line-up Preview',
            'previewPdfUrl' => route('export.final_selection', ['vacancy_id' => $vacancy_id, 'html' => 1]),
            'downloadDocxUrl' => route('export.final_selection', ['vacancy_id' => $vacancy_id]),
            'downloadPdfUrl' => route('export.final_selection', ['vacancy_id' => $vacancy_id, 'pdf' => 1]),
        ]);
    }

    public function previewListOfApplicants($vacancy_id)
    {
        return view('admin.reports.preview', [
            'title' => 'List of Applicants Preview',
            'previewPdfUrl' => route('export.list_of_applicants', ['vacancy_id' => $vacancy_id, 'html' => 1]),
            'downloadDocxUrl' => route('export.list_of_applicants', ['vacancy_id' => $vacancy_id]),
            'downloadPdfUrl' => route('export.list_of_applicants', ['vacancy_id' => $vacancy_id, 'pdf' => 1]),
        ]);
    }

    public function exportFinalSelection(Request $request, $vacancy_id)
    {
        return $this->generateExport($request, $vacancy_id, 'qualified');
    }

    public function exportListOfApplicants(Request $request, $vacancy_id)
    {
        return $this->generateExport($request, $vacancy_id, 'all');
    }

    private function generateExport(Request $request, $vacancy_id, $type)
    {
        $vacancy = JobVacancy::whereRaw('UPPER(TRIM(vacancy_id)) = ?', [strtoupper(trim($vacancy_id))])->firstOrFail();
        
        // Fetch Signatories (Dynamic from DB/Auth as per "before is good" request)
        $rd = Signatory::orderBy('id')->first();
        $admin = Auth::guard('admin')->user();
        
        $notedByName = $rd ? strtoupper(trim("{$rd->first_name} " . ($rd->middle_name ? strtoupper(substr($rd->middle_name, 0, 1)) . '. ' : '') . $rd->last_name)) : 'REGIONAL DIRECTOR';
        $notedByDesignation = $rd->designation ?? "Regional Director";
        
        $preparedByName = $admin ? strtoupper(trim($admin->name)) : 'ADMINISTRATOR';
        $preparedByDesignation = $admin->designation ?? "Administrative Officer";
        
        $data = [
            'vacancy' => $vacancy,
            'rd_name' => $notedByName,
            'rd_designation' => $notedByDesignation,
            'admin_name' => $preparedByName,
            'admin_designation' => $preparedByDesignation,
            'applications' => []
        ];

        $query = Applications::with(['user', 'personalInformation'])
            ->whereRaw('UPPER(TRIM(vacancy_id)) = ?', [strtoupper(trim($vacancy_id))]);

        if ($type === 'qualified') {
            $query->where(function ($sub) {
                $sub->whereRaw('LOWER(TRIM(status)) IN (?, ?, ?, ?)', ['qualified', 'complete', 'passed', 'reviewed'])
                    ->orWhereRaw('LOWER(TRIM(qs_result)) = ?', ['qualified']);
            });
        }

        $apps = $query->orderBy('created_at', 'asc')->get();
        Log::info("Export Debug: Found {$apps->count()} applications for {$type} report of {$vacancy_id}");

        foreach ($apps as $app) {
            $user = $app->user;
            $pi = $app->personalInformation;
            
            $fullName = $pi 
                ? strtoupper("{$pi->surname}, {$pi->first_name} " . ($pi->middle_name ? strtoupper(substr($pi->middle_name, 0, 1)) . '. ' : '') . "{$pi->name_extension}")
                : strtoupper($user->name ?? 'Unknown Applicant');
            
            $pdsData = $this->getApplicantPdsSummary($app->user_id);
            
            $data['applications'][] = array_merge([
                'full_name' => $fullName,
                'is_qualified' => (strtoupper($app->status) === 'QUALIFIED' || strtoupper($app->qs_result) === 'QUALIFIED'),
            ], $pdsData);
        }

        // Return HTML Preview
        if ($request->has('html')) {
            $view = ($type === 'qualified') ? 'admin.reports.final_selection_html' : 'admin.reports.list_of_applicants_html';
            return view($view, $data);
        }

        // Return PDF
        if ($request->has('pdf')) {
            $view = ($type === 'qualified') ? 'admin.reports.final_selection_html' : 'admin.reports.list_of_applicants_html';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, $data);
            $filename = ($type === 'qualified' ? 'FinalSelectionLineup_' : 'ListOfApplicants_') . $vacancy_id . '.pdf';
            return $pdf->download($filename);
        }

        // Return Word (Fallback/Legacy)
        return $this->generateWordExport($vacancy, $data, $type);
    }

    private function generateWordExport($vacancy, $data, $type)
    {
        $templateFile = ($type === 'qualified') ? 'Blank FINAL SELECTION LINE-UP.docx' : 'Blank LIST OF APPLICANTS.docx';
        $templatePath = resource_path('templates/' . $templateFile);

        if (!file_exists($templatePath)) {
            return back()->with('error', "Template file not found: {$templateFile}");
        }

        try {
            $templateProcessor = new TemplateProcessor($templatePath);
            $vacancy_id = $vacancy->vacancy_id;

            // Set Vacancy Info
            $templateProcessor->setValue('position_title', strtoupper($vacancy->position_title));
            $templateProcessor->setValue('item_no', $vacancy->plantilla_item_no ?: 'N/A');
            $templateProcessor->setValue('salary_grade', $vacancy->salary_grade ?: 'N/A');
            $templateProcessor->setValue('monthly_salary', number_format($vacancy->monthly_salary, 2));
            $templateProcessor->setValue('office', strtoupper($vacancy->place_of_assignment));
            $templateProcessor->setValue('closing_date', \Carbon\Carbon::parse($vacancy->closing_date)->format('F j, Y'));
            $templateProcessor->setValue('pcn_no', $vacancy->pcn_no ?: 'N/A');
            $templateProcessor->setValue('date_published', $vacancy->created_at->format('F j, Y'));
            
            // Signatory Aliases
            $sigMap = [
                'admin_name' => $data['admin_name'],
                'admin_designation' => $data['admin_designation'],
                'prepared_by_name' => $data['admin_name'],
                'rd_name' => $data['rd_name'],
                'rd_designation' => $data['rd_designation'],
                'approved_by_name' => $data['rd_name'],
            ];

            foreach ($sigMap as $key => $val) {
                $templateProcessor->setValue($key, $val);
                $templateProcessor->setValue(strtoupper($key), $val);
            }

            if (count($data['applications']) > 0) {
                $availableVariables = $templateProcessor->getVariables();
                $cloneKey = null;
                $possibleRowKeys = ['name', 'fullName', 'full_name', 'n', 'num', 'applicant_name', 'applicant'];
                
                foreach ($possibleRowKeys as $key) {
                    if (in_array($key, $availableVariables)) { $cloneKey = $key; break; }
                }
                
                if ($cloneKey) {
                    $templateProcessor->cloneRow($cloneKey, count($data['applications']));
                    foreach ($data['applications'] as $index => $app) {
                        $rowIndex = $index + 1;
                        $templateProcessor->setValue("n#{$rowIndex}", $rowIndex);
                        $templateProcessor->setValue("num#{$rowIndex}", $rowIndex);
                        $templateProcessor->setValue("name#{$rowIndex}", $app['full_name']);
                        $templateProcessor->setValue("education#{$rowIndex}", $app['education']);
                        $templateProcessor->setValue("training#{$rowIndex}", $app['training']);
                        $templateProcessor->setValue("experience#{$rowIndex}", $app['experience']);
                        $templateProcessor->setValue("eligibility#{$rowIndex}", $app['eligibility']);
                        $templateProcessor->setValue("pwd#{$rowIndex}", $app['pwd']);
                        
                        if ($type === 'all') {
                            $status = $app['is_qualified'] ? 'QUALIFIED' : 'NOT QUALIFIED';
                            $templateProcessor->setValue("status#{$rowIndex}", $status);
                        }
                    }
                }
            }

            $filename = ($type === 'qualified' ? 'FinalSelectionLineup_' : 'ListOfApplicants_') . $vacancy_id . '.docx';
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) @mkdir($tempDir, 0777, true);
            $tempPath = $tempDir . DIRECTORY_SEPARATOR . $filename;
            $templateProcessor->saveAs($tempPath);

            return response()->download($tempPath, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error("Word Export Error: " . $e->getMessage());
            return back()->with('error', "An error occurred during Word export: " . $e->getMessage());
        }
    }

    private function getApplicantPdsSummary($userId)
    {
        $summary = [
            'education' => 'N/A',
            'training' => 'N/A',
            'experience' => 'N/A',
            'eligibility' => 'N/A',
            'pwd' => 'No',
        ];

        // Education
        $edu = EducationalBackground::where('user_id', $userId)->first();
        if ($edu) {
            $highest = 'N/A';
            if (!empty($edu->grad)) {
                $grad = is_array($edu->grad) ? $edu->grad : json_decode($edu->grad, true);
                if (!empty($grad[0]['grad_school'])) $highest = "GRADUATE STUDIES: " . $grad[0]['grad_school'];
            } elseif (!empty($edu->college)) {
                $college = is_array($edu->college) ? $edu->college : json_decode($edu->college, true);
                if (!empty($college[0]['college_school'])) $highest = "COLLEGE: " . $college[0]['college_school'];
            } elseif ($edu->shs_school) {
                $highest = "SHS: " . $edu->shs_school;
            }
            $summary['education'] = $highest;
        }

        // Training
        $trainings = LearningAndDevelopment::where('user_id', $userId)->orderByDesc('learning_to')->limit(3)->get();
        if ($trainings->count() > 0) {
            $summary['training'] = $trainings->map(fn($t) => $t->learning_title)->implode("\n");
        }

        // Experience
        $experiences = WorkExperience::where('user_id', $userId)->orderByDesc('work_exp_to')->limit(3)->get();
        if ($experiences->count() > 0) {
            $summary['experience'] = $experiences->map(fn($e) => $e->work_exp_position)->implode("\n");
        }

        // Eligibility
        $eligibilities = CivilServiceEligibility::where('user_id', $userId)->orderByDesc('cs_eligibility_date')->get();
        if ($eligibilities->count() > 0) {
            $summary['eligibility'] = $eligibilities->map(fn($e) => $e->cs_eligibility_name)->implode("\n");
        }

        // PWD Status
        $misc = MiscInfos::where('user_id', $userId)->first();
        if ($misc) {
            $pwdValue = trim((string)$misc->pwd_40_b);
            if (strcasecmp($pwdValue, 'yes') === 0 || $pwdValue === '1' || $pwdValue === 'true') {
                $summary['pwd'] = 'Yes';
            }
        }

        return $summary;
    }
}
