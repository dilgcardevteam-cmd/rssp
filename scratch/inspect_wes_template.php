<?php

require __DIR__ . '/../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

$pdf = new Fpdi();
$path = __DIR__ . '/../resources/templates/WES_Template.pdf';

if (!file_exists($path)) {
    fwrite(STDERR, "Missing template: {$path}\n");
    exit(1);
}

$pageCount = $pdf->setSourceFile($path);
$templateId = $pdf->importPage(1);
$size = $pdf->getTemplateSize($templateId);

echo "Pages: {$pageCount}\n";
var_export($size);
echo "\n";
