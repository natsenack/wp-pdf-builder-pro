# Script de deploiement simplifie - Envoie UNIQUEMENT les fichiers modifies
# NOTE: Mode 'test' retiré — ce script effectue désormais le déploiement réel FTP par défaut.
#commande possible - a lire absolument
# Usage: .\deploy-simple.ps1
#.\build\deploy-simple.ps1

param(
    [switch]$All,
    [switch]$SkipConnectionTest,
    [switch]$FastMode
)

# Paramètres par défaut pour les options supprimées
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
        $ftpRequest.UsePassive = $false
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

# Fonction pour vérifier l'intégrité d'un fichier déployé
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
        $dateRequest.Timeout = 10000
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
        $ftpRequest.Timeout = 30000
        
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
            elseif ($remotePath -eq "config/autoloader.php") {
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
    }
    catch {
        Write-Host "Erreur lors du filtrage des fichiers: $($_.Exception.Message)" -ForegroundColor Yellow
    }
}    # Toujours inclure les fichiers dist s'ils ont été modifiés récemment (dans les dernières 5 minutes)
    try {
        $distFiles = Get-ChildItem "$WorkingDir\plugin\assets\js\dist\*.js" -ErrorAction SilentlyContinue | Where-Object { $_.LastWriteTime -gt (Get-Date).AddMinutes(-5) } | Select-Object -ExpandProperty FullName
        $distFilesRelative = $distFiles | ForEach-Object { $_.Replace("$WorkingDir\", "").Replace("\", "/") }
        $pluginModified = @($pluginModified) + @($distFilesRelative) | Sort-Object -Unique
    } catch {
        Write-Log "npm non disponible, compilation ignorée" "WARN"
        $npmAvailable = $false
    }

    if ($npmAvailable) {
        Write-Log "Exécution de 'npm run build'" "INFO"
        # Utiliser cmd /c pour mieux gérer stderr
        $buildProcess = Start-Process -FilePath "cmd.exe" -ArgumentList "/c npm run build" -WorkingDirectory $WorkingDir -NoNewWindow -Wait -PassThru -RedirectStandardOutput "build_output.txt" -RedirectStandardError "build_error.txt"
        $buildOutput = Get-Content "build_output.txt" -Raw
        $buildError = Get-Content "build_error.txt" -Raw

        # Nettoyer les fichiers temporaires
        Remove-Item "build_output.txt" -ErrorAction SilentlyContinue
        Remove-Item "build_error.txt" -ErrorAction SilentlyContinue

        $fullOutput = $buildOutput + $buildError

        # Vérifier si la compilation a réussi
        $compilationSuccessful = $fullOutput -match "compiled successfully" -or $buildProcess.ExitCode -eq 0

        if ($compilationSuccessful) {
            Write-Log "Compilation réussie" "SUCCESS"
            # Afficher les avertissements s'il y en a
            if ($fullOutput -match "deoptimised the styling") {
                Write-Log "Avertissements Babel (normaux pour les gros fichiers)" "WARN"
            }
        } else {
            Write-Log "Compilation échouée (code $($buildProcess.ExitCode))" "ERROR"
            Write-Host "Sortie de la compilation:" -ForegroundColor Red
            Write-Host $fullOutput -ForegroundColor Red
            Write-Log "Arrêt du déploiement à cause de l'échec de compilation" "ERROR"
            exit 1
        }
    }
else {
    Write-Log "package.json non trouvé, compilation ignorée" "WARN"
}
Pop-Location

# 2.5 GIT ADD DES FICHIERS MODIFIÉS
Write-Host "`n2.5 Git add..." -ForegroundColor Magenta
Write-Log "Ajout des fichiers modifiés à Git" "INFO"
Push-Location $WorkingDir
try {
    & git add .
    
    # Force add critical compiled files
    $criticalCompiledFiles = @(
        "plugin/assets/js/pdf-builder-react.js",
        "plugin/assets/js/pdf-builder-react.js.map"
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

    # Créer tous les répertoires en parallèle
    Write-Host "   Creation des repertoires..." -ForegroundColor Yellow
    $dirs = @{}
    foreach ($file in $pluginModified) {
        $dir = Split-Path $file -Parent
        if ($dir -and !$dirs.ContainsKey($dir)) {
            $dirs[$dir] = $true
        }
    }

    # Ajouter tous les répertoires parents nécessaires (récursif)
    $allDirs = @{}
    foreach ($dir in $dirs.Keys) {
        $currentDir = $dir
        while ($currentDir -and $currentDir -ne "." -and $currentDir -ne "plugin") {
            if (!$allDirs.ContainsKey($currentDir)) {
                $allDirs[$currentDir] = $true
            }
            $currentDir = Split-Path $currentDir -Parent
        }
    }

    # Fonction pour créer récursivement tous les répertoires nécessaires
    function New-FtpDirectoryRecursive {
        param([string]$ftpPath)

        try {
            # Créer le répertoire directement (FTP gère la récursion automatiquement)
            $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$ftpPath/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 5000  # Augmenté pour la création récursive
            $ftpRequest.KeepAlive = $false
            $response = $ftpRequest.GetResponse()
            $response.Close()
            return $true
        } catch {
            # Le répertoire existe probablement déjà, ou il y a eu une erreur
            return $false
        }
    }

    # Créer tous les répertoires en parallèle avec gestion récursive
    $dirJobs = @()
    foreach ($dir in $allDirs.Keys) {
        # Corriger le calcul du chemin FTP
        if ($dir.StartsWith("plugin/")) {
            $ftpDir = $dir.Substring(7)
        } elseif ($dir.StartsWith("plugin\")) {
            $ftpDir = $dir.Substring(7)
        } else {
            $ftpDir = $dir
        }
        $ftpDir = $ftpDir.Replace("\", "/")
        $fullPath = "$FtpPath/$ftpDir".TrimEnd('/')

        if ($fullPath -ne $FtpPath) {
            $job = Start-Job -ScriptBlock {
                param($ftpHost, $ftpUser, $ftpPass, $fullPath)
                try {
                    # Créer le répertoire récursivement
                    $ftpUri = "ftp://$using:FtpUser`:$using:FtpPass@$using:FtpHost$fullPath/"
                    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                    $ftpRequest.UseBinary = $true
                    $ftpRequest.UsePassive = $true
                    $ftpRequest.Timeout = 5000
                    $ftpRequest.KeepAlive = $false
                    $response = $ftpRequest.GetResponse()
                    $response.Close()
                    return @{ Success = $true; Path = $fullPath }
                } catch {
                    return @{ Success = $false; Path = $fullPath; Error = $_.Exception.Message }
                }
            } -ArgumentList $FtpHost, $FtpUser, $FtpPass, $fullPath
            $dirJobs += $job
        }
    }

    # Attendre la fin de la création des répertoires (max 15 secondes pour la récursion)
    $dirTimeout = 15
    $dirStartTime = Get-Date
    while ($dirJobs.Count -gt 0 -and ((Get-Date) - $dirStartTime).TotalSeconds -lt $dirTimeout) {
        $completedDirJobs = $dirJobs | Where-Object { $_.State -eq 'Completed' }
        foreach ($job in $completedDirJobs) {
            $result = Receive-Job $job
            if ($result.Success) {
                Write-Log "Upload réussi: $($result.RelativePath)" "SUCCESS"
                $uploadCount++
            } else {
                Write-Log "Erreur upload $($result.RelativePath) : $($result.Error)" "ERROR"
                $errorCount++
            }
            Remove-Job $job
            $jobs.Remove($job) | Out-Null
            $completed++
        }
        if ($jobs.Count -ge $maxConcurrent) {
            Start-Sleep -Milliseconds 500
        }
    }

    $filesToDeploy = $pluginModified
    $jobs = @()
    $completed = 0
    $progressId = 1
    foreach ($file in $filesToDeploy) {
        $relativePath = $file
        $ftpFilePath = $file
        $percentComplete = [math]::Round(($completed / $filesToDeploy.Count) * 100)
        Write-Progress -Id $progressId -Activity "Upload FTP" -Status "$relativePath" -PercentComplete $percentComplete

        if ($DryRun) {
            Write-Log "SIMULATION: $relativePath" "INFO"
            $uploadCount++
            $completed++
            continue
        }

        # Démarrer le job d'upload
        $job = Start-Job -ScriptBlock {
            param($file, $ftpFilePath, $ftpHost, $ftpUser, $ftpPass, $ftpPath, $WorkingDir)
            
            try {
                $basePath = if ($ftpPath) { "$ftpHost$ftpPath" } else { $ftpHost }
                $ftpUri = "ftp://$ftpUser`:$ftpPass@$basePath/$ftpFilePath"
                
                # Supprimer le fichier existant
                try {
                    $deleteRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                    $deleteRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
                    $deleteRequest.UseBinary = $true
                    $deleteRequest.UsePassive = $false
                    $deleteRequest.Timeout = 10000
                    $deleteResponse = $deleteRequest.GetResponse()
                    $deleteResponse.Close()
                } catch {}
                
                # Créer le répertoire si nécessaire
                $remoteDir = [System.IO.Path]::GetDirectoryName($ftpFilePath)
                if ($remoteDir) {
                    $remoteDir = $remoteDir -replace '\\', '/'
                    $segments = $remoteDir -split '/' | Where-Object { $_ }
                    $currentPath = ""
                    foreach ($segment in $segments) {
                        $currentPath += "/$segment"
                        try {
                            $dirUri = "ftp://$ftpUser`:$ftpPass@$basePath$currentPath/"
                            $dirRequest = [System.Net.FtpWebRequest]::Create($dirUri)
                            $dirRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                            $dirRequest.UseBinary = $true
                            $dirRequest.UsePassive = $false
                            $dirRequest.Timeout = 15000
                            $dirResponse = $dirRequest.GetResponse()
                            $dirResponse.Close()
                        } catch {}
                    }
                }
                
                # Upload du fichier - Mode binaire pour TOUS les fichiers pour éviter la corruption
                $isBinary = $true  # Toujours utiliser le mode binaire
                $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                $ftpRequest.UseBinary = $isBinary
                $ftpRequest.UsePassive = $false
                $ftpRequest.Timeout = 60000

                $fullPath = "$WorkingDir\$file"
                $fileContent = [System.IO.File]::ReadAllBytes($fullPath)
                $ftpRequest.ContentLength = $fileContent.Length

                $requestStream = $ftpRequest.GetRequestStream()
                $requestStream.Write($fileContent, 0, $fileContent.Length)
                $requestStream.Close()

                $response = $ftpRequest.GetResponse()
                $response.Close()

                return @{ Success = $true; RelativePath = $file; Error = $null }
            } catch {
                return @{ Success = $false; RelativePath = $file; Error = $_.Exception.Message }
            }
        } -ArgumentList $file, $ftpFilePath, $FtpHost, $FtpUser, $FtpPass, $FtpPath, $WorkingDir
        
        $jobs.Add($job) | Out-Null
    }

# Attendre la fin de tous les jobs
while ($jobs.Count -gt 0) {
    $finishedJobs = $jobs | Where-Object { $_.State -eq 'Completed' }
    foreach ($job in $finishedJobs) {
        $result = Receive-Job $job
        if ($result.Success) {
            Write-Log "Upload réussi: $($result.RelativePath)" "SUCCESS"
            $uploadCount++
        } else {
            Write-Log "Erreur upload $($result.RelativePath) : $($result.Error)" "ERROR"
            $errorCount++
        }
        Remove-Job $job
        $completed++
    }
    $jobs = $jobs | Where-Object { $_.State -ne 'Completed' }
    
    $percentComplete = [math]::Round(($completed / $filesToDeploy.Count) * 100)
    Write-Progress -Id $progressId -Activity "Upload FTP" -Status "Finalisation..." -PercentComplete $percentComplete
    
    if ($jobs.Count -gt 0) {
        Start-Sleep -Milliseconds 500
    }
}

Write-Progress -Id $progressId -Activity "Upload FTP" -Completed

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

# 4 GIT COMMIT + PUSH + TAG
Write-Host "`n4 Git commit + push + tag..." -ForegroundColor Magenta

$commitCreated = $false
$pushSuccess = $false

try {
    Push-Location $WorkingDir

    # ✅ CORRECTION: Ajouter TOUS les fichiers modifiés (même s'ils ne sont pas dans $pluginModified)
    Write-Host "   Staging de TOUS les fichiers modifies..." -ForegroundColor Yellow
    $ErrorActionPreference = "Continue"
    cmd /c "cd /d $WorkingDir && git add -A" 2>&1 | Out-Null
    $ErrorActionPreference = "Stop"

    # Vérifier s'il y a des changements à committer
    $statusOutput = cmd /c "cd /d $WorkingDir && git status --porcelain" 2>&1
    $stagedFiles = $statusOutput | Where-Object { $_ -and $_ -match "^[AM]" }
    
    if ($stagedFiles -and $stagedFiles.Count -gt 0) {
        # Afficher les fichiers qui seront committés
        Write-Host "   Fichiers à committer:" -ForegroundColor Cyan
        $stagedFiles | ForEach-Object {
            Write-Host "     $_" -ForegroundColor Gray
        }
        
        # Générer un message de commit intelligent basé sur les fichiers modifiés
        $commitMsg = "Deploy $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
        Write-Host "   Commit: $commitMsg" -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        $commitResult = cmd /c "cd /d $WorkingDir && git commit -m `"$commitMsg`"" 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            Write-Host "   Commit cree" -ForegroundColor Green
            $commitCreated = $true
        } else {
            Write-Host "   Erreur commit: $($commitResult -join ' ')" -ForegroundColor Red
            $commitCreated = $false
        }
    } else {
        Write-Host "   Rien a committer (deja a jour)" -ForegroundColor Gray
        $commitCreated = $false
    }

    # Push seulement si un commit a été créé
    if ($commitCreated) {
        Write-Host "   Push vers remote..." -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        $pushResult = cmd /c "cd /d $WorkingDir && git push origin dev" 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            Write-Host "   Push reussi" -ForegroundColor Green
            $pushSuccess = $true
        } else {
            Write-Host "   Erreur push: $($pushResult -join ' ')" -ForegroundColor Red
            $pushSuccess = $false
        }
    } else {
        Write-Host "   Pas de push (pas de commit)" -ForegroundColor Gray
        $pushSuccess = $true  # Pas d'erreur si pas de commit
    }

    # Tag seulement si push réussi - OPTIONNEL, peut être désactivé pour accélérer
    if ($pushSuccess -and $commitCreated) {
        # ✅ CORRECTION: Utiliser le format de version déployé (comme dans les logs)
        $version = Get-Date -Format "v1.0.0-11eplo25-ddMMyyyy-HHmmss"
        Write-Host "   Tag: $version" -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        $tagResult = cmd /c "cd /d $WorkingDir && git tag -a $version -m `"Deploiement $version`"" 2>&1
        $ErrorActionPreference = "Stop"

        if ($LASTEXITCODE -eq 0) {
            $ErrorActionPreference = "Continue"
            $tagPushResult = cmd /c "cd /d $WorkingDir && git push origin $version" 2>&1
            $ErrorActionPreference = "Stop"

            if ($LASTEXITCODE -eq 0) {
                Write-Host "   Tag cree et pousse" -ForegroundColor Green
            } else {
                Write-Host "   Erreur push tag: $($tagPushResult -join ' ')" -ForegroundColor Red
            }
        } else {
            Write-Host "   Erreur creation tag: $($tagResult -join ' ')" -ForegroundColor Red
        }
    } else {
        Write-Host "   Pas de tag (pas de push ou commit)" -ForegroundColor Gray
    }

    Pop-Location
} catch {
    Write-Host "   Erreur git: $($_.Exception.Message)" -ForegroundColor Red
}

# FIN
Write-Host "`nDEPLOIEMENT TERMINE AVEC SUCCES!" -ForegroundColor Green
Write-Host ("=" * 60) -ForegroundColor White
Write-Host "Resume:" -ForegroundColor Cyan
Write-Host "   Compilation: OK" -ForegroundColor Green

# Afficher le statut FTP selon le mode
Write-Host "   Upload FTP: OK ($uploadCount fichiers)" -ForegroundColor Green

# Afficher le statut Git selon les résultats
if ($commitCreated -and $pushSuccess) {
    Write-Host "   Git: OK (commit + push + tag)" -ForegroundColor Green
} elseif ($commitCreated) {
    Write-Host "   Git: PARTIEL (commit OK, push/tag echoue)" -ForegroundColor Yellow
} else {
    Write-Host "   Git: SKIP (rien a committer)" -ForegroundColor Gray
}
Write-Host ""

# ✅ FINAL GIT PUSH - S'assurer que tout est pousse et clean
Write-Host "5 Final Git Push..." -ForegroundColor Cyan
try {
    Push-Location $WorkingDir
    
    # ✅ CORRECTION: Vérifier qu'il n'y a plus de fichiers non committés
    $ErrorActionPreference = "Continue"
    $finalStatus = cmd /c "cd /d $WorkingDir && git status --porcelain" 2>&1
    $ErrorActionPreference = "Stop"
    
    # Filtrer pour ne montrer que les fichiers modifiés (pas les fichiers non suivis)
    $unstagedFiles = $finalStatus | Where-Object { $_ -match "^ [MADRCU]" }
    
    if ($unstagedFiles -and $unstagedFiles.Count -gt 0) {
        Write-Host "   ⚠️ Fichiers modifies non commits detects:" -ForegroundColor Yellow
        $unstagedFiles | ForEach-Object {
            Write-Host "     $_" -ForegroundColor Gray
        }
        
        # Ajouter et commiter les fichiers restants
        Write-Host "   Commitment des fichiers restants..." -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        cmd /c "cd /d $WorkingDir && git add -A" 2>&1 | Out-Null
        $commitMsg = "chore: Commit final des fichiers restants - $(Get-Date -Format 'dd/MM/yyyy HH:mm:ss')"
        cmd /c "cd /d $WorkingDir && git commit -m `"$commitMsg`"" 2>&1 | Out-Null
        $ErrorActionPreference = "Stop"
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "   ✅ Commit final cree" -ForegroundColor Green
        }
    }
    
    # Pousser tout vers le remote
    $ErrorActionPreference = "Continue"
    $finalPushResult = cmd /c "cd /d $WorkingDir && git push origin dev" 2>&1
    $ErrorActionPreference = "Stop"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✅ Final push vers origin/dev reussi" -ForegroundColor Green
    } else {
        Write-Host "   ⚠️ Final push info: $($finalPushResult -join ' ')" -ForegroundColor Yellow
    }
    
    Pop-Location
} catch {
    Write-Host "   ⚠️ Erreur lors du final push: $($_.Exception.Message)" -ForegroundColor Yellow
}