# Script simplifié pour supprimer TOASTR du serveur distant
# Vérifie et supprime les fichiers spécifiques contenant toastr

param(
    [switch]$TestMode
)

$ErrorActionPreference = "Stop"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "NETTOYAGE TOASTR CIBLÉ SUR SERVEUR DISTANT" -ForegroundColor Cyan
Write-Host ("=" * 70) -ForegroundColor White

if ($TestMode) {
    Write-Host "MODE TEST - Simulation uniquement" -ForegroundColor Yellow
}

# Fonction pour vérifier si un fichier contient "toastr"
function Test-FileContainsToastr {
    param([string]$remotePath)

    try {
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$remotePath"
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
        # Fichier n'existe pas ou erreur d'accès
        return $false
    }
}

# Liste des fichiers à vérifier et potentiellement supprimer
$filesToCheck = @(
    # Fichiers JavaScript principaux
    "assets/js/pdf-preview-api-client.js",
    "assets/js/pdf-preview-integration.js",
    "assets/js/dist/pdf-builder-react.js",

    # Fichiers CSS
    "assets/css/admin.css",
    "assets/css/frontend.css",

    # Templates PHP qui pourraient contenir du JS inline
    "templates/admin/settings-parts/settings-general.php",
    "templates/admin/settings-parts/settings-licence.php",

    # Fichiers de bibliothèque potentiels
    "assets/js/toastr.js",
    "assets/js/toastr.min.js",
    "assets/css/toastr.css",
    "assets/css/toastr.min.css",

    # Anciens fichiers de notification
    "assets/js/notifications.js",
    "assets/css/notifications.css"
)

Write-Host "`nVérification des fichiers pour 'toastr'..." -ForegroundColor Magenta

$suspiciousFiles = @()

foreach ($file in $filesToCheck) {
    $remotePath = "$FtpPath/$file"
    Write-Host "   Vérification: $file" -ForegroundColor Gray

    if (Test-FileContainsToastr -remotePath $remotePath) {
        $suspiciousFiles += $file
        Write-Host "   ⚠️  SUSPECT: $file contient 'toastr'" -ForegroundColor Yellow
    } else {
        Write-Host "   ✅ OK: $file propre" -ForegroundColor Green
    }
}

# Supprimer les fichiers suspects
$deletedCount = 0
$errorCount = 0

if ($suspiciousFiles.Count -eq 0) {
    Write-Host "`nAucun fichier suspect trouvé !" -ForegroundColor Green
} else {
    Write-Host "`nSuppression des fichiers suspects..." -ForegroundColor Magenta

    foreach ($file in $suspiciousFiles) {
        $remotePath = "$FtpPath/$file"

        if ($TestMode) {
            Write-Host "   [TEST] Suppression simulée: $file" -ForegroundColor Gray
            $deletedCount++
            continue
        }

        try {
            Write-Host "   Suppression: $file" -ForegroundColor Yellow

            $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$remotePath"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
            $ftpRequest.UseBinary = $false
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 10000
            $ftpRequest.KeepAlive = $false

            $response = $ftpRequest.GetResponse()
            $response.Close()

            Write-Host "   ✅ Supprimé: $file" -ForegroundColor Green
            $deletedCount++

        } catch {
            $errorCount++
            Write-Host "   ❌ Erreur: $file - $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}

Write-Host "`nNETTOYAGE TOASTR TERMINÉ" -ForegroundColor White
Write-Host ("=" * 70) -ForegroundColor White
Write-Host "Résumé:" -ForegroundColor Cyan
Write-Host "   Fichiers vérifiés: $($filesToCheck.Count)" -ForegroundColor Gray
Write-Host "   Fichiers suspects trouvés: $($suspiciousFiles.Count)" -ForegroundColor Yellow
Write-Host "   Fichiers supprimés: $deletedCount" -ForegroundColor Green
Write-Host "   Erreurs: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })

if ($TestMode) {
    Write-Host "`nPour exécuter réellement le nettoyage, relancer sans -TestMode" -ForegroundColor Yellow
} else {
    if ($suspiciousFiles.Count -eq 0) {
        Write-Host "`nServeur distant déjà propre - aucun fichier toastr trouvé ✅" -ForegroundColor Green
    } else {
        Write-Host "`nServeur distant nettoyé de toutes références toastr ✅" -ForegroundColor Green
    }
}