#!/usr/bin/env pwsh

Write-Host "🚀 DÉPLOIEMENT FEATURE APERÇU & TÉLÉCHARGEMENT" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan

# Charger la configuration FTP
$envPath = ".\tools\ftp-config.env"
if (-not (Test-Path $envPath)) {
    Write-Host "❌ Fichier de configuration non trouvé: $envPath" -ForegroundColor Red
    exit 1
}

$env:Path += ";C:\Program Files\OpenSSH"
Get-Content $envPath | ForEach-Object {
    if ($_ -match '^\s*([^#=]+)\s*=\s*(.*)') {
        $key = $matches[1].Trim()
        $value = $matches[2].Trim()
        [Environment]::SetEnvironmentVariable($key, $value)
    }
}

$FTP_SERVER = [Environment]::GetEnvironmentVariable("FTP_SERVER")
$FTP_USER = [Environment]::GetEnvironmentVariable("FTP_USER")
$FTP_PASSWORD = [Environment]::GetEnvironmentVariable("FTP_PASSWORD")
$FTP_DESTINATION = [Environment]::GetEnvironmentVariable("FTP_DESTINATION")

if (-not $FTP_SERVER -or -not $FTP_USER -or -not $FTP_PASSWORD -or -not $FTP_DESTINATION) {
    Write-Host "❌ Configuration FTP incomplète" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Configuration FTP chargée"
Write-Host "   Serveur: $FTP_SERVER"

# Fichiers à déployer (la feature d'aperçu/téléchargement)
$files_to_deploy = @(
    "src/Admin/PDF_Builder_Admin.php",
    "src/Managers/PDF_Builder_WooCommerce_Integration.php"
)

Write-Host "`n📦 Fichiers à déployer:"
$files_to_deploy | ForEach-Object { Write-Host "   - $_" }

# Créer le script WinSCP
$winscp_script = @"
option batch abort
option confirm off
open sftp://${FTP_USER}:${FTP_PASSWORD}@${FTP_SERVER}
cd ${FTP_DESTINATION}
"@

foreach ($file in $files_to_deploy) {
    if (Test-Path $file) {
        $remote_path = $file.Replace("\", "/")
        $winscp_script += "`nput ""$file"" ""$remote_path"""
        Write-Host "✅ Ajout du fichier: $file"
    } else {
        Write-Host "⚠️  Fichier non trouvé: $file" -ForegroundColor Yellow
    }
}

$winscp_script += "`nexit"

# Sauvegarder le script WinSCP
$script_path = ".\winscp_deploy.txt"
$winscp_script | Out-File -Encoding ASCII $script_path

Write-Host "`n📤 Déploiement des fichiers..."

# Vérifier si WinSCP existe
$winscp_path = "C:\Program Files (x86)\WinSCP\WinSCP.com"
if (-not (Test-Path $winscp_path)) {
    Write-Host "⚠️  WinSCP pas trouvé. Tentative avec SSH..." -ForegroundColor Yellow
    
    # Utiliser SSH directement (à implémenter)
    Write-Host "❌ SSH non implémenté. Veuillez installer WinSCP." -ForegroundColor Red
    Remove-Item $script_path -Force
    exit 1
}

# Exécuter WinSCP
& $winscp_path /script=$script_path

# Vérifier le résultat
if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ Déploiement réussi !" -ForegroundColor Green
    Write-Host "📋 Fichiers déployés:"
    $files_to_deploy | ForEach-Object { Write-Host "   ✓ $_" }
    
    # Nettoyer
    Remove-Item $script_path -Force
    
    Write-Host "`n🎉 Vous pouvez maintenant tester la feature sur le site!" -ForegroundColor Green
    Write-Host "   https://threeaxe.fr/wp-admin"
    
} else {
    Write-Host "`n❌ Erreur lors du déploiement (Code: $LASTEXITCODE)" -ForegroundColor Red
    Remove-Item $script_path -Force
    exit 1
}
