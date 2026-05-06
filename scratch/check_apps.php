<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Applications;
use App\Models\JobVacancy;

$vacancy_id = 'AOIII-044';
$vacancy = JobVacancy::where('vacancy_id', $vacancy_id)->first();

if (!$vacancy) {
    echo "Vacancy not found\n";
    exit;
}

$apps = Applications::where('vacancy_id', $vacancy_id)->get();
echo "Total Applications for {$vacancy_id}: " . $apps->count() . "\n";

foreach ($apps as $app) {
    echo "Applicant ID: {$app->id}, User ID: {$app->user_id}, Status: '{$app->status}', QS Result: '{$app->qs_result}'\n";
}
