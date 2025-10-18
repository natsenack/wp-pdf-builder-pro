#!/usr/bin/env pwsh

Write-Host "🚀 DÉPLOIEMENT SFTP - APERÇU & TÉLÉCHARGEMENT" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan

# Configuration
$FTP_HOST = "65.108.242.181"
$FTP_USER = "nats"
$FTP_PASS = "Threeaxe#2024"
$FTP_PATH = "/wp-content/plugins/wp-pdf-builder-pro"

$files_to_deploy = @(
    @{ local = "src\Admin\PDF_Builder_Admin.php"; remote = "src/Admin/PDF_Builder_Admin.php" },
    @{ local = "src\Managers\PDF_Builder_WooCommerce_Integration.php"; remote = "src/Managers/PDF_Builder_WooCommerce_Integration.php" }
)

Write-Host "`n📦 Fichiers à déployer:"
$files_to_deploy | ForEach-Object { Write-Host "   - $($_.local)" }

# Utiliser curl pour uploader via FTP
Write-Host "`n📤 Déploiement via FTP..."

$failed = $false

foreach ($file in $files_to_deploy) {
    $local_path = $file.local
    $remote_path = $file.remote
    
    if (-not (Test-Path $local_path)) {
        Write-Host "❌ Fichier non trouvé: $local_path" -ForegroundColor Red
        $failed = $true
        continue
    }
    
    $remote_url = "ftp://${FTP_HOST}${FTP_PATH}/${remote_path}"
    
    Write-Host "   ⬆️  $local_path..."
    
    try {
        # Utiliser curl pour uploader
        $result = curl.exe -T "$local_path" --user "${FTP_USER}:${FTP_PASS}" "$remote_url" -s -o /dev/null -w "%{http_code}"
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "      ✅ Succès"
        } else {
            Write-Host "      ❌ Erreur (code: $LASTEXITCODE)"
            $failed = $true
        }
    } catch {
        Write-Host "      ❌ Exception: $_" -ForegroundColor Red
        $failed = $true
    }
}

if ($failed) {
    Write-Host "`n❌ Certains fichiers n'ont pas pu être déployés" -ForegroundColor Red
    exit 1
} else {
    Write-Host "`n✅ Déploiement réussi !" -ForegroundColor Green
    Write-Host "   Vous pouvez tester sur: https://threeaxe.fr/wp-admin" -ForegroundColor Green
}
