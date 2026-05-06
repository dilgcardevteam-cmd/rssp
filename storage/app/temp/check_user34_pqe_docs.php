<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select("SELECT user_id, vacancy_id, document_type, status, original_name, created_at FROM uploaded_documents WHERE user_id = 34 AND document_type = 'pqe_result' ORDER BY vacancy_id, created_at");
if (empty($rows)) {
    echo "No pqe_result docs for user 34" . PHP_EOL;
} else {
    foreach ($rows as $r) {
        echo 'user=' . $r->user_id . ' vacancy=' . ($r->vacancy_id ?? 'NULL') . ' doc=' . $r->document_type . ' status=' . ($r->status ?? 'NULL') . ' created=' . $r->created_at . PHP_EOL;
    }
}
