<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo 'HAS_COLUMN=' . (Schema::hasColumn('applications', 'initial_assessment_has_pqe') ? 'YES' : 'NO') . PHP_EOL;

echo PHP_EOL . 'GLOBAL DISTRIBUTION' . PHP_EOL;
$rows = DB::select('SELECT initial_assessment_has_pqe, COUNT(*) AS c FROM applications GROUP BY initial_assessment_has_pqe ORDER BY initial_assessment_has_pqe IS NULL, initial_assessment_has_pqe');
foreach ($rows as $r) {
    $key = is_null($r->initial_assessment_has_pqe) ? 'NULL' : (string) $r->initial_assessment_has_pqe;
    echo $key . ':' . $r->c . PHP_EOL;
}

echo PHP_EOL . 'PENDING PER VACANCY' . PHP_EOL;
$rows2 = DB::select("SELECT vacancy_id, COUNT(*) AS total, SUM(CASE WHEN initial_assessment_has_pqe = 1 THEN 1 ELSE 0 END) AS yes_cnt, SUM(CASE WHEN initial_assessment_has_pqe = 0 THEN 1 ELSE 0 END) AS no_cnt, SUM(CASE WHEN initial_assessment_has_pqe IS NULL THEN 1 ELSE 0 END) AS null_cnt FROM applications WHERE LOWER(TRIM(status)) = 'pending' GROUP BY vacancy_id ORDER BY vacancy_id");
foreach ($rows2 as $r) {
    echo $r->vacancy_id . ' | total=' . $r->total . ' yes=' . $r->yes_cnt . ' no=' . $r->no_cnt . ' null=' . $r->null_cnt . PHP_EOL;
}
