<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$v = App\Models\JobVacancy::query()->where('vacancy_id', 'AOIII-034')->first();
if (!$v) { echo "missing\n"; exit; }
echo 'vacancy_id=' . $v->vacancy_id . PHP_EOL;
echo 'education=' . $v->qualification_education . PHP_EOL;
echo 'eligibility=' . $v->qualification_eligibility . PHP_EOL;
