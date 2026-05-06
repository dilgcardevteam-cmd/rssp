@echo off
setlocal

if "%~1"=="" (
  echo Usage: launch-exam-kiosk.cmd ^<exam_url^> [edge^|chrome]
  exit /b 1
)

set "EXAM_URL=%~1"
set "BROWSER=%~2"
if "%BROWSER%"=="" set "BROWSER=edge"

powershell -ExecutionPolicy Bypass -File "%~dp0launch-exam-kiosk.ps1" -Url "%EXAM_URL%" -Browser "%BROWSER%" -KillExisting
