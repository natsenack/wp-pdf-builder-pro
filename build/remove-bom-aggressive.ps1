# Script de suppression agressive du BOM UTF-8
# Ce script nettoie TOUS les fichiers PHP du BOM UTF-8 qui cause l'erreur namespace

param(
    [string]$PluginDir = "i:\wp-pdf-builder-pro-V2\plugin"
)

Write-Host "🔧 Nettoyage aggressif des fichiers PHP (Suppression BOM UTF-8)" -ForegroundColor Cyan
Write-Host "================================================================`n" -ForegroundColor Cyan

$phpFiles = Get-ChildItem -Path $PluginDir -Filter "*.php" -Recurse -File
$cleanedCount = 0
$bomRemovedCount = 0

foreach ($file in $phpFiles) {
    try {
        # Lire le contenu brut en bytes
        $bytes = [System.IO.File]::ReadAllBytes($file.FullName)
        
        # Vérifier s'il y a un BOM UTF-8 (EF BB BF)
        $hasBom = $bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF
        
        if ($hasBom) {
            # Supprimer le BOM
            $cleanBytes = $bytes[3..($bytes.Length - 1)]
            [System.IO.File]::WriteAllBytes($file.FullName, $cleanBytes)
            
            Write-Host "✂️  BOM supprimé: $($file.Name)" -ForegroundColor Yellow
            $bomRemovedCount++
        }
        
        $cleanedCount++
    }
    catch {
        Write-Host "❌ Erreur sur $($file.Name): $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "`n📊 Résumé:" -ForegroundColor Cyan
Write-Host "   Total fichiers PHP: $($phpFiles.Count)" -ForegroundColor Gray
Write-Host "   Fichiers traités: $cleanedCount" -ForegroundColor Gray
Write-Host "   BOM supprimés: $bomRemovedCount" -ForegroundColor Green

Write-Host "`n✅ Nettoyage BOM terminé!" -ForegroundColor Green
