# Script de déploiement LOCAL pour PDF Builder Pro
# Copie conforme de deploy-simple.ps1 mais avec déploiement en local
# Usage: .\deploy-simple-local.ps1 [-All] [-IncludeVendor] [-SkipConnectionTest]

param(
    [switch]$All,
    [switch]$SkipConnectionTest,
    [switch]$IncludeVendor
)

# Paramètres par défaut pour les options supprimées
$Clean = $false
$Verbose = $false
$DryRun = $false

$ErrorActionPreference = "Stop"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# Configuration locale
$LocalPath = "D:\site\wp\wp-content\plugins\pdf-builder-pro"

# Détecter automatiquement le répertoire de travail
$WorkingDir = Split-Path $PSScriptRoot -Parent

# Vérifier que le répertoire de travail est valide
if (!(Test-Path (Join-Path $WorkingDir "plugin"))) {
    Write-Host "❌ Répertoire de travail invalide: $WorkingDir" -ForegroundColor Red
    Write-Host "   Le script doit être exécuté depuis le dossier build/ du projet." -ForegroundColor Red
    exit 1
}

$PluginDir = Join-Path $WorkingDir "plugin"
$LogFile = Join-Path $PSScriptRoot "deployment-local.log"

# Fonction de logging
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
    if ($Verbose) { Add-Content -Path $LogFile -Value $logMessage }
}

# Fonction pour vérifier si un répertoire existe localement
function Test-LocalDirectoryExists {
    param([string]$localDir)
    return Test-Path $localDir -PathType Container
}

# Fonction pour créer un répertoire localement (récursif)
function New-LocalDirectory {
    param([string]$localDir)
    if (!(Test-Path $localDir)) {
        Write-Log "Création répertoire local: $localDir" "INFO"
        try {
            New-Item -ItemType Directory -Path $localDir -Force -ErrorAction Stop | Out-Null
            Write-Log "Répertoire créé: $localDir" "SUCCESS"
            return $true
        } catch {
            Write-Log "Erreur création répertoire $localDir : $($_.Exception.Message)" "ERROR"
            return $false
        }
    } else {
        Write-Log "Répertoire existe déjà: $localDir" "INFO"
        return $true
    }
}

# Fonction pour supprimer un fichier local
function Remove-LocalFile {
    param([string]$filePath)
    try {
        Remove-Item -Path $filePath -Force -ErrorAction Stop
        return $true
    } catch {
        Write-Log "Erreur suppression $filePath : $($_.Exception.Message)" "ERROR"
        return $false
    }
}

function Test-DeployedFileIntegrity {
    param([string]$relativePath, [string]$expectedContent = "")
    try {
        $localFilePath = Join-Path $LocalPath $relativePath
        
        if (!(Test-Path $localFilePath)) {
            Write-Log "Fichier non trouvé: $localFilePath" "ERROR"
            return $false
        }
        
        # Vérifier la date de modification du fichier
        $fileInfo = Get-Item $localFilePath
        $lastModified = $fileInfo.LastWriteTime
        
        $timeSinceModified = [DateTime]::Now - $lastModified
        if ($timeSinceModified.TotalMinutes -gt 5) {
            Write-Log "ATTENTION: Fichier $relativePath modifié il y a plus de 5 minutes ($lastModified)" "WARN"
        } else {
            Write-Log "Date modification récente: $relativePath ($lastModified)" "SUCCESS"
        }
        
        # Lire le contenu du fichier
        $contentBytes = [System.IO.File]::ReadAllBytes($localFilePath)
        $content = [System.Text.Encoding]::UTF8.GetString($contentBytes)
        
        # Comparer avec le hash du fichier source
        $sourceFilePath = Join-Path $PluginDir $relativePath
        if (Test-Path $sourceFilePath) {
            $sourceBytes = [System.IO.File]::ReadAllBytes($sourceFilePath)
            $sourceHash = [System.BitConverter]::ToString([System.Security.Cryptography.SHA256]::Create().ComputeHash($sourceBytes)).Replace("-", "").ToLower()
            
            $deployedHash = [System.BitConverter]::ToString([System.Security.Cryptography.SHA256]::Create().ComputeHash($contentBytes)).Replace("-", "").ToLower()
            
            # Vérifier la taille exacte en octets
            if ($contentBytes.Length -ne $sourceBytes.Length) {
                Write-Log "SIZE MISMATCH: $relativePath - Source: $($sourceBytes.Length), Deployed: $($contentBytes.Length)" "ERROR"
                return $false
            }
            
            if ($deployedHash -ne $sourceHash) {
                Write-Log "HASH MISMATCH: $relativePath - Source: $sourceHash, Deployed: $deployedHash" "ERROR"
                Write-Log "Contenu déployé corrompu ou différent" "ERROR"
                return $false
            }
            Write-Log "Hash vérifié: $relativePath" "SUCCESS"
        }
        
        # Vérifications d'intégrité
        if ($content.Length -eq 0) {
            Write-Log "Fichier vide détecté: $relativePath" "ERROR"
            return $false
        }
        
        # Pour les fichiers PHP
        if ($relativePath -like "*.php") {
            $firstLine = ($content -split "`n" | Where-Object { $_.Trim() -ne "" })[0].Trim()
            $hasPhpTag = $content -match "<\?php"
            $hasValidStart = $firstLine -match "^(/\*|\*\*|//|namespace|use|class|function|if|define)" -or $hasPhpTag
            
            if (-not $hasValidStart) {
                Write-Log "Fichier PHP invalide (pas de code PHP valide): $relativePath" "ERROR"
                Write-Log "Première ligne: '$firstLine'" "ERROR"
                return $false
            }
            
            # Vérifications spécifiques
            if ($relativePath -eq "src/Core/PDF_Builder_Unified_Ajax_Handler.php") {
                if ($content -notmatch "class PDF_Builder_Unified_Ajax_Handler") {
                    Write-Log "Classe PDF_Builder_Unified_Ajax_Handler non trouvée" "ERROR"
                    return $false
                }
                Write-Log "Classe PDF_Builder_Unified_Ajax_Handler trouvée et valide" "SUCCESS"
            }
        }
        
        Write-Log "Intégrité OK: $relativePath ($($content.Length) caractères)" "SUCCESS"
        return $true
    } catch {
        Write-Log "Erreur vérification $relativePath : $($_.Exception.Message)" "ERROR"
        return $false
    }
}

Write-Host "🚀 DÉPLOIEMENT LOCAL PDF BUILDER PRO" -ForegroundColor Cyan
$mode = if ($All) { "COMPLET (-All)" } else { "MODIFIÉ UNIQUEMENT" }
$vendorMode = if ($IncludeVendor) { "AVEC VENDOR" } else { "SANS VENDOR" }
Write-Host "Mode: $mode | $vendorMode" -ForegroundColor Yellow
Write-Host "Destination: $LocalPath" -ForegroundColor Yellow
Write-Host ("=" * 60) -ForegroundColor White
Write-Log "Début du déploiement en mode $mode ($vendorMode)"

# 1 DETECTION DES FICHIERS A DEPLOYER
Write-Host "`n1 Detection des fichiers..." -ForegroundColor Magenta

$filesToDeploy = @()

if ($All) {
    Write-Log "Mode complet: tous les fichiers du plugin" "INFO"
    $exclusions = @('\\\.git\\', 'node_modules', 'tests', 'temp\.js$', 'composer-setup\.php$', 'phpstan\.neon$', '\.log$', '\.tmp$', 'plugin\\resources\\assets\\js\\dist\\plugin\\resources\\assets', '\.ts$', '\.tsx$', '\.map$', '\.md$', 'README\.md$', 'config\.ts', 'tsconfig')
    if (-not $IncludeVendor) {
        $exclusions += 'vendor'
    }
    # Include critical files that should not be excluded
    $criticalFiles = @()
    $filesToDeploy = @(Get-ChildItem -Path $PluginDir -Recurse -File | Where-Object {
        $path = $_.FullName
        # Always include readme.txt
        if ($_.Name -eq 'readme.txt') {
            $criticalFiles += $_
            return $false
        }
        -not ($exclusions | Where-Object { $path -match $_ })
    })
    $filesToDeploy += $criticalFiles
} else {
    Write-Log "Mode normal: fichiers modifiés" "INFO"
    $modified = @(& git diff --name-only)
    $staged = @(& git diff --cached --name-only)
    $untracked = @(& git ls-files --others --exclude-standard)
    $allFiles = ($modified + $staged + $untracked) | Select-Object -Unique | Where-Object { $_ -like "plugin/*" -and (Test-Path (Join-Path $WorkingDir $_)) }
    $filesToDeploy = @($allFiles | ForEach-Object { Get-Item (Join-Path $WorkingDir $_) })
}

# Always include critical compiled files
$criticalCompiledFiles = @(
    "plugin/assets/js/pdf-builder-react-wrapper.min.js"
    "plugin/assets/js/pdf-builder-react.min.js"
    "plugin/assets/js/vendors.min.js"
    # react-vendor.min.js supprimé — React est bundlé dans pdf-builder-react.min.js
    "plugin/assets/js/canvas-settings.min.js"
    "plugin/assets/js/pdf-builder-react-init.min.js"
    "plugin/assets/js/pdf-builder-react-executor.min.js"
    "plugin/assets/js/ajax-throttle.min.js"
    "plugin/assets/js/notifications.min.js"
    "plugin/assets/js/pdf-builder-wrap.min.js"
    "plugin/assets/js/pdf-builder-init.min.js"
    "plugin/assets/js/predefined-templates.js"
    "plugin/assets/js/settings-main.min.js"
    "plugin/assets/js/settings-tabs.min.js"
    "plugin/assets/css/pdf-builder-react.min.css"
    "plugin/assets/css/notifications.min.css"
    "plugin/assets/css/notifications-css.min.css"
    "plugin/assets/css/dashboard-css.min.css"
    "plugin/assets/css/templates-page-css.min.css"
    "plugin/assets/css/settings-systeme-css.min.css"
)
foreach ($criticalCompiledFile in $criticalCompiledFiles) {
    $criticalCompiledPath = Join-Path $WorkingDir $criticalCompiledFile
    if (Test-Path $criticalCompiledPath) {
        $fileItem = Get-Item $criticalCompiledPath
        if ($filesToDeploy.FullName -notcontains $fileItem.FullName) {
            $filesToDeploy += $fileItem
            Write-Log "Fichier compilé critique ajouté: $criticalCompiledFile" "INFO"
        }
    }
}

# Always include critical files
$criticalFiles = @("pdf-builder-pro.php", "src/Core/PDF_Builder_Unified_Ajax_Handler.php", "src/Core/core/autoloader.php")
foreach ($criticalFile in $criticalFiles) {
    $criticalPath = Join-Path $PluginDir $criticalFile
    if (Test-Path $criticalPath) {
        # Forcer la suppression du fichier critique en destination pour garantir une copie fraîche
        $localCriticalDest = Join-Path $LocalPath $criticalFile.Replace("/", "\")
        if (Test-Path $localCriticalDest) {
            Remove-Item -Force $localCriticalDest -ErrorAction SilentlyContinue
            Write-Log "Fichier critique supprimé (copie fraîche) : $criticalFile" "INFO"
        }
        $fileItem = Get-Item $criticalPath
        if ($filesToDeploy.FullName -notcontains $fileItem.FullName) {
            $filesToDeploy += $fileItem
            Write-Log "Fichier critique ajouté: $criticalFile" "INFO"
        }
    }
}

Write-Log "$($filesToDeploy.Count) fichier(s) détecté(s)" "SUCCESS"

# 2 COMPILATION WEBPACK
Write-Host "`n2 Compilation Webpack..." -ForegroundColor Magenta
Write-Log "Début de la compilation webpack" "INFO"

Push-Location $WorkingDir
try {
    $npmAvailable = Get-Command npm -ErrorAction SilentlyContinue
    if (-not $npmAvailable) {
        Write-Log "npm n'est pas disponible, compilation ignorée" "WARN"
    } else {
        if (Test-Path "package.json") {
            Write-Log "Lancement de npm run build" "INFO"
            $buildResult = & npm run build 2>&1
            $buildExitCode = $LASTEXITCODE
            
            foreach ($line in $buildResult) {
                if ($line -match "ERROR" -or $line -match "error") {
                    Write-Log "Webpack: $line" "ERROR"
                } elseif ($line -match "WARNING" -or $line -match "warning") {
                    Write-Log "Webpack: $line" "WARN"
                } elseif ($line -match "compiled successfully") {
                    Write-Log "Webpack: $line" "SUCCESS"
                } else {
                    Write-Log "Webpack: $line" "INFO"
                }
            }
            
            if ($buildExitCode -eq 0) {
                Write-Log "Compilation webpack réussie" "SUCCESS"
            } else {
                Write-Log "Erreur lors de la compilation webpack (code: $buildExitCode)" "ERROR"
                Write-Host "`n❌ ERREUR WEBPACK - Arrêt du déploiement" -ForegroundColor Red
                exit 1
            }
        } else {
            Write-Log "package.json non trouvé, compilation ignorée" "WARN"
        }
    }
} catch {
    Write-Log "Exception lors de la compilation: $($_.Exception.Message)" "ERROR"
    Write-Host "`n❌ ERREUR WEBPACK - Arrêt du déploiement" -ForegroundColor Red
    exit 1
} finally {
    Pop-Location
}

# 2.5 GIT ADD DES FICHIERS MODIFIÉS
Write-Host "`n2.5 Git add..." -ForegroundColor Magenta
Write-Log "Ajout des fichiers modifiés à Git" "INFO"
Push-Location $WorkingDir
try {
    $gitAddResult = & git add . 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Log "Tentative avec --ignore-errors" "INFO"
        & git add --ignore-errors . 2>$null
    } else {
        $errorMessages = @()
        foreach ($result in $gitAddResult) {
            $message = $result.ToString()
            if ($message -and $message -notmatch "^warning:" -and $message -notmatch "^\s*$") {
                $errorMessages += $message
            }
        }
        if ($errorMessages.Count -gt 0) {
            Write-Log "Erreur git add: $($errorMessages -join '; ')" "ERROR"
        }
    }

    $criticalSystemFiles = @(
        "plugin/assets/js/pdf-builder-react-wrapper.min.js"
        "plugin/assets/js/ajax-throttle.min.js"
        "plugin/assets/js/notifications.min.js"
        "plugin/assets/js/pdf-builder-wrap.min.js"
        "plugin/assets/js/pdf-builder-init.min.js"
        "plugin/assets/js/predefined-templates.js"
        "plugin/assets/css/notifications.min.css"
    )
    foreach ($criticalFile in $criticalSystemFiles) {
        if (Test-Path $criticalFile) {
            & git add $criticalFile 2>$null
            Write-Log "Fichier critique ajouté à Git: $criticalFile" "INFO"
        }
    }

    Write-Log "Git add réussi" "SUCCESS"
} catch {
    Write-Log "Erreur git add: $($_.Exception.Message)" "ERROR"
} finally {
    Pop-Location
}

# 3 COPIE LOCAL
Write-Host "`n3 Copie des fichiers en local..." -ForegroundColor Magenta
Write-Log "Début de la copie locale vers: $LocalPath" "INFO"

if ($filesToDeploy.Count -eq 0) {
    Write-Log "❌ AUCUN FICHIER À COPIER! Vérifiez les exclusions." "ERROR"
    exit 1
}

Write-Log "Fichiers à copier: $($filesToDeploy.Count)" "INFO"

# Afficher quelques fichiers de debug
$filesToDeploy | Select-Object -First 5 | ForEach-Object {
    Write-Log "DEBUG: À copier: $($_.FullName)" "INFO"
}


$startTime = Get-Date
$copyCount = 0
$errorCount = 0

# Vérifier que le répertoire destination existe
if (!(Test-Path $LocalPath)) {
    Write-Log "Création du répertoire de destination: $LocalPath" "INFO"
    New-Item -ItemType Directory -Path $LocalPath -Force | Out-Null
}

Write-Log "Répertoire destination: $LocalPath (existe: $(Test-Path $LocalPath))" "INFO"

# Vérifier qu'on copie vraiment vers le bon endroit
Write-Log "Vérification: [0]=$($filesToDeploy[0].FullName) copié vers $(Join-Path $LocalPath 'test.txt')" "INFO"

# Créer tous les répertoires nécessaires avant la copie
Write-Host "`n3.1 Création des répertoires..." -ForegroundColor Magenta
$directoriesToCreate = @()
foreach ($file in $filesToDeploy) {
    $relativePath = $file.FullName.Replace("$PluginDir\", "").Replace("\", "/")
    $remoteDir = [System.IO.Path]::GetDirectoryName($relativePath)
    if ($remoteDir) {
        $remoteDir = $remoteDir -replace '\\', '/'
        $segments = $remoteDir -split '/' | Where-Object { $_ }
        $currentPath = ""
        foreach ($segment in $segments) {
            $currentPath += "/$segment"
            if ($directoriesToCreate -notcontains $currentPath) {
                $directoriesToCreate += $currentPath
            }
        }
    }
}

Write-Log "Création de $($directoriesToCreate.Count) répertoire(s)" "INFO"
$dirProgressId = 2
Write-Progress -Id $dirProgressId -Activity "Création répertoires" -Status "Initialisation..." -PercentComplete 0
$dirCompleted = 0
$dirErrors = 0
foreach ($dir in $directoriesToCreate) {
    $dirPercent = [math]::Round(($dirCompleted / $directoriesToCreate.Count) * 100)
    Write-Progress -Id $dirProgressId -Activity "Création répertoires" -Status "$dir" -PercentComplete $dirPercent
    Write-Log "Création répertoire: $dir" "INFO"
    $dirWindows = $dir.TrimStart('/').Replace('/', '\')
    $localDir = Join-Path $LocalPath $dirWindows
    $result = New-LocalDirectory $localDir
    if (-not $result) {
        $dirErrors++
        Write-Host "❌ Erreur création répertoire: $localDir" -ForegroundColor Red
    }
    $dirCompleted++
}
Write-Progress -Id $dirProgressId -Activity "Création répertoires" -Completed

if ($dirErrors -gt 0) {
    Write-Host "❌ $dirErrors erreur(s) lors de la création des répertoires" -ForegroundColor Red
    Write-Log "Création des répertoires: $dirErrors erreur(s)" "ERROR"
} else {
    Write-Host "   ✅ Répertoires créés" -ForegroundColor Green
}

# Copie avec parallélisation
Write-Host "`n3.2 Copie des fichiers..." -ForegroundColor Magenta
$copyProgressId = 3
Write-Progress -Id $copyProgressId -Activity "Copie locale" -Status "Initialisation..." -PercentComplete 0
$completed = 0
$copyStartTime = Get-Date

foreach ($file in $filesToDeploy) {
    $relativePath = $file.FullName.Replace("$PluginDir\", "").Replace("\", "/")
    $percentComplete = [math]::Round(($completed / $filesToDeploy.Count) * 100)
    $elapsed = (Get-Date) - $copyStartTime
    $speed = if ($elapsed.TotalSeconds -gt 0) { [math]::Round($completed / $elapsed.TotalSeconds, 2) } else { 0 }
    Write-Progress -Id $copyProgressId -Activity "Copie locale" -Status "$relativePath ($speed fichiers/s)" -PercentComplete $percentComplete

    if ($DryRun) {
        Write-Log "SIMULATION: $relativePath" "INFO"
        $copyCount++
        $completed++
        continue
    }

    try {
        $sourceFile = $file.FullName
        # Convertir les slashes forward en backslashes pour Windows
        $relativePathWindows = $relativePath.Replace("/", "\")
        $localFile = Join-Path $LocalPath $relativePathWindows
        
        # S'assurer que le répertoire parent existe avec l'API .NET directement
        $parentDir = [System.IO.Path]::GetDirectoryName($localFile)
        if ($parentDir) {
            [System.IO.Directory]::CreateDirectory($parentDir) | Out-Null
        }
        
        # Utiliser l'API .NET directement pour la copie
        [System.IO.File]::Copy($sourceFile, $localFile, $true)
        Write-Log "Copie réussie: $relativePath" "SUCCESS"
        $copyCount++
    } catch {
        Write-Host "❌ Erreur copie $relativePath : $($_.Exception.Message)" -ForegroundColor Red
        Write-Log "Erreur copie $relativePath : $($_.Exception.Message)" "ERROR"
        $errorCount++
    }
    $completed++
}

Write-Progress -Id $copyProgressId -Activity "Copie locale" -Completed

$duration = [math]::Round(((Get-Date) - $startTime).TotalSeconds, 1)
$speed = if ($duration -gt 0) { [math]::Round($copyCount / $duration, 2) } else { 0 }
Write-Host "`n📊 RÉSUMÉ:" -ForegroundColor Cyan
Write-Host "   ✅ $copyCount copie(s) réussie(s)" -ForegroundColor Green
Write-Host "   ❌ $errorCount erreur(s)" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })
Write-Host "   ⏱️  Durée: $duration s" -ForegroundColor Yellow
Write-Host "   🚀 Vitesse: $speed fichiers/s" -ForegroundColor Yellow

if ($errorCount -gt 0) {
    Write-Log "Déploiement terminé avec $errorCount erreur(s)" "WARN"
    if (!$DryRun) { exit 1 }
} else {
    Write-Log "Déploiement réussi" "SUCCESS"
}

# 4 VÉRIFICATION POST-DÉPLOIEMENT
Write-Host "`n4 Vérification post-déploiement..." -ForegroundColor Magenta
Write-Log "Vérification de l'intégrité des fichiers déployés" "INFO"

$criticalFiles = @(
    "src/Core/PDF_Builder_Unified_Ajax_Handler.php",
    "pdf-builder-pro.php",
    "src/Core/core/autoloader.php"
)

$integrityErrors = 0
$integrityWarnings = 0
foreach ($criticalFile in $criticalFiles) {
    $result = Test-DeployedFileIntegrity $criticalFile
    if ($result -eq $false) {
        $integrityErrors++
        Write-Log "ÉCHEC intégrité: $criticalFile" "ERROR"
    }
}

if ($integrityErrors -gt 0) {
    Write-Log "ÉCHEC: $integrityErrors fichier(s) critique(s) défaillant(s)" "ERROR"
    Write-Host "`n❌ INTÉGRITÉ COMPROMISE - Redéploiement recommandé" -ForegroundColor Red
    if (!$DryRun) { exit 1 }
} else {
    Write-Log "Intégrité des fichiers critiques vérifiée" "SUCCESS"
    Write-Host "`n✅ INTÉGRITÉ VÉRIFIÉE" -ForegroundColor Green
}

# NETTOYAGE
if ($Clean -and !$DryRun) {
    Write-Host "`n5 Nettoyage..." -ForegroundColor Magenta
    Write-Log "Début du nettoyage" "INFO"

    $oldFiles = @(
        "src/backend/core/Core/PDF_Builder_Nonce_Manager.php",
        "src/backend/core/Core/PDF_Builder_Performance_Monitor.php",
        "src/backend/core/Core/PDF_Builder_Unified_Ajax_Handler.php",
        "src/backend/core/Core/core/PdfBuilderAutoloader.php"
    )
    foreach ($file in $oldFiles) {
        $localFile = Join-Path $LocalPath $file
        if (Test-Path $localFile) {
            if (Remove-LocalFile $localFile) {
                Write-Log "Fichier obsolète supprimé: $file" "INFO"
            }
        }
    }

    Write-Log "Nettoyage terminé" "SUCCESS"
}

# COMMIT GIT
if (!$DryRun) {
    Write-Host "`n6 Commit Git..." -ForegroundColor Magenta
    Write-Log "Vérification des changements Git" "INFO"

    Push-Location $WorkingDir
    try {
        $status = & git status --porcelain
        if ($status) {
            $message = "deploy: $(Get-Date -Format 'dd/MM/yyyy HH:mm') - $($filesToDeploy.Count) fichiers (local)"
            if ($All) { $message += " (complet)" }
            & git commit -m $message
            $currentBranch = & git branch --show-current
            
            # Pull avant push pour éviter les rejets
            Write-Log "Synchronisation avec la branche distante..." "INFO"
            & git pull --rebase origin $currentBranch
            
            & git push origin $currentBranch
            Write-Log "Commit et push Git réussis" "SUCCESS"
        } else {
            Write-Log "Aucun changement à committer" "INFO"
        }
    } catch {
        Write-Log "Erreur Git: $($_.Exception.Message)" "ERROR"
    } finally {
        Pop-Location
    }
}

Write-Host "`n🎉 DÉPLOIEMENT LOCAL TERMINÉ !" -ForegroundColor Green
Write-Log "Fin du déploiement local" "INFO"

