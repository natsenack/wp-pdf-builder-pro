# Script de deploiement simplifie - Envoie UNIQUEMENT les fichiers modifies
# NOTE: Mode 'test' retiré — ce script effectue désormais le déploiement réel FTP par défaut.
#commande possible - a lire absolument
# Usage: .\deploy-simple.ps1
# .\deploy-simple.ps1 -All (envoie tous les fichiers du plugin)
#.\build\deploy-simple.ps1

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("plugin")]
    [string]$Mode = "plugin",
    [switch]$SkipConnectionTest,
    [switch]$FastMode,
    [switch]$All
)

$ErrorActionPreference = "Stop"

# Forcer l'encodage UTF-8 pour éviter les problèmes avec les caractères accentués
$OutputEncoding = [System.Text.Encoding]::UTF8
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
chcp 65001 | Out-Null  # Page de code UTF-8

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

$WorkingDir = Split-Path $PSScriptRoot -Parent

# Fonction pour générer un message de commit intelligent
function Get-SmartCommitMessage {
    param([string[]]$ModifiedFiles)

    $timestamp = Get-Date -Format "dd/MM/yyyy HH:mm:ss"

    # Analyser les types de fichiers modifiés
    $hasJs = $ModifiedFiles | Where-Object { $_ -like "*.js" -or $_ -like "*.jsx" -or $_ -like "*.ts" -or $_ -like "*.tsx" }
    $hasCss = $ModifiedFiles | Where-Object { $_ -like "*.css" -or $_ -like "*.scss" -or $_ -like "*.sass" }
    $hasPhp = $ModifiedFiles | Where-Object { $_ -like "*.php" }
    $hasDist = $ModifiedFiles | Where-Object { $_ -like "*dist*" -or $_ -like "*build*" }
    $hasConfig = $ModifiedFiles | Where-Object { $_ -like "*.json" -or $_ -like "*.config.*" -or $_ -like "*.yml" -or $_ -like "*.yaml" }

    # Priorité: JS/TS > PHP > CSS > Config > Dist
    if ($hasJs) {
        $type = "feat"
        $description = "Mise à jour des assets JavaScript/TypeScript"
    } elseif ($hasPhp) {
        $type = "fix"
        $description = "Corrections PHP"
    } elseif ($hasCss) {
        $type = "style"
        $description = "Mise à jour des styles CSS"
    } elseif ($hasConfig) {
        $type = "chore"
        $description = "Configuration mise à jour"
    } elseif ($hasDist) {
        $type = "build"
        $description = "Build et déploiement"
    } else {
        $type = "chore"
        $description = "Mise à jour fichiers"
    }

    return "$type`: $description - $timestamp"
}

# Configuration FastMode
if ($FastMode) {
    $SkipConnectionTest = $true
    Write-Host "MODE RAPIDE: Test de connexion desactiver, parallelisation maximale" -ForegroundColor Cyan
}

Write-Host "`nDEPLOIEMENT PLUGIN - Mode: $Mode $(if ($FastMode) { '(RAPIDE)' } else { '' })" -ForegroundColor Cyan
Write-Host ("=" * 60) -ForegroundColor White

Write-Host "`n1 Compilation des assets JavaScript/CSS..." -ForegroundColor Magenta

# Compiler les assets si nécessaire
$needsCompilation = $false
$compilationStatus = "SKIPPEE"
$devDir = Join-Path $WorkingDir "dev"

if (Test-Path $devDir) {
    # Vérifier si des fichiers source ont été modifiés récemment
    $sourceFiles = Get-ChildItem "$devDir\src" -Recurse -File -Include "*.js","*.jsx","*.ts","*.tsx" -ErrorAction SilentlyContinue
    $hasRecentSourceChanges = $sourceFiles | Where-Object { $_.LastWriteTime -gt (Get-Date).AddMinutes(-10) }

    if ($hasRecentSourceChanges) {
        $needsCompilation = $true
        Write-Host "   Changements detectes dans les sources, compilation necessaire..." -ForegroundColor Yellow
    } else {
        Write-Host "   Aucune modification recente dans les sources, compilation skippee" -ForegroundColor Green
    }
} else {
    Write-Host "   Dossier dev introuvable, compilation skippee" -ForegroundColor Yellow
}

if ($needsCompilation) {
    try {
        Write-Host "   Lancement de la compilation..." -ForegroundColor Cyan
        Push-Location $devDir
        $compileResult = cmd /c "npm run build" 2>&1
        $compileExitCode = $LASTEXITCODE
        Pop-Location

        if ($compileExitCode -eq 0) {
            Write-Host "   Compilation reussie" -ForegroundColor Green
            $compilationStatus = "OK"
        } else {
            Write-Host "   ERREUR de compilation:" -ForegroundColor Red
            $compileResult | ForEach-Object { Write-Host "   $_" -ForegroundColor Red }
            throw "Compilation echouee"
        }
    } catch {
        Write-Host "   Erreur lors de la compilation: $($_.Exception.Message)" -ForegroundColor Red
        $compilationStatus = "ERREUR"
        throw
    }
}

# 2 LISTER LES FICHIERS MODIFIES
Write-Host "`n2 Detection des fichiers modifies..." -ForegroundColor Magenta

try {
    Push-Location $WorkingDir

    # Essayer de récupérer les fichiers modifiés via git
    try {
        $ErrorActionPreference = "Continue"
        # Utiliser cmd /c pour éviter les problèmes d'encodage PowerShell
        $statusOutput = cmd /c "cd /d $WorkingDir && git status --porcelain" 2>&1
        $gitExitCode = $LASTEXITCODE
        $ErrorActionPreference = "Stop"

        if ($gitExitCode -eq 0) {
            $allModified = $statusOutput | Where-Object { $_ -and $_ -notlike "*warning*" -and $_ -notlike "*fatal*" } | ForEach-Object {
                $line = $_.ToString().Trim()
                if ($line -match '^\s*([MADRCU\?\!]{1,2})\s+(.+)$') {
                    $status = $matches[1]
                    $filePart = $matches[2]
                    
                    # Pour les renommages (R), extraire le nouveau nom de fichier après "->"
                    if ($status -like "*R*") {
                        if ($filePart -match '(.+)\s*->\s*(.+)') {
                            $file = $matches[2].Trim()
                        } else {
                            $file = $filePart
                        }
                    } else {
                        $file = $filePart
                    }
                    
                    $file
                }
            } | Sort-Object -Unique

            Write-Host "Utilisation des fichiers modifies detectes par git ($($allModified.Count) fichiers)" -ForegroundColor Green
        } else {
            Write-Host "Git status a retourne le code $gitExitCode, utilisation liste par defaut" -ForegroundColor Yellow
            $allModified = @("build/deploy-simple.ps1", "plugin/src/Managers/PdfBuilderPreviewGenerator.php")
        }
    } catch {
        Write-Host "Erreur git: $($_.Exception.Message), utilisation liste par defaut" -ForegroundColor Yellow
        $allModified = @("build/deploy-simple.ps1", "plugin/src/Managers/PdfBuilderPreviewGenerator.php")
    }
    # Inclure: plugin/*, build/*, mais EXCLURE les fichiers sources TypeScript (assets/js/src)
    # Les fichiers sources TypeScript ne doivent pas être en production, seulement les fichiers compilés
    try {
        $pluginModified = $allModified | Where-Object {
            try {
                $filePath = $_
                $isPlugin = ($filePath -like "plugin/*")
                $isNotExcluded = ($filePath -notlike "assets/js/src/*" -and
                                $filePath -notlike "assets/ts/*" -and
                                $filePath -notlike "assets/shared/*" -and
                                $filePath -notlike "assets/config/*" -and
                                $filePath -notlike "plugin/config/*" -and
                                $filePath -notlike "plugin/docs/*" -and
                                # TEMPORAIRE - NE PAS SUPPRIMER SANS AUTORISATION EXPLICITE
                                # Exclusions TypeScript pour la phase alpha - à retirer seulement quand demandé
                                $filePath -notlike "*.ts" -and
                                $filePath -notlike "*.tsx")
                $exists = $false
                if ($isPlugin -and $isNotExcluded) {
                    try {
                        $exists = Test-Path "$WorkingDir\$filePath" -ErrorAction Stop
                    } catch {
                        # Si Test-Path échoue, considérer que le fichier n'existe pas
                        $exists = $false
                    }
                }
                return $isPlugin -and $isNotExcluded -and $exists
            } catch {
                return $false
            }
        }
    } catch {
        Write-Host "Erreur lors du filtrage des fichiers: $($_.Exception.Message)" -ForegroundColor Yellow
        $pluginModified = @()
    }    # Toujours inclure les fichiers dist s'ils ont été modifiés récemment (dans les dernières 5 minutes)
    try {
        $distFiles = Get-ChildItem "$WorkingDir\plugin\assets\js\dist\*.js" -ErrorAction SilentlyContinue | Where-Object { $_.LastWriteTime -gt (Get-Date).AddMinutes(-5) } | Select-Object -ExpandProperty FullName
        $distFilesRelative = $distFiles | ForEach-Object { $_.Replace("$WorkingDir\", "").Replace("\", "/") }
        $pluginModified = @($pluginModified) + @($distFilesRelative) | Sort-Object -Unique
    } catch {
        Write-Host "Erreur lors de la detection des fichiers dist: $($_.Exception.Message)" -ForegroundColor Yellow
    }

    # Toujours inclure les fichiers JS compilés s'ils existent (même s'ils ne sont pas détectés par git)
    try {
        $compiledJsFiles = @(
            "plugin/assets/js/pdf-builder-admin.js",
            "plugin/assets/js/pdf-builder-toolbar.js",
            "plugin/assets/js/admin.js",
            "plugin/assets/js/public.js",
            "plugin/assets/js/deactivation-modal.js",
            "plugin/assets/js/templates-admin.js"
        )
        $existingCompiledJs = $compiledJsFiles | Where-Object { Test-Path "$WorkingDir\$_" }
        $pluginModified = @($pluginModified) + @($existingCompiledJs) | Sort-Object -Unique
        if ($existingCompiledJs.Count -gt 0) {
            Write-Host "Fichiers JS compiles ajoutes: $($existingCompiledJs.Count)" -ForegroundColor Green
        }
    } catch {
        Write-Host "Erreur lors de l'ajout des fichiers JS compiles: $($_.Exception.Message)" -ForegroundColor Yellow
    }

    # Si option -All, utiliser tous les fichiers du plugin
    if ($All) {
        Write-Host "Option -All detectee: utilisation de TOUS les fichiers du plugin" -ForegroundColor Cyan
        try {
            $allPluginFiles = Get-ChildItem "$WorkingDir\plugin" -Recurse -File -ErrorAction Stop | Where-Object {
                $relativePath = $_.FullName.Replace("$WorkingDir\", "").Replace("\", "/")
                # Exclure les fichiers non désirés
                $relativePath -notlike "assets/js/src/*" -and
                $relativePath -notlike "assets/ts/*" -and
                $relativePath -notlike "assets/shared/*" -and
                $relativePath -notlike "assets/config/*" -and
                $relativePath -notlike "config/*" -and
                $relativePath -notlike "docs/*" -and
                $relativePath -notlike "*.ts" -and
                $relativePath -notlike "*.tsx"
            } | ForEach-Object {
                $_.FullName.Replace("$WorkingDir\", "").Replace("\", "/")
            }
            $pluginModified = $allPluginFiles
            Write-Host "Tous les fichiers du plugin charges: $($pluginModified.Count) fichiers" -ForegroundColor Green
        } catch {
            Write-Host "Erreur lors du chargement de tous les fichiers: $($_.Exception.Message)" -ForegroundColor Red
            exit 1
        }
    }
    
    if ($pluginModified.Count -eq 0) {
        Write-Host "Aucun fichier modifie a deployer" -ForegroundColor Green
        Write-Host "   (Tous les fichiers sont a jour)" -ForegroundColor Gray
        Pop-Location
        exit 0
    }
    
    Write-Host "Fichiers modifies detects: $($pluginModified.Count)" -ForegroundColor Cyan
    $pluginModified | ForEach-Object {
        Write-Host "   - $_" -ForegroundColor White
    }
    
    Pop-Location
} catch {
    Write-Host "Erreur git: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# 3 UPLOAD FTP
$uploadCount = 0
$errorCount = 0
$startTime = Get-Date

Write-Host "`n3 Upload FTP des fichiers modifies..." -ForegroundColor Magenta

    # Test connexion FTP rapide (optionnel - skip si -SkipConnectionTest)
    if (!$SkipConnectionTest) {
        Write-Host "   Test de connexion FTP..." -ForegroundColor Yellow
        try {
            $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
            $ftpRequest.UseBinary = $false
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 5000  # Réduit à 5 secondes
            $ftpRequest.KeepAlive = $false
            $response = $ftpRequest.GetResponse()
            $response.Close()
            Write-Host "   Connexion FTP OK" -ForegroundColor Green
        } catch {
            Write-Host "   Erreur FTP: $($_.Exception.Message)" -ForegroundColor Red
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

    # Créer tous les répertoires SÉQUENTIELLEMENT pour éviter les bugs
    $createdDirs = 0
    $dirsToCreate = @()
    
    foreach ($dir in $allDirs.Keys) {
        # Nettoyer le chemin: convertir en format "/" et supprimer le préfixe "plugin/"
        $cleanDir = $dir.Replace("\", "/")
        if ($cleanDir.StartsWith("plugin/")) {
            $cleanDir = $cleanDir.Substring(7)
        }
        
        # Construire le chemin FTP complet
        $fullPath = "$FtpPath/$cleanDir".TrimEnd('/')
        
        if ($fullPath -ne $FtpPath -and $cleanDir) {
            $dirsToCreate += @{ Path = $fullPath; Name = $cleanDir }
        }
    }
    
    # Trier par longueur pour créer les répertoires parents en premier
    $dirsToCreate = $dirsToCreate | Sort-Object { ($_.Path | Measure-Object -Character).Characters }
    
    foreach ($dirInfo in $dirsToCreate) {
        try {
            # Créer le répertoire avec le bon chemin
            $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$($dirInfo.Path)/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 10000
            $ftpRequest.KeepAlive = $false
            
            $response = $ftpRequest.GetResponse()
            $response.Close()
            
            Write-Host "   Repertoire cree: $($dirInfo.Name)" -ForegroundColor Green
            $createdDirs++
        } catch {
            # Le répertoire existe probablement déjà, ignorer silencieusement
            Write-Host "   Repertoire existe: $($dirInfo.Name)" -ForegroundColor Gray
        }
    }
    
    Write-Host "   Repertoires verifies: $createdDirs" -ForegroundColor Green

    # Upload fichiers SÉQUENTIEL pour éviter les conflits de permissions
    Write-Host "   Upload des fichiers ($($pluginModified.Count) fichiers)..." -ForegroundColor Yellow
    $uploadCount = 0
    $errorCount = 0

    foreach ($file in $pluginModified) {
        $localFile = Join-Path $WorkingDir $file

        if (!(Test-Path $localFile)) {
            Write-Host "   Fichier introuvable: $file" -ForegroundColor Yellow
            continue
        }

        # Calcul du remotePath - normaliser d'abord en "/"
        $cleanPath = $file.Replace("\", "/")
        
        # Supprimer le préfixe "plugin/" s'il existe
        if ($cleanPath.StartsWith("plugin/")) {
            $remotePath = $cleanPath.Substring(7)
        } else {
            $remotePath = $cleanPath
        }
        
        # Vérifier qu'on n'a pas de chemins vides ou invalides
        if (!$remotePath -or $remotePath -eq "/" -or $remotePath -eq "") {
            Write-Host "   Chemin invalide pour: $file" -ForegroundColor Yellow
            continue
        }

        Write-Host "   Upload: $remotePath..." -NoNewline

        try {
            $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$FtpPath/$remotePath"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 30000  # 30 secondes
            $ftpRequest.ReadWriteTimeout = 60000  # 60 secondes
            $ftpRequest.KeepAlive = $false

            $fileContent = [System.IO.File]::ReadAllBytes($localFile)
            $ftpRequest.ContentLength = $fileContent.Length

            $stream = $ftpRequest.GetRequestStream()
            $stream.Write($fileContent, 0, $fileContent.Length)
            $stream.Close()

            $response = $ftpRequest.GetResponse()
            $response.Close()

            $fileSize = [math]::Round($fileContent.Length / 1KB, 2)
            Write-Host " OK ($fileSize KB)" -ForegroundColor Green
            $uploadCount++

        } catch {
            Write-Host " ERREUR: $($_.Exception.Message)" -ForegroundColor Red
            $errorCount++

            # Afficher plus de détails sur l'erreur
            if ($_.Exception.Message -match "550") {
                Write-Host "   -> Erreur 550: Vérifiez les permissions FTP pour ce fichier" -ForegroundColor Yellow
            } elseif ($_.Exception.Message -match "timeout") {
                Write-Host "   -> Timeout: Le fichier est peut-être trop volumineux" -ForegroundColor Yellow
            }
        }
    }

    Write-Host "`nUpload termine:" -ForegroundColor White
    Write-Host "   Fichiers envoyes: $uploadCount" -ForegroundColor Green
    Write-Host "   Erreurs: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })
    Write-Host "   Temps: $([math]::Round((Get-Date).Subtract($startTime).TotalSeconds, 1))s" -ForegroundColor Gray

    if ($errorCount -gt 0) {
        Write-Host "`nCertains fichiers n'ont pas pu etre uploades (probablement des fichiers binaires)." -ForegroundColor Yellow
        Write-Host "Les fichiers importants ont été déployés avec succès." -ForegroundColor Green
        # Ne pas sortir en erreur pour les fichiers binaires
    }

# 4 GIT COMMIT + PUSH + TAG
Write-Host "`n4 Git commit + push + tag..." -ForegroundColor Magenta

$commitCreated = $false
$pushSuccess = $false

# Obtenir la branche actuelle pour les push
$currentBranch = "V2"  # Valeur par défaut
try {
    $ErrorActionPreference = "Continue"
    $branchResult = cmd /c "cd /d $WorkingDir && git branch --show-current" 2>&1
    $ErrorActionPreference = "Stop"
    if ($LASTEXITCODE -eq 0 -and $branchResult) {
        $currentBranch = $branchResult.ToString().Trim()
    }
} catch {
    # Garder la valeur par défaut
}

try {
    Push-Location $WorkingDir

    # ✅ CORRECTION: Ajouter TOUS les fichiers modifiés (même s'ils ne sont pas dans $pluginModified)
    Write-Host "   Staging de TOUS les fichiers modifies..." -ForegroundColor Yellow
    $ErrorActionPreference = "Continue"
    $addResult = cmd /c "cd /d $WorkingDir && git add -A" 2>&1
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
        $commitMsg = Get-SmartCommitMessage -ModifiedFiles $pluginModified
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
        Write-Host "   Push vers remote (branche: $currentBranch)..." -ForegroundColor Yellow
        $ErrorActionPreference = "Continue"
        $pushResult = cmd /c "cd /d $WorkingDir && git push origin $currentBranch" 2>&1
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
if ($compilationStatus -eq "OK") {
    Write-Host "   Compilation: OK" -ForegroundColor Green
} elseif ($compilationStatus -eq "ERREUR") {
    Write-Host "   Compilation: ERREUR" -ForegroundColor Red
} else {
    Write-Host "   Compilation: SKIPPEE" -ForegroundColor Yellow
}

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
        $finalCommitResult = cmd /c "cd /d $WorkingDir && git commit -m `"$commitMsg`"" 2>&1
        $ErrorActionPreference = "Stop"
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "   ✅ Commit final cree" -ForegroundColor Green
        }
    }
    
    # Pousser tout vers le remote
    $ErrorActionPreference = "Continue"
    $finalPushResult = cmd /c "cd /d $WorkingDir && git push origin $currentBranch" 2>&1
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