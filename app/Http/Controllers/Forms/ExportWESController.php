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

        $allWesEntries = WorkExpSheet::where('user_id', $userId)->get();
        $shownWesEntries = $allWesEntries->filter(static fn ($row): bool => (bool) data_get($row, 'isDisplayed'));

        // If there are at least 2 shown entries, keep respecting the toggle.
        // Otherwise, export all saved WES entries so the 2nd box gets filled when records exist.
        $experiences = $shownWesEntries->count() >= 2 ? $shownWesEntries->values() : $allWesEntries;

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
        $this->wesRenderMeta = [
            'mode' => 'drawn_form',
            'templatePath' => null,
            'templateSource' => null,
        ];

        // Template-free renderer: draws the sheet layout directly (prevents template mismatch/overlay issues).
        return $this->buildWesPdfDrawnForm($fullName, $experiences);
    }

    private function buildWesPdfDrawnForm(string $fullName, Collection $experiences): \FPDF
    {
        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);

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

        // Avoid rendering completely blank rows (e.g., user added an entry but left it empty).
        $entries = $entries
            ->filter(fn ($row): bool => !$this->wesEntryIsBlank($row))
            ->values();

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

        $chunks = $entries->chunk(2)->values();
        foreach ($chunks as $chunk) {
            $pageEntries = $chunk
                ->values()
                ->filter(fn ($row): bool => !$this->wesEntryIsBlank($row))
                ->values();

            if ($pageEntries->isEmpty()) {
                continue;
            }

            $pdf->AddPage();
            $this->drawWesStaticPage($pdf, (int) $pageEntries->count());

            $this->overlayWesSignatureBlock($pdf, $fullName);

            foreach ($pageEntries as $index => $exp) {
                $this->overlayWesEntryIntoBox($pdf, $exp, (int) $index);
            }
        }

        return $pdf;
    }

    private function wesEntryIsBlank($exp): bool
    {
        $startDate = data_get($exp, 'start_date');
        $endDate = data_get($exp, 'end_date');

        if ($startDate !== null && trim((string) $startDate) !== '') {
            return false;
        }
        if ($endDate !== null && trim((string) $endDate) !== '') {
            return false;
        }

        foreach (['position', 'office', 'supervisor', 'agency'] as $field) {
            $value = trim((string) data_get($exp, $field, ''));
            if ($value !== '') {
                return false;
            }
        }

        foreach (['accomplishments', 'duties'] as $listField) {
            $items = data_get($exp, $listField);
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (trim((string) $item) !== '') {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function drawWesStaticPage(\FPDF $pdf, int $boxCount = 2): void
    {
        $left = 10.0;
        $width = 190.0;

        // Header text.
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Text($left, 10.0, $this->toPdfText('Attachment to CS Form No. 212'));

        // Title bar.
        $titleY = 12.5;
        $titleH = 12.0;
        $pdf->SetFillColor(160, 160, 160);
        $pdf->Rect($left, $titleY, $width, $titleH, 'DF');
        $pdf->SetFont('Arial', 'B', 15);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY($left, $titleY + 2.2);
        $pdf->Cell($width, 7.0, $this->toPdfText('WORK EXPERIENCE SHEET'), 0, 0, 'C');
        $pdf->SetTextColor(0, 0, 0);

        // Instructions box.
        $instY = $titleY + $titleH;
        $instH = 30.0;
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Rect($left, $instY, $width, $instH, 'DF');
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Rect($left, $instY, $width, $instH);

        $pdf->SetFont('Arial', 'BI', 10);
        $pdf->SetXY($left + 2.5, $instY + 2.0);
        $pdf->Cell(24, 5, $this->toPdfText('Instructions:'), 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9.2);

        $bodyX = $left + 26.0;
        $bodyY = $instY + 2.0;
        $pdf->SetXY($bodyX, $bodyY);
        $pdf->MultiCell(
            $width - ($bodyX - $left) - 2.5,
            4.2,
            $this->toPdfText(
                "1. Include only the work experiences relevant to the position being applied to.\n" .
                "2. The duration should include start and finish dates, if known, month in abbreviated form, if known, and year in full. For the current position, use the word Present, e.g., 1998-Present. Work experience should be listed from most recent first."
            ),
            0,
            'L'
        );

        $pdf->SetFont('Arial', 'BU', 8.8);
        $pdf->SetXY($left + 2.5, $instY + $instH - 6.2);
        $pdf->Cell(
            $width - 5.0,
            4.8,
            $this->toPdfText('Sample: If applying to Supervising Administrative Officer (Human Resource Management Officer IV)'),
            0,
            0,
            'L'
        );

        // Two entry boxes.
        $boxTop = $instY + $instH + 2.0;
        // Make boxes taller to better fit lists and avoid clipping.
        $boxH = 96.0;
        $this->drawWesEntryBox($pdf, $boxTop, $boxH);
        if ($boxCount > 1) {
            $this->drawWesEntryBox($pdf, $boxTop + $boxH + 6.0, $boxH);
        }
    }

    private function drawWesEntryBox(\FPDF $pdf, float $y, float $h): void
    {
        $left = 10.0;
        $width = 190.0;

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Rect($left, $y, $width, $h);

        $xBullet = $left + 12.0;
        $xLabel = $xBullet + 6.0;

        $pdf->SetFont('Arial', '', 10);
        $lineY = $y + 10.0;
        $rowGap = 6.2;

        foreach ([
            'Duration',
            'Position',
            'Name of Office/Unit',
            'Immediate Supervisor',
            'Name of Agency/Organization and Location',
        ] as $label) {
            $pdf->Text($xBullet, $lineY, $this->toPdfText('•'));
            $pdf->Text($xLabel, $lineY, $this->toPdfText($label . ':'));
            $lineY += $rowGap;
        }

        // Scale the accomplishments/duties sections to the available box height.
        $listsTop = $y + 44.0;
        $listsBottom = $y + $h - 7.0;
        $halfH = max(12.0, ($listsBottom - $listsTop) / 2.0);

        $title1Y = $listsTop;
        $titleBulletX = $left + 52.0;
        $titleTextX = $titleBulletX + 7.0;
        $pdf->Text($titleBulletX, $title1Y, $this->toPdfText('•'));
        $pdf->Text($titleTextX, $title1Y, $this->toPdfText('List of Accomplishments and Contributions (if any)'));

        $title2Y = $listsTop + $halfH;
        $pdf->Text($titleBulletX, $title2Y, $this->toPdfText('•'));
        $pdf->Text($titleTextX, $title2Y, $this->toPdfText('Summary of Actual Duties'));
    }

    private function overlayWesEntryIntoBox(\FPDF $pdf, $exp, int $boxIndex): void
    {
        $left = 10.0;
        $width = 190.0;
        $instY = 24.5;
        $instH = 30.0;
        $boxTop = $instY + $instH + 2.0;
        $boxH = 96.0;
        $boxY = $boxIndex === 0 ? $boxTop : ($boxTop + $boxH + 6.0);

        $xBullet = $left + 12.0;
        $xLabel = $xBullet + 6.0;
        $labels = [
            'Duration',
            'Position',
            'Name of Office/Unit',
            'Immediate Supervisor',
            'Name of Agency/Organization and Location',
        ];

        // Place values after the widest label so long labels don't overlap.
        $pdf->SetFont('Arial', '', 10);
        $maxLabelW = 0.0;
        foreach ($labels as $label) {
            $w = $pdf->GetStringWidth($this->toPdfText($label . ':'));
            if ($w > $maxLabelW) {
                $maxLabelW = $w;
            }
        }

        $valueX = $xLabel + $maxLabelW + 4.0;
        $valueW = ($left + $width) - $valueX - 6.0;
        // Keep value text aligned with the label baselines drawn in drawWesEntryBox().
        $startBaselineY = $boxY + 10.0;
        $rowGap = 6.2;

        $durationFrom = $this->formatMonthYear($exp->start_date);
        $durationTo = $exp->end_date ? $this->formatMonthYear($exp->end_date) : 'Present';
        $duration = trim(($durationFrom !== '' ? $durationFrom : 'N/A') . ' to ' . ($durationTo !== '' ? $durationTo : 'N/A'));

        $values = [
            $duration,
            trim((string) ($exp->position ?? '')) ?: 'N/A',
            trim((string) ($exp->office ?? '')) ?: 'N/A',
            trim((string) ($exp->supervisor ?? '')) ?: 'N/A',
            trim((string) ($exp->agency ?? '')) ?: 'N/A',
        ];

        $pdf->SetTextColor(0, 0, 0);
        foreach ($values as $i => $value) {
            $this->writeFittedTextBaseline($pdf, mb_strtoupper($value), $valueX, $startBaselineY + ($i * $rowGap), $valueW, 10.0, 7.5);
        }

        // Accomplishments list (responsive: auto-fit within the box when possible).
        $itemsX = $left + 60.0;
        $itemsW = ($left + $width) - $itemsX - 6.0;
        $baseItemsFont = 9.2;
        $minItemsFont = 7.2;

        $listsTop = $boxY + 44.0;
        $listsBottom = $boxY + $boxH - 7.0;
        $halfH = max(12.0, ($listsBottom - $listsTop) / 2.0);

        $title1Y = $listsTop;
        $accStartY = $title1Y + 4.8;
        $title2Y = $listsTop + $halfH;
        $dutyStartY = $title2Y + 4.8;

        $accItems = $this->listItemsForPreview($exp->accomplishments ?? []);
        $dutyItems = $this->listItemsForPreview($exp->duties ?? []);

        $chosenFont = $baseItemsFont;
        $chosenLineHeight = 4.2;
        $accMaxLines = 3;
        $dutyMaxLines = 3;

        for ($tryFont = $baseItemsFont; $tryFont >= $minItemsFont; $tryFont -= 0.2) {
            $tryLineHeight = max(3.6, $tryFont * 0.46);

            $tryAccMaxLines = (int) floor(max(0.0, ($title2Y - 2.0 - $accStartY) / $tryLineHeight));
            $tryDutyMaxLines = (int) floor(max(0.0, ($listsBottom - $dutyStartY) / $tryLineHeight));

            $tryAccMaxLines = max(1, $tryAccMaxLines);
            $tryDutyMaxLines = max(1, $tryDutyMaxLines);

            $pdf->SetFont('Arial', '', $tryFont);
            $accNeeded = $this->estimateBulletWrappedLineCount($pdf, $accItems, $itemsW);
            $dutyNeeded = $this->estimateBulletWrappedLineCount($pdf, $dutyItems, $itemsW);

            $chosenFont = $tryFont;
            $chosenLineHeight = $tryLineHeight;
            $accMaxLines = $tryAccMaxLines;
            $dutyMaxLines = $tryDutyMaxLines;

            if ($accNeeded <= $tryAccMaxLines && $dutyNeeded <= $tryDutyMaxLines) {
                break;
            }
        }

        $pdf->SetFont('Arial', '', $chosenFont);
        $this->writeBulletItems($pdf, $accItems, $itemsX, $accStartY, $itemsW, $chosenLineHeight, $accMaxLines);
        $this->writeBulletItems($pdf, $dutyItems, $itemsX, $dutyStartY, $itemsW, $chosenLineHeight, $dutyMaxLines);
    }

    private function estimateBulletWrappedLineCount(\FPDF $pdf, array $items, float $w): int
    {
        $count = 0;
        foreach ($items as $item) {
            $text = trim((string) $item);
            if ($text === '') {
                continue;
            }

            $wrapped = $this->splitTextByWidth($pdf, mb_strtoupper($text), $w - 6.0);
            foreach ($wrapped as $line) {
                if (trim((string) $line) === '') {
                    continue;
                }
                $count++;
            }
        }

        return $count;
    }

    private function writeFittedTextBaseline(\FPDF $pdf, string $text, float $x, float $baselineY, float $w, float $baseSize, float $minSize): void
    {
        $raw = trim($text);
        if ($raw === '') {
            $raw = 'N/A';
        }

        $display = $raw;
        $size = $baseSize;
        for ($try = $baseSize; $try >= $minSize; $try -= 0.5) {
            $pdf->SetFont('Arial', '', $try);
            $width = $pdf->GetStringWidth($this->toPdfText($display));
            if ($width <= $w) {
                $size = $try;
                break;
            }
            $size = $try;
        }

        $pdf->SetFont('Arial', '', $size);
        if ($pdf->GetStringWidth($this->toPdfText($display)) > $w) {
            $display = $this->truncateToWidth($pdf, $display, $w);
        }

        $pdf->Text($x, $baselineY, $this->toPdfText($display));
    }

    private function overlayWesSignatureBlock(\FPDF $pdf, string $fullName): void
    {
        $name = trim($fullName) !== '' ? trim($fullName) : 'N/A';
        $dateText = Carbon::now()->format('m/d/Y');

        // Signature area (bottom-right).
        $sigLineX1 = 140.0;
        $sigLineX2 = 198.0;
        $sigLineY = 265.0;
        $sigW = $sigLineX2 - $sigLineX1;

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line($sigLineX1, $sigLineY, $sigLineX2, $sigLineY);

        // Printed name (fit to the signature line width).
        $displayName = $name;
        $chosen = 11.0;
        for ($try = 11.0; $try >= 8.0; $try -= 0.5) {
            $pdf->SetFont('Arial', '', $try);
            if ($pdf->GetStringWidth($this->toPdfText($displayName)) <= $sigW) {
                $chosen = $try;
                break;
            }
            $chosen = $try;
        }
        $pdf->SetFont('Arial', '', $chosen);
        if ($pdf->GetStringWidth($this->toPdfText($displayName)) > $sigW) {
            $displayName = $this->truncateToWidth($pdf, $displayName, $sigW);
        }
        $pdf->SetXY($sigLineX1, $sigLineY - 8.0);
        $pdf->Cell($sigW, 6.0, $this->toPdfText($displayName), 0, 0, 'C');

        $pdf->SetFont('Arial', '', 8.0);
        $pdf->SetXY($sigLineX1, $sigLineY + 1.5);
        $pdf->MultiCell($sigW, 4.0, $this->toPdfText("(Signature over Printed Name\nof Employee/Applicant)"), 0, 'C');

        $pdf->SetFont('Arial', '', 10.0);
        $dateLabelX = 140.0;
        $dateLabelY = 279.5;
        $dateLineX1 = 153.0;
        $dateLineX2 = 198.0;
        $dateLineY = 279.7;
        $pdf->Text($dateLabelX, $dateLabelY, $this->toPdfText('Date:'));
        $pdf->Line($dateLineX1, $dateLineY, $dateLineX2, $dateLineY);
        $pdf->SetXY($dateLineX1, $dateLineY - 5.2);
        $pdf->Cell($dateLineX2 - $dateLineX1, 6.0, $this->toPdfText($dateText), 0, 0, 'C');
    }

    private function writeFittedTextCell(\FPDF $pdf, string $text, float $x, float $y, float $w, float $baseSize, float $minSize): void
    {
        $raw = trim($text);
        if ($raw === '') {
            $raw = 'N/A';
        }

        $display = $raw;
        $size = $baseSize;
        for ($try = $baseSize; $try >= $minSize; $try -= 0.5) {
            $pdf->SetFont('Arial', '', $try);
            $width = $pdf->GetStringWidth($this->toPdfText($display));
            if ($width <= $w) {
                $size = $try;
                break;
            }
            $size = $try;
        }

        $pdf->SetFont('Arial', '', $size);
        if ($pdf->GetStringWidth($this->toPdfText($display)) > $w) {
            $display = $this->truncateToWidth($pdf, $display, $w);
        }

        $pdf->SetXY($x, $y);
        $pdf->Cell($w, 5.0, $this->toPdfText($display), 0, 0, 'L');
    }

    private function truncateToWidth(\FPDF $pdf, string $text, float $maxWidth): string
    {
        $candidate = trim($text);
        if ($candidate === '') {
            return '';
        }

        if ($pdf->GetStringWidth($this->toPdfText($candidate)) <= $maxWidth) {
            return $candidate;
        }

        $ellipsis = '...';
        while (mb_strlen($candidate) > 1) {
            $candidate = rtrim(mb_substr($candidate, 0, mb_strlen($candidate) - 1));
            $trial = $candidate . $ellipsis;
            if ($pdf->GetStringWidth($this->toPdfText($trial)) <= $maxWidth) {
                return $trial;
            }
        }

        return $ellipsis;
    }

    private function writeBulletItems(\FPDF $pdf, array $items, float $x, float $y, float $w, float $lineHeight, int $maxLines): void
    {
        $linesWritten = 0;
        foreach ($items as $item) {
            if ($linesWritten >= $maxLines) {
                break;
            }

            $text = trim((string) $item);
            if ($text === '') {
                continue;
            }

            $wrapped = $this->splitTextByWidth($pdf, mb_strtoupper($text), $w - 6.0);
            foreach ($wrapped as $wrappedLine) {
                if ($linesWritten >= $maxLines) {
                    break;
                }
                $pdf->SetXY($x, $y + ($linesWritten * $lineHeight));
                $pdf->Cell(6.0, $lineHeight, $this->toPdfText('•'), 0, 0, 'L');
                $pdf->Cell($w - 6.0, $lineHeight, $this->toPdfText($wrappedLine), 0, 0, 'L');
                $linesWritten++;
            }
        }
    }

    private function splitTextByWidth(\FPDF $pdf, string $text, float $maxWidth): array
    {
        $text = trim($text);
        if ($text === '') {
            return [''];
        }

        $words = preg_split('/\s+/', $text) ?: [$text];
        $lines = [];
        $current = '';
        foreach ($words as $word) {
            $trial = $current === '' ? $word : ($current . ' ' . $word);
            if ($pdf->GetStringWidth($this->toPdfText($trial)) <= $maxWidth) {
                $current = $trial;
                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
                $current = $word;
                continue;
            }

            // Single word longer than the width; hard-truncate.
            $lines[] = $this->truncateToWidth($pdf, $word, $maxWidth);
            $current = '';
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
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
