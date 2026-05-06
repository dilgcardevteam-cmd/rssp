param(
    [Parameter(Mandatory = $true)]
    [string]$Url,

    [ValidateSet('edge', 'chrome')]
    [string]$Browser = 'edge',

    [switch]$KillExisting
)

$ErrorActionPreference = 'Stop'

if ([string]::IsNullOrWhiteSpace($Url)) {
    throw 'URL is required.'
}

if (-not ($Url.StartsWith('http://') -or $Url.StartsWith('https://'))) {
    throw 'URL must start with http:// or https://'
}

$browserMap = @{
    edge = @(
        "$Env:ProgramFiles\Microsoft\Edge\Application\msedge.exe",
        "$Env:ProgramFiles(x86)\Microsoft\Edge\Application\msedge.exe"
    )
    chrome = @(
        "$Env:ProgramFiles\Google\Chrome\Application\chrome.exe",
        "$Env:ProgramFiles(x86)\Google\Chrome\Application\chrome.exe"
    )
}

$exePath = $browserMap[$Browser] | Where-Object { Test-Path $_ } | Select-Object -First 1
if (-not $exePath) {
    throw "Could not find $Browser executable."
}

if ($KillExisting) {
    if ($Browser -eq 'edge') {
        Get-Process -Name msedge -ErrorAction SilentlyContinue | Stop-Process -Force
    }
    if ($Browser -eq 'chrome') {
        Get-Process -Name chrome -ErrorAction SilentlyContinue | Stop-Process -Force
    }
}

$args = @()
if ($Browser -eq 'edge') {
    $args += '--kiosk'
    $args += $Url
    $args += '--edge-kiosk-type=fullscreen'
    $args += '--no-first-run'
    $args += '--disable-features=msUndersideButton'
    $args += '--disable-pinch'
    $args += '--overscroll-history-navigation=0'
}
else {
    $args += '--kiosk'
    $args += $Url
    $args += '--no-first-run'
    $args += '--disable-pinch'
    $args += '--overscroll-history-navigation=0'
}

Start-Process -FilePath $exePath -ArgumentList $args | Out-Null
Write-Host "Kiosk mode launched in $Browser for $Url"
