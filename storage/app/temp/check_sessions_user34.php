<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select("SELECT id, user_id, last_activity, payload FROM sessions WHERE user_id = 34 ORDER BY last_activity DESC LIMIT 5");
if (empty($rows)) {
    echo "No sessions for user 34" . PHP_EOL;
    exit;
}

foreach ($rows as $idx => $row) {
    echo "--- SESSION #" . ($idx + 1) . " id=" . $row->id . " last_activity=" . date('Y-m-d H:i:s', (int)$row->last_activity) . PHP_EOL;
    $decoded = base64_decode($row->payload, true);
    if ($decoded === false) {
        echo "payload base64 decode failed" . PHP_EOL;
        continue;
    }

    $data = @unserialize($decoded);
    if (!is_array($data)) {
        echo "payload unserialize failed or not array" . PHP_EOL;
        continue;
    }

    $found = false;
    foreach ($data as $key => $value) {
        if (strpos((string)$key, 'initial_assessment_answers') === 0) {
            $found = true;
            echo "key=" . $key . PHP_EOL;
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if (is_array($v)) {
                        echo "  " . $k . "=" . json_encode($v) . PHP_EOL;
                    } else {
                        echo "  " . $k . "=" . var_export($v, true) . PHP_EOL;
                    }
                }
            } else {
                echo "  value=" . var_export($value, true) . PHP_EOL;
            }
        }
    }
    if (!$found) {
        echo "no initial_assessment_answers keys in this session" . PHP_EOL;
    }
}
