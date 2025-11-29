# Script complet de nettoyage de TOASTR sur le serveur distant
# Recherche et supprime tous les fichiers contenant des références à toastr

param(
    [switch]$TestMode,
    [switch]$DeepScan
)

$ErrorActionPreference = "Stop"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "NETTOYAGE COMPLET TOASTR SUR SERVEUR DISTANT" -ForegroundColor Cyan
Write-Host ("=" * 70) -ForegroundColor White

if ($TestMode) {
    Write-Host "MODE TEST - Simulation uniquement" -ForegroundColor Yellow
}

# Fonction pour lister récursivement tous les fichiers sur le FTP
function Get-FtpDirectoryListing {
    param([string]$ftpPath)

    try {
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$ftpPath/"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectoryDetails
        $ftpRequest.UseBinary = $false
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000
        $ftpRequest.KeepAlive = $false

        $response = $ftpRequest.GetResponse()
        $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
        $listing = $reader.ReadToEnd()
        $reader.Close()
        $response.Close()

        return $listing -split "`n" | Where-Object { $_ -and $_.Trim() } | ForEach-Object {
            # Parser la ligne de listing FTP (format Unix-like)
            if ($_ -match '^(d|l|-)([rwx-]{9})\s+\d+\s+\w+\s+\w+\s+(\d+)\s+\w+\s+\d+\s+[\d:]+\s+(.+)$') {
                $type = $matches[1]
                $size = [int]$matches[3]
                $name = $matches[4]

                @{
                    Name = $name
                    IsDirectory = ($type -eq 'd')
                    Size = $size
                    FullPath = "$ftpPath/$name".Replace('//', '/')
                }
            }
        }
    } catch {
        Write-Host "Erreur lors du listing FTP: $($_.Exception.Message)" -ForegroundColor Red
        return @()
    }
}

# Fonction récursive pour scanner tous les fichiers
function Scan-FtpFiles {
    param([string]$currentPath, [int]$depth = 0)

    if ($depth -gt 10) { return @() } # Limite de profondeur

    $files = @()
    $items = Get-FtpDirectoryListing -ftpPath $currentPath

    foreach ($item in $items) {
        if ($item.IsDirectory -and $item.Name -notmatch '^\.\.?$') {
            # Scanner récursivement les sous-dossiers
            $subFiles = Scan-FtpFiles -currentPath $item.FullPath -depth ($depth + 1)
            $files += $subFiles
        } else {
            # Ajouter le fichier à la liste
            $files += $item
        }
    }

    return $files
}

# Fonction pour vérifier si un fichier contient "toastr"
function Test-FileContainsToastr {
    param([string]$filePath)

    try {
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$filePath"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 5000
        $ftpRequest.KeepAlive = $false

        $response = $ftpRequest.GetResponse()
        $stream = $response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($stream)
        $content = $reader.ReadToEnd()
        $reader.Close()
        $response.Close()

        # Vérifier si le contenu contient "toastr" (insensible à la casse)
        return $content -match '(?i)toastr'

    } catch {
        return $false
    }
}

Write-Host "`nScanning du serveur FTP pour les fichiers contenant 'toastr'..." -ForegroundColor Magenta

# Scanner tous les fichiers JavaScript et CSS
$allFiles = Scan-FtpFiles -currentPath $FtpPath
$suspiciousFiles = @()

Write-Host "Fichiers trouvés: $($allFiles.Count)" -ForegroundColor Gray

# Filtrer les fichiers suspects (JS, CSS, PHP qui pourraient contenir toastr)
$jsCssFiles = $allFiles | Where-Object {
    $_.Name -match '\.(js|css|php)$' -and
    $_.Name -notmatch '(jquery|bootstrap|admin|wp-)' -and  # Exclure les libs communes
    $_.Size -gt 0 -and $_.Size -lt 1000000  # Fichiers de taille raisonnable
}

Write-Host "Fichiers à analyser: $($jsCssFiles.Count)" -ForegroundColor Gray

foreach ($file in $jsCssFiles) {
    Write-Host "   Analyse: $($file.Name)" -ForegroundColor Gray
    if (Test-FileContainsToastr -filePath $file.FullPath) {
        $suspiciousFiles += $file
        Write-Host "   ⚠️  SUSPECT: $($file.Name) contient 'toastr'" -ForegroundColor Yellow
    }
}

# Ajouter les fichiers connus du système de notification
$knownNotificationFiles = @(
    "assets/js/notifications.js",
    "assets/css/notifications.css",
    "core/PDF_Builder_Notification_Manager.php",
    "templates/notifications/toast-container.php",
    "templates/notifications/toast-script.php",
    "templates/notifications/admin-notice.php",
    "notifications-test.php"
)

$filesToDelete = @()
$filesToDelete += $suspiciousFiles | Select-Object -ExpandProperty FullPath
$filesToDelete += $knownNotificationFiles | ForEach-Object { "$FtpPath/$_" }

# Supprimer les doublons
$filesToDelete = $filesToDelete | Sort-Object -Unique

Write-Host "`nFichiers à supprimer:" -ForegroundColor Magenta
$filesToDelete | ForEach-Object {
    $shortName = $_.Replace("$FtpPath/", "")
    Write-Host "   - $shortName" -ForegroundColor White
}

$deletedCount = 0
$errorCount = 0

if ($TestMode) {
    Write-Host "`nMODE TEST - Aucune suppression réelle" -ForegroundColor Yellow
    $deletedCount = $filesToDelete.Count
} else {
    Write-Host "`nSuppression des fichiers..." -ForegroundColor Magenta

    foreach ($file in $filesToDelete) {
        try {
            Write-Host "   Suppression: $($file.Replace("$FtpPath/", ''))" -ForegroundColor Yellow

            $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$file"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
            $ftpRequest.UseBinary = $false
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 10000
            $ftpRequest.KeepAlive = $false

            $response = $ftpRequest.GetResponse()
            $response.Close()

            Write-Host "   ✅ Supprimé" -ForegroundColor Green
            $deletedCount++

        } catch {
            $errorCount++
            Write-Host "   ❌ Erreur: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}

Write-Host "`nNETTOYAGE TOASTR TERMINÉ" -ForegroundColor White
Write-Host ("=" * 70) -ForegroundColor White
Write-Host "Résumé:" -ForegroundColor Cyan
Write-Host "   Fichiers analysés: $($jsCssFiles.Count)" -ForegroundColor Gray
Write-Host "   Fichiers suspects trouvés: $($suspiciousFiles.Count)" -ForegroundColor Yellow
Write-Host "   Fichiers supprimés: $deletedCount" -ForegroundColor Green
Write-Host "   Erreurs: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })

if ($TestMode) {
    Write-Host "`nPour exécuter réellement le nettoyage, relancer sans -TestMode" -ForegroundColor Yellow
} else {
    Write-Host "`nServeur distant nettoyé de toutes références toastr ✅" -ForegroundColor Green
}