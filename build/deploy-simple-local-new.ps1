# Script de déploiement local pour PDF Builder Pro
# Reproduit deploy-simple.ps1 pour déploiement local
# domaine : threeaxe.fr
#le script ne doit pas etre modifier sans la permission de l'utilisateur

param(
    [switch]$All,
    [switch]$SkipConnectionTest,
    [switch]$IncludeVendor,
    [string]$ConfigFile = "ftp-config.json"
)

# Paramètres par défaut pour les options supprimées
$Clean = $false
$Verbose = $false
$DryRun = $false

$ErrorActionPreference = "Stop"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# Configuration pour déploiement local
$DestDir = "D:\site\wp\wp-content\plugins\pdf-builder-pro"

# Vérifier la sécurité de la configuration (non applicable pour local)

# Variables de configuration
$WorkingDir = Split-Path $PSScriptRoot -Parent

# Vérifier que le répertoire de travail est valide
if (!(Test-Path (Join-Path $WorkingDir "plugin"))) {
    Write-Host "❌ Répertoire de travail invalide: $WorkingDir" -ForegroundColor Red
    Write-Host "   Le script doit être exécuté depuis le dossier build/ du projet." -ForegroundColor Red
    exit 1
}

$PluginDir = Join-Path $WorkingDir "plugin"
$LogFile = Join-Path $PSScriptRoot "deployment.log"

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
    return Test-Path $localDir
}

# Fonction pour créer un répertoire localement
function New-LocalDirectory {
    param([string]$localDir)
    if (!(Test-Path $localDir)) {
        New-Item -ItemType Directory -Path $localDir -Force | Out-Null
        Write-Log "Répertoire créé: $localDir" "SUCCESS"
    } else {
        Write-Log "Répertoire existe déjà: $localDir" "INFO"
    }
}

# Fonction pour lister récursivement tous les fichiers localement
function Get-LocalFiles {
    param([string]$localPath = "")
    $files = @()
    $fullPath = if ($localPath) { Join-Path $DestDir $localPath } else { $DestDir }
    if (Test-Path $fullPath) {
        Get-ChildItem -Path $fullPath -Recurse -File | ForEach-Object {
            $relativePath = $_.FullName.Replace("$DestDir\", "").Replace("\", "/")
            $files += $relativePath
        }
    }
    return $files
}

# Fonction pour supprimer un fichier localement
function Remove-LocalFile {
    param([string]$localPath)
    $fullPath = Join-Path $DestDir $localPath
    if (Test-Path $fullPath) {
        Remove-Item -Path $fullPath -Force
        return $true
    }
    return $false
}

# Fonction pour vérifier l'intégrité d'un fichier déployé localement
function Test-DeployedFileIntegrity {
    param([string]$localPath, [string]$expectedContent = "")
    try {
        $fullPath = Join-Path $DestDir $localPath
        if (!(Test-Path $fullPath)) {
            Write-Log "Fichier non trouvé: $localPath" "ERROR"
            return $false
        }

        $content = Get-Content -Path $fullPath -Raw -Encoding UTF8

        # Calculer le hash du contenu déployé
        $deployedHash = [System.BitConverter]::ToString([System.Security.Cryptography.SHA256]::Create().ComputeHash([System.Text.Encoding]::UTF8.GetBytes($content))).Replace("-", "").ToLower()

        # Comparer avec le hash du fichier local si disponible
        $localFilePath = Join-Path $WorkingDir "plugin\$localPath"
        if (Test-Path $localFilePath) {
            $localBytes = [System.IO.File]::ReadAllBytes($localFilePath)
            $localHash = [System.BitConverter]::ToString([System.Security.Cryptography.SHA256]::Create().ComputeHash($localBytes)).Replace("-", "").ToLower()
            $localSize = $localBytes.Length

            # Vérifier la taille exacte en octets
            $contentBytes = [System.Text.Encoding]::UTF8.GetBytes($content)
            if ($contentBytes.Length -ne $localSize) {
                Write-Log "SIZE MISMATCH: $localPath - Local: $localSize, Deployed: $($contentBytes.Length)" "ERROR"
                return $false
            }

            if ($deployedHash -ne $localHash) {
                Write-Log "HASH MISMATCH: $localPath - Local: $localHash, Deployed: $deployedHash" "ERROR"
                Write-Log "Contenu déployé corrompu ou différent" "ERROR"
                return $false
            }
            Write-Log "Hash vérifié: $localPath" "SUCCESS"
        }

        # Vérifications d'intégrité
        if ($content.Length -eq 0) {
            Write-Log "Fichier vide détecté: $localPath" "ERROR"
            return $false
        }

        # Pour les fichiers PHP, vérifier qu'ils contiennent du code PHP valide
        if ($localPath -like "*.php") {
            $firstLine = ($content -split "`n" | Where-Object { $_.Trim() -ne "" })[0].Trim()
            $hasPhpTag = $content -match "<\?php"
            $hasValidStart = $firstLine -match "^(/\*|\*\*|//|namespace|use|class|function|if|define)" -or $hasPhpTag

            if (-not $hasValidStart) {
                Write-Log "Fichier PHP invalide (pas de code PHP valide): $localPath" "ERROR"
                Write-Log "Première ligne: '$firstLine'" "ERROR"
                return $false
            }

            # Vérifications spécifiques pour les fichiers critiques
            if ($localPath -eq "src/Core/PDF_Builder_Unified_Ajax_Handler.php") {
                if ($content -notmatch "class PDF_Builder_Unified_Ajax_Handler") {
                    Write-Log "Classe PDF_Builder_Unified_Ajax_Handler non trouvée dans le fichier déployé" "ERROR"
                    return $false
                }
                Write-Log "Classe PDF_Builder_Unified_Ajax_Handler trouvée et valide" "SUCCESS"
            }
            elseif ($localPath -eq "pdf-builder-pro.php") {
                if ($content -notmatch "PDF_Builder_Unified_Ajax_Handler") {
                    Write-Log "Référence à PDF_Builder_Unified_Ajax_Handler manquante dans pdf-builder-pro.php" "WARN"
                }
            }
            elseif ($localPath -eq "src/Core/core/autoloader.php") {
                if ($content -notmatch "PDF_Builder_Unified_Ajax_Handler") {
                    Write-Log "Autoloader ne couvre pas PDF_Builder_Unified_Ajax_Handler" "WARN"
                }
            }
        }

        # Vérification de contenu attendu si fourni
        if ($expectedContent -and $content -notmatch [regex]::Escape($expectedContent)) {
            Write-Log "Contenu attendu non trouvé dans: $localPath" "WARN"
        }

        Write-Log "Intégrité OK: $localPath ($($content.Length) caractères)" "SUCCESS"
        return $true
    } catch {
        Write-Log "Erreur vérification $localPath : $($_.Exception.Message)" "ERROR"
        return $false
    }
}

Write-Host "🚀 DÉPLOIEMENT LOCAL PDF BUILDER PRO" -ForegroundColor Cyan
$mode = if ($All) { "COMPLET (-All)" } else { "MODIFIÉ UNIQUEMENT" }
$vendorMode = if ($IncludeVendor) { "AVEC VENDOR" } else { "SANS VENDOR" }
Write-Host "Mode: $mode | $vendorMode" -ForegroundColor Yellow
Write-Host ("=" * 60) -ForegroundColor White
Write-Log "Début du déploiement en mode $mode ($vendorMode)"

# 1 DETECTION DES FICHIERS A DEPLOYER
Write-Host "`n1 Detection des fichiers..." -ForegroundColor Magenta

$filesToDeploy = @()

if ($All) {
    Write-Log "Mode complet: tous les fichiers du plugin" "INFO"
    $exclusions = @('\\\.git\\', 'node_modules', 'tests', 'temp\.js$', 'composer-setup\.php$', 'phpstan\.neon$', '\.log$', '\.tmp$', 'plugin\\resources\\assets\\js\\dist\\plugin\\resources\\assets', '\.ts$', '\.tsx$', '\.map$', '\.md$', 'README', 'config\.ts', 'tsconfig')
    if (-not $IncludeVendor) {
        $exclusions += 'vendor'
    }
    $filesToDeploy = @(Get-ChildItem -Path $PluginDir -Recurse -File | Where-Object {
        $path = $_.FullName
        -not ($exclusions | Where-Object { $path -match $_ })
    })
} else {
    Write-Log "Mode normal: fichiers modifiés" "INFO"
    $modified = @(& git diff --name-only)
    $staged = @(& git diff --cached --name-only)
    $untracked = @(& git ls-files --others --exclude-standard)
    $allFiles = ($modified + $staged + $untracked) | Select-Object -Unique | Where-Object { $_ -like "plugin/*" -and (Test-Path (Join-Path $WorkingDir $_)) }
    $filesToDeploy = @($allFiles | ForEach-Object { Get-Item (Join-Path $WorkingDir $_) })
}

# Compiled files are already included in the main detection

# Always include critical compiled files (force add even if not detected as modified)
$criticalCompiledFiles = @(
    "plugin/assets/js/pdf-builder-react-wrapper.min.js"
    "plugin/assets/js/pdf-builder-react.min.js"
    "plugin/assets/js/react-vendor.min.js"
    "plugin/assets/js/canvas-settings.min.js"
    "plugin/assets/js/pdf-builder-react-init.min.js"
    "plugin/assets/js/ajax-throttle.min.js"
    "plugin/assets/js/notifications.min.js"
    "plugin/assets/js/pdf-builder-wrap.min.js"
    "plugin/assets/js/pdf-builder-init.min.js"
    "plugin/assets/css/pdf-builder-react.min.css"
    "plugin/assets/css/notifications.min.css"
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
    # Vérifier si npm est disponible
    $npmAvailable = Get-Command npm -ErrorAction SilentlyContinue
    if (-not $npmAvailable) {
        Write-Log "npm n'est pas disponible, compilation ignorée" "WARN"
    } else {
        # Vérifier si package.json existe
        if (Test-Path "package.json") {
            Write-Log "Lancement de npm run build" "INFO"
            $buildResult = & npm run build 2>&1
            $buildExitCode = $LASTEXITCODE

            # Afficher la sortie de webpack
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
    # Utiliser git add avec gestion des erreurs d'ignore
    $gitAddResult = & git add . 2>&1
    if ($LASTEXITCODE -ne 0) {
        # Si git add échoue à cause des fichiers ignorés, essayer avec --ignore-errors
        Write-Log "Tentative avec --ignore-errors" "INFO"
        & git add --ignore-errors . 2>$null
    } else {
        # Vérifier s'il y a des vraies erreurs (pas seulement des avertissements)
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

    # Force add critical compiled files
    $criticalCompiledFiles = @(
        "plugin/assets/js/pdf-builder-react-wrapper.min.js"
        "plugin/assets/js/ajax-throttle.min.js"
        "plugin/assets/js/notifications.min.js"
        "plugin/assets/js/pdf-builder-wrap.min.js"
        "plugin/assets/js/pdf-builder-init.min.js"
        "plugin/assets/css/notifications.min.css"
    )
    foreach ($criticalFile in $criticalCompiledFiles) {
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

# 3 COPIE LOCALE
Write-Host "`n3 Copie locale..." -ForegroundColor Magenta
Write-Log "Début de la copie locale" "INFO"

$startTime = Get-Date
$copyCount = 0
$errorCount = 0

# Test connexion au répertoire local
if (!$SkipConnectionTest) {
    Write-Log "Test de connexion au répertoire local" "INFO"
    if (!(Test-Path $DestDir)) {
        Write-Log "Répertoire de destination introuvable: $DestDir" "ERROR"
        exit 1
    }
    Write-Log "Connexion au répertoire local OK" "SUCCESS"
}

# Créer tous les répertoires nécessaires avant la copie
Write-Host "`n3.1 Création des répertoires..." -ForegroundColor Magenta
$directoriesToCreate = @()
foreach ($file in $filesToDeploy) {
    if ($file.PSObject.Properties.Match('RelativePath').Count -gt 0) {
        $relativePath = $file.RelativePath
    } else {
        $relativePath = $file.FullName.Replace("$PluginDir\", "").Replace("\", "/")
    }
    $localDir = [System.IO.Path]::GetDirectoryName($relativePath)
    if ($localDir) {
        $localDir = $localDir -replace '\\', '/'
        $segments = $localDir -split '/' | Where-Object { $_ }
        $currentPath = ""
        foreach ($segment in $segments) {
            $currentPath += "/$segment"
            $fullLocalPath = Join-Path $DestDir $currentPath
            if ($directoriesToCreate -notcontains $fullLocalPath) {
                $directoriesToCreate += $fullLocalPath
            }
        }
    }
}

Write-Log "Création de $($directoriesToCreate.Count) répertoire(s)" "INFO"
$dirProgressId = 2
Write-Progress -Id $dirProgressId -Activity "Création répertoires" -Status "Initialisation..." -PercentComplete 0
$dirCompleted = 0
foreach ($dir in $directoriesToCreate) {
    $dirPercent = [math]::Round(($dirCompleted / $directoriesToCreate.Count) * 100)
    Write-Progress -Id $dirProgressId -Activity "Création répertoires" -Status "$dir" -PercentComplete $dirPercent
    New-LocalDirectory $dir
    $dirCompleted++
}
Write-Progress -Id $dirProgressId -Activity "Création répertoires" -Completed
Write-Host "   ✅ Répertoires créés" -ForegroundColor Green

# Copie des fichiers
Write-Host "`n3.2 Copie des fichiers..." -ForegroundColor Magenta
$copyProgressId = 3
Write-Progress -Id $copyProgressId -Activity "Copie locale" -Status "Initialisation..." -PercentComplete 0
$jobs = New-Object System.Collections.ArrayList
$completed = 0
$copyStartTime = Get-Date

foreach ($file in $filesToDeploy) {
    # Calculer le chemin relatif sans le préfixe "plugin/"
    if ($file.PSObject.Properties.Match('RelativePath').Count -gt 0) {
        $relativePath = $file.RelativePath
    } else {
        $relativePath = $file.FullName.Replace("$PluginDir\", "").Replace("\", "/")
    }
    $localFilePath = $relativePath
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

    # Copie locale
    try {
        $destFilePath = Join-Path $DestDir $localFilePath
        Copy-Item -Path $file.FullName -Destination $destFilePath -Force
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
} elseif ($integrityWarnings -gt 0) {
    Write-Log "AVERTISSEMENT: $integrityWarnings fichier(s) critique(s) avec problèmes temporaires" "WARN"
    Write-Host "`n⚠️  INTÉGRITÉ PARTIELLE - $integrityWarnings fichier(s) avec avertissements" -ForegroundColor Yellow
} else {
    Write-Log "Intégrité des fichiers critiques vérifiée" "SUCCESS"
    Write-Host "`n✅ INTÉGRITÉ VÉRIFIÉE" -ForegroundColor Green
}

# NETTOYAGE
if ($Clean -and !$DryRun) {
    Write-Host "`n5 Nettoyage..." -ForegroundColor Magenta
    Write-Log "Début du nettoyage" "INFO"

    # Supprimer fichiers déplacés connus
    $oldFiles = @(
        "src/backend/core/Core/PDF_Builder_Nonce_Manager.php",
        "src/backend/core/Core/PDF_Builder_Performance_Monitor.php",
        "src/backend/core/Core/PDF_Builder_Unified_Ajax_Handler.php",
        "src/backend/core/Core/core/PdfBuilderAutoloader.php"
    )
    foreach ($file in $oldFiles) {
        if (Remove-LocalFile $file) {
            Write-Log "Fichier obsolète supprimé: $file" "INFO"
        }
    }

    # Supprimer fichiers obsolètes
    $localFiles = $filesToDeploy | ForEach-Object {
        $_.FullName.Replace("$WorkingDir\", "").Replace("\", "/").Replace("plugin/", "")
    }
    $destFiles = Get-LocalFiles
    $toDelete = $destFiles | Where-Object { $localFiles -notcontains $_ }
    foreach ($file in $toDelete) {
        if (Remove-LocalFile $file) {
            Write-Log "Fichier obsolète supprimé: $file" "INFO"
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
            $message = "deploy local: $(Get-Date -Format 'dd/MM/yyyy HH:mm') - $($filesToDeploy.Count) fichiers"
            if ($All) { $message += " (complet)" }
            & git commit -m $message
            $currentBranch = & git branch --show-current
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

Write-Host "`n🎉 DÉPLOIEMENT TERMINÉ !" -ForegroundColor Green
Write-Log "Fin du déploiement" "INFO"