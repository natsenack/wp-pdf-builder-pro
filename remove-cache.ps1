# Script pour supprimer le cache dynamique (get_option) dans tous les fichiers PHP
Write-Host "Suppression du cache dynamique dans tous les fichiers PHP..." -ForegroundColor Yellow

# Liste des fichiers PHP à traiter
$phpFiles = Get-ChildItem -Path "plugin\src" -Filter "*.php" -Recurse | Where-Object {
    $_.FullName -notlike "*DISABLED*" -and
    $_.FullName -notlike "*backup*" -and
    $_.FullName -notlike "*temp*"
}

Write-Host "Traitement de $($phpFiles.Count) fichiers PHP..." -ForegroundColor Cyan

foreach ($file in $phpFiles) {
    $content = Get-Content $file.FullName -Raw

    # Remplacer get_option par des valeurs fixes simples
    $content = $content -replace 'get_option\([^)]+\)', 'false'

    # Écrire le contenu modifié
    Set-Content -Path $file.FullName -Value $content -Encoding UTF8

    Write-Host "✓ Traité: $($file.Name)" -ForegroundColor Green
}

Write-Host "Cache dynamique supprimé de tous les fichiers !" -ForegroundColor Green
