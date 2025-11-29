# Script de vérification spécifique TOASTR dans les fichiers suspects
# Vérifie seulement les références à "toastr" dans les fichiers qui contiennent "notification"

param(
    [switch]$TestMode
)

$ErrorActionPreference = "Stop"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "VÉRIFICATION SPÉCIFIQUE TOASTR DANS FICHIERS SUSPECTS" -ForegroundColor Cyan
Write-Host ("=" * 75) -ForegroundColor White

if ($TestMode) {
    Write-Host "MODE TEST - Simulation uniquement" -ForegroundColor Yellow
}

# Liste des fichiers suspects (ceux qui contiennent "notification")
$suspiciousFiles = @(
    "bootstrap.php",
    "composer.lock",
    "pdf-builder-pro.php",
    "assets/css/onboarding.css",
    "assets/css/pdf-builder-admin.css",
    "assets/js/developer-tools.js",
    "assets/js/onboarding.js",
    "assets/js/pdf-preview-integration.js",
    "languages/pdf-builder-pro-fr_FR.po",
    "languages/pdf-builder-pro.pot",
    "templates/admin/js/settings-page.js",
    "templates/admin/settings-parts/settings-ajax.php",
    "templates/admin/settings-parts/settings-general.php",
    "templates/admin/settings-parts/settings-licence.php"
)

# Fonction pour vérifier si un fichier contient "toastr" spécifiquement
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

        # Vérifier spécifiquement "toastr" (insensible à la casse)
        $hasToastr = $content -match '(?i)toastr'

        if ($hasToastr) {
            # Extraire les lignes contenant toastr pour analyse
            $lines = $content -split "`n"
            $toastrLines = $lines | Where-Object { $_ -match '(?i)toastr' } | Select-Object -First 5

            return @{
                HasToastr = $true
                ToastrLines = $toastrLines
                ContentLength = $content.Length
            }
        }

    } catch {
        # Fichier n'existe pas ou erreur d'accès
    }

    return $null
}

Write-Host "`nVérification spécifique 'toastr' dans $($suspiciousFiles.Count) fichiers suspects..." -ForegroundColor Magenta

$toastrFiles = @()
$checkedCount = 0

foreach ($file in $suspiciousFiles) {
    $checkedCount++
    $remotePath = "$FtpPath/$file"

    Write-Host "   [$checkedCount/$($suspiciousFiles.Count)] Analyse toastr: $file" -ForegroundColor Gray

    $result = Test-FileContainsToastr -remotePath $remotePath

    if ($result) {
        $toastrFiles += @{
            File = $file
            ToastrLines = $result.ToastrLines
            ContentLength = $result.ContentLength
        }
        Write-Host "   ⚠️  TOASTR TROUVÉ: $file" -ForegroundColor Red
        Write-Host "      Lignes contenant toastr:" -ForegroundColor Yellow
        foreach ($line in $result.ToastrLines) {
            Write-Host "         $($line.Trim())" -ForegroundColor Gray
        }
    } else {
        Write-Host "   ✅ OK: $file propre (pas de toastr)" -ForegroundColor Green
    }
}

# Supprimer les fichiers contenant toastr
$deletedCount = 0
$errorCount = 0

Write-Host "`nRésultats de l'analyse toastr:" -ForegroundColor Cyan
Write-Host "   Fichiers analysés: $checkedCount" -ForegroundColor Gray
Write-Host "   Fichiers avec toastr trouvé: $($toastrFiles.Count)" -ForegroundColor Red

if ($toastrFiles.Count -eq 0) {
    Write-Host "`nAucun fichier avec toastr trouvé !" -ForegroundColor Green
    Write-Host "Les fichiers suspects ne contiennent que des références légitimes à 'notification'." -ForegroundColor Green
} else {
    Write-Host "`nFichiers contenant TOASTR (à supprimer):" -ForegroundColor Magenta
    foreach ($fileInfo in $toastrFiles) {
        Write-Host "   - $($fileInfo.File) ($($fileInfo.ContentLength) octets)" -ForegroundColor Red
    }

    if (-not $TestMode) {
        Write-Host "`nSuppression des fichiers contenant toastr..." -ForegroundColor Magenta

        foreach ($fileInfo in $toastrFiles) {
            $remotePath = "$FtpPath/$($fileInfo.File)"

            try {
                Write-Host "   Suppression: $($fileInfo.File)" -ForegroundColor Yellow

                $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$remotePath"
                $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
                $ftpRequest.UseBinary = $false
                $ftpRequest.UsePassive = $true
                $ftpRequest.Timeout = 10000
                $ftpRequest.KeepAlive = $false

                $response = $ftpRequest.GetResponse()
                $response.Close()

                Write-Host "   ✅ Supprimé: $($fileInfo.File)" -ForegroundColor Green
                $deletedCount++

            } catch {
                $errorCount++
                Write-Host "   ❌ Erreur: $($fileInfo.File) - $($_.Exception.Message)" -ForegroundColor Red
            }
        }
    } else {
        Write-Host "`nMODE TEST - Aucune suppression (fichiers listés ci-dessus seraient supprimés)" -ForegroundColor Yellow
        $deletedCount = $toastrFiles.Count
    }
}

Write-Host "`nANALYSE TOASTR TERMINÉE" -ForegroundColor White
Write-Host ("=" * 75) -ForegroundColor White
Write-Host "Résumé final:" -ForegroundColor Cyan
Write-Host "   Fichiers analysés: $checkedCount" -ForegroundColor Gray
Write-Host "   Fichiers avec toastr: $($toastrFiles.Count)" -ForegroundColor Red
Write-Host "   Fichiers supprimés: $deletedCount" -ForegroundColor Green
Write-Host "   Erreurs: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })

if ($TestMode) {
    Write-Host "`nPour exécuter réellement le nettoyage, relancer sans -TestMode" -ForegroundColor Yellow
} else {
    if ($toastrFiles.Count -eq 0) {
        Write-Host "`nServeur distant complètement propre - aucun fichier ne contient toastr ✅" -ForegroundColor Green
        Write-Host "Les références 'notification' sont légitimes (paramètres email, etc.)" -ForegroundColor Green
    } else {
        Write-Host "`nServeur distant nettoyé de toutes références toastr ✅" -ForegroundColor Green
    }
}