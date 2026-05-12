<?php

$paths = [
    __DIR__ . '/../resources/templates/WES_Template.pdf',
    __DIR__ . '/../resources/templates/work_experience_template.pdf',
];

$needles = [
    'WORK EXPERIENCE SHEET',
    'Attachment to CS Form No. 212',
    'Instructions',
    'Sample',
];

foreach ($paths as $path) {
    echo basename($path) . "\n";
    $content = @file_get_contents($path);
    if (!is_string($content)) {
        echo "  unreadable\n\n";
        continue;
    }
    echo '  bytes: ' . strlen($content) . "\n";
    foreach ($needles as $needle) {
        echo '  ' . $needle . ' => ' . (strpos($content, $needle) !== false ? 'yes' : 'no') . "\n";
    }
    echo "\n";
}
