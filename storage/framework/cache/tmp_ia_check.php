<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$vacancy = App\Models\JobVacancy::query()->where('vacancy_id', 'AOIII-034')->first();
if (!$vacancy) {
    echo "vacancy AOIII-034 not found\n";
    exit(0);
}

$controller = $app->make(App\Http\Controllers\JobVacancyController::class);
$ref = new ReflectionClass($controller);
$method = $ref->getMethod('isInitialAssessmentEducationAligned');
$method->setAccessible(true);

$inputs = [
    'Community Development',
    'Information Technology',
    'First year college undergraduate',
    'Senior High School Graduate',
    'Bachelor of Science in Information Technology',
];

foreach ($inputs as $degree) {
    $result = (bool) $method->invoke($controller, $vacancy, $degree);
    echo $degree . ' => ' . ($result ? 'PASS' : 'FAIL') . PHP_EOL;
}
