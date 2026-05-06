<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select("SELECT a.id, a.vacancy_id, j.vacancy_type, a.initial_assessment_degree, a.initial_assessment_eligibility, a.initial_assessment_q1_passed, a.initial_assessment_q2_passed, a.initial_assessment_has_pqe, a.created_at FROM applications a LEFT JOIN job_vacancies j ON j.vacancy_id = a.vacancy_id WHERE a.id BETWEEN 19 AND 28 ORDER BY a.id");
foreach ($rows as $r) {
    echo 'id=' . $r->id
        . ' vacancy=' . $r->vacancy_id
        . ' type=' . ($r->vacancy_type ?? 'NULL')
        . ' q1=' . (is_null($r->initial_assessment_q1_passed) ? 'NULL' : $r->initial_assessment_q1_passed)
        . ' q2=' . (is_null($r->initial_assessment_q2_passed) ? 'NULL' : $r->initial_assessment_q2_passed)
        . ' pqe=' . (is_null($r->initial_assessment_has_pqe) ? 'NULL' : $r->initial_assessment_has_pqe)
        . ' degree=' . (is_null($r->initial_assessment_degree) ? 'NULL' : $r->initial_assessment_degree)
        . ' eligibility=' . (is_null($r->initial_assessment_eligibility) ? 'NULL' : $r->initial_assessment_eligibility)
        . ' created=' . $r->created_at
        . PHP_EOL;
}
