<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Models\PersonalInformation;
use App\Models\WorkExperience;
use App\Models\WorkExpSheet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;
use setasign\Fpdi\Fpdi;

class ExportWESController extends Controller
{
    /**
     * Runtime metadata for the last WES rendering strategy used.
     *
     * @var array{mode:string,templatePath:?string,templateSource:?string}
     */
    private array $wesRenderMeta = [
        'mode' => 'unknown',
        'templatePath' => null,
        'templateSource' => null,
    ];

    public function exportWES(Request $request)
    {
        $user = Auth::guard('web')->user();
        abort_if(!$user, 401);

        $prepared = $this->prepareWesData($user->id, $user);
        $fullName = $prepared['full_name'];
        $experiences = $prepared['experiences'];

        $pdf = $this->buildWesPdf($fullName, $experiences);

        $timestamp = now()->format('Ymd_His');
        $filename = "WorkExperienceSheet_{$timestamp}.pdf";

        $forceInline = $request->boolean('preview') || $request->boolean('print');
        if ($request->boolean('download')) {
            $forceInline = false;
        }

        activity()
            ->causedBy($user)
            ->event('export')
            ->withProperties([
                'exported_file' => $filename,
                'entries_count' => $experiences->count(),
                'section' => 'Export',
                'format' => 'pdf',
            ])
            ->log('Exported Work Experience Sheet.');

        $content = $pdf->Output('S');

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($forceInline ? 'inline' : 'attachment') . '; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-WES-Renderer' => (string) ($this->wesRenderMeta['mode'] ?? 'unknown'),
            'X-WES-Guard' => 'web',
            'X-WES-User-ID' => (string) $user->id,
        ];

        if (!empty($this->wesRenderMeta['templateSource'])) {
            $headers['X-WES-Template-Source'] = (string) $this->wesRenderMeta['templateSource'];
        }

        $templatePath = $this->wesRenderMeta['templatePath'] ?? null;
        if (is_string($templatePath) && $templatePath !== '' && file_exists($templatePath)) {
            $templateHash = hash_file('sha256', $templatePath);
            if (is_string($templateHash) && $templateHash !== '') {
                $headers['X-WES-Template-SHA256'] = $templateHash;
            }
        }

        return response($content, 200, $headers);
    }

    public function previewWES()
    {
        return view('pds.wes_preview');
    }

    private function prepareWesData(int $userId, $user): array
    {
        $personalInfo = PersonalInformation::where('user_id', $userId)->first();
        $firstName = $personalInfo->first_name ?? ($user->first_name ?? '');
        $middleName = $personalInfo->middle_name ?? ($user->middle_name ?? '');
        $surname = $personalInfo->surname ?? ($user->last_name ?? '');
        $extension = $personalInfo->name_extension ?? ($user->name_extension ?? '');

        $middleInitial = $middleName ? strtoupper(mb_substr($middleName, 0, 1)) . '.' : '';
        $fullName = strtoupper(trim($firstName . ' ' . $middleInitial . ' ' . $surname));
        if (!empty($extension)) {
            $fullName .= ', ' . strtoupper($extension);
        }
        if (trim($fullName) === '') {
            $fullName = strtoupper($user->name ?? 'N/A');
        }

        $experiences = WorkExpSheet::where('user_id', $userId)
            ->where('isDisplayed', true)
            ->get();

        $experiences = $this->sortWesEntriesByStartDate($experiences, 'start_date');

        if ($experiences->isEmpty()) {
            $experiences = WorkExperience::where('user_id', $userId)
                ->get()
                ->map(function ($row) {
                    return (object) [
                        'start_date' => $row->work_exp_from,
                        'end_date' => $row->work_exp_to,
                        'position' => $row->work_exp_position,
                        'office' => $row->work_exp_department,
                        'supervisor' => '',
                        'agency' => $row->work_exp_department,
                        'accomplishments' => ['None specified'],
                        'duties' => ['None specified'],
                    ];
                });

            $experiences = $this->sortWesEntriesByStartDate($experiences, 'start_date');
        }

        if ($experiences->isEmpty()) {
            $experiences = collect([
                (object) [
                    'start_date' => null,
                    'end_date' => null,
                    'position' => 'N/A',
                    'office' => 'N/A',
                    'supervisor' => 'N/A',
                    'agency' => 'N/A',
                    'accomplishments' => ['N/A'],
                    'duties' => ['N/A'],
                ],
            ]);
        }

        return [
            'full_name' => $fullName,
            'experiences' => $experiences,
        ];
    }

    private function sortWesEntriesByStartDate(Collection $entries, string $field): Collection
    {
        $toTimestamp = function ($value): ?int {
            if ($value === null) {
                return null;
            }

            $text = trim((string) $value);
            if ($text === '' || strtoupper($text) === 'NOINPUT') {
                return null;
            }

            try {
                return Carbon::parse($text)->startOfDay()->getTimestamp();
            } catch (\Throwable $e) {
                return null;
            }
        };

        return $entries->sort(function ($a, $b) use ($field, $toTimestamp) {
            $aDate = $toTimestamp(data_get($a, $field));
            $bDate = $toTimestamp(data_get($b, $field));

            if ($aDate !== $bDate) {
                if ($aDate === null) {
                    return 1;
                }
                if ($bDate === null) {
                    return -1;
                }

                return $aDate <=> $bDate;
            }

            $aEnd = $toTimestamp(data_get($a, 'end_date'));
            $bEnd = $toTimestamp(data_get($b, 'end_date'));
            if ($aEnd !== $bEnd) {
                if ($aEnd === null) {
                    return 1;
                }
                if ($bEnd === null) {
                    return -1;
                }

                return $aEnd <=> $bEnd;
            }

            return ((int) data_get($a, 'id', 0)) <=> ((int) data_get($b, 'id', 0));
        })->values();
    }

    private function buildWesPdf(string $fullName, Collection $experiences): \FPDF
    {
        $allowPdfTemplateOverlay = filter_var(env('WES_ALLOW_PDF_TEMPLATE_OVERLAY', true), FILTER_VALIDATE_BOOL);
        if ($allowPdfTemplateOverlay) {
            $templatePdfCandidate = $this->resolveWesTemplatePdfCandidate();
            if (is_array($templatePdfCandidate)) {
                $templatePdfPath = (string) ($templatePdfCandidate['path'] ?? '');
                $templatePdfSource = (string) ($templatePdfCandidate['source'] ?? '');
                try {
                    $this->wesRenderMeta = [
                        'mode' => 'template_pdf_overlay',
                        'templatePath' => $templatePdfPath,
                        'templateSource' => $templatePdfSource !== '' ? $templatePdfSource : null,
                    ];
                    return $this->buildWesPdfFromTemplate($templatePdfPath, $fullName, $experiences);
                } catch (\Throwable $e) {
                    Log::warning('WES template-based export failed; falling back to legacy WES PDF renderer.', [
                        'error' => $e->getMessage(),
                        'template_pdf' => $templatePdfPath,
                    ]);
                }
            }
        } else {
            Log::info('WES PDF template overlay disabled; using legacy renderer when responsive DOCX conversion is unavailable.');
        }

        foreach ($this->resolveResponsiveWesDocxCandidates() as $candidate) {
            $responsiveDocxTemplatePath = $candidate['path'];
            $responsiveDocxTemplateSource = $candidate['source'];

            if (!$this->docxHasResponsiveWesPlaceholders($responsiveDocxTemplatePath)) {
                Log::warning('Skipping WES DOCX template without required placeholders.', [
                    'template_docx' => $responsiveDocxTemplatePath,
                ]);
                continue;
            }

            try {
                $this->wesRenderMeta = [
                    'mode' => 'responsive_docx',
                    'templatePath' => $responsiveDocxTemplatePath,
                    'templateSource' => $responsiveDocxTemplateSource,
                ];
                return $this->buildWesPdfFromResponsiveDocxTemplate($responsiveDocxTemplatePath, $fullName, $experiences);
            } catch (\Throwable $e) {
                Log::warning('Responsive WES DOCX template render failed; trying next fallback.', [
                    'error' => $e->getMessage(),
                    'template_docx' => $responsiveDocxTemplatePath,
                ]);
            }
        }

        // Fallback: legacy in-code renderer (kept for resilience when template conversion is unavailable).
        $this->wesRenderMeta = [
            'mode' => 'legacy_renderer',
            'templatePath' => null,
            'templateSource' => null,
        ];
        return $this->buildWesPdfLegacy($fullName, $experiences);
    }

    private function buildWesPdfFromResponsiveDocxTemplate(
        string $templateDocxPath,
        string $fullName,
        Collection $experiences
    ): \FPDF {
        $entries = $experiences->values();
        if ($entries->isEmpty()) {
            $entries = collect([(object) [
                'start_date' => null,
                'end_date' => null,
                'position' => 'N/A',
                'office' => 'N/A',
                'supervisor' => 'N/A',
                'agency' => 'N/A',
                'accomplishments' => ['N/A'],
                'duties' => ['N/A'],
            ]]);
        }

        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $tempDir = storage_path('app/temp');
        @mkdir($tempDir, 0777, true);

        foreach ($entries->values() as $index => $exp) {
            $templateProcessor = new TemplateProcessor($templateDocxPath);
            // Keep placeholder empty and place the name via PDF overlay for precise centering on the underline.
            $templateProcessor->setValue('name', '');
            $templateProcessor->setValue('date', '');

            $from = $this->formatMonthYear($exp->start_date);
            $to = $exp->end_date ? $this->formatMonthYear($exp->end_date) : 'Present';

            $this->setTemplateValueOnce($templateProcessor, 'from', $from !== '' ? $from : 'N/A');
            $this->setTemplateValueOnce($templateProcessor, 'to', $to !== '' ? $to : 'N/A');
            $this->setTemplateValueOnce($templateProcessor, 'position', trim((string) ($exp->position ?? '')) ?: 'N/A');
            $this->setTemplateValueOnce($templateProcessor, 'office', trim((string) ($exp->office ?? '')) ?: 'N/A');
            $this->setTemplateValueOnce($templateProcessor, 'supervisor', trim((string) ($exp->supervisor ?? '')) ?: 'N/A');
            $this->setTemplateValueOnce($templateProcessor, 'agency', trim((string) ($exp->agency ?? '')) ?: 'N/A');
            $this->setTemplateValueOnce(
                $templateProcessor,
                'accomplishments',
                $this->formatTemplateMultilineList($exp->accomplishments ?? [])
            );
            $this->setTemplateValueOnce(
                $templateProcessor,
                'duties',
                $this->formatTemplateMultilineList($exp->duties ?? [])
            );

            // Keep one rendered experience block per generated page and drop marker rows.
            $templateProcessor->setValue('experience', '', 1);
            $templateProcessor->setValue('/experience', '', 1);

            $token = uniqid('wes_runtime_' . $index . '_', true);
            $tempDocxPath = $tempDir . DIRECTORY_SEPARATOR . $token . '.docx';
            $tempPdfPath = $tempDir . DIRECTORY_SEPARATOR . $token . '.pdf';

            $templateProcessor->saveAs($tempDocxPath);
            $converted = $this->convertDocxTemplateToPdf($tempDocxPath, $tempPdfPath);
            if (!$converted || !file_exists($tempPdfPath)) {
                @unlink($tempDocxPath);
                @unlink($tempPdfPath);
                throw new \RuntimeException('DOCX to PDF conversion failed for responsive WES template.');
            }

            $pageCount = $pdf->setSourceFile($tempPdfPath);
            for ($page = 1; $page <= $pageCount; $page++) {
                $templateId = $pdf->importPage($page);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
                $overlayProfile = $this->resolveTemplateOverlayProfile($templateDocxPath);
                $this->overlayWesFooterDate($pdf, $overlayProfile);
                $this->overlayWesSignatureName($pdf, $fullName);
            }

            @unlink($tempDocxPath);
            @unlink($tempPdfPath);
        }

        return $pdf;
    }

    private function buildWesPdfFromTemplate(string $templatePdfPath, string $fullName, Collection $experiences): \FPDF
    {
        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->setSourceFile($templatePdfPath);
        $templateId = $pdf->importPage(1);
        $templateSize = $pdf->getTemplateSize($templateId);
        $overlayProfile = $this->resolveTemplateOverlayProfile($templatePdfPath);

        $entries = $experiences->values();
        if ($entries->isEmpty()) {
            $entries = collect([(object) [
                'start_date' => null,
                'end_date' => null,
                'position' => 'N/A',
                'office' => 'N/A',
                'supervisor' => 'N/A',
                'agency' => 'N/A',
                'accomplishments' => ['N/A'],
                'duties' => ['N/A'],
            ]]);
        }

        // Render one entry per page for consistent output across environments.
        foreach ($entries as $exp) {
            $pdf->AddPage($templateSize['orientation'], [$templateSize['width'], $templateSize['height']]);
            $pdf->useTemplate($templateId);

            $this->writeTemplateEntryOverlay($pdf, $exp, $overlayProfile);

            $overlayFontSize = (float) ($overlayProfile['overlay_font_size'] ?? $overlayProfile['value_font_size'] ?? 8.4);
            $this->overlayWesFooterDate($pdf, $overlayProfile);
            $this->overlayWesSignatureName(
                $pdf,
                $fullName,
                $overlayFontSize,
                (float) ($overlayProfile['signature_y'] ?? 124.0)
            );
        }

        return $pdf;
    }

    private function writeTemplateEntryOverlay(Fpdi $pdf, $exp, array $profile): void
    {
        $durationFrom = $this->formatMonthYear($exp->start_date);
        $durationTo = $exp->end_date ? $this->formatMonthYear($exp->end_date) : 'Present';
        $duration = trim(($durationFrom !== '' ? $durationFrom : 'N/A') . ' to ' . ($durationTo !== '' ? $durationTo : 'N/A'));

        $position = trim((string) ($exp->position ?? '')) ?: 'N/A';
        $office = trim((string) ($exp->office ?? '')) ?: 'N/A';
        $supervisor = trim((string) ($exp->supervisor ?? '')) ?: 'N/A';
        $agency = trim((string) ($exp->agency ?? '')) ?: 'N/A';

        $valueWidth = (float) ($profile['value_width'] ?? 92.0);
        $rowHeight = (float) ($profile['row_height'] ?? 4.6);

        $pdf->SetFont('Arial', '', (float) ($profile['value_font_size'] ?? 8.4));
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY((float) ($profile['duration_x'] ?? 94.0), (float) ($profile['duration_y'] ?? 69.2));
        $pdf->Cell($valueWidth, $rowHeight, $this->toPdfText($duration), 0, 0, 'L');

        $pdf->SetXY((float) ($profile['position_x'] ?? 94.0), (float) ($profile['position_y'] ?? (69.2 + 8.35)));
        $pdf->Cell($valueWidth, $rowHeight, $this->toPdfText($position), 0, 0, 'L');

        $pdf->SetXY((float) ($profile['office_x'] ?? 94.0), (float) ($profile['office_y'] ?? (69.2 + (8.35 * 2))));
        $pdf->Cell($valueWidth, $rowHeight, $this->toPdfText($office), 0, 0, 'L');

        $pdf->SetXY((float) ($profile['supervisor_x'] ?? 94.0), (float) ($profile['supervisor_y'] ?? (69.2 + (8.35 * 3))));
        $pdf->Cell($valueWidth, $rowHeight, $this->toPdfText($supervisor), 0, 0, 'L');

        $pdf->SetXY((float) ($profile['agency_x'] ?? 94.0), (float) ($profile['agency_y'] ?? (69.2 + (8.35 * 4))));
        $pdf->Cell($valueWidth, $rowHeight, $this->toPdfText($agency), 0, 0, 'L');

        $accomplishments = array_slice($this->listItemsForPreview($exp->accomplishments ?? []), 0, 3);
        $duties = array_slice($this->listItemsForPreview($exp->duties ?? []), 0, 3);

        $bulletX = (float) ($profile['bullet_x'] ?? 61.5);
        $bulletLineHeight = (float) ($profile['bullet_line_height'] ?? 4.25);
        $accomplishmentY = (float) ($profile['accomplishment_y'] ?? (69.2 + 40.6));
        $dutyY = (float) ($profile['duty_y'] ?? (69.2 + 55.9));
        $bulletWidth = (float) ($profile['bullet_width'] ?? 123.0);
        $bulletRowHeight = (float) ($profile['bullet_row_height'] ?? 4.0);

        foreach ($accomplishments as $index => $item) {
            $pdf->SetXY($bulletX, $accomplishmentY + ($index * $bulletLineHeight));
            $pdf->Cell($bulletWidth, $bulletRowHeight, $this->toPdfText('• ' . $item), 0, 0, 'L');
        }

        foreach ($duties as $index => $item) {
            $pdf->SetXY($bulletX, $dutyY + ($index * $bulletLineHeight));
            $pdf->Cell($bulletWidth, $bulletRowHeight, $this->toPdfText('• ' . $item), 0, 0, 'L');
        }
    }

    private function resolveTemplateOverlayProfile(string $templatePdfPath): array
    {
        $normalized = strtolower(str_replace('\\', '/', $templatePdfPath));
        $normalized = preg_replace('/\\.docx$/', '.pdf', $normalized) ?? $normalized;
        if (str_ends_with($normalized, '/wes_template.pdf')) {
            // Coordinates calibrated against resources/templates/WES_Template.pdf.
            return [
                'duration_x' => 41.5,
                'position_x' => 41.0,
                'office_x' => 62.0,
                'supervisor_x' => 66.0,
                'agency_x' => 107.0,
                'duration_y' => 61.7,
                'position_y' => 66.8,
                'office_y' => 72.0,
                'supervisor_y' => 77.2,
                'agency_y' => 82.3,
                'value_width' => 85.0,
                'row_height' => 4.4,
                'value_font_size' => 8.2,
                'overlay_font_size' => 8.2,
                'bullet_x' => 30.0,
                'bullet_line_height' => 4.2,
                'accomplishment_y' => 98.0,
                'duty_y' => 112.0,
                'bullet_width' => 120.0,
                'bullet_row_height' => 4.0,
                'signature_y' => 124.0,
            ];
        }

        // Backward-compatible profile for legacy template overlays.
        return [
            'duration_x' => 94.0,
            'position_x' => 94.0,
            'office_x' => 94.0,
            'supervisor_x' => 94.0,
            'agency_x' => 94.0,
            'duration_y' => 69.2,
            'position_y' => 77.55,
            'office_y' => 85.9,
            'supervisor_y' => 94.25,
            'agency_y' => 102.6,
            'value_width' => 92.0,
            'row_height' => 4.6,
            'value_font_size' => 8.4,
            'overlay_font_size' => 8.4,
            'bullet_x' => 61.5,
            'bullet_line_height' => 4.25,
            'accomplishment_y' => 109.8,
            'duty_y' => 125.1,
            'bullet_width' => 123.0,
            'bullet_row_height' => 4.0,
            'signature_y' => 124.0,
        ];
    }

    private function overlayWesFooterDate(\FPDF $pdf, array $overlayProfile): void
    {
        $overlayFontSize = (float) ($overlayProfile['overlay_font_size'] ?? $overlayProfile['value_font_size'] ?? 8.4);
        $dateText = Carbon::now()->format('m/d/Y');

        $pdf->SetFont('Arial', '', $overlayFontSize);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Text($this->getWesFooterDateX(), $this->getWesFooterDateY(), $dateText);
    }

    private function getWesFooterDateX(): float
    {
        // Single source of truth for footer date X coordinate.
        return 145.0;
    }

    private function getWesFooterDateY(): float
    {
        // Single source of truth for footer date Y coordinate.
        return 146.3;
    }

    private function buildWesPdfLegacy(string $fullName, Collection $experiences): \FPDF
    {
        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(0, 8, $this->toPdfText('Attachment to CS Form No. 212'), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 8, $this->toPdfText('WORK EXPERIENCE SHEET'), 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(20, 8, $this->toPdfText('Name:'), 0, 0);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(120, 8, $this->toPdfText($fullName), 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(12, 8, $this->toPdfText('Date:'), 0, 0);
        $pdf->SetX($pdf->GetX() + 2.0);
        $pdf->Cell(0, 8, $this->toPdfText(now()->format('F d, Y')), 0, 1);
        $pdf->Ln(2);
        $pdf->Line(12, $pdf->GetY(), 198, $pdf->GetY());
        $pdf->Ln(4);

        foreach ($experiences as $index => $exp) {
            $this->ensurePdfSpace($pdf, 42);
            $entryNo = $index + 1;
            $from = $this->formatMonthYear($exp->start_date);
            $to = $exp->end_date ? $this->formatMonthYear($exp->end_date) : 'Present';
            $duration = trim(($from !== '' ? $from : 'N/A') . ' to ' . ($to !== '' ? $to : 'N/A'));

            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(0, 7, $this->toPdfText("Entry {$entryNo}"), 0, 1);
            $pdf->SetFont('Arial', '', 10);

            $this->pdfLabelValue($pdf, 'Duration', $duration);
            $this->pdfLabelValue($pdf, 'Position', (string) ($exp->position ?? 'N/A'));
            $this->pdfLabelValue($pdf, 'Name of Office/Unit', (string) ($exp->office ?? 'N/A'));
            $this->pdfLabelValue($pdf, 'Immediate Supervisor', (string) ($exp->supervisor ?? 'N/A'));
            $this->pdfLabelValue($pdf, 'Agency/Organization and Location', (string) ($exp->agency ?? 'N/A'));

            $this->pdfListBlock($pdf, 'Accomplishments and Contributions', $this->listItemsForPreview($exp->accomplishments ?? []));
            $this->pdfListBlock($pdf, 'Summary of Actual Duties', $this->listItemsForPreview($exp->duties ?? []));

            $pdf->Ln(2);
            $pdf->SetDrawColor(220, 220, 220);
            $pdf->Line(12, $pdf->GetY(), 198, $pdf->GetY());
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->Ln(3);
        }

        $this->ensurePdfSpace($pdf, 24);
        $pdf->Ln(6);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(120, 6, '', 0, 0);
        $pdf->Cell(64, 6, '', 0, 1, 'C');
        $pdf->Cell(120, 5, '', 0, 0);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(64, 5, $this->toPdfText('(Signature over Printed Name of Employee/Applicant)'), 0, 1, 'C');
        $pdf->Ln(6);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Text($this->getWesFooterDateX(), $this->getWesFooterDateY(), Carbon::now()->format('m/d/Y'));

        return $pdf;
    }

    private function resolveWesTemplatePdfCandidate(): ?array
    {
        $allowLegacyOverlay = filter_var(env('WES_ALLOW_LEGACY_PDF_OVERLAY', false), FILTER_VALIDATE_BOOL);

        $candidates = [
            [
                'path' => resource_path('templates/WES_Template.pdf'),
                'source' => 'resources/templates/WES_Template.pdf',
            ],
            [
                'path' => public_path('templates/WES_Template.pdf'),
                'source' => 'public/templates/WES_Template.pdf',
            ],
        ];

        if ($allowLegacyOverlay) {
            $candidates[] = [
                'path' => resource_path('templates/work_experience_template.pdf'),
                'source' => 'resources/templates/work_experience_template.pdf',
            ];
        }

        foreach ($candidates as $candidate) {
            $path = (string) ($candidate['path'] ?? '');
            if ($path === '' || !file_exists($path)) {
                continue;
            }

            if ($this->pdfTemplateContainsPlaceholderMarkers($path)) {
                Log::warning('Skipping WES PDF template because unresolved placeholders were detected.', [
                    'template_pdf' => $path,
                    'template_source' => (string) ($candidate['source'] ?? ''),
                ]);
                continue;
            }

            return $candidate;
        }

        if (!$allowLegacyOverlay) {
            Log::info('WES template PDF overlay skipped: no WES_Template.pdf found and legacy overlay is disabled.', [
                'expected_candidates' => array_map(static fn (array $candidate): string => (string) ($candidate['source'] ?? ''), $candidates),
            ]);
        }

        return null;
    }

    private function pdfTemplateContainsPlaceholderMarkers(string $pdfPath): bool
    {
        $content = @file_get_contents($pdfPath);
        if (!is_string($content) || $content === '') {
            return false;
        }

        foreach ([
            '${experience}',
            '${/experience}',
            '${from}',
            '${to}',
            '${position}',
            '${office}',
            '${supervisor}',
            '${agency}',
            '${accomplishments}',
            '${duties}',
            '${name}',
            '${date}',
        ] as $marker) {
            if (str_contains($content, $marker)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ordered WES DOCX candidates for responsive rendering.
     * Priority starts with resources/templates/WES_Template.docx.
     *
     * @return array<int, array{path:string,source:string}>
     */
    private function resolveResponsiveWesDocxCandidates(): array
    {
        $candidates = [
            [
                'path' => resource_path('templates/WES_Template.docx'),
                'source' => 'resources/templates/WES_Template.docx',
            ],
            [
                'path' => public_path('templates/WES_Template.docx'),
                'source' => 'public/templates/WES_Template.docx',
            ],
        ];

        return array_values(array_filter($candidates, static function (array $candidate): bool {
            return is_string($candidate['path'] ?? null)
                && $candidate['path'] !== ''
                && file_exists($candidate['path']);
        }));
    }

    private function docxHasResponsiveWesPlaceholders(string $templateDocxPath): bool
    {
        if (!class_exists(\ZipArchive::class)) {
            // If Zip extension is unavailable, keep behavior permissive.
            return true;
        }

        $requiredMarkers = [
            '${from}',
            '${to}',
            '${position}',
            '${office}',
            '${supervisor}',
            '${agency}',
            '${accomplishments}',
            '${duties}',
            '${experience}',
            '${/experience}',
            '${name}',
        ];

        $zip = new \ZipArchive();
        $opened = $zip->open($templateDocxPath);
        if ($opened !== true) {
            return false;
        }

        $documentXml = (string) $zip->getFromName('word/document.xml');
        $zip->close();

        if ($documentXml === '') {
            return false;
        }

        foreach ($requiredMarkers as $marker) {
            if (!str_contains($documentXml, $marker)) {
                return false;
            }
        }

        return true;
    }

    private function convertDocxTemplateToPdf(string $docxPath, string $pdfPath): bool
    {
        $escapedDocx = str_replace("'", "''", $docxPath);
        $escapedPdf = str_replace("'", "''", $pdfPath);

        $script = <<<'PS'
$ErrorActionPreference = 'Stop'
$docxPath = '__DOCX__'
$pdfPath = '__PDF__'
$word = $null
$document = $null
try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $word.DisplayAlerts = 0
    $document = $word.Documents.Open($docxPath, $false, $true)
    $wdFormatPDF = 17
    $document.SaveAs([ref]$pdfPath, [ref]$wdFormatPDF)
}
finally {
    if ($document -ne $null) { $document.Close($false) | Out-Null }
    if ($word -ne $null) { $word.Quit() | Out-Null }
    if ($document -ne $null) { [System.Runtime.Interopservices.Marshal]::ReleaseComObject($document) | Out-Null }
    if ($word -ne $null) { [System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null }
    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}
PS;

        $script = str_replace('__DOCX__', $escapedDocx, $script);
        $script = str_replace('__PDF__', $escapedPdf, $script);

        $scriptPath = storage_path('app/temp/wes_docx_to_pdf.ps1');
        @mkdir(dirname($scriptPath), 0777, true);
        file_put_contents($scriptPath, $script);

        $command = 'powershell -NoProfile -ExecutionPolicy Bypass -File ' . escapeshellarg($scriptPath);
        $output = [];
        $exitCode = 1;
        @exec($command, $output, $exitCode);

        @unlink($scriptPath);

        if ($exitCode !== 0) {
            Log::warning('Failed to convert WES DOCX template to PDF.', [
                'docx' => $docxPath,
                'pdf' => $pdfPath,
                'exit_code' => $exitCode,
                'output' => implode("\n", $output),
            ]);
            return false;
        }

        return file_exists($pdfPath);
    }

    private function ensurePdfSpace(\FPDF $pdf, float $heightNeeded): void
    {
        if ($pdf->GetY() + $heightNeeded <= 285) {
            return;
        }

        $pdf->AddPage();
    }

    private function pdfLabelValue(\FPDF $pdf, string $label, string $value): void
    {
        $label = rtrim($label, ':') . ':';
        $safeValue = trim($value) !== '' ? trim($value) : 'N/A';

        $pdf->SetFont('Arial', '', 8.0);
        $pdf->Cell(56, 6, $this->toPdfText($label), 0, 0);
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->MultiCell(130, 6, $this->toPdfText($safeValue), 0, 'L');
    }

    private function pdfListBlock(\FPDF $pdf, string $title, array $items): void
    {
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->Cell(0, 6, $this->toPdfText($title . ':'), 0, 1);
        $pdf->SetFont('Arial', '', 9.5);

        foreach ($items as $item) {
            $pdf->Cell(4, 6, $this->toPdfText('•'), 0, 0);
            $pdf->MultiCell(178, 6, $this->toPdfText($item), 0, 'L');
        }
    }

    private function listItemsForPreview($value): array
    {
        if (empty($value)) {
            return ['N/A'];
        }

        $items = is_string($value) ? explode('|', $value) : (array) $value;
        $items = array_values(array_filter(array_map(function ($item) {
            return trim((string) $item);
        }, $items), function ($item) {
            return $item !== '';
        }));

        return empty($items) ? ['N/A'] : $items;
    }

    private function formatTemplateMultilineList($value): string
    {
        $items = $this->listItemsForPreview($value);
        if (empty($items)) {
            return '• N/A';
        }

        return implode('  ', array_map(function ($item) {
            return '• ' . trim((string) $item);
        }, $items));
    }

    private function overlayWesSignatureName(
        \FPDF $pdf,
        string $fullName,
        ?float $fontSize = null,
        ?float $y = null
    ): void
    {
        $name = trim($fullName) !== '' ? trim($fullName) : 'N/A';

        // Signature line text area at lower-right of WES template.
        $pdf->SetFont('Arial', '', (float) ($fontSize ?? 14.0));
        $pdf->SetTextColor(0, 0, 0);
        $signatureLineX = 128.0;
        $signatureLineWidth = 50.0;

        $pdf->SetXY($signatureLineX, (float) ($y ?? 10.0));
        $pdf->Cell($signatureLineWidth, 5.0, $this->toPdfText($name), 0, 0, 'C');
    }

    private function setTemplateValueOnce(TemplateProcessor $templateProcessor, string $key, string $value): void
    {
        $templateProcessor->setValue($key, $this->sanitizeDocxText($value), 1);
    }

    private function sanitizeDocxText(string $value): string
    {
        $cleaned = str_replace(
            ["\r\n", "\r", "\u{00A0}"],
            [" ", " ", ' '],
            trim($value)
        );

        return $cleaned === '' ? 'N/A' : $cleaned;
    }

    private function formatMonthYear($date): string
    {
        try {
            return Carbon::parse($date)->format('M Y');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function toPdfText(string $text): string
    {
        $cleaned = strtr($text, [
            '’' => "'",
            '‘' => "'",
            '“' => '"',
            '”' => '"',
            '–' => '-',
            '—' => '-',
            '…' => '...',
            "\u{00A0}" => ' ',
        ]);

        $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $cleaned);

        return $converted === false ? utf8_decode($cleaned) : $converted;
    }
}
