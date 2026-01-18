#!/usr/bin/env powershell
<#
.SYNOPSIS
    Récupère et affiche les logs d'erreurs du serveur WordPress
#>

param(
    [string]$FtpHost = "65.108.242.181",
    [string]$FtpUser = "nats",
    [string]$FtpPass = "iZ6vU3zV2y"
)

function Get-FtpFileLines {
    param(
        [string]$remoteFile,
        [int]$lineCount = 50
    )
    
    try {
        $ftpUri = "ftp://$FtpHost$remoteFile"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
        $ftpRequest.UseBinary = $false
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 15000
        
        $response = $ftpRequest.GetResponse()
        $stream = $response.GetResponseStream()
        
        $lines = @()
        $reader = New-Object System.IO.StreamReader($stream)
        while ($null -ne ($line = $reader.ReadLine())) {
            $lines += $line
        }
        $reader.Close()
        $response.Close()
        
        # Retourner les dernières N lignes
        if ($lines.Count -gt $lineCount) {
            return $lines[($lines.Count - $lineCount)..$($lines.Count - 1)]
        } else {
            return $lines
        }
    }
    catch {
        return $null
    }
}

Write-Host "📋 Vérification des logs d'erreurs WordPress`n" -ForegroundColor Cyan

# Chemins possibles des logs d'erreurs
$logPaths = @(
    "/var/www/nats/data/www/threeaxe.fr/wp-content/debug.log",
    "/var/www/nats/data/www/threeaxe.fr/wp-content/php-error.log",
    "/var/log/php-errors.log"
)

foreach ($logPath in $logPaths) {
    Write-Host "🔍 Cherche le log: $logPath" -ForegroundColor Yellow
    
    $lines = Get-FtpFileLines $logPath 30
    if ($lines) {
        Write-Host "   ✓ Trouvé! Dernières erreurs:" -ForegroundColor Green
        $lines | ForEach-Object {
            if ($_ -match "Fatal|Error|Warning|namespace|PDF_Builder") {
                Write-Host "   🔴 $_" -ForegroundColor Red
            } elseif ($_ -match "Notice|Deprecated") {
                Write-Host "   🟡 $_" -ForegroundColor Yellow
            }
        }
        Write-Host ""
    } else {
        Write-Host "   ❌ Non trouvé`n" -ForegroundColor Gray
    }
}

Write-Host "✅ Vérification terminée" -ForegroundColor Green
