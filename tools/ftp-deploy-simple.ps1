# 🚀 FTP DEPLOY - VERSION OPTIMISÉE
# ================================
# Version optimisée - Déploiement FTP avec vérification des changements et parallélisation

Write-Host "FTP DEPLOY - VERSION SIMPLE & ROBUSTE" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green

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
# 2. COMPILATION
# ============================================================================
Write-Host "2. Compilation du projet..." -ForegroundColor Cyan

Push-Location $projectRoot

if (-not (Test-Path "package.json")) {
    Write-Host "ERREUR: package.json introuvable" -ForegroundColor Red
    Pop-Location
    exit 1
}

& npm run build
if ($LASTEXITCODE -ne 0) {
Write-Host "Erreur: La compilation a echoue" -ForegroundColor Red
    Pop-Location
    exit 1
}

Write-Host "Compilation reussie" -ForegroundColor Green
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
    if (Test-Path $fullPath) {
        $filesToDeploy += @{
            FullPath = $fullPath
            RelativePath = $file
        }
    }
}

Pop-Location

Write-Host ($filesToDeploy.Count + " fichiers modifies a deployer") -ForegroundColor Green
if ($filesToDeploy.Count -eq 0) {
Write-Host "`nINFO: Aucun fichier modifie detecte. Deploiement annule." -ForegroundColor Yellow
    exit 0
}

# ============================================================================
# 4. CONNEXION FTP ET UPLOAD PARALLÈLE (OPTIMISÉ)
# ============================================================================
Write-Host "4. Connexion FTP et upload..." -ForegroundColor Cyan

$ftpUri = "ftp://" + $ftpHost + $remotePath + "/"
$credential = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)

$uploadedCount = 0
$failedCount = 0
$maxConcurrentUploads = 5  # Nombre maximum d'uploads simultanés

# Fonction d'upload pour un fichier
function Send-FtpFile {
    param($fileInfo, $ftpUri, $ftpUser, $ftpPassword)

    try {
        $localFile = $fileInfo.FullPath
        $remoteFile = $ftpUri + ($fileInfo.RelativePath -replace '\\', '/')

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

# Upload simple et séquentiel
$results = @()
foreach ($fileInfo in $filesToDeploy) {
    try {
        $localFile = $fileInfo.FullPath
        $remoteFile = $ftpUri + ($fileInfo.RelativePath -replace '\\', '/')

        $webClient = New-Object System.Net.WebClient
        $webClient.Credentials = $credential
        $webClient.UploadFile($remoteFile, $localFile)

        $results += @{
            Success = $true
            FilePath = $fileInfo.RelativePath
        }
        Write-Host "OK $($fileInfo.RelativePath)" -ForegroundColor Green
        $uploadedCount++
    }
    catch {
        $results += @{
            Success = $false
            FilePath = $fileInfo.RelativePath
            Error = $_.Exception.Message
        }
        Write-Host "ERREUR uploading $($fileInfo.RelativePath): $($_.Exception.Message)" -ForegroundColor Red
        $failedCount++
    }
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
