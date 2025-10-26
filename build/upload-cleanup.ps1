# Script FTP pour uploader le cleanup.php et supprimer le plugin

param(
    [string]$FtpHost = "65.108.242.181",
    [string]$FtpUser = "ftp",
    [string]$FtpPass = "t-3=,DGq%Z8("
)

Write-Host "🧹 NETTOYAGE COMPLET DU PLUGIN" -ForegroundColor Cyan
Write-Host "===============================" -ForegroundColor Cyan
Write-Host ""

# 1. Upload cleanup.php
Write-Host "📤 Étape 1 : Envoi du script de nettoyage..." -ForegroundColor Yellow

$ftp_uri = "ftp://${FtpHost}/wp-content/plugins/cleanup.php"
$local_file = "D:\wp-pdf-builder-pro\cleanup.php"

try {
    $request = [System.Net.FtpWebRequest]::Create($ftp_uri)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
    $request.UseBinary = $true
    
    $fileStream = [System.IO.File]::OpenRead($local_file)
    $request.ContentLength = $fileStream.Length
    
    $requestStream = $request.GetRequestStream()
    $fileStream.CopyTo($requestStream)
    $requestStream.Close()
    
    $response = $request.GetResponse()
    $response.Close()
    
    Write-Host "✅ cleanup.php uploade avec succès" -ForegroundColor Green
    Write-Host ""
    Write-Host "📍 URL : http://threeaxe.fr/wp-content/plugins/cleanup.php" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "🔗 Ouvrez cette URL dans votre navigateur :" -ForegroundColor Green
    Write-Host "   http://threeaxe.fr/wp-content/plugins/cleanup.php?cleanup_key=clean-wp-pdf-builder-pro-2025" -ForegroundColor Green
    Write-Host ""
    Write-Host "⏳ Attendez le message de confirmation..." -ForegroundColor Yellow
    Write-Host ""
    
} catch {
    Write-Host "❌ Erreur lors de l'upload : $_" -ForegroundColor Red
}
