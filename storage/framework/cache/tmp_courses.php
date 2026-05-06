<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$table = Illuminate\Support\Facades\Schema::hasTable('course_preset') ? 'course_preset' : (Illuminate\Support\Facades\Schema::hasTable('course_presets') ? 'course_presets' : null);
if (!$table) { echo "NO_TABLE\n"; exit; }
$rows = Illuminate\Support\Facades\DB::table($table)->orderBy('course_name')->limit(60)->pluck('course_name');
foreach ($rows as $name) { echo $name . PHP_EOL; }
