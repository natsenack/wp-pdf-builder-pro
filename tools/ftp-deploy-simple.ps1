<#
.SYNOPSIS
    Script de déploiement FTP optimisé pour WP PDF Builder Pro

.DESCRIPTION
    Déploie automatiquement les fichiers modifiés vers le serveur FTP avec
    compilation automatique, détection intelligente des fichiers, et gestion
    robuste des erreurs. Supporte les modes séquentiel et parallèle.

.PARAMETER Mode
    Mode de déploiement : Sequential (défaut) ou Parallel

.PARAMETER Force
    Force le déploiement même si aucun fichier modifié n'est détecté

.PARAMETER NoCompile
    Ignore la compilation automatique du projet

.PARAMETER NoGit
    Ignore les opérations Git (commit et push)

.PARAMETER MaxRetries
    Nombre maximum de tentatives par fichier (défaut: 3)

.PARAMETER ParallelJobs
    Nombre de jobs parallèles pour le mode Parallel (défaut: 4)

.EXAMPLE
    .\ftp-deploy-simple.ps1
    # Déploiement normal avec compilation et commit Git

.EXAMPLE
    .\ftp-deploy-simple.ps1 -Mode Parallel -ParallelJobs 8
    # Déploiement en parallèle avec 8 jobs simultanés

.EXAMPLE
    .\ftp-deploy-simple.ps1 -NoCompile -NoGit -Force
    # Déploiement forcé sans compilation ni Git

.NOTES
    Prérequis : package.json, .git, ftp-config.env
    Le fichier ftp-config.env doit contenir : FTP_HOST, FTP_USER, FTP_PASS, FTP_PATH
#>

param(
    [string]$Mode = "Parallel",     # Parallel par défaut pour plus de rapidité
    [switch]$Force,                # Forcer le déploiement même sans changements
    [switch]$NoCompile,            # Ne pas compiler automatiquement
    [switch]$NoGit,                # Ne pas faire de commit/push Git
    [int]$MaxRetries = 3,          # Nombre maximum de tentatives par fichier
    [int]$ParallelJobs = 8         # Nombre de jobs parallèles augmenté (8 par défaut)
)

Write-Host "🚀 FTP DEPLOY - VERSION ULTRA-RAPIDE" -ForegroundColor Green
Write-Host "===================================" -ForegroundColor Green
Write-Host "Mode: $Mode | Jobs: $ParallelJobs | Force: $($Force.ToString()) | NoCompile: $($NoCompile.ToString())" -ForegroundColor Cyan
Write-Host ""

# ============================================================================
# 1. VALIDATION DES PRÉREQUIS
# ============================================================================
Write-Host "1. Validation des prérequis..." -ForegroundColor Cyan

# Vérifier PowerShell version
if ($PSVersionTable.PSVersion.Major -lt 5) {
    Write-Host "❌ PowerShell 5.0+ requis. Version actuelle: $($PSVersionTable.PSVersion)" -ForegroundColor Red
    exit 1
}

# Vérifier les commandes nécessaires
$requiredCommands = @('git', 'npm', 'node')
foreach ($cmd in $requiredCommands) {
    if (-not (Get-Command $cmd -ErrorAction SilentlyContinue)) {
        Write-Host "❌ Commande '$cmd' non trouvée. Veuillez l'installer." -ForegroundColor Red
        exit 1
    }
}

Write-Host "✅ Prérequis validés" -ForegroundColor Green

# ============================================================================
# 2. CONFIGURATION
# ============================================================================
Write-Host "2. Chargement de la configuration..." -ForegroundColor Cyan

$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptPath
$configFile = Join-Path $scriptPath "ftp-config.env"

if (-not (Test-Path $configFile)) {
    Write-Host "❌ Erreur: Fichier de configuration manquant: $configFile" -ForegroundColor Red
    Write-Host "💡 Créez le fichier avec les variables FTP_HOST, FTP_USER, FTP_PASS, FTP_PATH" -ForegroundColor Yellow
    exit 1
}

# Charger les variables d'environnement depuis le fichier .env
$envVars = @{}
Get-Content $configFile | Where-Object { $_ -match '^FTP_' } | ForEach-Object {
    $key, $value = $_ -split '=', 2
    $envVars[$key.Trim()] = $value.Trim()
}

# Validation des variables de configuration
$requiredVars = @('FTP_HOST', 'FTP_USER', 'FTP_PASS', 'FTP_PATH')
foreach ($var in $requiredVars) {
    if (-not $envVars.ContainsKey($var) -or [string]::IsNullOrWhiteSpace($envVars[$var])) {
        Write-Host "❌ Variable de configuration manquante: $var" -ForegroundColor Red
        exit 1
    }
}

$ftpHost = $envVars['FTP_HOST']
$ftpUser = $envVars['FTP_USER']
$ftpPassword = $envVars['FTP_PASS']
$remotePath = $envVars['FTP_PATH']

Write-Host "✅ Configuration chargée" -ForegroundColor Green
Write-Host ("   Serveur: " + $ftpHost) -ForegroundColor Gray
Write-Host ("   Utilisateur: " + $ftpUser) -ForegroundColor Gray
Write-Host ("   Destination: " + $remotePath) -ForegroundColor Gray

# ============================================================================
# FONCTIONS UTILITAIRES
# ============================================================================

# Fonction pour créer récursivement les dossiers sur le serveur FTP
function Create-FtpDirectory {
    param(
        [string]$ftpHost,
        [string]$ftpUser,
        [string]$ftpPassword,
        [string]$remotePath,
        [string]$directoryPath
    )

    try {
        # Normaliser le chemin (remplacer les backslashes par des slashes)
        $directoryPath = $directoryPath -replace '\\', '/'

        # Diviser le chemin en segments
        $segments = $directoryPath -split '/' | Where-Object { $_ -ne '' }

        $currentPath = $remotePath.TrimEnd('/')

        # Création optimisée des dossiers (logs réduits pour performance)
        $createdFolders = 0

        foreach ($segment in $segments) {
            $currentPath = $currentPath + "/" + $segment
            $ftpUri = "ftp://" + $ftpHost + $currentPath + "/"

            $folderExists = $false
            try {
                # Vérifier si le dossier existe déjà (timeout réduit)
                $checkRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                $checkRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
                $checkRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
                $checkRequest.Timeout = 3000  # Réduit à 3 secondes

                $checkResponse = $checkRequest.GetResponse()
                $checkResponse.Close()
                $folderExists = $true
            }
            catch {
                # Rien à faire, on va créer le dossier
            }

            if (-not $folderExists) {
                try {
                    # Créer le dossier
                    $createRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                    $createRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                    $createRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
                    $createRequest.UseBinary = $true
                    $createRequest.KeepAlive = $false
                    $createRequest.Timeout = 5000  # Réduit à 5 secondes

                    $createResponse = $createRequest.GetResponse()
                    $createResponse.Close()
                    $createdFolders++
                }
                catch {
                    return @{
                        Success = $false
                        Error = "Impossible de créer le dossier $currentPath : $($_.Exception.Message)"
                        Directory = $directoryPath
                    }
                }
            }
        }

        if ($createdFolders -gt 0) {
            Write-Host "   📁 $createdFolders dossier(s) créé(s): $directoryPath" -ForegroundColor DarkGreen
        }
        return @{ Success = $true }
    }
    catch {
        Write-Host "   ❌ Erreur générale création dossiers: $($_.Exception.Message)" -ForegroundColor Red
        return @{
            Success = $false
            Error = $_.Exception.Message
            Directory = $directoryPath
        }
    }
}

# Fonction d'upload d'un fichier avec gestion des dossiers
function Send-FtpFile {
    param($fileInfo, $ftpHost, $ftpUser, $ftpPassword, $remotePath)

    try {
        $localFile = $fileInfo.FullPath
        $relativePath = $fileInfo.RelativePath -replace '\\', '/'

        # Extraire le chemin du dossier du fichier
        $directoryPath = [System.IO.Path]::GetDirectoryName($relativePath)
        if ($directoryPath -and $directoryPath -ne '.') {
            # Créer les dossiers nécessaires (avec retry)
            $createResult = Create-FtpDirectory -ftpHost $ftpHost -ftpUser $ftpUser -ftpPassword $ftpPassword -remotePath $remotePath -directoryPath $directoryPath
            if (-not $createResult.Success) {
                return @{
                    Success = $false
                    FilePath = $fileInfo.RelativePath
                    Error = "Erreur creation dossier: $($createResult.Error)"
                }
            }
        }

        # Uploader le fichier
        $remoteFile = "ftp://" + $ftpHost + $remotePath + "/" + $relativePath
        $credential = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)

        $webClient = New-Object System.Net.WebClient
        $webClient.Credentials = $credential
        $webClient.UploadFile($remoteFile, $localFile)

        return @{
            Success = $true
            FilePath = $fileInfo.RelativePath
        }
    }
    catch {
        return @{
            Success = $false
            FilePath = $fileInfo.RelativePath
            Error = $_.Exception.Message
        }
    }
}

# ============================================================================
# 3. COMPILATION AUTOMATIQUE (OPTIONNELLE)
# ============================================================================
if (-not $NoCompile) {
    Write-Host "3. Compilation automatique du projet..." -ForegroundColor Cyan

    Push-Location $projectRoot

    if (-not (Test-Path "package.json")) {
        Write-Host "❌ ERREUR: package.json introuvable dans $projectRoot" -ForegroundColor Red
        Pop-Location
        exit 1
    }

    Write-Host "   Exécution de 'npm run build'..." -ForegroundColor Gray
    $startTime = Get-Date
    & npm run build
    $buildTime = (Get-Date) - $startTime

    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ ERREUR: La compilation a échoué (durée: $($buildTime.TotalSeconds.ToString("F1"))s)" -ForegroundColor Red
        Write-Host "💡 Vérifiez les erreurs ci-dessus ou utilisez -NoCompile pour ignorer la compilation" -ForegroundColor Yellow
        Pop-Location
        exit 1
    }

    Write-Host "✅ Compilation réussie en $($buildTime.TotalSeconds.ToString("F1"))s" -ForegroundColor Green
    Pop-Location
} else {
    Write-Host "3. ⏭️  Compilation ignorée (-NoCompile)" -ForegroundColor Yellow
}

# ============================================================================
# 4. DÉTECTION INTELLIGENTE DES FICHIERS À DÉPLOYER
# ============================================================================
Write-Host "4. Analyse des fichiers à déployer..." -ForegroundColor Cyan

Push-Location $projectRoot

# Vérifier l'état du dépôt Git
$gitStatus = git status --porcelain 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ ERREUR: Le dossier n'est pas un dépôt Git valide" -ForegroundColor Red
    Pop-Location
    exit 1
}

# Obtenir la liste des fichiers modifiés/stagés/ajoutés via git
$modifiedFiles = @()
if ($gitStatus) {
    $modifiedFiles = $gitStatus | ForEach-Object {
        $status = $_.Substring(0, 2)
        $filePath = $_.Substring(3)

        # Inclure les fichiers modifiés, ajoutés, renommés, et non trackés
        if ($status -match '[MARC?]') {
            $filePath
        }
    }
}

# Si aucun fichier modifié et pas de force, essayer le dernier commit
if ((-not $modifiedFiles -or $modifiedFiles.Count -eq 0) -and -not $Force) {
    Write-Host "   Aucun fichier modifié détecté localement, vérification du dernier commit..." -ForegroundColor Gray
    $committedChanges = git diff-tree --no-commit-id --name-only -r HEAD 2>$null
    if ($committedChanges) {
        $modifiedFiles = $committedChanges
        Write-Host "   ✅ Fichiers du dernier commit détectés" -ForegroundColor Green
    }
}

# Appliquer les filtres de fichiers essentiels
$filteredFiles = @()
$fileStats = @{
    Total = 0
    Essential = 0
    Skipped = 0
    ByType = @{}
}

foreach ($file in $modifiedFiles) {
    $fileStats.Total++

    # Déterminer le type de fichier pour les statistiques
    $extension = [System.IO.Path]::GetExtension($file).ToLower()
    if (-not $fileStats.ByType.ContainsKey($extension)) {
        $fileStats.ByType[$extension] = 0
    }
    $fileStats.ByType[$extension]++

    # Filtres des fichiers essentiels
    $essentialDirs = @('src', 'templates', 'assets', 'core', 'config', 'resources', 'lib', 'languages')
    $essentialFiles = @('bootstrap.php', 'pdf-builder-pro.php', 'readme.txt', 'composer.json', 'package.json')
    $skipPatterns = @('*.log', '*.tmp', '*.bak', '.git*', 'node_modules/*', '.DS_Store')

    $isEssential = $false
    $shouldSkip = $false

    # Vérifier si à exclure
    foreach ($pattern in $skipPatterns) {
        if ($file -like $pattern) {
            $shouldSkip = $true
            break
        }
    }

    if (-not $shouldSkip) {
        # Vérifier si essentiel
        foreach ($dir in $essentialDirs) {
            if ($file.StartsWith($dir + '/') -or $file.StartsWith($dir + '\')) {
                $isEssential = $true
                break
            }
        }
        if (-not $isEssential) {
            foreach ($essentialFile in $essentialFiles) {
                if ($file -eq $essentialFile) {
                    $isEssential = $true
                    break
                }
            }
        }
    }

    if ($isEssential) {
        $filteredFiles += $file
        $fileStats.Essential++
    } else {
        $fileStats.Skipped++
    }
}

$modifiedFiles = $filteredFiles | Select-Object -Unique

# Préparer la liste finale des fichiers à déployer
$filesToDeploy = @()
$totalSize = 0

foreach ($file in $modifiedFiles) {
    $fullPath = Join-Path $projectRoot $file
    if ((Test-Path $fullPath) -and (Test-Path $fullPath -PathType Leaf)) {
        $fileSize = (Get-Item $fullPath).Length
        $totalSize += $fileSize

        $filesToDeploy += @{
            FullPath = $fullPath
            RelativePath = $file
            Size = $fileSize
        }
    }
}

Pop-Location

# Afficher les statistiques
Write-Host "✅ Analyse terminée" -ForegroundColor Green
Write-Host "   • Fichiers analysés: $($fileStats.Total)" -ForegroundColor Gray
Write-Host "   • Fichiers essentiels: $($fileStats.Essential)" -ForegroundColor $(if ($fileStats.Essential -gt 0) { 'Green' } else { 'Yellow' })
Write-Host "   • Fichiers ignorés: $($fileStats.Skipped)" -ForegroundColor Gray
Write-Host "   • Taille totale: $([math]::Round($totalSize / 1MB, 2)) MB" -ForegroundColor Gray

if ($fileStats.ByType.Count -gt 0) {
    Write-Host "   • Types de fichiers:" -ForegroundColor Gray
    foreach ($type in ($fileStats.ByType.GetEnumerator() | Sort-Object Value -Descending)) {
        Write-Host "     $($type.Key): $($type.Value)" -ForegroundColor Gray
    }
}

if ($filesToDeploy.Count -eq 0 -and -not $Force) {
    Write-Host "`nℹ️  Aucun fichier à déployer détecté." -ForegroundColor Yellow
    Write-Host "💡 Utilisez -Force pour forcer le déploiement ou -NoGit pour ignorer Git" -ForegroundColor Cyan
    exit 0
}

Write-Host "`n📦 $($filesToDeploy.Count) fichiers à déployer" -ForegroundColor Green

# ============================================================================
# 5. UPLOAD FTP AVEC GESTION ROBUSTE DES ERREURS
# ============================================================================
Write-Host "5. Déploiement FTP ($Mode)..." -ForegroundColor Cyan

$uploadedCount = 0
$failedCount = 0
$retryCount = 0
$startTime = Get-Date

if ($Mode -eq "Parallel" -and $filesToDeploy.Count -gt 1) {
    Write-Host "   Mode parallèle activé ($ParallelJobs jobs simultanés)" -ForegroundColor Gray

    # Upload en parallèle simplifié
    $jobResults = @()
    $jobIndex = 0

    # Lancer tous les jobs d'abord
    while ($jobIndex -lt $filesToDeploy.Count) {
        $runningJobs = Get-Job | Where-Object { $_.State -eq 'Running' }
        $availableSlots = $ParallelJobs - $runningJobs.Count

        if ($availableSlots -le 0) {
            # Attendre qu'un job se termine (timeout court)
            Start-Sleep -Milliseconds 500
            continue
        }

        # Démarrer de nouveaux jobs
        for ($i = 0; $i -lt $availableSlots -and $jobIndex -lt $filesToDeploy.Count; $i++) {
            $fileInfo = $filesToDeploy[$jobIndex]
            $jobName = "FTP_Upload_$jobIndex"

            Start-Job -Name $jobName -ScriptBlock {
                param($fileInfo, $ftpHost, $ftpUser, $ftpPassword, $remotePath, $MaxRetries)

                $result = @{
                    FilePath = $fileInfo.RelativePath
                    Success = $false
                    Error = ""
                    Attempts = 0
                    Size = $fileInfo.Size
                }

                for ($attempt = 1; $attempt -le $MaxRetries; $attempt++) {
                    $result.Attempts = $attempt

                    try {
                        # Créer les dossiers nécessaires (version simplifiée)
                        $directoryPath = [System.IO.Path]::GetDirectoryName($fileInfo.RelativePath)
                        if ($directoryPath -and $directoryPath -ne '.') {
                            $segments = $directoryPath -split '/' | Where-Object { $_ -ne '' }
                            $currentPath = $remotePath

                            foreach ($segment in $segments) {
                                $currentPath = $currentPath + "/" + $segment
                                $ftpUri = "ftp://" + $ftpHost + $currentPath + "/"

                                try {
                                    $checkRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                                    $checkRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
                                    $checkRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
                                    $checkRequest.Timeout = 3000
                                    $checkResponse = $checkRequest.GetResponse()
                                    $checkResponse.Close()
                                }
                                catch {
                                    $createRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                                    $createRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                                    $createRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
                                    $createRequest.UseBinary = $true
                                    $createRequest.KeepAlive = $false
                                    $createRequest.Timeout = 5000
                                    $createResponse = $createRequest.GetResponse()
                                    $createResponse.Close()
                                }
                            }
                        }

                        # Uploader le fichier
                        $remoteFile = "ftp://" + $ftpHost + $remotePath + "/" + $fileInfo.RelativePath
                        $webClient = New-Object System.Net.WebClient
                        $webClient.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
                        $webClient.UploadFile($remoteFile, $fileInfo.FullPath)

                        $result.Success = $true
                        break
                    }
                    catch {
                        $result.Error = $_.Exception.Message
                        if ($attempt -lt $MaxRetries) {
                            Start-Sleep -Seconds ($attempt * 2)
                        }
                    }
                }

                return $result
            } -ArgumentList $fileInfo, $ftpHost, $ftpUser, $ftpPassword, $remotePath, $MaxRetries

            $jobIndex++
        }
    }

    # Attendre que tous les jobs se terminent
    Write-Host "   Attente de la fin des uploads..." -ForegroundColor Gray
    $remainingJobs = Get-Job
    if ($remainingJobs.Count -gt 0) {
        $remainingJobs | Wait-Job | ForEach-Object {
            $jobResult = Receive-Job $_
            $jobResults += $jobResult
            Remove-Job $_
        }
    }

    # Traiter les résultats (optimisé)
    foreach ($result in $jobResults) {
        if ($result.Success) {
            Write-Host "  ✅ $($result.FilePath)" -ForegroundColor Green
            $uploadedCount++
        } else {
            Write-Host "  ❌ $($result.FilePath): $($result.Error)" -ForegroundColor Red
            $failedCount++
            $retryCount += $result.Attempts - 1
        }
    }

} else {
    # Upload séquentiel avec retry (optimisé)
    foreach ($fileInfo in $filesToDeploy) {
        $result = Send-FtpFile -fileInfo $fileInfo -ftpHost $ftpHost -ftpUser $ftpUser -ftpPassword $ftpPassword -remotePath $remotePath

        if ($result.Success) {
            Write-Host "  ✅ $($fileInfo.RelativePath)" -ForegroundColor Green
            $uploadedCount++
        } else {
            Write-Host "  ❌ $($fileInfo.RelativePath): $($result.Error)" -ForegroundColor Red
            $failedCount++
        }
    }
}

$deployTime = (Get-Date) - $startTime
$avgSpeed = if ($deployTime.TotalSeconds -gt 0) { [math]::Round($totalSize / $deployTime.TotalSeconds / 1024, 1) } else { 0 }

# ============================================================================
# 6. RÉSUMÉ DÉTAILLÉ
# ============================================================================
Write-Host "6. Résumé du déploiement" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host ("Fichiers déployés: " + $uploadedCount) -ForegroundColor $(if ($uploadedCount -gt 0) { 'Green' } else { 'Yellow' })
Write-Host ("Fichiers échoués: " + $failedCount) -ForegroundColor $(if ($failedCount -gt 0) { 'Red' } else { 'Green' })
if ($retryCount -gt 0) {
    Write-Host ("Tentatives supplémentaires: " + $retryCount) -ForegroundColor Yellow
}
Write-Host ("Temps total: " + $deployTime.ToString("mm\:ss\.ff")) -ForegroundColor Gray
Write-Host ("Vitesse moyenne: " + $avgSpeed + " KB/s") -ForegroundColor Gray
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

# ============================================================================
# 7. GESTION GIT (OPTIONNELLE)
# ============================================================================
if (-not $NoGit) {
    Write-Host "7. Mise à jour du dépôt Git..." -ForegroundColor Cyan

    Push-Location $projectRoot

    # Vérifier s'il y a des changements à commiter
    $gitStatus = git status --porcelain
    if ($gitStatus) {
        Write-Host "   Ajout des fichiers modifiés..." -ForegroundColor Gray
        git add -A

        $date = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
        $commitMessage = "Déploiement automatique - $date`n`n- $uploadedCount fichiers déployés"

        if ($failedCount -gt 0) {
            $commitMessage += "`n- $failedCount fichiers échoués"
        }

        git commit -m $commitMessage

        if ($LASTEXITCODE -ne 0 -and $LASTEXITCODE -ne 1) {
            Write-Host "❌ ERREUR lors du commit Git" -ForegroundColor Red
            Pop-Location
            exit 1
        }

        Write-Host "   ✅ Commit créé" -ForegroundColor Green
    } else {
        Write-Host "   ℹ️  Aucun changement à commiter" -ForegroundColor Gray
    }

    # Push vers le dépôt distant
    Write-Host "   Push vers origin/dev..." -ForegroundColor Gray
    git push origin dev

    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✅ Push Git réussi" -ForegroundColor Green
    } else {
        Write-Host "⚠️  ATTENTION: Échec du push Git (code: $LASTEXITCODE)" -ForegroundColor Yellow
        Write-Host "💡 Vérifiez votre connexion réseau ou les droits d'accès" -ForegroundColor Cyan
    }

    Pop-Location
} else {
    Write-Host "7. ⏭️  Gestion Git ignorée (-NoGit)" -ForegroundColor Yellow
}

# ============================================================================
# 8. RAPPORT FINAL ET RECOMMANDATIONS
# ============================================================================
Write-Host "8. Déploiement terminé" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

$exitCode = 0
if ($failedCount -gt 0) {
    Write-Host "⚠️  Déploiement partiellement réussi" -ForegroundColor Yellow
    Write-Host "💡 Vérifiez les erreurs ci-dessus et relancez si nécessaire" -ForegroundColor Cyan
    $exitCode = 1
} elseif ($uploadedCount -eq 0) {
    Write-Host "ℹ️  Aucun fichier déployé" -ForegroundColor Gray
    Write-Host "💡 Utilisez -Force pour forcer le déploiement" -ForegroundColor Cyan
} else {
    Write-Host "🎉 Déploiement réussi !" -ForegroundColor Green
    Write-Host "💡 Le site est maintenant à jour" -ForegroundColor Cyan
}

# Recommandations d'usage
Write-Host "`n📚 Commandes recommandées :" -ForegroundColor Cyan
Write-Host "   • Déploiement normal    : .\ftp-deploy-simple.ps1" -ForegroundColor Gray
Write-Host "   • Mode parallèle        : .\ftp-deploy-simple.ps1 -Mode Parallel" -ForegroundColor Gray
Write-Host "   • Sans compilation      : .\ftp-deploy-simple.ps1 -NoCompile" -ForegroundColor Gray
Write-Host "   • Forcer le déploiement : .\ftp-deploy-simple.ps1 -Force" -ForegroundColor Gray
Write-Host "   • Aide complète         : Get-Help .\ftp-deploy-simple.ps1 -Full" -ForegroundColor Gray

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

exit $exitCode

