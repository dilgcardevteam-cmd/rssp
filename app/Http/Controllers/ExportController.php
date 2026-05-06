<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\JobVacancy;
use App\Models\Applications;

class ExportController extends Controller
{
    public function exportCOS()
    {
    $timestamp = now()->format('Y-m-d_H-i-s');
    $fileName = "job_vacancies(COS)_{$timestamp}.csv";
    $jobVacancies = JobVacancy::where('vacancy_type', 'COS')->orderBy('id');

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $columns = [        
            'Position Title',
            'Vacancy Type',
            'Closing Date',
            'Status',
            'Monthly Salary',
            'Place of Assignment',
            'Qualification Education',
            'Qualification Training',
            'Qualification Experience',
            'Qualification Eligibility',
            'Expected Output',
            'Scope of Work',
            'Duration of Work',
            'to Person',
            'to Position',
            'to Office',
            'to Office Address',
        ];

        $callback = function() use ($jobVacancies, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($jobVacancies->cursor() as $vacancy) {
                fputcsv($file, [
                    $vacancy->position_title,
                    $vacancy->vacancy_type,
                    $vacancy->closing_date,
                    $vacancy->status,
                    $vacancy->monthly_salary,
                    $vacancy->place_of_assignment,
                    $vacancy->qualification_education,
                    $vacancy->qualification_training,
                    $vacancy->qualification_experience,
                    $vacancy->qualification_eligibility,
                    $vacancy->expected_output,
                    $vacancy->scope_of_work,
                    $vacancy->duration_of_work,
                    $vacancy->to_person,
                    $vacancy->to_position,
                    $vacancy->to_office,
                    $vacancy->to_office_address,
                ]);
            }

            fclose($file);
        };

        activity()
            ->causedBy(auth('admin')->user())
            ->withProperties(['section' => 'Vacancies Management'])
            ->event('export')
            ->log('Exported job vacancies of type COS');

        return response()->stream($callback, 200, $headers);
    }

    public function exportPlantilla()
    {
    $timestamp = now()->format('Y-m-d_H-i-s');
    $fileName = "job_vacancies(Plantilla)_{$timestamp}.csv";
    $jobVacancies = JobVacancy::where('vacancy_type', 'Plantilla')->orderBy('id');

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $columns = [        
            'Position Title',
            'Vacancy Type',
            'PCN No.',
            'Plantilla Item No',
            'Closing Date',
            'Status',
            'Monthly Salary',
            'Salary Grade',
            'Place of Assignment',
            'Qualification Education',
            'Qualification Training',
            'Qualification Experience',
            'Qualification Eligibility',
            'Competencies',
            'to Person',
            'to Position',
            'to Office',
            'to Office Address',
        ];

        $callback = function() use ($jobVacancies, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($jobVacancies->cursor() as $vacancy) {
                fputcsv($file, [
                    $vacancy->position_title,
                    $vacancy->vacancy_type,
                    $vacancy->pcn_no,
                    $vacancy->plantilla_item_no,
                    $vacancy->closing_date,
                    $vacancy->status,
                    $vacancy->monthly_salary,
                    $vacancy->salary_grade,
                    $vacancy->place_of_assignment,
                    $vacancy->qualification_education,
                    $vacancy->qualification_training,
                    $vacancy->qualification_experience,
                    $vacancy->qualification_eligibility,
                    $vacancy->competencies,
                    $vacancy->to_person,
                    $vacancy->to_position,
                    $vacancy->to_office,
                    $vacancy->to_office_address,
                ]);
            }

            fclose($file);
        };

        activity()
            ->causedBy(auth('admin')->user())
            ->withProperties(['section' => 'Vacancies Management'])
            ->event('export')
            ->log('Exported job vacancies of type Plantilla');

        return response()->stream($callback, 200, $headers);
    }

    public function exportAllVacancies()
    {
    $timestamp = now()->format('Y-m-d_H-i-s');
    $fileName = "job_vacancies(All)_{$timestamp}.csv";
    $jobVacancies = JobVacancy::query()->orderBy('id');

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $columns = [        
            'Position Title',
            'Vacancy Type',
            'PCN No.',
            'Plantilla Item No',
            'Closing Date',
            'Status',
            'Monthly Salary',
            'Salary Grade',
            'Place of Assignment',
            'Qualification Education',
            'Qualification Training',
            'Qualification Experience',
            'Qualification Eligibility',
            'Competencies',
            'Expected Output',
            'Scope of Work',
            'Duration of Work',
            'to Person',
            'to Position',
            'to Office',
            'to Office Address',
        ];

        $callback = function() use ($jobVacancies, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($jobVacancies->cursor() as $vacancy) {
                fputcsv($file, [
                    $vacancy->position_title,
                    $vacancy->vacancy_type,
                    $vacancy->pcn_no,
                    $vacancy->plantilla_item_no,
                    $vacancy->closing_date,
                    $vacancy->status,
                    $vacancy->monthly_salary,
                    $vacancy->salary_grade,
                    $vacancy->place_of_assignment,
                    $vacancy->qualification_education,
                    $vacancy->qualification_training,
                    $vacancy->qualification_experience,
                    $vacancy->qualification_eligibility,
                    $vacancy->competencies,
                    $vacancy->expected_output,
                    $vacancy->scope_of_work,
                    $vacancy->duration_of_work,
                    $vacancy->to_person,
                    $vacancy->to_position,
                    $vacancy->to_office,
                    $vacancy->to_office_address,
                ]);
            }

            fclose($file);
        };

        activity()
            ->causedBy(auth('admin')->user())
            ->withProperties(['section' => 'Vacancies Management'])
            ->event('export')
            ->log('Exported job vacancies of type All');

        return response()->stream($callback, 200, $headers);
    }

    public function exportActivities()
    {
    $timestamp = now()->format('Y-m-d_H-i-s');
    $fileName = "activity_logs_{$timestamp}.csv";
    $activities = Activity::query()->orderByDesc('id'); // You can add filters if needed

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $columns = [
            'Log Name',
            'Description',
            'Event',
            'Subject Type',
            'Subject ID',
            'Causer Type',
            'Causer ID',
            'Properties',
            'Created At',
        ]; 
        

        $callback = function () use ($activities, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($activities->cursor() as $activity) {
                $target = 'N/A';

                if ($activity->subject) {
                    if (isset($activity->subject->name)) {
                        $target = $activity->subject->name;
                    } elseif (isset($activity->subject->position_title)) {
                        $target = $activity->subject->position_title;
                    } elseif (isset($activity->subject->title)) {
                        $target = $activity->subject->title;
                    } elseif (isset($activity->subject->id)) {
                        $target = 'ID: ' . $activity->subject->id;
                    }
                }

                // Check fallback in properties if subject is null or lacks name
                if ($target === 'N/A' && isset($activity->properties['position_title'])) {
                    $target = $activity->properties['position_title'];
                }

                fputcsv($file, [
                    $activity->id,
                    $activity->created_at->format('Y-m-d H:i:s'),
                    optional($activity->causer)->name ?? 'N/A',
                    $activity->properties['section'] ?? 'N/A',
                    $activity->description ?? 'N/A',
                    $target,
                ]);
            }

            fclose($file);
        };

        activity()
            ->causedBy(auth('admin')->user())
            ->withProperties(['section' => 'Activity Log'])
            ->event('export')
            ->log('Exported Activity Log');

        return response()->stream($callback, 200, $headers);
    }

public function exportReviewedApplications($vacancy_id)
{
    $timestamp = now()->format('Y-m-d_H-i-s');
    $fileName = "reviewed_applications_{$timestamp}.csv";
    $applications = Applications::query()
        ->where('status', '!=', 'Pending')
        ->where('vacancy_id', $vacancy_id)
        ->orderBy('id');

    $headers = [
        "Content-Type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
    ];

    $columns = [
        'User ID',
        'Updated By Admin ID',
        'Vacancy ID',
        'Status',
        'Result',
        'Answers',
        'Scores',
        'Is Valid',
        'Deadline Date',
        'Deadline Time',
        'File Original Name',
        'File Stored Name',
        'File Storage Path',
        'File Remarks',
        'File Status',
        'File Size (8b)',
        'QS Education',
        'QS Eligibility',
        'QS Experience',
        'QS Training',
        'QS Result',
        'Application Remarks',
    ];

    $callback = function () use ($applications, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($applications->cursor() as $app) {
            fputcsv($file, [
                $app->user_id,
                $app->updated_by_admin_id,
                $app->vacancy_id,
                $app->status,
                $app->result,
                $app->answers,
                $app->scores,
                $app->is_valid,
                $app->deadline_date,
                $app->deadline_time,
                $app->file_original_name,
                $app->file_stored_name,
                $app->file_storage_path,
                $app->file_remarks,
                $app->file_status,
                $app->file_size_8b,
                $app->qs_education,
                $app->qs_eligibility,
                $app->qs_experience,
                $app->qs_training,
                $app->qs_result,
                $app->application_remarks,
            ]);
        }

        fclose($file);
    };

        activity()
            ->causedBy(auth('admin')->user())
            ->withProperties(['section' => 'Applications List'], ['vacancy_id' => $vacancy_id])
            ->performedOn(JobVacancy::where('vacancy_id', $vacancy_id)->first())
            ->event('export')
            ->log('Exported Reviewed Applications for Vacancy ID: ' . $vacancy_id);

    return response()->stream($callback, 200, $headers);
}

public function exportNotReviewedApplications($vacancy_id)
{
    $timestamp = now()->format('Y-m-d_H-i-s');
    $fileName = "not_reviewed_applications_{$timestamp}.csv";
    $applications = Applications::query()
        ->where('status', 'Pending')
        ->where('vacancy_id', $vacancy_id)
        ->orderBy('id');


    $headers = [
        "Content-Type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
    ];

    $columns = [
        'User ID',
        'Updated By Admin ID',
        'Vacancy ID',
        'Status',
        'Result',
        'Answers',
        'Scores',
        'Is Valid',
        'Deadline Date',
        'Deadline Time',
        'File Original Name',
        'File Stored Name',
        'File Storage Path',
        'File Remarks',
        'File Status',
        'File Size (8b)',
        'QS Education',
        'QS Eligibility',
        'QS Experience',
        'QS Training',
        'QS Result',
        'Application Remarks',
    ];

    $callback = function () use ($applications, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($applications->cursor() as $app) {
            fputcsv($file, [
                $app->user_id,
                $app->updated_by_admin_id,
                $app->vacancy_id,
                $app->status,
                $app->result,
                $app->answers,
                $app->scores,
                $app->is_valid,
                $app->deadline_date,
                $app->deadline_time,
                $app->file_original_name,
                $app->file_stored_name,
                $app->file_storage_path,
                $app->file_remarks,
                $app->file_status,
                $app->file_size_8b,
                $app->qs_education,
                $app->qs_eligibility,
                $app->qs_experience,
                $app->qs_training,
                $app->qs_result,
                $app->application_remarks,
            ]);
        }

        fclose($file);
    };

        activity()
            ->causedBy(auth('admin')->user())
            ->withProperties(['section' => 'Applications List'], ['vacancy_id' => $vacancy_id])
            ->performedOn(JobVacancy::where('vacancy_id', $vacancy_id)->first())
            ->event('export')
            ->log('Exported Not Reviewed Applications for Vacancy ID: ' . $vacancy_id);

    return response()->stream($callback, 200, $headers);
}


}
