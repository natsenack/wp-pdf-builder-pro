#!/usr/bin/env powershell
<#
.SYNOPSIS
    Vérifie les erreurs du serveur après déploiement
#>

param(
    [string]$FtpHost = "65.108.242.181",
    [string]$FtpUser = "nats",
    [string]$FtpPass = "iZ6vU3zV2y",
    [string]$RemotePath = "/wp-content/plugins/wp-pdf-builder-pro"
)

function Get-FtpFile {
    param(
        [string]$remoteFile,
        [string]$localPath
    )
    
    try {
        $ftpUri = "ftp://$FtpHost$remoteFile"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
        $ftpRequest.UseBinary = $false
        $ftpRequest.UsePassive = $true
        
        $response = $ftpRequest.GetResponse()
        $stream = $response.GetResponseStream()
        
        $content = @()
        $reader = New-Object System.IO.StreamReader($stream)
        while ($null -ne ($line = $reader.ReadLine())) {
            $content += $line
        }
        $reader.Close()
        $response.Close()
        
        return $content -join "`n"
    }
    catch {
        Write-Host "❌ Erreur FTP: $($_.Exception.Message)" -ForegroundColor Red
        return $null
    }
}

Write-Host "🔍 Diagnostic du serveur après déploiement`n" -ForegroundColor Cyan

# Vérifier le fichier PDF_Builder_Asset_Optimizer.php
Write-Host "📄 Vérifie le fichier problématique (PDF_Builder_Asset_Optimizer.php)..." -ForegroundColor Yellow
$optFile = Get-FtpFile "$RemotePath/src/Managers/PDF_Builder_Asset_Optimizer.php" $null
if ($optFile) {
    $lines = $optFile -split "`n"
    Write-Host "   ✓ Fichier trouvé, affichage des 5 premières lignes:" -ForegroundColor Green
    for ($i = 0; $i -lt [Math]::Min(5, $lines.Length); $i++) {
        Write-Host "   L$(($i+1).ToString('000')): $($lines[$i])" -ForegroundColor Gray
    }
}

# Vérifier bootstrap.php
Write-Host "`n📄 Vérifie bootstrap.php..." -ForegroundColor Yellow
$bootstrapFile = Get-FtpFile "$RemotePath/bootstrap.php" $null
if ($bootstrapFile) {
    $lines = $bootstrapFile -split "`n"
    Write-Host "   ✓ Fichier trouvé, affichage des premières lignes:" -ForegroundColor Green
    for ($i = 0; $i -lt [Math]::Min(5, $lines.Length); $i++) {
        Write-Host "   L$(($i+1).ToString('000')): $($lines[$i])" -ForegroundColor Gray
    }
}

Write-Host "`n✅ Diagnostic terminé" -ForegroundColor Green
