param(
    [switch]$Force
)

$ErrorActionPreference = 'Stop'

$startupDir = [Environment]::GetFolderPath('Startup')
$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$workerCmd = (Resolve-Path (Join-Path $PSScriptRoot 'run-queue-worker.cmd')).Path
$launcherPath = Join-Path $startupDir 'DILG-CAR-Queue-Worker.vbs'

if ((Test-Path $launcherPath) -and (-not $Force)) {
    Write-Host "Startup launcher already exists: $launcherPath"
    Write-Host "Use -Force to overwrite."
    exit 0
}

$escapedCmd = $workerCmd.Replace('\', '\\')
$vbs = @"
Set shell = CreateObject("WScript.Shell")
shell.CurrentDirectory = "$repoRoot"
shell.Run Chr(34) & "$escapedCmd" & Chr(34), 0, False
"@

try {
    Set-Content -Path $launcherPath -Value $vbs -Encoding ASCII
    Write-Host "Installed startup launcher:"
    Write-Host "  $launcherPath"
    Write-Host "Queue worker will start automatically on Windows login."
} catch {
    Write-Error "Failed to install startup launcher at '$launcherPath'. Run PowerShell with enough permissions and retry."
    throw
}
