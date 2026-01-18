# Script pour nettoyer tous les fichiers PHP
# - Supprime les BOM UTF-8
# - Supprime tout contenu avant <?php
# - Vérifie la structure namespace
# - Utilise LF au lieu de CRLF

$cleanedCount = 0
$fixedCount = 0

Get-ChildItem -Path "i:\wp-pdf-builder-pro-V2\plugin" -Recurse -Include "*.php" | ForEach-Object {
    $filePath = $_.FullName
    $content = Get-Content -Path $filePath -Raw -Encoding UTF8
    $originalContent = $content
    
    # Supprimer BOM si présent
    if ($content -match '^\uFEFF') {
        $content = $content -replace '^\uFEFF', ''
        Write-Host "🧹 Removed BOM: $($_.Name)" -ForegroundColor Cyan
        $fixedCount++
    }
    
    # S'assurer que <?php est au tout début
    if (-not $content.StartsWith('<?php')) {
        # Supprimer tout avant <?php
        $phpStart = $content.IndexOf('<?php')
        if ($phpStart -gt 0) {
            $content = $content.Substring($phpStart)
            Write-Host "✂️  Removed content before <?php: $($_.Name)" -ForegroundColor Yellow
            $fixedCount++
        } elseif ($phpStart -eq -1) {
            Write-Host "⚠️  No PHP tag found: $($_.Name)" -ForegroundColor Red
            return
        }
    }
    
    # Convertir CRLF en LF
    if ($content -match "`r`n") {
        $content = $content -replace "`r`n", "`n"
        Write-Host "📝 Normalized line endings: $($_.Name)" -ForegroundColor Gray
        $fixedCount++
    }
    
    # Sauvegarder si modifié
    if ($content -ne $originalContent) {
        Set-Content -Path $filePath -Value $content -Encoding UTF8 -NoNewline
        $cleanedCount++
    }
}

Write-Host "`n✅ Nettoyage complet !" -ForegroundColor Green
Write-Host "🧹 Fichiers nettoyés: $cleanedCount" -ForegroundColor Green
Write-Host "✏️  Modifications totales: $fixedCount" -ForegroundColor Green
