@echo off
setlocal

set "ROOT=%~dp0.."
cd /d "%ROOT%"

set "PHP_BIN=php"
if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"

:loop
"%PHP_BIN%" artisan queue:work --queue=default --sleep=1 --tries=3 --timeout=120 --memory=512
timeout /t 5 /nobreak >nul
goto loop

