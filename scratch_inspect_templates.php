<?php
require 'vendor/autoload.php';
use PhpOffice\PhpWord\TemplateProcessor;

$templates = [
    'resources/templates/Blank FINAL SELECTION LINE-UP.docx',
    'resources/templates/Blank LIST OF APPLICANTS.docx'
];

foreach ($templates as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "File: $file\n";
        try {
            $tp = new TemplateProcessor($path);
            print_r($tp->getVariables());
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
        echo "\n-------------------\n";
    } else {
        echo "File not found: $file\n";
    }
}
