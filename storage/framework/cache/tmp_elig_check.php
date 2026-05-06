<?php
require __DIR__ . '/../../../vendor/autoload.php';
$app = require __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$vacancy = App\Models\JobVacancy::query()->where('vacancy_id', 'AOIII-034')->first();
$controller = $app->make(App\Http\Controllers\JobVacancyController::class);
$ref = new ReflectionClass($controller);
$method = $ref->getMethod('isInitialAssessmentEligibilityAligned');
$method->setAccessible(true);

$inputs = [
    'CSC Professional Eligibility',
    'CS Professional/Second Level Eligibility',
    'Subprofessional Eligibility',
    'RA 1080',
];

foreach ($inputs as $input) {
    $result = (bool) $method->invoke($controller, $vacancy, $input);
    echo $input . ' => ' . ($result ? 'PASS' : 'FAIL') . PHP_EOL;
}
