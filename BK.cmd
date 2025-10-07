@echo off
rem Wrapper to run the bk.ps1 script from CMD or PowerShell by typing BK
set SCRIPT_PATH=%~dp0bk.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File "%SCRIPT_PATH%" %*
