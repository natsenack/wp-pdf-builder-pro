# 🚀 FTP DEPLOY - VERSION OPTIMISÉE
# ================================
# Version optimisée - Déploiement FTP avec vérification des changements et parallélisation

Write-Host "🚀 FTP DEPLOY - VERSION SIMPLE & ROBUSTE" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Green

# ============================================================================
# 1. CONFIGURATION
# ============================================================================
Write-Host "`n📋 1. Chargement de la configuration..." -ForegroundColor Cyan

$projectRoot = Split-Path (Get-Location) -Parent
$configFile = Join-Path $projectRoot "tools/ftp-config.env"

if (-not (Test-Path $configFile)) {
    Write-Host "❌ Erreur: Fichier de configuration manquant: $configFile" -ForegroundColor Red
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

Write-Host "✅ Configuration chargée" -ForegroundColor Green
Write-Host "   Serveur: $ftpHost" -ForegroundColor Gray
Write-Host "   User: $ftpUser" -ForegroundColor Gray
Write-Host "   Destination: $remotePath" -ForegroundColor Gray

# ============================================================================
# 2. COMPILATION
# ============================================================================
Write-Host "`n🔨 2. Compilation du projet..." -ForegroundColor Cyan

Push-Location $projectRoot

if (-not (Test-Path "package.json")) {
    Write-Host "❌ Erreur: package.json introuvable" -ForegroundColor Red
    Pop-Location
    exit 1
}

& npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Erreur: La compilation a échoué" -ForegroundColor Red
    Pop-Location
    exit 1
}

Write-Host "✅ Compilation réussie" -ForegroundColor Green
Pop-Location

# ============================================================================
# 3. DÉTECTION DES FICHIERS MODIFIÉS (OPTIMISÉ)
# ============================================================================
Write-Host "`n� 3. Détection des fichiers modifiés..." -ForegroundColor Cyan

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

# Obtenir aussi les fichiers trackés modifiés par rapport au dernier commit
$committedChanges = git diff --name-only HEAD~1 2>$null
if ($committedChanges) {
    $modifiedFiles += $committedChanges
}

# Éliminer les doublons et filtrer
$modifiedFiles = $modifiedFiles | Select-Object -Unique | Where-Object {
    $file = $_
    # Inclure seulement les fichiers dans les dossiers essentiels
    $essentialDirs = @('src', 'templates', 'assets', 'core', 'config', 'resources', 'lib', 'languages')
    $essentialFiles = @('bootstrap.php', 'pdf-builder-pro.php', 'readme.txt')

    $isInEssentialDir = $essentialDirs | Where-Object { $file.StartsWith("$_\") -or $file.StartsWith($_ + '/') }
    $isEssentialFile = $essentialFiles -contains $file

    $isInEssentialDir -or $isEssentialFile
}

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

Write-Host "✅ $($filesToDeploy.Count) fichiers modifiés à déployer" -ForegroundColor Green
if ($filesToDeploy.Count -eq 0) {
    Write-Host "ℹ️  Aucun fichier modifié détecté. Déploiement annulé." -ForegroundColor Yellow
    exit 0
}

# ============================================================================
# 4. CONNEXION FTP ET UPLOAD PARALLÈLE (OPTIMISÉ)
# ============================================================================
Write-Host "`n📤 4. Connexion FTP et upload parallèle..." -ForegroundColor Cyan

$ftpUri = "ftp://$ftpHost/$remotePath/"
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

# Upload en parallèle par lots
$results = @()
for ($i = 0; $i -lt $filesToDeploy.Count; $i += $maxConcurrentUploads) {
    $batch = $filesToDeploy[$i..([Math]::Min($i + $maxConcurrentUploads - 1, $filesToDeploy.Count - 1))]

    Write-Host "📦 Traitement du lot $($i / $maxConcurrentUploads + 1) ($($batch.Count) fichiers)..." -ForegroundColor Gray

    # Lancer les uploads en parallèle
    $jobs = $batch | ForEach-Object {
        Start-Job -ScriptBlock ${function:Send-FtpFile} -ArgumentList $_, $ftpUri, $ftpUser, $ftpPassword
    }

    # Attendre la fin de tous les jobs du lot
    $jobs | Wait-Job | Out-Null

    # Récupérer les résultats
    $batchResults = $jobs | ForEach-Object {
        $result = Receive-Job -Job $_
        Remove-Job -Job $_
        $result
    }

    $results += $batchResults

    # Afficher les résultats du lot
    foreach ($result in $batchResults) {
        if ($result.Success) {
            Write-Host "✅ $($result.FilePath)" -ForegroundColor Green
            $uploadedCount++
        } else {
            Write-Host "❌ Erreur uploading $($result.FilePath): $($result.Error)" -ForegroundColor Red
            $failedCount++
        }
    }
}

# ============================================================================
# 5. RÉSUMÉ
# ============================================================================
Write-Host "`n📊 5. Résumé du déploiement" -ForegroundColor Cyan
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray
Write-Host "✅ Fichiers uploadés: $uploadedCount" -ForegroundColor Green
Write-Host "❌ Fichiers échoués: $failedCount" -ForegroundColor $(if ($failedCount -gt 0) { "Red" } else { "Green" })
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

# ============================================================================
# 6. PUSH GIT
# ============================================================================
Write-Host "`n🔄 6. Push Git..." -ForegroundColor Cyan

Push-Location $projectRoot

git add -A
git commit -m "Déploiement automatique - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"

if ($LASTEXITCODE -ne 0 -and $LASTEXITCODE -ne 1) {
    Write-Host "❌ Erreur Git" -ForegroundColor Red
    Pop-Location
    exit 1
}

git push origin dev
if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Push Git réussi" -ForegroundColor Green
} else {
    Write-Host "⚠️  Erreur lors du push Git" -ForegroundColor Yellow
}

Pop-Location

# ============================================================================
# FIN
# ============================================================================
Write-Host "`n✅ Déploiement terminé!" -ForegroundColor Green
Write-Host "Destination: ftp://$ftpHost/$remotePath/" -ForegroundColor Cyan
