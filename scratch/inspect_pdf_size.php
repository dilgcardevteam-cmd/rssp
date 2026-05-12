<?php

require __DIR__ . '/../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

$path = $argv[1] ?? '';
if ($path === '') {
    fwrite(STDERR, "Usage: php inspect_pdf_size.php <pdfPath>\n");
    exit(2);
}
if (!file_exists($path)) {
    fwrite(STDERR, "Missing: {$path}\n");
    exit(1);
}

$pdf = new Fpdi();
$pageCount = $pdf->setSourceFile($path);
$templateId = $pdf->importPage(1);
$size = $pdf->getTemplateSize($templateId);

echo "File: {$path}\n";
echo "Pages: {$pageCount}\n";
var_export($size);
echo "\n";
