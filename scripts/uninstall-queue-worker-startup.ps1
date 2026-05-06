$startupDir = [Environment]::GetFolderPath('Startup')
$launcherPath = Join-Path $startupDir 'DILG-CAR-Queue-Worker.vbs'

if (Test-Path $launcherPath) {
    Remove-Item $launcherPath -Force
    Write-Host "Removed startup launcher: $launcherPath"
} else {
    Write-Host "No startup launcher found at: $launcherPath"
}

