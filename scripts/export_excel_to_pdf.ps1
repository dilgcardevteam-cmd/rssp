param(
    [Parameter(Mandatory = $true)]
    [string]$TemplateXlsx,

    [Parameter(Mandatory = $true)]
    [string]$DataJson,

    [Parameter(Mandatory = $true)]
    [string]$OutputPdf
)

$ErrorActionPreference = 'Stop'

$excel = $null
$workbook = $null

try {
    if (-not (Test-Path -LiteralPath $TemplateXlsx)) {
        throw "Template workbook not found: $TemplateXlsx"
    }
    if (-not (Test-Path -LiteralPath $DataJson)) {
        throw "Cell map JSON not found: $DataJson"
    }

    $outputDir = Split-Path -Path $OutputPdf -Parent
    if (-not [string]::IsNullOrWhiteSpace($outputDir) -and -not (Test-Path -LiteralPath $outputDir)) {
        New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
    }

    $excel = New-Object -ComObject Excel.Application
    $excel.Visible = $false
    $excel.DisplayAlerts = $false

    $workbook = $excel.Workbooks.Open($TemplateXlsx)

    # Fix C2 layout width in exported PDF: expand the C2 table columns so
    # CIVIL SERVICE ELIGIBILITY / WORK EXPERIENCE matches the width profile
    # of the other pages while keeping a single-page C2 export.
    try {
        $c2Sheet = $workbook.Worksheets.Item('C2')
        foreach ($colName in @('A','B','C','D','E','F','G','H','I','J','K')) {
            $col = $c2Sheet.Columns.Item($colName)
            $col.ColumnWidth = $col.ColumnWidth * 1.17
            [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($col)
        }
        [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($c2Sheet)
    } catch {
        # Non-fatal: proceed with the workbook's original layout if C2 is unavailable.
    }

    $jsonText = Get-Content -LiteralPath $DataJson -Raw
    $cellMap = $jsonText | ConvertFrom-Json

    foreach ($sheetProp in $cellMap.PSObject.Properties) {
        $sheetName = [string]$sheetProp.Name
        $sheetValues = $sheetProp.Value
        if ($null -eq $sheetValues) { continue }

        try {
            $ws = $workbook.Worksheets.Item($sheetName)
        } catch {
            continue
        }

        foreach ($cellProp in $sheetValues.PSObject.Properties) {
            $addr = [string]$cellProp.Name
            $value = [string]$cellProp.Value
            $ws.Range($addr).Value2 = $value
        }
    }

    # 0 = xlTypePDF, 0 = xlQualityStandard
    $workbook.ExportAsFixedFormat(0, $OutputPdf, 0, $true, $false)
}
finally {
    if ($workbook -ne $null) {
        $workbook.Close($false) | Out-Null
        [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($workbook)
    }

    if ($excel -ne $null) {
        $excel.Quit()
        [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel)
    }

    [gc]::Collect()
    [gc]::WaitForPendingFinalizers()
}
