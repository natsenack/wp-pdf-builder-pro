# 🚀 FTP DEPLOY - VERSION CORRIGÉE
# ================================
# Version corrigée - Déploiement FTP avec création automatique des dossiers

Write-Host "FTP DEPLOY - VERSION CORRIGÉE" -ForegroundColor Green
Write-Host "==============================" -ForegroundColor Green

# ============================================================================
# 1. CONFIGURATION
# ============================================================================
Write-Host "1. Chargement de la configuration..." -ForegroundColor Cyan

$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptPath
$configFile = Join-Path $scriptPath "ftp-config.env"

if (-not (Test-Path $configFile)) {
Write-Host "Erreur: Fichier de configuration manquant:" $configFile -ForegroundColor Red
    exit 1
}

# Charger les variables d'environnement depuis le fichier .env
$envVars = @{}
Get-Content $configFile | Where-Object { $_ -match '^FTP_' } | ForEach-Object {
    $key, $value = $_ -split '=', 2
    $envVars[$key.Trim()] = $value.Trim()
}

$ftpHost = $envVars['FTP_HOST']
$ftpUser = $envVars['FTP_USER']
$ftpPassword = $envVars['FTP_PASS']
$remotePath = $envVars['FTP_PATH']

Write-Host "Configuration chargee" -ForegroundColor Green
Write-Host ("   Serveur: " + $ftpHost) -ForegroundColor Gray
Write-Host ("   User: " + $ftpUser) -ForegroundColor Gray
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
        # Diviser le chemin en segments
        $segments = $directoryPath -split '/' | Where-Object { $_ -ne '' }

        $currentPath = $remotePath

        foreach ($segment in $segments) {
            $currentPath = $currentPath + "/" + $segment
            $ftpUri = "ftp://" + $ftpHost + $currentPath + "/"

            try {
                # Vérifier si le dossier existe déjà
                $checkRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                $checkRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
                $checkRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
                $checkRequest.Timeout = 5000

                $checkResponse = $checkRequest.GetResponse()
                $checkResponse.Close()
                # Le dossier existe déjà, continuer
            }
            catch {
                # Le dossier n'existe pas, le créer
                $createRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                $createRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                $createRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)
                $createRequest.UseBinary = $true
                $createRequest.KeepAlive = $false
                $createRequest.Timeout = 10000

                $createResponse = $createRequest.GetResponse()
                $createResponse.Close()
            }
        }

        return @{ Success = $true }
    }
    catch {
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
            # Créer les dossiers nécessaires
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
# 2. COMPILATION AUTOMATIQUE
# ============================================================================
Write-Host "2. Compilation automatique du projet..." -ForegroundColor Cyan

Push-Location $projectRoot

if (-not (Test-Path "package.json")) {
    Write-Host "ERREUR: package.json introuvable" -ForegroundColor Red
    Pop-Location
    exit 1
}

& npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "ERREUR: La compilation a échoué - arrêt du déploiement" -ForegroundColor Red
    Write-Host "Détails de l'erreur:" -ForegroundColor Yellow
    Pop-Location
    exit 1
}

Write-Host "Compilation réussie" -ForegroundColor Green
Pop-Location

# ============================================================================
# 3. DÉTECTION DES FICHIERS MODIFIÉS (OPTIMISÉ)
# ============================================================================
Write-Host "3. Detection des fichiers modifies..." -ForegroundColor Cyan

Push-Location $projectRoot

# Obtenir la liste des fichiers modifiés/stagés/ajoutés via git
$modifiedFiles = git status --porcelain | ForEach-Object {
    $status = $_.Substring(0, 2)
    $filePath = $_.Substring(3)

    # Inclure les fichiers modifiés, ajoutés, renommés, et non trackés
    if ($status -match '[MARC?]') {
        $filePath
    }
}

# Obtenir aussi les fichiers du dernier commit
if (-not $modifiedFiles -or $modifiedFiles.Count -eq 0) {
    $committedChanges = git diff-tree --no-commit-id --name-only -r HEAD 2>$null
    if ($committedChanges) {
        $modifiedFiles = $committedChanges
    }
}

# Éliminer les doublons et filtrer
$filteredFiles = @()
foreach ($file in $modifiedFiles) {
    $essentialDirs = @('src', 'templates', 'assets', 'core', 'config', 'resources', 'lib', 'languages')
    $essentialFiles = @('bootstrap.php', 'pdf-builder-pro.php', 'readme.txt')

    $isEssential = $false
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

    if ($isEssential) {
        $filteredFiles += $file
    }
}
$modifiedFiles = $filteredFiles | Select-Object -Unique

$filesToDeploy = @()
foreach ($file in $modifiedFiles) {
    $fullPath = Join-Path $projectRoot $file
    if ((Test-Path $fullPath) -and (Test-Path $fullPath -PathType Leaf)) {
        $filesToDeploy += @{
            FullPath = $fullPath
            RelativePath = $file
        }
    }
}

Pop-Location

Write-Host "$($filesToDeploy.Count) fichiers modifies a deployer" -ForegroundColor Green
if ($filesToDeploy.Count -eq 0) {
Write-Host "`nINFO: Aucun fichier modifie detecte. Deploiement annule." -ForegroundColor Yellow
    exit 0
}

# ============================================================================
# 4. CONNEXION FTP ET UPLOAD AVEC GESTION DES DOSSIERS
# ============================================================================
Write-Host "4. Connexion FTP et upload avec gestion des dossiers..." -ForegroundColor Cyan

$uploadedCount = 0
$failedCount = 0

# Upload séquentiel avec gestion des dossiers
$results = @()
foreach ($fileInfo in $filesToDeploy) {
    Write-Host "Uploading $($fileInfo.RelativePath)..." -ForegroundColor Gray

    $result = Send-FtpFile -fileInfo $fileInfo -ftpHost $ftpHost -ftpUser $ftpUser -ftpPassword $ftpPassword -remotePath $remotePath

    if ($result.Success) {
        Write-Host "  ✅ OK $($fileInfo.RelativePath)" -ForegroundColor Green
        $uploadedCount++
    } else {
        Write-Host "  ❌ ERREUR $($fileInfo.RelativePath): $($result.Error)" -ForegroundColor Red
        $failedCount++
    }

    $results += $result
}

# ============================================================================
# 5. RÉSUMÉ
# ============================================================================
Write-Host "5. Resume du deploiement" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host ("Fichiers uploades: " + $uploadedCount) -ForegroundColor Green
Write-Host ('Fichiers echoues: ' + $failedCount) -ForegroundColor $(if ($failedCount -gt 0) { 'Red' } else { 'Green' })
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

# ============================================================================
# 6. PUSH GIT
# ============================================================================
Write-Host "6. Push Git..." -ForegroundColor Cyan

Push-Location $projectRoot

git add -A
$date = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
git commit -m "Déploiement automatique - $date"

if ($LASTEXITCODE -ne 0 -and $LASTEXITCODE -ne 1) {
    Write-Host "ERREUR Git" -ForegroundColor Red
    Pop-Location
    exit 1
}

git push origin dev
if ($LASTEXITCODE -eq 0) {
    Write-Host "Push Git reussi" -ForegroundColor Green
} else {
    Write-Host "ATTENTION: Erreur lors du push Git" -ForegroundColor Yellow
}

Pop-Location

# ============================================================================
# FIN
# ============================================================================
