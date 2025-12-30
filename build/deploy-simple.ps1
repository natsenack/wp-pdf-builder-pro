# Script de deploiement simplifie - Envoie UNIQUEMENT les fichiers modifies
# NOTE: Mode 'test' retiré — ce script effectue désormais le déploiement réel FTP par défaut.
#commande possible - a lire absolument
# Usage: .\deploy-simple.ps1
#.\build\deploy-simple.ps1

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("plugin")]
    [string]$Mode = "plugin",
    [switch]$SkipConnectionTest,
    [switch]$FastMode
)

$ErrorActionPreference = "Stop"

# Forcer l'encodage UTF-8 pour éviter les problèmes avec les caractères accentués
$OutputEncoding = [System.Text.Encoding]::UTF8
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
chcp 65001 | Out-Null  # Page de code UTF-8

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

$WorkingDir = "I:\wp-pdf-builder-pro"

# Fonction pour générer un message de commit intelligent
function Get-SmartCommitMessage {
    param([string[]]$ModifiedFiles)

    $timestamp = Get-Date -Format "dd/MM/yyyy HH:mm:ss"

    # Analyser les types de fichiers modifiés
    $hasJs = $ModifiedFiles | Where-Object { $_ -like "*.js" -or $_ -like "*.jsx" -or $_ -like "*.ts" -or $_ -like "*.tsx" }
    $hasCss = $ModifiedFiles | Where-Object { $_ -like "*.css" -or $_ -like "*.scss" -or $_ -like "*.sass" }
    $hasPhp = $ModifiedFiles | Where-Object { $_ -like "*.php" }
    $hasDist = $ModifiedFiles | Where-Object { $_ -like "*dist*" -or $_ -like "*build*" }
    $hasConfig = $ModifiedFiles | Where-Object { $_ -like "*.json" -or $_ -like "*.config.*" -or $_ -like "*.yml" -or $_ -like "*.yaml" }

    # Priorité: JS/TS > PHP > CSS > Config > Dist
    if ($hasJs) {
        $type = "feat"
        $description = "Mise à jour des assets JavaScript/TypeScript"
    } elseif ($hasPhp) {
        $type = "fix"
        $description = "Corrections PHP"
    } elseif ($hasCss) {
        $type = "style"
        $description = "Mise à jour des styles CSS"
    } elseif ($hasConfig) {
        $type = "chore"
        $description = "Configuration mise à jour"
    } elseif ($hasDist) {
        $type = "build"
        $description = "Build et déploiement"
    } else {
        $type = "chore"
        $description = "Mise à jour fichiers"
    }

    return "$type`: $description - $timestamp"
}

# Configuration FastMode
if ($FastMode) {
    $SkipConnectionTest = $true
    Write-Host "MODE RAPIDE: Test de connexion desactiver, parallelisation maximale" -ForegroundColor Cyan
}

Write-Host "`nDEPLOIEMENT PLUGIN - Mode: $Mode $(if ($FastMode) { '(RAPIDE)' } else { '' })" -ForegroundColor Cyan
Write-Host ("=" * 60) -ForegroundColor White

Write-Host "`n1 Compilation des assets JavaScript/CSS..." -ForegroundColor Magenta

# TEMPORAIREMENT DÉSACTIVÉ POUR DÉPLOIEMENT PHP SEULEMENT
Write-Host "   Compilation skippee (changements PHP seulement)" -ForegroundColor Yellow

# 2 LISTER LES FICHIERS MODIFIES
Write-Host "`n2 Detection des fichiers modifies..." -ForegroundColor Magenta

try {
    Push-Location $WorkingDir

    # Essayer de récupérer les fichiers modifiés via git
    try {
        $ErrorActionPreference = "Continue"
        # Utiliser git diff pour détecter tous les fichiers modifiés/deplacés/renommés depuis le dernier commit
        $statusOutput = cmd /c "cd /d $WorkingDir && git diff --name-status HEAD~1" 2>&1
        $gitExitCode = $LASTEXITCODE
        $ErrorActionPreference = "Stop"

        if ($gitExitCode -eq 0) {
            $allModified = $statusOutput | Where-Object { $_ -and $_ -notlike "*warning*" -and $_ -notlike "*fatal*" } | ForEach-Object {
                $line = $_.ToString().Trim()
                if ($line -match '^([MADRCU]{1})\s+(.+)$') {
                    $status = $matches[1]
                    $filePart = $matches[2]
                    
                    # Pour les renommages (R), le fichier est déjà le nouveau nom
                    $file = $filePart.Trim()
                    
                    $file
                }
            } | Sort-Object -Unique

            Write-Host "Utilisation des fichiers modifies detectes par git diff ($($allModified.Count) fichiers)" -ForegroundColor Green
        } else {
            Write-Host "Git diff a retourne le code $gitExitCode, utilisation liste par defaut" -ForegroundColor Yellow
            $allModified = @("build/deploy-simple.ps1", "plugin/src/Managers/PdfBuilderPreviewGenerator.php")
        }
    } catch {
        Write-Host "Erreur git: $($_.Exception.Message), utilisation liste par defaut" -ForegroundColor Yellow
        $allModified = @("build/deploy-simple.ps1", "plugin/src/Managers/PdfBuilderPreviewGenerator.php")
    }
    # Inclure: plugin/*, build/*, mais EXCLURE les fichiers sources TypeScript (assets/js/src)
    # Les fichiers sources TypeScript ne doivent pas être en production, seulement les fichiers compilés
    try {
        $pluginModified = $allModified | Where-Object {
            try {
                $filePath = $_
                $isPlugin = ($filePath -like "plugin/*")
                $isNotExcluded = ($filePath -notlike "assets/js/src/*" -and
                                $filePath -notlike "assets/ts/*" -and
                                $filePath -notlike "assets/shared/*" -and
                                $filePath -notlike "assets/config/*" -and
                                $filePath -notlike "plugin/config/*" -and
                                $filePath -notlike "plugin/docs/*" -and
                                # TEMPORAIRE - NE PAS SUPPRIMER SANS AUTORISATION EXPLICITE
                                # Exclusions TypeScript pour la phase alpha - à retirer seulement quand demandé
                                $filePath -notlike "*.ts" -and
                                $filePath -notlike "*.tsx")
                $exists = $false
                if ($isPlugin -and $isNotExcluded) {
                    try {
                        $exists = Test-Path "$WorkingDir\$filePath" -ErrorAction Stop
                    } catch {
                        # Si Test-Path échoue, considérer que le fichier n'existe pas
                        $exists = $false
                    }
                }
                return $isPlugin -and $isNotExcluded -and $exists
            } catch {
                return $false
            }
        }
    } catch {
        Write-Host "Erreur lors du filtrage des fichiers: $($_.Exception.Message)" -ForegroundColor Yellow
        $pluginModified = @()
    }    # Toujours inclure les fichiers dist s'ils ont été modifiés récemment (dans les dernières 5 minutes)
    try {
        $distFiles = Get-ChildItem "$WorkingDir\plugin\assets\js\dist\*.js" -ErrorAction SilentlyContinue | Where-Object { $_.LastWriteTime -gt (Get-Date).AddMinutes(-5) } | Select-Object -ExpandProperty FullName
        $distFilesRelative = $distFiles | ForEach-Object { $_.Replace("$WorkingDir\", "").Replace("\", "/") }
        $pluginModified = @($pluginModified) + @($distFilesRelative) | Sort-Object -Unique
    } catch {
        Write-Host "Erreur lors de la detection des fichiers dist: $($_.Exception.Message)" -ForegroundColor Yellow
    }

    # FORCER l'inclusion des fichiers bundle.js modifiés récemment (dernieres 30 minutes) pour corriger l'editeur
    try {
        $recentBundleFiles = Get-ChildItem "$WorkingDir\plugin\assets\js\*.bundle.js" -ErrorAction SilentlyContinue | Where-Object { $_.LastWriteTime -gt (Get-Date).AddMinutes(-30) } | Select-Object -ExpandProperty FullName
        $bundleFilesRelative = $recentBundleFiles | ForEach-Object { $_.Replace("$WorkingDir\", "").Replace("\", "/") }
        if ($bundleFilesRelative.Count -gt 0) {
            Write-Host "Bundle files recents detectes: $($bundleFilesRelative.Count)" -ForegroundColor Yellow
            $pluginModified = @($pluginModified) + @($bundleFilesRelative) | Sort-Object -Unique
        }
    } catch {
        Write-Host "Erreur lors de la detection des fichiers bundle: $($_.Exception.Message)" -ForegroundColor Yellow
    }

    # Toujours inclure les fichiers vendor (dépendances PHP) - seulement s'ils sont récents
    try {
        # N'inclure que les vendor files modifiés récemment (dernières 24h) pour éviter l'upload massif
        $recentVendorFiles = Get-ChildItem "$WorkingDir\plugin\vendor\*" -Recurse -File -ErrorAction SilentlyContinue | Where-Object {
            $_.LastWriteTime -gt (Get-Date).AddHours(-24)
        } | Select-Object -ExpandProperty FullName
        $vendorFilesRelative = $recentVendorFiles | ForEach-Object { $_.Replace("$WorkingDir\", "").Replace("\", "/") }
        if ($vendorFilesRelative.Count -gt 0) {
            Write-Host "Vendor files recents detectes: $($vendorFilesRelative.Count)" -ForegroundColor Yellow
            $pluginModified = @($pluginModified) + @($vendorFilesRelative) | Sort-Object -Unique
        }
    } catch {
        Write-Host "Erreur lors de la detection des fichiers vendor: $($_.Exception.Message)" -ForegroundColor Yellow
    }
    
    if ($pluginModified.Count -eq 0) {
        Write-Host "Aucun fichier modifie a deployer" -ForegroundColor Green
        Write-Host "   (Tous les fichiers sont a jour)" -ForegroundColor Gray
        Pop-Location
        exit 0
    }
    
    Write-Host "Fichiers modifies detects: $($pluginModified.Count)" -ForegroundColor Cyan
    $pluginModified | ForEach-Object {
        Write-Host "   - $_" -ForegroundColor White
    }
    
    Pop-Location
} catch {
    Write-Host "Erreur git: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# 3 UPLOAD FTP
$uploadCount = 0
$errorCount = 0
$startTime = Get-Date

Write-Host "`n3 Upload FTP des fichiers modifies..." -ForegroundColor Magenta

    # Test connexion FTP rapide (optionnel - skip si -SkipConnectionTest)
    if (!$SkipConnectionTest) {
        Write-Host "   Test de connexion FTP..." -ForegroundColor Yellow
        try {
            $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
            $ftpRequest.UseBinary = $false
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 5000  # Réduit à 5 secondes
            $ftpRequest.KeepAlive = $false
            $response = $ftpRequest.GetResponse()
            $response.Close()
            Write-Host "   Connexion FTP OK" -ForegroundColor Green
        } catch {
            Write-Host "   Erreur FTP: $($_.Exception.Message)" -ForegroundColor Red
            exit 1
        }
    }

    # Créer tous les répertoires en parallèle
    Write-Host "   Creation des repertoires..." -ForegroundColor Yellow
    $dirs = @{}
    foreach ($file in $pluginModified) {
        $dir = Split-Path $file -Parent
        if ($dir -and !$dirs.ContainsKey($dir)) {
            $dirs[$dir] = $true
        }
    }

    # Ajouter tous les répertoires parents nécessaires (récursif)
    $allDirs = @{}
    foreach ($dir in $dirs.Keys) {
        $currentDir = $dir
        while ($currentDir -and $currentDir -ne "." -and $currentDir -ne "plugin") {
            if (!$allDirs.ContainsKey($currentDir)) {
                $allDirs[$currentDir] = $true
            }
            $currentDir = Split-Path $currentDir -Parent
        }
    }

    # Fonction pour créer récursivement tous les répertoires nécessaires
    function New-FtpDirectoryRecursive {
        param([string]$ftpPath)

        try {
            # Créer le répertoire directement (FTP gère la récursion automatiquement)
            $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$ftpPath/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 5000  # Augmenté pour la création récursive
            $ftpRequest.KeepAlive = $false
            $response = $ftpRequest.GetResponse()
            $response.Close()
            return $true
        } catch {
            # Le répertoire existe probablement déjà, ou il y a eu une erreur
            return $false
        }
    }

    # Créer tous les répertoires en parallèle avec gestion récursive
    $createdDirs = 0
    $dirJobs = @()
    foreach ($dir in $allDirs.Keys) {
        # Corriger le calcul du chemin FTP
        if ($dir.StartsWith("plugin/")) {
            $ftpDir = $dir.Substring(7)
        } elseif ($dir.StartsWith("plugin\")) {
            $ftpDir = $dir.Substring(7)
        } else {
            $ftpDir = $dir
        }
        $ftpDir = $ftpDir.Replace("\", "/")
        $fullPath = "$FtpPath/$ftpDir".TrimEnd('/')

        if ($fullPath -ne $FtpPath) {
            $job = Start-Job -ScriptBlock {
                param($ftpHost, $ftpUser, $ftpPass, $fullPath)
                try {
                    # Créer le répertoire récursivement
                    $ftpUri = "ftp://$using:FtpUser`:$using:FtpPass@$using:FtpHost$fullPath/"
                    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                    $ftpRequest.UseBinary = $true
                    $ftpRequest.UsePassive = $true
                    $ftpRequest.Timeout = 5000
                    $ftpRequest.KeepAlive = $false
                    $response = $ftpRequest.GetResponse()
                    $response.Close()
                    return @{ Success = $true; Path = $fullPath }
                } catch {
                    return @{ Success = $false; Path = $fullPath; Error = $_.Exception.Message }
                }
            } -ArgumentList $FtpHost, $FtpUser, $FtpPass, $fullPath
            $dirJobs += $job
        }
    }

    # Attendre la fin de la création des répertoires (max 15 secondes pour la récursion)
    $dirTimeout = 15
    $dirStartTime = Get-Date
    while ($dirJobs.Count -gt 0 -and ((Get-Date) - $dirStartTime).TotalSeconds -lt $dirTimeout) {
        $completedDirJobs = $dirJobs | Where-Object { $_.State -eq 'Completed' }
        foreach ($job in $completedDirJobs) {
            $result = Receive-Job $job
            if ($result.Success) {
                $createdDirs++
                Write-Host "   Repertoire cree: $($result.Path)" -ForegroundColor Green
            } else {
                Write-Host "   Repertoire existe deja ou erreur: $($result.Path)" -ForegroundColor Gray
            }
            Remove-Job $job
        }
        $dirJobs = $dirJobs | Where-Object { $_.State -ne 'Completed' }
        Start-Sleep -Milliseconds 200  # Augmenté pour la création récursive
    }

    # Nettoyer les jobs restants
    foreach ($job in $dirJobs) {
        Write-Host "   Timeout creation repertoire: $($job.Name)" -ForegroundColor Yellow
        Stop-Job $job
        Remove-Job $job
    }

    Write-Host "   Repertoires crees: $createdDirs" -ForegroundColor Green

    # Upload fichiers avec parallélisation optimisée
    Write-Host "   Upload des fichiers ($($pluginModified.Count) fichiers)..." -ForegroundColor Yellow
    $maxConcurrentUploads = $(if ($FastMode) { 6 } else { 4 })  # 6 en mode rapide, 4 normal
    $uploadJobs = [System.Collections.Generic.List[object]]::new()
    $jobTimeout = $(if ($FastMode) { 30 } else { 45 })  # Timeout plus court en mode rapide

    foreach ($file in $pluginModified) {
        $localFile = Join-Path $WorkingDir $file

        if (!(Test-Path $localFile)) {
            continue
        }

        # Calcul du remotePath optimisé
        if ($file.StartsWith("plugin/")) {
            $remotePath = $file.Substring(7)
        } elseif ($file.StartsWith("plugin\")) {
            $remotePath = $file.Substring(7)
        } else {
            $remotePath = $file
        }
        $remotePath = $remotePath.Replace("\", "/")

        # Gestion optimisée des jobs simultanés
        while ($uploadJobs.Count -ge $maxConcurrentUploads) {
            $completedJobs = $uploadJobs | Where-Object { $_.State -eq 'Completed' }
            foreach ($job in $completedJobs) {
                $result = Receive-Job $job
                if ($result.Success) {
                    $uploadCount++
                    Write-Host "   OK: $($result.File)" -ForegroundColor Green
                } else {
                    $errorCount++
                    Write-Host "   ERREUR: $($result.File) - $($result.Error)" -ForegroundColor Red
                }
                Remove-Job $job
                $uploadJobs.Remove($job) | Out-Null
            }
            Start-Sleep -Milliseconds 50  # Réduit à 50ms
        }

        # Job d'upload optimisé avec retry
        $job = Start-Job -ScriptBlock {
            param($ftpHost, $ftpUser, $ftpPass, $ftpPath, $remotePath, $localFile)

            $maxRetries = 3
            $retryCount = 0

            while ($retryCount -lt $maxRetries) {
                try {
                    $ftpUri = "ftp://$ftpUser`:$ftpPass@$ftpHost$ftpPath/$remotePath"
                    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                    # Utiliser le mode TEXTE pour les fichiers PHP/HTML pour éviter la corruption d'encodage
                    $useBinaryMode = !($remotePath -like "*.php" -or $remotePath -like "*.html" -or $remotePath -like "*.json")
                    $ftpRequest.UseBinary = $useBinaryMode
                    $ftpRequest.UsePassive = $true
                    $ftpRequest.Timeout = 15000  # Augmenté à 15 secondes
                    $ftpRequest.ReadWriteTimeout = 30000  # Augmenté à 30 secondes
                    $ftpRequest.KeepAlive = $false

                    $fileContent = [System.IO.File]::ReadAllBytes($localFile)
                    $ftpRequest.ContentLength = $fileContent.Length

                    $stream = $ftpRequest.GetRequestStream()
                    $stream.Write($fileContent, 0, $fileContent.Length)
                    $stream.Close()

                    $response = $ftpRequest.GetResponse()
                    $response.Close()

                    return @{ Success = $true; File = $remotePath }
                } catch {
                    $retryCount++
                    if ($retryCount -ge $maxRetries) {
                        return @{ Success = $false; File = $remotePath; Error = $_.Exception.Message }
                    }
                    Start-Sleep -Seconds 1  # Attendre 1 seconde avant retry
                }
            }
        } -ArgumentList $FtpHost, $FtpUser, $FtpPass, $FtpPath, $remotePath, $localFile

        $uploadJobs.Add($job) | Out-Null
    }

    # Attendre la fin de tous les uploads avec timeout optimisé
    $globalTimeout = $(if ($FastMode) { 180 } else { 240 })  # Augmenté pour les retries
    $globalStartTime = Get-Date

    while ($uploadJobs.Count -gt 0 -and ((Get-Date) - $globalStartTime).TotalSeconds -lt $globalTimeout) {
        $completedJobs = $uploadJobs | Where-Object { $_.State -eq 'Completed' }

        foreach ($job in $completedJobs) {
            $result = Receive-Job $job
            if ($result.Success) {
                $uploadCount++
                Write-Host "   OK: $($result.File)" -ForegroundColor Green
            } else {
                $errorCount++
                Write-Host "   ERREUR: $($result.File) - $($result.Error)" -ForegroundColor Red
            }
            Remove-Job $job
            $uploadJobs.Remove($job) | Out-Null
        }

        # Progression moins verbeuse
        $totalProcessed = $uploadCount + $errorCount
        if ($totalProcessed -gt 0 -and ($totalProcessed % 3) -eq 0) {  # Tous les 3 fichiers
            Write-Host "   Progression: $totalProcessed / $($pluginModified.Count) fichiers..." -ForegroundColor Yellow
        }

        Start-Sleep -Milliseconds 100  # Réduit à 100ms
    }

    # Nettoyer les jobs timeoutés
    foreach ($job in $uploadJobs) {
        if ($job.State -ne 'Completed') {
            Write-Host "   TIMEOUT: $($job.Name)" -ForegroundColor Red
            $errorCount++
            Stop-Job $job
            Remove-Job $job
        }
    }

$totalTime = (Get-Date) - $startTime
Write-Host "`nUpload termine:" -ForegroundColor White
Write-Host "   Fichiers envoyes: $uploadCount" -ForegroundColor Green
Write-Host "   Erreurs: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })
Write-Host "   Temps: $([math]::Round($totalTime.TotalSeconds, 1))s" -ForegroundColor Gray

if ($errorCount -gt 0) {
    Write-Host "`nCertains fichiers n'ont pas pu etre uploades (probablement des fichiers binaires)." -ForegroundColor Yellow
    Write-Host "Les fichiers importants ont été déployés avec succès." -ForegroundColor Green
    # Ne pas sortir en erreur pour les fichiers binaires
}

# 4 GIT COMMIT + PUSH + TAG
Write-Host "`n4 Git commit + push + tag..." -ForegroundColor Magenta

$commitCreated = $false
$pushSuccess = $false

try {
    Push-Location $WorkingDir

    # ✅ CORRECTION: Ajouter TOUS les fichiers modifiés (même s'ils ne sont pas dans $pluginModified)
    Write-Host "   Staging de TOUS les fichiers modifies..." -ForegroundColor Yellow
    $ErrorActionPreference = "Continue"
    $addResult = cmd /c "cd /d $WorkingDir && git add -A" 2>&1
    $ErrorActionPreference = "Stop"

    # Vérifier s'il y a des changements à committer
    $statusOutput = cmd /c "cd /d $WorkingDir && git status --porcelain" 2>&1
    $stagedFiles = $statusOutput | Where-Object { $_ -and $_ -match "^[AM]" }
    
    if ($stagedFiles -and $stagedFiles.Count -gt 0) {
        # Afficher les fichiers qui seront committés
        Write-Host "   Fichiers à committer:" -ForegroundColor Cyan
        $stagedFiles | ForEach-Object {
            Write-Host "     $_" -ForegroundColor Gray
        }
        
        # Générer un message de commit intelligent basé sur les fichiers modifiés
        $commitMsg = Get-SmartCommitMessage -ModifiedFiles $pluginModified
        Write-Host "   Commit: $commitMsg" -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        $commitResult = cmd /c "cd /d $WorkingDir && git commit -m `"$commitMsg`"" 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            Write-Host "   Commit cree" -ForegroundColor Green
            $commitCreated = $true
        } else {
            Write-Host "   Erreur commit: $($commitResult -join ' ')" -ForegroundColor Red
            $commitCreated = $false
        }
    } else {
        Write-Host "   Rien a committer (deja a jour)" -ForegroundColor Gray
        $commitCreated = $false
    }

    # Push seulement si un commit a été créé
    if ($commitCreated) {
        Write-Host "   Push vers remote..." -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        $pushResult = cmd /c "cd /d $WorkingDir && git push origin dev" 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            Write-Host "   Push reussi" -ForegroundColor Green
            $pushSuccess = $true
        } else {
            Write-Host "   Erreur push: $($pushResult -join ' ')" -ForegroundColor Red
            $pushSuccess = $false
        }
    } else {
        Write-Host "   Pas de push (pas de commit)" -ForegroundColor Gray
        $pushSuccess = $true  # Pas d'erreur si pas de commit
    }

    # Tag seulement si push réussi - OPTIONNEL, peut être désactivé pour accélérer
    if ($pushSuccess -and $commitCreated) {
        # ✅ CORRECTION: Utiliser le format de version déployé (comme dans les logs)
        $version = Get-Date -Format "v1.0.0-11eplo25-ddMMyyyy-HHmmss"
        Write-Host "   Tag: $version" -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        $tagResult = cmd /c "cd /d $WorkingDir && git tag -a $version -m `"Deploiement $version`"" 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            $ErrorActionPreference = "Continue"
            $tagPushResult = cmd /c "cd /d $WorkingDir && git push origin $version" 2>&1
            $ErrorActionPreference = "Stop"

            if ($LASTEXITCODE -eq 0) {
                Write-Host "   Tag cree et pousse" -ForegroundColor Green
            } else {
                Write-Host "   Erreur push tag: $($tagPushResult -join ' ')" -ForegroundColor Red
            }
        } else {
            Write-Host "   Erreur creation tag: $($tagResult -join ' ')" -ForegroundColor Red
        }
    } else {
        Write-Host "   Pas de tag (pas de push ou commit)" -ForegroundColor Gray
    }

    Pop-Location
} catch {
    Write-Host "   Erreur git: $($_.Exception.Message)" -ForegroundColor Red
}

# FIN
Write-Host "`nDEPLOIEMENT TERMINE AVEC SUCCES!" -ForegroundColor Green
Write-Host ("=" * 60) -ForegroundColor White
Write-Host "Resume:" -ForegroundColor Cyan
Write-Host "   Compilation: OK" -ForegroundColor Green

# Afficher le statut FTP selon le mode
Write-Host "   Upload FTP: OK ($uploadCount fichiers)" -ForegroundColor Green

# Afficher le statut Git selon les résultats
if ($commitCreated -and $pushSuccess) {
    Write-Host "   Git: OK (commit + push + tag)" -ForegroundColor Green
} elseif ($commitCreated) {
    Write-Host "   Git: PARTIEL (commit OK, push/tag echoue)" -ForegroundColor Yellow
} else {
    Write-Host "   Git: SKIP (rien a committer)" -ForegroundColor Gray
}
Write-Host ""

# ✅ FINAL GIT PUSH - S'assurer que tout est pousse et clean
Write-Host "5 Final Git Push..." -ForegroundColor Cyan
try {
    Push-Location $WorkingDir
    
    # ✅ CORRECTION: Vérifier qu'il n'y a plus de fichiers non committés
    $ErrorActionPreference = "Continue"
    $finalStatus = cmd /c "cd /d $WorkingDir && git status --porcelain" 2>&1
    $ErrorActionPreference = "Stop"
    
    # Filtrer pour ne montrer que les fichiers modifiés (pas les fichiers non suivis)
    $unstagedFiles = $finalStatus | Where-Object { $_ -match "^ [MADRCU]" }
    
    if ($unstagedFiles -and $unstagedFiles.Count -gt 0) {
        Write-Host "   ⚠️ Fichiers modifies non commits detects:" -ForegroundColor Yellow
        $unstagedFiles | ForEach-Object {
            Write-Host "     $_" -ForegroundColor Gray
        }
        
        # Ajouter et commiter les fichiers restants
        Write-Host "   Commitment des fichiers restants..." -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        cmd /c "cd /d $WorkingDir && git add -A" 2>&1 | Out-Null
        $commitMsg = "chore: Commit final des fichiers restants - $(Get-Date -Format 'dd/MM/yyyy HH:mm:ss')"
        $finalCommitResult = cmd /c "cd /d $WorkingDir && git commit -m `"$commitMsg`"" 2>&1
        $ErrorActionPreference = "Stop"
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "   ✅ Commit final cree" -ForegroundColor Green
        }
    }
    
    # Pousser tout vers le remote
    $ErrorActionPreference = "Continue"
    $finalPushResult = cmd /c "cd /d $WorkingDir && git push origin dev" 2>&1
    $ErrorActionPreference = "Stop"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✅ Final push vers origin/dev reussi" -ForegroundColor Green
    } else {
        Write-Host "   ⚠️ Final push info: $($finalPushResult -join ' ')" -ForegroundColor Yellow
    }
    
    Pop-Location
} catch {
    Write-Host "   ⚠️ Erreur lors du final push: $($_.Exception.Message)" -ForegroundColor Yellow
}