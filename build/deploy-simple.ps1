# Script de déploiement FTP simplifié et optimisé pour PDF Builder Pro
# Usage: .\deploy-simple.ps1 [-All] [-SkipTests]
# Version optimisée - moins de bugs, plus de fiabilité

param(
    [switch]$All,
    [switch]$SkipTests
)

# Configuration simplifiée
$FtpConfig = @{
    Host = "65.108.242.181"
    User = "nats"
    Pass = "iZ6vU3zV2y"
    RemotePath = "/wp-content/plugins/wp-pdf-builder-pro"
}

# Variables globales pour éviter les conflits
$Script:FtpHost = $FtpConfig.Host
$Script:FtpUser = $FtpConfig.User
$Script:FtpPass = $FtpConfig.Pass
$Script:FtpPath = $FtpConfig.RemotePath

# Détection automatique des chemins
$Script:WorkingDir = Split-Path $PSScriptRoot -Parent
$Script:PluginDir = Join-Path $Script:WorkingDir "plugin"

# Fonction pour afficher une barre de progression
function Show-ProgressBar {
    param(
        [int]$Current,
        [int]$Total,
        [string]$Activity = "Progression",
        [string]$Status = "",
        [int]$Width = 50,
        [datetime]$StartTime = (Get-Date)
    )

    $percentage = if ($Total -gt 0) { [math]::Round(($Current / $Total) * 100, 1) } else { 0 }
    $completed = [math]::Floor(($Current / $Total) * $Width)
    $remaining = $Width - $completed

    $bar = "[" + ("█" * $completed) + ("░" * $remaining) + "]"

    $elapsed = (Get-Date) - $StartTime
    $elapsedSeconds = $elapsed.TotalSeconds
    $speed = if ($elapsedSeconds -gt 0) { [math]::Round($Current / $elapsedSeconds, 2) } else { 0 }
    $eta = if ($speed -gt 0) { [math]::Round(($Total - $Current) / $speed, 1) } else { 0 }

    Write-Host "`r$Activity : $bar $percentage% ($Current/$Total) | Vitesse: $speed/s | ETA: ${eta}s | $Status" -NoNewline
}

# Fonction de logging simplifiée
function Write-Log {
    param([string]$Message, [string]$Level = "INFO")
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logMessage = "[$timestamp] [$Level] $Message"
    $color = switch ($Level) {
        "ERROR" { "Red" }
        "WARN" { "Yellow" }
        "SUCCESS" { "Green" }
        default { "Gray" }
    }
    Write-Host $logMessage -ForegroundColor $color
}

# Fonction pour afficher les statistiques détaillées
function Show-DetailedStats {
    param(
        [int]$Processed,
        [int]$Total,
        [int]$Errors,
        [datetime]$StartTime,
        [long]$TotalBytes = 0
    )

    $elapsed = (Get-Date) - $StartTime
    $elapsedSeconds = [math]::Round($elapsed.TotalSeconds, 1)

    $filesPerSecond = if ($elapsedSeconds -gt 0) { [math]::Round($Processed / $elapsedSeconds, 2) } else { 0 }
    $bytesPerSecond = if ($elapsedSeconds -gt 0) { [math]::Round($TotalBytes / $elapsedSeconds, 0) } else { 0 }

    $avgFileSize = if ($Processed -gt 0) { [math]::Round($TotalBytes / $Processed / 1024, 1) } else { 0 }

    Write-Host "`n📊 STATISTIQUES DÉTAILLÉES:" -ForegroundColor Cyan
    Write-Host "   📁 Fichiers traités: $Processed/$Total" -ForegroundColor White
    Write-Host "   ❌ Erreurs: $Errors" -ForegroundColor $(if ($Errors -gt 0) { "Red" } else { "Green" })
    Write-Host "   ⏱️  Temps écoulé: $elapsedSeconds s" -ForegroundColor Yellow
    Write-Host "   🚀 Vitesse: $filesPerSecond fichiers/s" -ForegroundColor Yellow
    Write-Host "   📊 Débit: $([math]::Round($bytesPerSecond / 1024, 1)) KB/s" -ForegroundColor Yellow
    Write-Host "   📏 Taille moyenne: ${avgFileSize} KB/fichier" -ForegroundColor Yellow
}

# Fonction FTP simplifiée pour créer des répertoires
function New-FtpDirectory {
    param([string]$remoteDir)
    try {
        $ftpUri = "ftp://$Script:FtpUser`:$Script:FtpPass@$Script:FtpHost$remoteDir/"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000
        $response = $ftpRequest.GetResponse()
        $response.Close()
        return $true
    } catch {
        # Ignore les erreurs "répertoire existe déjà"
        if ($_.Exception.Message -notmatch "550") {
            Write-Log "Erreur création répertoire $remoteDir : $($_.Exception.Message)" "ERROR"
        }
        return $false
    }
}

# Fonction FTP simplifiée pour uploader un fichier
function Send-FtpFile {
    param([string]$localPath, [string]$remotePath)

    try {
        # Vérifier que le fichier local existe
        if (!(Test-Path $localPath)) {
            throw "Fichier local introuvable: $localPath"
        }

        # Créer le répertoire distant si nécessaire (CORRECTION: utiliser split au lieu de Path.GetDirectoryName)
        $remoteDir = $remotePath -replace '/[^/]*$', ''
        if ($remoteDir -and $remoteDir -ne "/") {
            New-FtpDirectory $remoteDir | Out-Null
        }

        # Upload du fichier
        $ftpUri = "ftp://$Script:FtpUser`:$Script:FtpPass@$Script:FtpHost$remotePath"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 30000

        $fileContent = [System.IO.File]::ReadAllBytes($localPath)
        $ftpRequest.ContentLength = $fileContent.Length

        $requestStream = $ftpRequest.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()

        $response = $ftpRequest.GetResponse()
        $response.Close()

        return $true
    } catch {
        Write-Log "Erreur upload $remotePath : $($_.Exception.Message)" "ERROR"
        return $false
    }
}

# Fonction pour obtenir la liste des fichiers à déployer
function Get-FilesToDeploy {
    $files = New-Object System.Collections.ArrayList

    if ($All) {
        Write-Log "Mode COMPLET: tous les fichiers du plugin" "INFO"

        # Exclusions simplifiées
        $exclusions = @(
            '\\\.git\\',
            'node_modules',
            '\\\.log$',
            '\\\.tmp$',
            '\\\.md$',
            'README',
            '\\\.ts$',
            '\\\.tsx$',
            '\\\.map$',
            'tsconfig',
            'composer-setup\.php$',
            'phpstan\.neon$'
        )

        if (!$IncludeVendor) {
            $exclusions += 'vendor'
        }

        $files = @(Get-ChildItem -Path $Script:PluginDir -Recurse -File | Where-Object {
            $path = $_.FullName
            -not ($exclusions | Where-Object { $path -match $_ })
        })
        # Convertir en ArrayList pour éviter les problèmes avec +=
        $files = New-Object System.Collections.ArrayList(,$files)
    } else {
        Write-Log "Mode NORMAL: fichiers modifiés récemment" "INFO"

        # Utiliser Git si disponible
        try {
            Push-Location $Script:WorkingDir
            $modified = & git diff --name-only 2>$null
            $staged = & git diff --cached --name-only 2>$null
            $untracked = & git ls-files --others --exclude-standard 2>$null
            $allFiles = ($modified + $staged + $untracked) | Select-Object -Unique |
                       Where-Object { $_ -like "plugin/*" -and (Test-Path (Join-Path $Script:WorkingDir $_)) }
            $files = @($allFiles | ForEach-Object { Get-Item (Join-Path $Script:WorkingDir $_) })
            # Convertir en ArrayList
            $files = New-Object System.Collections.ArrayList(,$files)
        } catch {
            Write-Log "Git non disponible, utilisation du mode timestamp" "WARN"
        } finally {
            Pop-Location
        }

        # Fallback: fichiers modifiés dans les dernières 24h
        if ($files.Count -eq 0) {
            $cutoffTime = (Get-Date).AddHours(-24)
            $files = @(Get-ChildItem -Path $Script:PluginDir -Recurse -File |
                    Where-Object { $_.LastWriteTime -gt $cutoffTime })
            # Convertir en ArrayList
            $files = New-Object System.Collections.ArrayList(,$files)
        }
    }

    # Ajouter toujours les fichiers critiques
    $criticalFiles = @(
        "pdf-builder-pro.php",
        "src/Core/PDF_Builder_Unified_Ajax_Handler.php",
        "src/Core/core/autoloader.php",
        "assets/js/settings-main.js",
        "assets/js/pdf-builder-react.min.js",
        "assets/css/pdf-builder-react.min.css"
    )

    foreach ($criticalFile in $criticalFiles) {
        $criticalPath = Join-Path $Script:PluginDir $criticalFile
        if (Test-Path $criticalPath) {
            $fileItem = Get-Item $criticalPath
            $exists = $false
            foreach ($existingFile in $files) {
                if ($existingFile.FullName -eq $fileItem.FullName) {
                    $exists = $true
                    break
                }
            }
            if (-not $exists) {
                $files.Add($fileItem) | Out-Null
                Write-Log "Fichier critique ajouté: $criticalFile" "INFO"
            }
        }
    }

    return $files
}

# Fonction principale de déploiement
function Invoke-Deployment {
    Write-Host "🚀 DÉPLOIEMENT FTP PDF BUILDER PRO" -ForegroundColor Cyan
    $mode = if ($All) { "COMPLET" } else { "MODIFIÉ" }
    Write-Host "Mode: $mode" -ForegroundColor Yellow
    Write-Host ("=" * 50) -ForegroundColor White

    # Test de connexion FTP
    if (!$SkipTests) {
        Write-Log "Test de connexion FTP..." "INFO"
        $ftpTestStart = Get-Date
        Show-ProgressBar -Current 0 -Total 1 -Activity "Test connexion FTP" -Status "Connexion..." -StartTime $ftpTestStart
        try {
            $ftpUri = "ftp://$Script:FtpUser`:$Script:FtpPass@$Script:FtpHost/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
            $ftpRequest.Timeout = 5000
            $ftpRequest.UsePassive = $true
            $response = $ftpRequest.GetResponse()
            $response.Close()
            Show-ProgressBar -Current 1 -Total 1 -Activity "Test connexion FTP" -Status "OK" -StartTime $ftpTestStart
            Write-Host "" # Nouvelle ligne
            Write-Log "Connexion FTP OK" "SUCCESS"
        } catch {
            Show-ProgressBar -Current 1 -Total 1 -Activity "Test connexion FTP" -Status "Erreur" -StartTime $ftpTestStart
            Write-Host "" # Nouvelle ligne
            Write-Log "Erreur de connexion FTP: $($_.Exception.Message)" "ERROR"
            exit 1
        }
    }

    # Créer le répertoire de base
    Write-Log "Création du répertoire de base..." "INFO"
    $mkdirStart = Get-Date
    Show-ProgressBar -Current 0 -Total 1 -Activity "Création répertoire" -Status "Création..." -StartTime $mkdirStart
    New-FtpDirectory $Script:FtpPath | Out-Null
    Show-ProgressBar -Current 1 -Total 1 -Activity "Création répertoire" -Status "Terminé" -StartTime $mkdirStart
    Write-Host "" # Nouvelle ligne

    # Détecter les fichiers à déployer
    Write-Host "`n1. Detection des fichiers..." -ForegroundColor Magenta
    $detectionStart = Get-Date
    Show-ProgressBar -Current 0 -Total 1 -Activity "Détection fichiers" -Status "Analyse en cours..." -StartTime $detectionStart
    $filesToDeploy = Get-FilesToDeploy
    Show-ProgressBar -Current 1 -Total 1 -Activity "Détection fichiers" -Status "Terminé" -StartTime $detectionStart
    Write-Host "" # Nouvelle ligne
    Write-Log "$($filesToDeploy.Count) fichier(s) détecté(s)" "SUCCESS"

    # Compiler Webpack
    Write-Host "`n2. Compilation Webpack..." -ForegroundColor Magenta
    Write-Log "Compilation en cours..." "INFO"
    $webpackStart = Get-Date
    Show-ProgressBar -Current 0 -Total 1 -Activity "Compilation Webpack" -Status "Build en cours..." -StartTime $webpackStart
    Push-Location $Script:WorkingDir
    try {
        & npm run build
        if ($LASTEXITCODE -ne 0) { throw "Erreur de compilation Webpack" }
        Show-ProgressBar -Current 1 -Total 1 -Activity "Compilation Webpack" -Status "Terminé" -StartTime $webpackStart
        Write-Host "" # Nouvelle ligne
        Write-Log "Compilation réussie" "SUCCESS"
    } catch {
        Show-ProgressBar -Current 1 -Total 1 -Activity "Compilation Webpack" -Status "Erreur" -StartTime $webpackStart
        Write-Host "" # Nouvelle ligne
        Write-Log "Erreur compilation: $($_.Exception.Message)" "ERROR"
        exit 1
    } finally {
        Pop-Location
    }

    # Upload des fichiers (OPTIMISÉ AVEC PARALLÉLISME ET PROGRESSION)
    Write-Host "`n3. Upload FTP..." -ForegroundColor Magenta
    $uploadCount = 0
    $errorCount = 0
    $startTime = Get-Date
    $totalBytesUploaded = [long]0
    $lastUpdate = Get-Date

    # Configuration du parallélisme
    $maxConcurrentJobs = 5  # Nombre maximum de jobs simultanés
    $runningJobs = New-Object System.Collections.ArrayList
    $jobResults = New-Object System.Collections.ArrayList
    $processedCount = 0

    Write-Log "Upload parallèle avec $maxConcurrentJobs jobs simultanés" "INFO"
    Write-Host "" # Ligne vide pour la barre de progression

    foreach ($file in $filesToDeploy) {
        # Calcul correct du chemin relatif (CORRECTION DU BUG PRINCIPAL)
        $relativePath = $file.FullName.Substring($Script:PluginDir.Length).Replace("\", "/").TrimStart("/")
        $remotePath = "$($Script:FtpPath)/$relativePath"

        # Attendre si on atteint la limite de jobs simultanés
        while ($runningJobs.Count -ge $maxConcurrentJobs) {
            # Vérifier les jobs terminés
            $completedJobs = $runningJobs | Where-Object { $_.State -ne "Running" }
            foreach ($job in $completedJobs) {
                $result = Receive-Job $job
                $jobResults.Add($result) | Out-Null
                Remove-Job $job
                $runningJobs.Remove($job) | Out-Null

                # Mettre à jour les compteurs
                if ($result.Success) {
                    $uploadCount++
                } else {
                    $errorCount++
                }
                $processedCount++
            }

            # Afficher la progression toutes les 500ms
            $now = Get-Date
            if (($now - $lastUpdate).TotalMilliseconds -gt 500) {
                Show-ProgressBar -Current $processedCount -Total $filesToDeploy.Count -Activity "Upload FTP" -Status "$($runningJobs.Count) jobs actifs" -StartTime $startTime
                $lastUpdate = $now
            }

            if ($runningJobs.Count -ge $maxConcurrentJobs) {
                Start-Sleep -Milliseconds 100
            }
        }

        # Lancer un nouveau job d'upload
        $job = Start-Job -ScriptBlock {
            param($localPath, $remotePath, $ftpHost, $ftpUser, $ftpPass)

            try {
                # Fonction d'upload dans le job
                function Send-FtpFileJob {
                    param([string]$localPath, [string]$remotePath, [string]$ftpHost, [string]$ftpUser, [string]$ftpPass)

                    if (!(Test-Path $localPath)) {
                        return @{ Success = $false; Error = "Fichier local introuvable: $localPath"; BytesUploaded = 0 }
                    }

                    $fileSize = (Get-Item $localPath).Length

                    # Créer le répertoire distant
                    $remoteDir = $remotePath -replace '/[^/]*$', ''
                    if ($remoteDir -and $remoteDir -ne "/") {
                        try {
                            $dirUri = "ftp://$ftpUser`:$ftpPass@$ftpHost$remoteDir/"
                            $dirRequest = [System.Net.FtpWebRequest]::Create($dirUri)
                            $dirRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                            $dirRequest.UseBinary = $true
                            $dirRequest.UsePassive = $true
                            $dirRequest.Timeout = 5000
                            $dirResponse = $dirRequest.GetResponse()
                            $dirResponse.Close()
                        } catch {
                            # Ignore les erreurs de répertoire existant
                        }
                    }

                    # Upload du fichier
                    try {
                        $ftpUri = "ftp://$ftpUser`:$ftpPass@$ftpHost$remotePath"
                        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                        $ftpRequest.UseBinary = $true
                        $ftpRequest.UsePassive = $true
                        $ftpRequest.Timeout = 10000

                        $fileContents = [System.IO.File]::ReadAllBytes($localPath)
                        $ftpRequest.ContentLength = $fileContents.Length

                        $requestStream = $ftpRequest.GetRequestStream()
                        $requestStream.Write($fileContents, 0, $fileContents.Length)
                        $requestStream.Close()

                        $response = $ftpRequest.GetResponse()
                        $response.Close()

                        return @{ Success = $true; Error = ""; BytesUploaded = $fileSize }
                    } catch {
                        return @{ Success = $false; Error = "Erreur upload: $($_.Exception.Message)"; BytesUploaded = 0 }
                    }
                }

                return Send-FtpFileJob -localPath $localPath -remotePath $remotePath -ftpHost $ftpHost -ftpUser $ftpUser -ftpPass $ftpPass

            } catch {
                return @{ Success = $false; Error = $_.Exception.Message }
            }

        } -ArgumentList $file.FullName, $remotePath, $Script:FtpHost, $Script:FtpUser, $Script:FtpPass

        $runningJobs.Add($job) | Out-Null
        Write-Host "   Job lancé: $relativePath" -ForegroundColor Gray
    }

    # Attendre que tous les jobs se terminent
    Write-Log "Attente de la fin des uploads..." "INFO"
    while ($runningJobs.Count -gt 0) {
        $completedJobs = $runningJobs | Where-Object { $_.State -ne "Running" }
        foreach ($job in $completedJobs) {
            $result = Receive-Job $job
            $jobResults.Add($result) | Out-Null
            Remove-Job $job
            $runningJobs.Remove($job) | Out-Null

            # Mettre à jour les compteurs
            if ($result.Success) {
                $uploadCount++
                $totalBytesUploaded += $result.BytesUploaded
            } else {
                $errorCount++
            }
            $processedCount++
        }

        # Afficher la progression
        $now = Get-Date
        if (($now - $lastUpdate).TotalMilliseconds -gt 500) {
            Show-ProgressBar -Current $processedCount -Total $filesToDeploy.Count -Activity "Upload FTP" -Status "$($runningJobs.Count) jobs actifs" -StartTime $startTime
            $lastUpdate = $now
        }

        if ($runningJobs.Count -gt 0) {
            Start-Sleep -Milliseconds 200
        }
    }

    # Finaliser la barre de progression
    Show-ProgressBar -Current $filesToDeploy.Count -Total $filesToDeploy.Count -Activity "Upload FTP" -Status "Terminé" -StartTime $startTime
    Write-Host "" # Nouvelle ligne après la barre

    # Afficher les statistiques détaillées
    Show-DetailedStats -Processed $uploadCount -Total $filesToDeploy.Count -Errors $errorCount -StartTime $startTime -TotalBytes $totalBytesUploaded

    if ($errorCount -gt 0) {
        Write-Log "Déploiement terminé avec $errorCount erreur(s)" "WARN"
        exit 1
    } else {
        Write-Host "`n🎉 DÉPLOIEMENT RÉUSSI !" -ForegroundColor Green
        Write-Log "Déploiement réussi" "SUCCESS"

        # Commit et push automatique après déploiement réussi
        Invoke-GitCommitAndPush
    }
}

function Invoke-GitCommitAndPush {
    param([string]$commitMessage = "Déploiement automatique - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')")

    Write-Host "`n🔄 COMMIT ET PUSH GIT..." -ForegroundColor Cyan
    Write-Log "Tentative de commit et push Git..." "INFO"

    try {
        # Vérifier si git est disponible
        $gitAvailable = Get-Command git -ErrorAction SilentlyContinue
        if (-not $gitAvailable) {
            Write-Log "Git non disponible, skip commit/push" "WARN"
            return
        }

        # Vérifier l'état du repository
        $status = & git status --porcelain
        if ($status) {
            Write-Log "Fichiers modifiés détectés, commit en cours..." "INFO"

            # Afficher les fichiers qui seront ajoutés
            $modifiedFiles = & git diff --name-only
            $newFiles = & git ls-files --others --exclude-standard
            Write-Host "📁 Fichiers à commiter:" -ForegroundColor Cyan
            if ($modifiedFiles) {
                $modifiedFiles | ForEach-Object { Write-Host "  ✏️  $_" -ForegroundColor Yellow }
            }
            if ($newFiles) {
                $newFiles | ForEach-Object { Write-Host "  ➕ $_" -ForegroundColor Green }
            }

            # Ajouter tous les fichiers
            Write-Log "Ajout des fichiers au staging..." "INFO"
            & git add .
            if ($LASTEXITCODE -ne 0) {
                throw "Erreur lors de git add"
            }
            Write-Host "✅ Fichiers ajoutés au staging" -ForegroundColor Green

            # Commit
            & git commit -m $commitMessage
            if ($LASTEXITCODE -ne 0) {
                throw "Erreur lors du commit"
            }

            # Vérifier si un remote est configuré avant le push
            $remotes = & git remote
            if ($remotes) {
                Write-Log "Remote détecté, tentative de push..." "INFO"
                & git push
                if ($LASTEXITCODE -ne 0) {
                    throw "Erreur lors du push"
                }
                Write-Host "✅ Commit et push réussis !" -ForegroundColor Green
                Write-Log "Commit et push Git réussis" "SUCCESS"
            } else {
                Write-Host "✅ Commit local réussi (pas de remote configuré)" -ForegroundColor Green
                Write-Log "Commit Git réussi (pas de remote configuré)" "SUCCESS"
            }
        } else {
            Write-Log "Aucun fichier modifié, skip commit/push" "INFO"
        }
    } catch {
        Write-Host "⚠️  Erreur Git: $($_.Exception.Message)" -ForegroundColor Yellow
        Write-Log "Erreur Git: $($_.Exception.Message)" "WARN"
        # Ne pas échouer le déploiement pour une erreur Git
    }
}

# Point d'entrée principal
try {
    Invoke-Deployment
} catch {
    Write-Log "Erreur fatale: $($_.Exception.Message)" "ERROR"
    exit 1
}
