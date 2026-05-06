<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function importCOS(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        try {
            $path = $request->file('import_file')->getRealPath();
            $file = fopen($path, 'r');

            // Skip header
            fgetcsv($file);

            while (($data = fgetcsv($file, 1000, ',')) !== FALSE) {
                \App\Models\JobVacancy::create([
                    'position_title' => $data[0],
                    'vacancy_type' => 'COS',
                    'closing_date' => $data[2],
                    'status' => $data[3],
                    'monthly_salary' => $data[4],
                    'salary_grade' => $data[5],
                    'place_of_assignment' => $data[6],
                    'qualification_education' => $data[7],
                    'qualification_training' => $data[8],
                    'qualification_experience' => $data[9],
                    'qualification_eligibility' => $data[10],
                    'competencies' => $data[11],
                    'expected_output' => $data[12],
                    'scope_of_work' => $data[13],
                    'duration_of_work' => $data[14],
                    'to_person' => $data[15],
                    'to_position' => $data[16],
                    'to_office' => $data[17],
                    'to_office_address' => $data[18],
                ]);
            }

            fclose($file);

            activity()
                ->causedBy(auth('admin')->user())
                ->withProperties(['section' => 'Vacancies Management'])
                ->event('import')
                ->log('Imported job vacancies of type COS');

            return redirect()->back()->with('success', 'COS vacancies imported successfully.');
        } catch (\Exception $e) {
            info($e->getMessage());
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function importPlantilla(Request $request)
    {
    $request->validate([
        'import_file' => 'required|mimes:csv,txt|max:2048',
    ]);

    try {
        $path = $request->file('import_file')->getRealPath();
        $file = fopen($path, 'r');

        // Skip header
        fgetcsv($file);

        while (($data = fgetcsv($file, 1000, ',')) !== FALSE) {
            \App\Models\JobVacancy::create([
                'position_title' => $data[0],
                'vacancy_type' => 'Plantilla',
                'pcn_no' => $data[2],
                'plantilla_item_no' => $data[3],
                'closing_date' => $data[4],
                'status' => $data[5],
                'monthly_salary' => $data[6],
                'salary_grade' => $data[7],
                'place_of_assignment' => $data[8],
                'qualification_education' => $data[9],
                'qualification_training' => $data[10],
                'qualification_experience' => $data[11],
                'qualification_eligibility' => $data[12],
                'competencies' => $data[13],
                'expected_output' => $data[14],
                'scope_of_work' => $data[15],
                'duration_of_work' => $data[16],
                'to_person' => $data[17],
                'to_position' => $data[18],
                'to_office' => $data[19],
                'to_office_address' => $data[20],
            ]);
        }

        fclose($file);

            activity()
                ->causedBy(auth('admin')->user())
                ->withProperties(['section' => 'Vacancies Management'])
                ->event('import')
                ->log('Imported job vacancies of type Plantilla');

        return redirect()->back()->with('success', 'Plantilla vacancies imported successfully.');
    } catch (\Exception $e) {
        info($e->getMessage());
        return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
    }
}

    public function downloadCOSTemplate()
    {
        $fileName = 'COS_vacancies_template.csv';

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

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fclose($file);
        };

            activity()
                ->causedBy(auth('admin')->user())
                ->withProperties(['section' => 'Vacancies Management'])
                ->event('download')
                ->log('Download template for job vacancies of type COS');

        return response()->stream($callback, 200, $headers);
    }

    public function downloadPlantillaTemplate()
    {
        $fileName = 'Plantilla_vacancies_template.csv';

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

        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fclose($file);
        };

        activity()
            ->causedBy(auth('admin')->user())
            ->withProperties(['section' => 'Vacancies Management'])
            ->event('download')
            ->log('Download template for job vacancies of type Plantilla');

        return response()->stream($callback, 200, $headers);
    }

}
