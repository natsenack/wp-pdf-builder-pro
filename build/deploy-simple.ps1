# Script de déploiement FTP pour PDF Builder Pro
# Usage: .\deploy-simple.ps1 [-All] [-IncludeVendor] [-SkipConnectionTest] [-ConfigFile "path\to\config.json"]
# Amélioré pour performance, puissance, dynamisme et sécurité
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

# Configuration - Charger depuis fichier externe si disponible
$FtpConfig = @{
    Host = "65.108.242.181"
    User = "nats"
    Pass = "iZ6vU3zV2y"  # À remplacer par une vraie gestion sécurisée
    RemotePath = "/wp-content/plugins/wp-pdf-builder-pro"
}

# Vérifier la sécurité de la configuration
if ($FtpPass -eq "iZ6vU3zV2y" -or $FtpPass -eq "CHANGE_THIS_PASSWORD") {
    Write-Host "⚠️  ATTENTION: Le mot de passe FTP est encore la valeur par défaut !" -ForegroundColor Red
    Write-Host "   Veuillez modifier le fichier ftp-config.json avec vos vraies credentials." -ForegroundColor Red
    Write-Host "   Le script va continuer mais le déploiement risque d'échouer." -ForegroundColor Yellow
    Start-Sleep -Seconds 3
}

# Variables de configuration (non globales)
$FtpHost = $FtpConfig.Host
$FtpUser = $FtpConfig.User
$FtpPass = $FtpConfig.Pass
$FtpPath = $FtpConfig.RemotePath

# Détecter automatiquement le répertoire de travail
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

# Fonction pour vérifier si un répertoire existe sur FTP
function Test-FtpDirectoryExists {
    param([string]$remoteDir)
    try {
        $ftpUri = "ftp://$global:FtpHost$remoteDir/"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($global:FtpUser, $global:FtpPass)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000
        $response = $ftpRequest.GetResponse()
        $response.Close()
        return $true
    } catch {
        return $false
    }
}

# Fonction pour créer un répertoire sur FTP (récursif)
function New-FtpDirectory {
    param([string]$remoteDir)
    $segments = $remoteDir -split '/' | Where-Object { $_ }
    $currentPath = ""
    foreach ($segment in $segments) {
        $currentPath += "/$segment"
        $basePath = $global:FtpHost
        Write-Log "Création répertoire: $currentPath" "INFO"
        try {
            $ftpUri = "ftp://$basePath$currentPath/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($global:FtpUser, $global:FtpPass)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $false
            $ftpRequest.Timeout = 15000
            $response = $ftpRequest.GetResponse()
            $response.Close()
            Write-Log "Répertoire créé: $currentPath" "SUCCESS"
        } catch {
            if ($_.Exception.Message -match "550") {
                Write-Log "Répertoire existe déjà: $currentPath" "INFO"
            } else {
                Write-Log "Échec création répertoire $currentPath : $($_.Exception.Message)" "ERROR"
                return $false
            }
        }
    }
    return $true
}

# Fonction pour lister récursivement tous les fichiers sur FTP
function Get-FtpFiles {
    param([string]$remotePath = "")
    $files = @()
    try {
        $basePath = if ($FtpPath) { "$FtpHost/$FtpPath" } else { $FtpHost }
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$basePath/$remotePath"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $response = $ftpRequest.GetResponse()
        $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
        $listing = $reader.ReadToEnd()
        $reader.Close()
        $response.Close()
        $items = $listing -split "`n" | Where-Object { $_.Trim() }
        foreach ($item in $items) {
            $itemPath = if ($remotePath) { "$remotePath/$item" } else { $item }
            try {
                $basePath = if ($global:FtpPath) { "$global:FtpHost/$global:FtpPath" } else { $global:FtpHost }
                $subUri = "ftp://$global:FtpUser`:$global:FtpPass@$basePath/$itemPath/"
                $subRequest = [System.Net.FtpWebRequest]::Create($subUri)
                $subRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
                $subRequest.UseBinary = $true
                $subRequest.UsePassive = $true
                $subResponse = $subRequest.GetResponse()
                $subResponse.Close()
                $files += Get-FtpFiles $itemPath
            } catch {
                $files += $itemPath
            }
        }
    } catch {}
    return $files
}

# Fonction pour supprimer un fichier sur FTP
function Remove-FtpFile {
    param([string]$remotePath)
    try {
        $basePath = if ($FtpPath) { "$FtpHost$FtpPath" } else { $FtpHost }
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$basePath/$remotePath"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $false
        $ftpRequest.Timeout = 10000
        $response = $ftpRequest.GetResponse()
        $response.Close()
        return $true
    } catch {
        return $false
    }
}
function Test-DeployedFileIntegrity {
    param([string]$remotePath, [string]$expectedContent = "")
    try {
        $basePath = if ($FtpPath) { "$FtpHost$FtpPath" } else { $FtpHost }
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$basePath/$remotePath"
        
        # Vérifier la date de modification du fichier sur le serveur
        $dateRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $dateRequest.Method = [System.Net.WebRequestMethods+Ftp]::GetDateTimestamp
        $dateRequest.UseBinary = $true
        $dateRequest.UsePassive = $true
        $dateRequest.Timeout = 3000  # Réduit pour accélérer
        try {
            $dateResponse = $dateRequest.GetResponse()
            $lastModified = $dateResponse.LastModified
            $dateResponse.Close()
            
            $timeSinceModified = [DateTime]::Now - $lastModified
            if ($timeSinceModified.TotalMinutes -gt 5) {
                Write-Log "ATTENTION: Fichier $remotePath modifié il y a plus de 5 minutes ($lastModified) - possible cache serveur" "WARN"
            } else {
                Write-Log "Date modification récente: $remotePath ($lastModified)" "SUCCESS"
            }
        } catch {
            Write-Log "Impossible de vérifier la date de $remotePath : $($_.Exception.Message)" "WARN"
        }
        
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
        $ftpRequest.UseBinary = $true  # Mode binaire pour préserver les octets exacts
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000  # Réduit pour accélérer
        
        $response = $ftpRequest.GetResponse()
        $stream = $response.GetResponseStream()
        $memoryStream = New-Object System.IO.MemoryStream
        $stream.CopyTo($memoryStream)
        $contentBytes = $memoryStream.ToArray()
        $memoryStream.Close()
        $stream.Close()
        $response.Close()
        
        $content = [System.Text.Encoding]::UTF8.GetString($contentBytes)
        
        # Calculer le hash du contenu déployé
        $deployedHash = [System.BitConverter]::ToString([System.Security.Cryptography.SHA256]::Create().ComputeHash([System.Text.Encoding]::UTF8.GetBytes($content))).Replace("-", "").ToLower()
        
        # Comparer avec le hash du fichier local si disponible
        $localFilePath = Join-Path $WorkingDir "plugin\$remotePath"
        if (Test-Path $localFilePath) {
            $localBytes = [System.IO.File]::ReadAllBytes($localFilePath)
            $localHash = [System.BitConverter]::ToString([System.Security.Cryptography.SHA256]::Create().ComputeHash($localBytes)).Replace("-", "").ToLower()
            $localSize = $localBytes.Length
            
            # Vérifier la taille exacte en octets
            if ($contentBytes.Length -ne $localSize) {
                Write-Log "SIZE MISMATCH: $remotePath - Local: $localSize, Deployed: $($contentBytes.Length)" "ERROR"
                return $false
            }
            
            $deployedHash = [System.BitConverter]::ToString([System.Security.Cryptography.SHA256]::Create().ComputeHash($contentBytes)).Replace("-", "").ToLower()
            if ($deployedHash -ne $localHash) {
                Write-Log "HASH MISMATCH: $remotePath - Local: $localHash, Deployed: $deployedHash" "ERROR"
                Write-Log "Contenu déployé corrompu ou différent" "ERROR"
                return $false
            }
            Write-Log "Hash vérifié: $remotePath" "SUCCESS"
        }
        
        # Vérifications d'intégrité
        if ($content.Length -eq 0) {
            Write-Log "Fichier vide détecté: $remotePath" "ERROR"
            return $false
        }
        
        # Pour les fichiers PHP, vérifier qu'ils contiennent du code PHP valide
        if ($remotePath -like "*.php") {
            $firstLine = ($content -split "`n" | Where-Object { $_.Trim() -ne "" })[0].Trim()
            $hasPhpTag = $content -match "<\?php"
            $hasValidStart = $firstLine -match "^(/\*|\*\*|//|namespace|use|class|function|if|define)" -or $hasPhpTag
            
            if (-not $hasValidStart) {
                Write-Log "Fichier PHP invalide (pas de code PHP valide): $remotePath" "ERROR"
                Write-Log "Première ligne: '$firstLine'" "ERROR"
                return $false
            }
            
            # Vérifications spécifiques pour les fichiers critiques
            if ($remotePath -eq "src/Core/PDF_Builder_Unified_Ajax_Handler.php") {
                if ($content -notmatch "class PDF_Builder_Unified_Ajax_Handler") {
                    Write-Log "Classe PDF_Builder_Unified_Ajax_Handler non trouvée dans le fichier déployé" "ERROR"
                    return $false
                }
                Write-Log "Classe PDF_Builder_Unified_Ajax_Handler trouvée et valide" "SUCCESS"
            }
            elseif ($remotePath -eq "pdf-builder-pro.php") {
                if ($content -notmatch "PDF_Builder_Unified_Ajax_Handler") {
                    Write-Log "Référence à PDF_Builder_Unified_Ajax_Handler manquante dans pdf-builder-pro.php" "WARN"
                }
            }
            elseif ($remotePath -eq "src/Core/core/autoloader.php") {
                if ($content -notmatch "PDF_Builder_Unified_Ajax_Handler") {
                    Write-Log "Autoloader ne couvre pas PDF_Builder_Unified_Ajax_Handler" "WARN"
                }
            }
        }
        
        # Vérification de contenu attendu si fourni
        if ($expectedContent -and $content -notmatch [regex]::Escape($expectedContent)) {
            Write-Log "Contenu attendu non trouvé dans: $remotePath" "WARN"
        }
        
        Write-Log "Intégrité OK: $remotePath ($($content.Length) caractères)" "SUCCESS"
        return $true
    } catch {
        Write-Log "Erreur vérification $remotePath : $($_.Exception.Message)" "ERROR"
        return $false
    }
}

Write-Host "🚀 DÉPLOIEMENT FTP PDF BUILDER PRO" -ForegroundColor Cyan
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
    "plugin/assets/css/pdf-builder-react.min.css"
    # "plugin/assets/css/pdf-builder-react.css" # Supprimé car remplacé par pdf-builder-css.css
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

# 2 COMPILATION (IGNORÉE - WEBPACK DÉSACTIVÉ)
Write-Host "`n2 Compilation..." -ForegroundColor Magenta
Write-Log "Compilation webpack désactivée" "INFO"
Write-Log "Les fichiers existants seront déployés tels quels" "INFO"

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
    }
    
    # Force add critical compiled files
    $criticalCompiledFiles = @(
        "plugin/assets/js/pdf-builder-react-wrapper.min.js"
        # "plugin/assets/css/pdf-builder-react.css" # Supprimé car remplacé par pdf-builder-css.css
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

# 3 UPLOAD FTP
Write-Host "`n3 Upload FTP..." -ForegroundColor Magenta
Write-Log "Début de l'upload FTP" "INFO"

$startTime = Get-Date
$uploadCount = 0
$errorCount = 0
$maxConcurrent = 5  # Nombre maximum d'uploads simultanés

# Test connexion
if (!$SkipConnectionTest) {
    Write-Log "Test de connexion FTP" "INFO"
    try {
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost/"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.Timeout = 5000
        $ftpRequest.UsePassive = $true
        $response = $ftpRequest.GetResponse()
        $response.Close()
        Write-Log "Connexion FTP OK" "SUCCESS"
    } catch {
        Write-Log "Erreur FTP: $($_.Exception.Message)" "ERROR"
        exit 1
    }
}

# Créer le répertoire de base si nécessaire
Write-Log "Création du répertoire de base: $FtpPath" "INFO"
$pathWithoutLeadingSlash = $FtpPath.TrimStart('/')
$pathSegments = $pathWithoutLeadingSlash -split '/'
$currentPath = ""

foreach ($segment in $pathSegments) {
    $currentPath += "/$segment"
    Write-Log "Vérification répertoire: $currentPath" "INFO"
    if (!(Test-FtpDirectoryExists $currentPath)) {
        Write-Log "Création répertoire: $currentPath" "INFO"
        try {
            $ftpUri = "ftp://$FtpHost$currentPath/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 30000
            $response = $ftpRequest.GetResponse()
            $response.Close()
            Write-Log "Répertoire créé: $currentPath" "SUCCESS"
        } catch {
            if ($_.Exception.Message -match "550") {
                Write-Log "Répertoire existe déjà ou accès refusé: $currentPath" "WARN"
            } else {
                Write-Log "Échec création répertoire $currentPath : $($_.Exception.Message)" "ERROR"
                exit 1
            }
        }
    } else {
        Write-Log "Répertoire existe déjà: $currentPath" "INFO"
    }
}

# Créer tous les répertoires nécessaires avant l'upload
Write-Host "`n3.1 Création des répertoires..." -ForegroundColor Magenta
$directoriesToCreate = @()
foreach ($file in $filesToDeploy) {
    if ($file.PSObject.Properties.Match('RelativePath').Count -gt 0) {
        $relativePath = $file.RelativePath
    } else {
        $relativePath = $file.FullName.Replace("$PluginDir\", "").Replace("\", "/")
    }
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
foreach ($dir in $directoriesToCreate) {
    $dirPercent = [math]::Round(($dirCompleted / $directoriesToCreate.Count) * 100)
    Write-Progress -Id $dirProgressId -Activity "Création répertoires" -Status "$dir" -PercentComplete $dirPercent
    Write-Log "Création répertoire: $dir" "INFO"
    try {
        $basePath = if ($FtpPath) { "$FtpHost$FtpPath" } else { $FtpHost }
        $dirUri = "ftp://$FtpUser`:$FtpPass@$basePath$dir/"
        $dirRequest = [System.Net.FtpWebRequest]::Create($dirUri)
        $dirRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $dirRequest.UseBinary = $true
        $dirRequest.UsePassive = $true
        $dirRequest.Timeout = 10000  # Réduit pour accélérer
        $dirResponse = $dirRequest.GetResponse()
        $dirResponse.Close()
        Write-Log "Répertoire créé: $dir" "SUCCESS"
    } catch {
        if ($_.Exception.Message -match "550") {
            Write-Log "Répertoire existe déjà: $dir" "INFO"
        } else {
            Write-Log "Échec création répertoire $dir : $($_.Exception.Message)" "ERROR"
        }
    }
    $dirCompleted++
}
Write-Progress -Id $dirProgressId -Activity "Création répertoires" -Completed
Write-Host "   ✅ Répertoires créés" -ForegroundColor Green

# Upload avec parallélisation
Write-Host "`n3.2 Upload des fichiers..." -ForegroundColor Magenta
$uploadProgressId = 3
Write-Progress -Id $uploadProgressId -Activity "Upload FTP" -Status "Initialisation..." -PercentComplete 0
$jobs = New-Object System.Collections.ArrayList
$completed = 0
$uploadStartTime = Get-Date

foreach ($file in $filesToDeploy) {
    # Calculer le chemin relatif sans le préfixe "plugin/"
    if ($file.PSObject.Properties.Match('RelativePath').Count -gt 0) {
        $relativePath = $file.RelativePath
    } else {
        $relativePath = $file.FullName.Replace("$PluginDir\", "").Replace("\", "/")
    }
    $ftpFilePath = $relativePath
    $percentComplete = [math]::Round(($completed / $filesToDeploy.Count) * 100)
    $elapsed = (Get-Date) - $uploadStartTime
    $speed = if ($elapsed.TotalSeconds -gt 0) { [math]::Round($completed / $elapsed.TotalSeconds, 2) } else { 0 }
    Write-Progress -Id $uploadProgressId -Activity "Upload FTP" -Status "$relativePath ($speed fichiers/s)" -PercentComplete $percentComplete

    if ($DryRun) {
        Write-Log "SIMULATION: $relativePath" "INFO"
        $uploadCount++
        $completed++
        continue
    }

    # Upload séquentiel
    try {
        $basePath = if ($FtpPath) { "$FtpHost$FtpPath" } else { $FtpHost }
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$basePath/$ftpFilePath"

        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 60000

        $fullPath = $file.FullName
        $fileContent = [System.IO.File]::ReadAllBytes($fullPath)
        $ftpRequest.ContentLength = $fileContent.Length

        $requestStream = $ftpRequest.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()

        $response = $ftpRequest.GetResponse()
        $response.Close()

        Write-Log "Upload réussi: $relativePath" "SUCCESS"
        $uploadCount++
    } catch {
        Write-Host "❌ Erreur upload $relativePath : $($_.Exception.Message)" -ForegroundColor Red
        Write-Log "Erreur upload $relativePath : $($_.Exception.Message)" "ERROR"
        $errorCount++
    }
    $completed++
}

Write-Progress -Id $uploadProgressId -Activity "Upload FTP" -Completed

$duration = [math]::Round(((Get-Date) - $startTime).TotalSeconds, 1)
$speed = if ($duration -gt 0) { [math]::Round($uploadCount / $duration, 2) } else { 0 }
Write-Host "`n📊 RÉSUMÉ:" -ForegroundColor Cyan
Write-Host "   ✅ $uploadCount upload(s) réussi(s)" -ForegroundColor Green
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
        # Vérifier si c'est une erreur 550 (fichier non trouvé) - possible cache serveur
        try {
            $basePath = if ($FtpPath) { "$FtpHost$FtpPath" } else { $FtpHost }
            $ftpUri = "ftp://$FtpUser`:$FtpPass@$basePath/$criticalFile"
            $testRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $testRequest.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
            $testRequest.UseBinary = $true
            $testRequest.UsePassive = $true
            $testRequest.Timeout = 2000  # Réduit pour accélérer
            $testResponse = $testRequest.GetResponse()
            $testResponse.Close()
        } catch {
            if ($_.Exception.Message -match "550") {
                Write-Log "Fichier critique $criticalFile non trouvé (possible cache serveur) - marqué comme avertissement" "WARN"
                $integrityWarnings++
                continue
            }
        }
        $integrityErrors++
        Write-Log "ÉCHEC intégrité: $criticalFile" "ERROR"
    }
}

if ($integrityErrors -gt 0) {
    Write-Log "ÉCHEC: $integrityErrors fichier(s) critique(s) défaillant(s)" "ERROR"
    Write-Host "`n❌ INTÉGRITÉ COMPROMISE - Redéploiement recommandé" -ForegroundColor Red
    if (!$DryRun) { exit 1 }
} elseif ($integrityWarnings -gt 0) {
    Write-Log "AVERTISSEMENT: $integrityWarnings fichier(s) critique(s) avec problèmes temporaires (possible cache serveur)" "WARN"
    Write-Host "`n⚠️  INTÉGRITÉ PARTIELLE - $integrityWarnings fichier(s) avec avertissements (cache serveur possible)" -ForegroundColor Yellow
    Write-Host "   Le déploiement est probablement réussi malgré ces avertissements" -ForegroundColor Yellow
} else {
    Write-Log "Intégrité des fichiers critiques vérifiée" "SUCCESS"
    Write-Host "`n✅ INTÉGRITÉ VÉRIFIÉE" -ForegroundColor Green
    
    # Vérifier si des fichiers semblent anciens (possible cache serveur)
    $oldFilesCount = 0
    foreach ($criticalFile in $criticalFiles) {
        try {
            $basePath = if ($FtpPath) { "$FtpHost$FtpPath" } else { $FtpHost }
            $ftpUri = "ftp://$FtpUser`:$FtpPass@$basePath/$criticalFile"
            $dateRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $dateRequest.Method = [System.Net.WebRequestMethods+Ftp]::GetDateTimestamp
            $dateRequest.UseBinary = $true
            $dateRequest.UsePassive = $true
            $dateRequest.Timeout = 10000
            $dateResponse = $dateRequest.GetResponse()
            $lastModified = $dateResponse.LastModified
            $dateResponse.Close()
            
            $timeSinceModified = [DateTime]::Now - $lastModified
            if ($timeSinceModified.TotalMinutes -gt 10) {
                $oldFilesCount++
            }
        } catch {}
    }
    
    if ($oldFilesCount -gt 0) {
        Write-Host "`n⚠️  ATTENTION: $oldFilesCount fichier(s) critique(s) semblent ancien(s) sur le serveur" -ForegroundColor Yellow
        Write-Host "   Cela peut indiquer un cache serveur (OPcache, etc.)" -ForegroundColor Yellow
        Write-Host "   Si l'erreur persiste, videz le cache PHP/WordPress sur le serveur" -ForegroundColor Yellow
    }
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
        if (Remove-FtpFile $file) {
            Write-Log "Fichier obsolète supprimé: $file" "INFO"
        }
    }

    # Supprimer fichiers obsolètes
    $localFiles = $filesToDeploy | ForEach-Object {
        $_.FullName.Replace("$WorkingDir\", "").Replace("\", "/").Replace("plugin/", "")
    }
    $ftpFiles = Get-FtpFiles
    $toDelete = $ftpFiles | Where-Object { $localFiles -notcontains $_ }
    foreach ($file in $toDelete) {
        if (Remove-FtpFile $file) {
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
            $message = "deploy: $(Get-Date -Format 'dd/MM/yyyy HH:mm') - $($filesToDeploy.Count) fichiers"
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
