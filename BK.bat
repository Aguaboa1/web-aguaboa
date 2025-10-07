@echo off
cd /d "C:\xampp\htdocs\gestao-aguaboa-php"
REM Run PowerShell backup script (BK.ps1) which creates ZIP and runs BK.php for DB dump
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0BK.ps1" %*
pause