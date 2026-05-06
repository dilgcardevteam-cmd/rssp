<?php
$path = 'c:\xampp\htdocs\DILG-CAR\resources\templates\Blank FINAL SELECTION LINE-UP.docx';
$zip = new ZipArchive();
$placeholders = [];
if ($zip->open($path) === true) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    preg_match_all('/\$\{[^}]+\}/', $xml, $matches);
    $placeholders = array_unique($matches[0]);
}
file_put_contents('c:\xampp\htdocs\DILG-CAR\brain\b5df39ce-5b41-43b9-a737-3c3276fb6709\scratch\placeholders_final.txt', implode("\n", $placeholders));

$path = 'c:\xampp\htdocs\DILG-CAR\resources\templates\Blank LIST OF APPLICANTS.docx';
$zip = new ZipArchive();
$placeholders = [];
if ($zip->open($path) === true) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();
    preg_match_all('/\$\{[^}]+\}/', $xml, $matches);
    $placeholders = array_unique($matches[0]);
}
file_put_contents('c:\xampp\htdocs\DILG-CAR\brain\b5df39ce-5b41-43b9-a737-3c3276fb6709\scratch\placeholders_list.txt', implode("\n", $placeholders));
