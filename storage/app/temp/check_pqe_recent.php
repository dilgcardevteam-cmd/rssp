<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo 'LATEST APPLICATIONS (pending)' . PHP_EOL;
$rows = DB::select("SELECT id, user_id, vacancy_id, initial_assessment_has_pqe, created_at FROM applications WHERE LOWER(TRIM(status))='pending' ORDER BY created_at DESC LIMIT 20");
foreach ($rows as $r) {
    $v = is_null($r->initial_assessment_has_pqe) ? 'NULL' : (string)$r->initial_assessment_has_pqe;
    echo 'id=' . $r->id . ' user=' . $r->user_id . ' vacancy=' . $r->vacancy_id . ' pqe=' . $v . ' created_at=' . $r->created_at . PHP_EOL;
}
