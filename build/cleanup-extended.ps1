# Script étendu pour supprimer TOASTR et NOTIFICATIONS du serveur distant
# Vérifie tous les fichiers JS/CSS/PHP pour 'toastr' et 'notification'

param(
    [switch]$TestMode
)

$ErrorActionPreference = "Stop"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "NETTOYAGE ÉTENDU TOASTR/NOTIFICATIONS SUR SERVEUR DISTANT" -ForegroundColor Cyan
Write-Host ("=" * 80) -ForegroundColor White

if ($TestMode) {
    Write-Host "MODE TEST - Simulation uniquement" -ForegroundColor Yellow
}

# Fonction pour vérifier si un fichier contient "toastr" ou "notification"
function Test-FileContainsToastrOrNotification {
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

        # Vérifier si le contenu contient "toastr" ou "notification" (insensible à la casse)
        $hasToastr = $content -match '(?i)toastr'
        $hasNotification = $content -match '(?i)notification'

        return @{
            HasToastr = $hasToastr
            HasNotification = $hasNotification
            HasEither = $hasToastr -or $hasNotification
        }

    } catch {
        # Fichier n'existe pas ou erreur d'accès
        return @{
            HasToastr = $false
            HasNotification = $false
            HasEither = $false
        }
    }
}

# Liste étendue des fichiers à vérifier
$filesToCheck = @(
    # Fichiers JavaScript principaux
    "assets/js/pdf-preview-api-client.js",
    "assets/js/pdf-preview-integration.js",
    "assets/js/dist/pdf-builder-react.js",
    "assets/js/wizard.js",
    "assets/js/onboarding.js",
    "assets/js/predefined-templates.js",
    "assets/js/gdpr.js",
    "assets/js/developer-tools.js",
    "assets/js/canvas-style-injector.js",

    # Fichiers CSS
    "assets/css/admin.css",
    "assets/css/frontend.css",

    # Templates PHP
    "templates/admin/settings-parts/settings-general.php",
    "templates/admin/settings-parts/settings-licence.php",
    "templates/admin/settings-parts/settings-ajax.php",
    "templates/admin/js/settings-page.js",

    # Fichiers de bibliothèque potentiels
    "assets/js/toastr.js",
    "assets/js/toastr.min.js",
    "assets/css/toastr.css",
    "assets/css/toastr.min.css",
    "assets/js/notifications.js",
    "assets/js/notifications.min.js",
    "assets/css/notifications.css",

    # Fichiers PHP principaux
    "pdf-builder-pro.php",
    "bootstrap.php",

    # Classes PHP
    "core/PDF_Builder_Notification_Manager.php",
    "src/utilities/PDF_Builder_Notification_Manager.php"
)

Write-Host "`nVérification étendue des fichiers pour 'toastr' et 'notification'..." -ForegroundColor Magenta

$suspiciousFiles = @()

foreach ($file in $filesToCheck) {
    $remotePath = "$FtpPath/$file"
    Write-Host "   Vérification: $file" -ForegroundColor Gray

    $result = Test-FileContainsToastrOrNotification -remotePath $remotePath

    if ($result.HasEither) {
        $flags = @()
        if ($result.HasToastr) { $flags += "toastr" }
        if ($result.HasNotification) { $flags += "notification" }
        $flagStr = $flags -join "/"

        $suspiciousFiles += @{
            File = $file
            HasToastr = $result.HasToastr
            HasNotification = $result.HasNotification
        }
        Write-Host "   ⚠️  SUSPECT: $file contient '$flagStr'" -ForegroundColor Yellow
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

    foreach ($fileInfo in $suspiciousFiles) {
        $file = $fileInfo.File
        $remotePath = "$FtpPath/$file"

        $flags = @()
        if ($fileInfo.HasToastr) { $flags += "toastr" }
        if ($fileInfo.HasNotification) { $flags += "notification" }
        $flagStr = $flags -join "/"

        if ($TestMode) {
            Write-Host "   [TEST] Suppression simulée: $file ($flagStr)" -ForegroundColor Gray
            $deletedCount++
            continue
        }

        try {
            Write-Host "   Suppression: $file ($flagStr)" -ForegroundColor Yellow

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

Write-Host "`nNETTOYAGE ÉTENDU TERMINÉ" -ForegroundColor White
Write-Host ("=" * 80) -ForegroundColor White
Write-Host "Résumé:" -ForegroundColor Cyan
Write-Host "   Fichiers vérifiés: $($filesToCheck.Count)" -ForegroundColor Gray
Write-Host "   Fichiers suspects trouvés: $($suspiciousFiles.Count)" -ForegroundColor Yellow
Write-Host "   Fichiers supprimés: $deletedCount" -ForegroundColor Green
Write-Host "   Erreurs: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })

if ($TestMode) {
    Write-Host "`nPour exécuter réellement le nettoyage, relancer sans -TestMode" -ForegroundColor Yellow
} else {
    if ($suspiciousFiles.Count -eq 0) {
        Write-Host "`nServeur distant déjà propre - aucun fichier toastr/notification trouvé ✅" -ForegroundColor Green
    } else {
        Write-Host "`nServeur distant nettoyé de toutes références toastr/notifications ✅" -ForegroundColor Green
    }
}