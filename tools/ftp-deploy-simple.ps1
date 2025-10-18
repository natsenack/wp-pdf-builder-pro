# 🚀 FTP DEPLOY - SIMPLE & ROBUST
# ================================
# Version simplifiée - Déploiement FTP stable et rapide

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
# 3. PRÉPARATION DES FICHIERS
# ============================================================================
Write-Host "`n📂 3. Préparation des fichiers..." -ForegroundColor Cyan

$filesToDeploy = @()

# Ajouter les dossiers essentiels
$essentialDirs = @(
    'src',
    'templates',
    'assets',
    'core',
    'config',
    'resources',
    'lib',
    'languages'
)

$essentialFiles = @(
    'bootstrap.php',
    'pdf-builder-pro.php',
    'readme.txt'
)

foreach ($dir in $essentialDirs) {
    $path = Join-Path $projectRoot $dir
    if (Test-Path $path) {
        Get-ChildItem -Path $path -Recurse -File | ForEach-Object {
            $filesToDeploy += @{
                FullPath = $_.FullName
                RelativePath = $_.FullName.Replace($projectRoot, "").TrimStart('\')
            }
        }
    }
}

foreach ($file in $essentialFiles) {
    $path = Join-Path $projectRoot $file
    if (Test-Path $path) {
        $filesToDeploy += @{
            FullPath = $path
            RelativePath = $file
        }
    }
}

Write-Host "✅ $($filesToDeploy.Count) fichiers à déployer" -ForegroundColor Green

# ============================================================================
# 4. CONNEXION FTP ET UPLOAD
# ============================================================================
Write-Host "`n📤 4. Connexion FTP et upload..." -ForegroundColor Cyan

# Créer une session FTP
$ftpUri = "ftp://$ftpHost/$remotePath/"
$credential = New-Object System.Net.NetworkCredential($ftpUser, $ftpPassword)

$uploadedCount = 0
$failedCount = 0

foreach ($fileInfo in $filesToDeploy) {
    try {
        $localFile = $fileInfo.FullPath
        $remoteFile = $ftpUri + ($fileInfo.RelativePath -replace '\\', '/')
        
        # Télécharger le fichier via WebClient
        $webClient = New-Object System.Net.WebClient
        $webClient.Credentials = $credential
        $webClient.UploadFile($remoteFile, $localFile)
        
        Write-Host "✅ $($fileInfo.RelativePath)" -ForegroundColor Green
        $uploadedCount++
    }
    catch {
        Write-Host "❌ Erreur uploading $($fileInfo.RelativePath): $_" -ForegroundColor Red
        $failedCount++
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
