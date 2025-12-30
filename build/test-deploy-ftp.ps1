# Script de test du déploiement FTP
# Vérifie la connexion et liste les fichiers sans les uploader

param(
    [switch]$Detailed
)

$ErrorActionPreference = "Stop"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

$WorkingDir = "I:\wp-pdf-builder-pro"
$PluginDir = Join-Path $WorkingDir "plugin"

Write-Host "TEST DEPLOIEMENT FTP - PDF Builder Pro" -ForegroundColor Cyan
Write-Host ("=" * 40) -ForegroundColor White

# Test connexion FTP
Write-Host "`n1. Test de connexion FTP..." -ForegroundColor Magenta
try {
    $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost/"
    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $ftpRequest.UseBinary = $false
    $ftpRequest.UsePassive = $true
    $ftpRequest.Timeout = 5000
    $ftpRequest.KeepAlive = $false
    $response = $ftpRequest.GetResponse()
    $response.Close()
    Write-Host "   ✓ Connexion FTP OK" -ForegroundColor Green
} catch {
    Write-Host "   ✗ Erreur FTP: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Collecter les fichiers
Write-Host "`n2. Analyse des fichiers..." -ForegroundColor Magenta

$allFiles = @()
$fileStats = @{
    php = 0
    js = 0
    css = 0
    images = 0
    other = 0
    totalSize = 0
}

Get-ChildItem -Path $PluginDir -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Replace($PluginDir, "").TrimStart("\")
    $allFiles += $relativePath

    # Statistiques
    $fileStats.totalSize += $_.Length
    $ext = $_.Extension.ToLower()
    switch ($ext) {
        ".php" { $fileStats.php++ }
        ".js" { $fileStats.js++ }
        ".css" { $fileStats.css++ }
        {$_ -in ".jpg",".jpeg",".png",".gif",".svg",".ico"} { $fileStats.images++ }
        default { $fileStats.other++ }
    }
}

Write-Host "   Fichiers trouvés: $($allFiles.Count)" -ForegroundColor Cyan
Write-Host "   Taille totale: $([math]::Round($fileStats.totalSize / 1MB, 2)) MB" -ForegroundColor Cyan

if ($Detailed) {
    Write-Host "`n   Statistiques détaillées:" -ForegroundColor Gray
    Write-Host "   - PHP: $($fileStats.php) fichiers" -ForegroundColor Gray
    Write-Host "   - JavaScript: $($fileStats.js) fichiers" -ForegroundColor Gray
    Write-Host "   - CSS: $($fileStats.css) fichiers" -ForegroundColor Gray
    Write-Host "   - Images: $($fileStats.images) fichiers" -ForegroundColor Gray
    Write-Host "   - Autres: $($fileStats.other) fichiers" -ForegroundColor Gray
}

# Test upload d'un petit fichier
Write-Host "`n3. Test d'upload..." -ForegroundColor Magenta

$testFile = Join-Path $WorkingDir "build\DEPLOIEMENT_FTP_SIMPLE.md"
if (Test-Path $testFile) {
    try {
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$FtpPath/test-connection.tmp"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000
        $ftpRequest.KeepAlive = $false

        $testContent = [System.Text.Encoding]::UTF8.GetBytes("Test de connexion - $(Get-Date)")
        $ftpRequest.ContentLength = $testContent.Length

        $stream = $ftpRequest.GetRequestStream()
        $stream.Write($testContent, 0, $testContent.Length)
        $stream.Close()

        $response = $ftpRequest.GetResponse()
        $response.Close()

        Write-Host "   ✓ Upload de test réussi" -ForegroundColor Green
    } catch {
        Write-Host "   ✗ Erreur upload test: $($_.Exception.Message)" -ForegroundColor Red
    }
} else {
    Write-Host "   ⚠ Fichier de test introuvable" -ForegroundColor Yellow
}

Write-Host "`n4. Prêt pour le déploiement!" -ForegroundColor Green
Write-Host ("=" * 40) -ForegroundColor White
Write-Host "Commandes disponibles:" -ForegroundColor Cyan
Write-Host "  .\deploy-ftp-simple.ps1          # Déploiement complet" -ForegroundColor White
Write-Host "  .\deploy-ftp-simple.ps1 -TestMode # Liste des fichiers seulement" -ForegroundColor White
Write-Host "  .\deploy-ftp-simple.ps1 -FastMode # Mode rapide" -ForegroundColor White
