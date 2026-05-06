<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = app(App\Http\Controllers\JobVacancyController::class);
$buildRule = new ReflectionMethod($controller, 'buildCompiledEducationRule');
$buildRule->setAccessible(true);

$rows = App\Models\JobVacancy::query()
    ->select(['id', 'vacancy_id', 'position_title', 'qualification_education', 'status'])
    ->orderBy('id')
    ->get();

$summary = [
    'total' => 0,
    'high' => 0,
    'medium' => 0,
    'low' => 0,
    'null' => 0,
];

$ruleCounts = [];
$phraseGroups = [];
$rowsOut = [];

foreach ($rows as $row) {
    $text = trim((string)($row->qualification_education ?? ''));
    $rule = $buildRule->invoke($controller, $text);

    $summary['total']++;
    if (!is_array($rule)) {
        $summary['null']++;
        $confidence = 'null';
        $ruleCode = 'null';
        $requiredFields = '';
    } else {
        $confidence = (string)($rule['confidence'] ?? 'low');
        $ruleCode = (string)($rule['rule_code'] ?? 'unknown_text');
        $requiredFields = implode(', ', (array)($rule['required_fields'] ?? []));
        if (!isset($summary[$confidence])) {
            $summary[$confidence] = 0;
        }
        $summary[$confidence]++;
    }

    if (!isset($ruleCounts[$ruleCode])) {
        $ruleCounts[$ruleCode] = 0;
    }
    $ruleCounts[$ruleCode]++;

    $phraseKey = strtolower(trim(preg_replace('/\s+/', ' ', $text)));
    if (!isset($phraseGroups[$phraseKey])) {
        $phraseGroups[$phraseKey] = [
            'text' => $text,
            'count' => 0,
            'rule_code' => $ruleCode,
            'confidence' => $confidence,
            'required_fields' => $requiredFields,
            'examples' => [],
        ];
    }
    $phraseGroups[$phraseKey]['count']++;
    if (count($phraseGroups[$phraseKey]['examples']) < 3) {
        $phraseGroups[$phraseKey]['examples'][] = [
            'vacancy_id' => (string)$row->vacancy_id,
            'position_title' => (string)$row->position_title,
            'status' => (string)$row->status,
        ];
    }

    $rowsOut[] = [
        'id' => (int)$row->id,
        'vacancy_id' => (string)$row->vacancy_id,
        'position_title' => (string)$row->position_title,
        'status' => (string)$row->status,
        'text' => $text,
        'rule_code' => $ruleCode,
        'confidence' => $confidence,
        'required_fields' => $requiredFields,
    ];
}

arsort($ruleCounts);

usort($rowsOut, function($a, $b) {
    $weight = ['low' => 0, 'null' => 0, 'medium' => 1, 'high' => 2];
    $wa = $weight[$a['confidence']] ?? 0;
    $wb = $weight[$b['confidence']] ?? 0;
    if ($wa !== $wb) return $wa <=> $wb;
    return strcmp($a['vacancy_id'], $b['vacancy_id']);
});

$phraseGroupsList = array_values($phraseGroups);
usort($phraseGroupsList, function($a, $b) {
    $weight = ['low' => 0, 'null' => 0, 'medium' => 1, 'high' => 2];
    $wa = $weight[$a['confidence']] ?? 0;
    $wb = $weight[$b['confidence']] ?? 0;
    if ($wa !== $wb) return $wa <=> $wb;
    if ($a['count'] !== $b['count']) return $b['count'] <=> $a['count'];
    return strcmp($a['text'], $b['text']);
});

$result = [
    'summary' => $summary,
    'rule_counts' => $ruleCounts,
    'rows' => $rowsOut,
    'phrase_groups' => $phraseGroupsList,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
