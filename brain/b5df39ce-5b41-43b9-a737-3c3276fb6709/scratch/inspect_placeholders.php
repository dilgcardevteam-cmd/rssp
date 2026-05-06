<?php
require 'vendor/autoload.php';

function getPlaceholders($path) {
    $zip = new ZipArchive();
    if ($zip->open($path) === true) {
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        
        preg_match_all('/\$\{[^}]+\}/', $xml, $matches);
        return array_unique($matches[0]);
    }
    return [];
}

$templates = [
    'c:\xampp\htdocs\DILG-CAR\resources\templates\Blank FINAL SELECTION LINE-UP.docx',
    'c:\xampp\htdocs\DILG-CAR\resources\templates\Blank LIST OF APPLICANTS.docx'
];

foreach ($templates as $template) {
    echo "Placeholders for " . basename($template) . ":\n";
    $placeholders = getPlaceholders($template);
    foreach ($placeholders as $p) {
        echo "  $p\n";
    }
    echo "\n";
}
