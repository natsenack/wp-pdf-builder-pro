# Script pour corriger les déclarations de namespace dupliquées
$fixedCount = 0
$errorCount = 0

Get-ChildItem -Path "i:\wp-pdf-builder-pro-V2\plugin" -Recurse -Include "*.php" | ForEach-Object {
    $filePath = $_.FullName
    $content = Get-Content -Path $filePath -Raw -Encoding UTF8
    
    # Chercher "namespace namespace" et le remplacer par "namespace"
    if ($content -match "namespace namespace ") {
        $corrected = $content -replace "namespace namespace ", "namespace "
        Set-Content -Path $filePath -Value $corrected -Encoding UTF8
        Write-Host "✅ Fixed double namespace: $($_.Name)" -ForegroundColor Green
        $fixedCount++
    }
}

Write-Host "`n✅ Correction complète !" -ForegroundColor Green
Write-Host "📊 Fichiers corrigés: $fixedCount" -ForegroundColor Green
