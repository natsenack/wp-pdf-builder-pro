# Analyse des erreurs PHP dans le projet PDF Builder Pro

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "PDF BUILDER PRO - ANALYSE DES ERREURS"
Write-Host "==========================================" -ForegroundColor Cyan

Write-Host "Analyse des fichiers PHP..." -ForegroundColor Yellow

# Compter les fichiers PHP
$phpFiles = Get-ChildItem -Path "..\plugin\src" -Recurse -Filter "*.php"
$totalFiles = $phpFiles.Count

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "RAPPORT D'ANALYSE"
Write-Host "==========================================" -ForegroundColor Cyan

Write-Host "Fichiers PHP analyses: $totalFiles" -ForegroundColor White

# Analyser les erreurs de syntaxe
$errorsFound = 0
$filesWithErrors = @()

foreach ($file in $phpFiles) {
    $filePath = $file.FullName
    try {
        $result = & php -l $filePath 2>&1
        if ($result -match "Fatal error|Parse error") {
            $errorsFound++
            $filesWithErrors += $filePath
        }
    } catch {
        $errorsFound++
        $filesWithErrors += $filePath
    }
}

Write-Host "Fichiers avec erreurs: $errorsFound" -ForegroundColor Red
Write-Host "Total d'erreurs: $errorsFound" -ForegroundColor Red

if ($errorsFound -gt 0) {
    Write-Host "`nDETAIL DES ERREURS:" -ForegroundColor Yellow
    foreach ($file in $filesWithErrors) {
        Write-Host "ERREUR: $file" -ForegroundColor Red
    }
}

Write-Host "==========================================" -ForegroundColor Cyan