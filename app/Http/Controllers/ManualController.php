<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class ManualController extends Controller
{
    public function applicantManual()
    {
        return view('manual.user', [
            'manualTitle' => 'Applicant Manual',
            'manualHtml' => $this->renderManual('docs/manual/applicant-manual.html'),
        ]);
    }

    public function adminManual()
    {
        $role = (string) (Auth::guard('admin')->user()->role ?? 'admin');

        $fileByRole = [
            'viewer' => 'docs/manual/viewer-manual.html',
            'hr_division' => 'docs/manual/hr-division-manual.html',
            'superadmin' => 'docs/manual/admin-manual.html',
            'admin' => 'docs/manual/admin-manual.html',
        ];

        $titleByRole = [
            'viewer' => 'Viewer Manual',
            'hr_division' => 'HR Division Manual',
            'superadmin' => 'Admin Manual',
            'admin' => 'Admin Manual',
        ];

        $manualPath = $fileByRole[$role] ?? 'docs/manual/admin-manual.html';
        $manualTitle = $titleByRole[$role] ?? 'Admin Manual';

        return view('manual.admin', [
            'manualTitle' => $manualTitle,
            'manualHtml' => $this->renderManual($manualPath),
        ]);
    }

    private function renderManual(string $relativePath): string
    {
        $fullPath = base_path($relativePath);

        if (!is_file($fullPath)) {
            return '<h2>Manual Not Found</h2><p>The requested manual file is missing.</p>';
        }

        $content = file_get_contents($fullPath);
        if (!is_string($content) || trim($content) === '') {
            return '<h2>Manual Not Available</h2><p>The manual file is empty.</p>';
        }

        return $content;
    }
}
