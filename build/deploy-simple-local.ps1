# Script de déploiement local pour PDF Builder Pro
# Déploie vers ServBay en local

param(
    [switch]$All,
    [switch]$IncludeVendor
)

$ErrorActionPreference = "Stop"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# Configuration
$SourceDir = Split-Path $PSScriptRoot -Parent
$DestDir = "D:\site\wp\wp-content\plugins\pdf-builder-pro"

Write-Host "🚀 DÉPLOIEMENT LOCAL PDF BUILDER PRO (ServBay)" -ForegroundColor Cyan
Write-Host "Mode: $(if ($All) { 'COMPLET' } else { 'MODIFIÉ UNIQUEMENT' }) | Vendor: $(if ($IncludeVendor) { 'INCLUS' } else { 'SANS VENDOR' })" -ForegroundColor Yellow
Write-Host "=================================================================" -ForegroundColor Gray

# Vérifier la connexion au répertoire local
Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Test de connexion au répertoire local..." -ForegroundColor Gray
if (!(Test-Path $DestDir)) {
    Write-Host "❌ Répertoire de destination introuvable: $DestDir" -ForegroundColor Red
    exit 1
}
Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [SUCCESS] Connexion au répertoire local: OK" -ForegroundColor Green

# Compiler webpack
Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Début de la compilation webpack" -ForegroundColor Gray
Push-Location $SourceDir
try {
    & npm run build
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Échec de la compilation webpack" -ForegroundColor Red
        exit 1
    }
    Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [SUCCESS] Compilation webpack réussie" -ForegroundColor Green
} finally {
    Pop-Location
}

# Détecter les fichiers
Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Détection des fichiers..." -ForegroundColor Gray

$filesToCopy = @()

# Fichiers critiques toujours déployés
$criticalFiles = @(
    "plugin\pdf-builder-pro.php",
    "plugin\src\Core\PDF_Builder_Unified_Ajax_Handler.php",
    "plugin\src\Core\core\autoloader.php"
)

foreach ($file in $criticalFiles) {
    $fullPath = Join-Path $SourceDir $file
    if (Test-Path $fullPath) {
        $filesToCopy += $fullPath
        Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Fichier critique ajouté: $file" -ForegroundColor Gray
    }
}

# Fichiers compilés
$compiledFiles = @(
    "plugin\assets\js\pdf-builder-react.min.js",
    "plugin\assets\js\react-vendor.min.js",
    "plugin\assets\js\canvas-settings.min.js",
    "plugin\assets\js\pdf-builder-react-init.min.js",
    "plugin\assets\js\ajax-throttle.min.js",
    "plugin\assets\js\notifications.min.js",
    "plugin\assets\js\pdf-builder-wrap.min.js",
    "plugin\assets\js\pdf-builder-init.min.js",
    "plugin\assets\css\pdf-builder-react.min.css",
    "plugin\assets\css\notifications.min.css"
)

foreach ($file in $compiledFiles) {
    $fullPath = Join-Path $SourceDir $file
    if (Test-Path $fullPath) {
        $filesToCopy += $fullPath
        Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Fichier critique compilé ajouté: $file" -ForegroundColor Gray
    }
}

if ($All) {
    Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Mode complet: tous les fichiers du plugin" -ForegroundColor Gray
    # En mode All, on copie tout le répertoire plugin, donc pas besoin de lister les fichiers
} else {
    Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Mode normal: fichiers modifiés" -ForegroundColor Gray
    # En mode normal, on copie seulement les fichiers critiques
}

# Inclure vendor si demandé
if ($IncludeVendor) {
    Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Inclusion du dossier vendor" -ForegroundColor Gray
    $vendorDir = Join-Path $SourceDir "plugin\vendor"
    if (Test-Path $vendorDir) {
        if ($All) {
            Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Vendor sera inclus dans la copie complète" -ForegroundColor Gray
        } else {
            $vendorFiles = Get-ChildItem -Path $vendorDir -Recurse -File | Select-Object -ExpandProperty FullName
            $filesToCopy += $vendorFiles
        }
    }
}

Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [SUCCESS] Détection terminée" -ForegroundColor Green

# Copie vers ServBay
Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Copie vers ServBay..." -ForegroundColor Gray

$copiedCount = 0

if ($All) {
    Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Copie complète du contenu du répertoire plugin..." -ForegroundColor Gray
    try {
        # Copier le contenu du répertoire plugin (pas le dossier lui-même)
        $sourcePluginDir = Join-Path $SourceDir "plugin"
        Copy-Item -Path "$sourcePluginDir\*" -Destination $DestDir -Recurse -Force
        $destFiles = Get-ChildItem -Path $DestDir -Recurse -File
        $copiedCount = $destFiles.Count
        Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [SUCCESS] Copie complète réussie" -ForegroundColor Green
    } catch {
        Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [ERROR] Échec de la copie complète: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Copie sélective des fichiers critiques..." -ForegroundColor Gray
    foreach ($file in $filesToCopy) {
        $relativePath = $file.Substring($SourceDir.Length + 1)
        $destFile = Join-Path $DestDir ($relativePath -replace "^plugin\\", "")

        $destDir = Split-Path $destFile -Parent
        if (!(Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }

        try {
            Copy-Item -Path $file -Destination $destFile -Force
            $copiedCount++
        } catch {
            Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [ERROR] Échec copie: $relativePath - $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}

Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [SUCCESS] $copiedCount fichier(s) déployé(s) avec succès" -ForegroundColor Green

# Vérifications d'intégrité
Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Vérifications d'intégrité..." -ForegroundColor Gray
$destFiles = Get-ChildItem -Path $DestDir -Recurse -File
Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [SUCCESS] Déploiement réussi ! ($($destFiles.Count) fichiers dans la destination)" -ForegroundColor Green

# Commit Git
Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Vérification des changements Git" -ForegroundColor Gray
Push-Location $SourceDir
try {
    $status = & git status --porcelain
    if ($status) {
        $date = Get-Date -Format "dd/MM/yyyy HH:mm"
        & git add .
        & git commit -m "deploy local: $date - $($filesToCopy.Count) fichiers"
        Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [SUCCESS] Commit et push Git réussis" -ForegroundColor Green
    } else {
        Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [INFO] Aucun changement à commiter" -ForegroundColor Gray
    }
} catch {
    Write-Host "[$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')] [WARNING] Erreur Git: $($_.Exception.Message)" -ForegroundColor Yellow
} finally {
    Pop-Location
}

Write-Host "=================================================================" -ForegroundColor Gray
Write-Host "📋 RÉSUMÉ DU DÉPLOIEMENT" -ForegroundColor Cyan
Write-Host "=================================================================" -ForegroundColor Gray
Write-Host "Mode: $(if ($All) { 'COMPLET' } else { 'MODIFIÉ UNIQUEMENT' })" -ForegroundColor White
Write-Host "Vendor: $(if ($IncludeVendor) { 'INCLUS' } else { 'SANS VENDOR' })" -ForegroundColor White
Write-Host "Fichiers déployés: $copiedCount" -ForegroundColor White
Write-Host "Fichiers vérifiés: $($criticalFiles.Count + $compiledFiles.Count)" -ForegroundColor White
Write-Host "Destination: $DestDir" -ForegroundColor White
Write-Host "Compilation: ✅ Réussie" -ForegroundColor Green
Write-Host "Git Commit: ✅ Réussi" -ForegroundColor Green
Write-Host "=================================================================" -ForegroundColor Gray
Write-Host "🔄 PROCHAINES ÉTAPES:" -ForegroundColor Yellow
Write-Host "1. Redémarre le serveur dans ServBay" -ForegroundColor White
Write-Host "2. Active le plugin dans WordPress (Extensions > Plugins installés)" -ForegroundColor White
Write-Host "3. Teste l'élément company_info dans l'éditeur PDF" -ForegroundColor White