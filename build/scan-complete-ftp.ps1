# Script de scan complet du serveur FTP pour UI obsolète
# Recherche récursive dans tous les fichiers

param(
    [switch]$TestMode,
    [int]$MaxFiles = 100  # Limite pour éviter de scanner trop de fichiers
)

$ErrorActionPreference = "Stop"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "SCAN COMPLET DU SERVEUR FTP POUR UI OBSOLÈTE" -ForegroundColor Cyan
Write-Host ("=" * 80) -ForegroundColor White

if ($TestMode) {
    Write-Host "MODE TEST - Simulation uniquement" -ForegroundColor Yellow
}

# Fonction pour lister récursivement tous les fichiers
function Get-AllFtpFiles {
    param([string]$currentPath, [int]$depth = 0)

    if ($depth -gt 5) { return @() } # Limite de profondeur

    $allFiles = @()

    try {
        # Lister le répertoire actuel
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$currentPath/"
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

        $items = $listing -split "`n" | Where-Object { $_ -and $_.Trim() } | ForEach-Object {
            if ($_ -match '^(d|l|-)([rwx-]{9})\s+\d+\s+\w+\s+\w+\s+(\d+)\s+\w+\s+\d+\s+[\d:]+\s+(.+)$') {
                $type = $matches[1]
                $size = [int]$matches[3]
                $name = $matches[4]

                if ($name -notmatch '^\.\.?$') {  # Exclure . et ..
                    @{
                        Name = $name
                        IsDirectory = ($type -eq 'd')
                        Size = $size
                        FullPath = "$currentPath/$name".Replace('//', '/')
                        RelativePath = $name
                    }
                }
            }
        }

        foreach ($item in $items) {
            if ($item.IsDirectory) {
                # Scanner récursivement les sous-répertoires
                $subFiles = Get-AllFtpFiles -currentPath $item.FullPath -depth ($depth + 1)
                $allFiles += $subFiles
            } else {
                # Ajouter le fichier à la liste
                $allFiles += $item
            }
        }

    } catch {
        Write-Host "Erreur listing: $currentPath - $($_.Exception.Message)" -ForegroundColor Red
    }

    return $allFiles
}

# Fonction pour vérifier si un fichier contient des marqueurs d'UI obsolète
function Test-FileContainsLegacyUI {
    param([string]$remotePath, [string]$fileName)

    # Ne scanner que les fichiers texte (extensions communes)
    $textExtensions = @('.js', '.css', '.php', '.html', '.txt', '.json', '.md')
    $isTextFile = $textExtensions | Where-Object { $fileName -like "*$_" }

    if (-not $isTextFile) {
        return $null  # Pas un fichier texte
    }

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

        # Vérifier les marqueurs d'UI obsolète (pattern générique)
        $hasLegacyUI = $content -match '(?i)legacy_ui'

        if ($hasLegacyUI) {
            return @{
                File = $remotePath
                HasLegacyUI = $hasLegacyUI
                Size = $content.Length
            }
        }

    } catch {
        # Erreur de lecture du fichier
    }

    return $null
}

Write-Host "`nScanning récursif de tous les fichiers sur le serveur..." -ForegroundColor Magenta

# Obtenir tous les fichiers
$allFiles = Get-AllFtpFiles -currentPath $FtpPath
Write-Host "Fichiers trouvés au total: $($allFiles.Count)" -ForegroundColor Gray

# Filtrer les fichiers texte et limiter le nombre
$textFiles = $allFiles | Where-Object {
    $_.Name -match '\.(js|css|php|html|txt|json|md)$' -and
    $_.Size -gt 0 -and $_.Size -lt 1000000  # Fichiers de taille raisonnable
} | Select-Object -First $MaxFiles

Write-Host "Fichiers texte à analyser (limite: $MaxFiles): $($textFiles.Count)" -ForegroundColor Gray

$suspiciousFiles = @()
$scannedCount = 0

foreach ($file in $textFiles) {
    $scannedCount++
    Write-Host "   [$scannedCount/$($textFiles.Count)] Analyse: $($file.RelativePath)" -ForegroundColor Gray

    $result = Test-FileContainsKeywords -remotePath $file.FullPath -fileName $file.Name

    if ($result) {
        $flags = @()
        if ($result.HasLegacyUI) { $flags += "legacy_ui" }
        $flagStr = $flags -join "/"

        $suspiciousFiles += $result
        Write-Host "   ⚠️  TROUVÉ: $($file.RelativePath) contient '$flagStr'" -ForegroundColor Yellow
    }
}

# Supprimer les fichiers suspects
$deletedCount = 0
$errorCount = 0

Write-Host "`nRésultats du scan:" -ForegroundColor Cyan
Write-Host "   Fichiers scannés: $scannedCount" -ForegroundColor Gray
Write-Host "   Fichiers suspects trouvés: $($suspiciousFiles.Count)" -ForegroundColor Yellow

if ($suspiciousFiles.Count -eq 0) {
    Write-Host "`nAucun fichier suspect trouvé dans le scan complet !" -ForegroundColor Green
} else {
    Write-Host "`nDétails des fichiers suspects:" -ForegroundColor Magenta
    foreach ($file in $suspiciousFiles) {
        $flags = @()
        if ($file.HasLegacyUI) { $flags += "legacy_ui" }
        $flagStr = $flags -join "/"
        $shortPath = $file.File.Replace("$FtpPath/", "")
        Write-Host "   - $shortPath ($flagStr, $($file.Size) octets)" -ForegroundColor Yellow
    }

    if (-not $TestMode) {
        Write-Host "`nSuppression des fichiers suspects..." -ForegroundColor Magenta

        foreach ($fileInfo in $suspiciousFiles) {
            $remotePath = $fileInfo.File

            $flags = @()
            if ($fileInfo.HasLegacyUI) { $flags += "legacy_ui" }
            $flagStr = $flags -join "/"

            try {
                $shortName = $remotePath.Replace("$FtpPath/", "")
                Write-Host "   Suppression: $shortName ($flagStr)" -ForegroundColor Yellow

                $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$remotePath"
                $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
                $ftpRequest.UseBinary = $false
                $ftpRequest.UsePassive = $true
                $ftpRequest.Timeout = 10000
                $ftpRequest.KeepAlive = $false

                $response = $ftpRequest.GetResponse()
                $response.Close()

                Write-Host "   ✅ Supprimé: $shortName" -ForegroundColor Green
                $deletedCount++

            } catch {
                $errorCount++
                Write-Host "   ❌ Erreur: $shortName - $($_.Exception.Message)" -ForegroundColor Red
            }
        }
    } else {
        Write-Host "`nMODE TEST - Aucune suppression (fichiers listés ci-dessus seraient supprimés)" -ForegroundColor Yellow
        $deletedCount = $suspiciousFiles.Count
    }
}

Write-Host "`nSCAN COMPLET TERMINÉ" -ForegroundColor White
Write-Host ("=" * 80) -ForegroundColor White
Write-Host "Résumé final:" -ForegroundColor Cyan
Write-Host "   Fichiers scannés: $scannedCount" -ForegroundColor Gray
Write-Host "   Fichiers suspects trouvés: $($suspiciousFiles.Count)" -ForegroundColor Yellow
Write-Host "   Fichiers supprimés: $deletedCount" -ForegroundColor Green
Write-Host "   Erreurs: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })

if ($TestMode) {
    Write-Host "`nPour exécuter réellement le nettoyage, relancer sans -TestMode" -ForegroundColor Yellow
} else {
    if ($suspiciousFiles.Count -eq 0) {
        Write-Host "`nServeur distant complètement propre - pas d'UI obsolète détectée ✅" -ForegroundColor Green
    } else {
        Write-Host "`nServeur distant nettoyé de toutes références d'UI obsolètes ✅" -ForegroundColor Green
    }
}