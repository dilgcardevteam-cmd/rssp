<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = App\Models\JobVacancy::query()
    ->select('vacancy_id','position_title','qualification_education')
    ->where('qualification_education','like','%bachelor%')
    ->orderBy('updated_at','desc')
    ->take(30)
    ->get();
foreach ($rows as $v) {
    echo $v->vacancy_id . '|' . $v->position_title . '|' . $v->qualification_education . PHP_EOL;
}
