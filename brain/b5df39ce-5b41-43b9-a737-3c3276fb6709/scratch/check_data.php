<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Applications;
use App\Models\JobVacancy;

$vacancies = JobVacancy::limit(5)->get();
foreach ($vacancies as $v) {
    $count = Applications::where('vacancy_id', $v->vacancy_id)->count();
    echo "Vacancy {$v->vacancy_id}: {$count} applications\n";
    if ($count > 0) {
        $app = Applications::where('vacancy_id', $v->vacancy_id)->first();
        echo "  First app status: {$app->status}, qs_result: {$app->qs_result}\n";
    }
}
