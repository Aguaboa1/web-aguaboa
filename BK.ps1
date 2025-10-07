Param(
    [string]$SourcePath = 'C:\xampp\htdocs\gestao-aguaboa-php',
    [string]$BackupsRoot = 'C:\xampp\htdocs\Backups'
)

try {
    $timestamp = (Get-Date).ToString('yyyy-MM-dd-HHmm')
    if (-not (Test-Path -LiteralPath $BackupsRoot)) {
        New-Item -ItemType Directory -Path $BackupsRoot -Force | Out-Null
    }

    $zipName = "gestao-aguaboa-php-backup-$timestamp.zip"
    $zipPath = Join-Path $BackupsRoot $zipName

    Write-Output "Creating backup ZIP: $zipPath"

    # Use Compress-Archive (avoids permission issues with audit data)
    if (-not (Test-Path -LiteralPath $SourcePath)) {
        throw "Source path does not exist: $SourcePath"
    }

    Compress-Archive -Path (Join-Path $SourcePath '*') -DestinationPath $zipPath -Force -ErrorAction Stop

    $file = Get-Item -LiteralPath $zipPath
    Write-Output "ZIP_CREATED:$($file.FullName)"
    Write-Output "ZIP_SIZE:$($file.Length)"
    exit 0
}
catch {
    Write-Error "Backup failed: $($_.Exception.Message)"
    exit 2
}
# COMANDO BK - PowerShell
# Execute: .\BK.ps1

Write-Host "🚀 COMANDO BK - BACKUP INSTANTÂNEO" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan

$projectPath = "C:\xampp\htdocs\gestao-aguaboa-php"
Set-Location $projectPath

& "c:\xampp\php\php.exe" "BK.php"

Write-Host "`n⚡ Digite 'BK' sempre que quiser fazer backup!" -ForegroundColor Green