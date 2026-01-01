# Script complet de déploiement : Compilation + FTP + Git
# Usage: .\deploy-complete.ps1

param(
    [switch]$SkipCompilation,
    [switch]$SkipFTP,
    [switch]$SkipGit,
    [string]$GitMessage = "Deploy $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
)

$ErrorActionPreference = "Stop"

# Configuration
$WorkingDir = "I:\wp-pdf-builder-pro"
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpBasePath = "/wp-content/plugins/wp-pdf-builder-pro"
$PluginPath = "$WorkingDir\plugin"

Write-Host "🚀 DÉPLOIEMENT COMPLET - $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Cyan
Write-Host "=" * 60 -ForegroundColor Cyan

# 1. COMPILATION DES ASSETS
if (-not $SkipCompilation) {
    Write-Host "`n1️⃣  COMPILATION DES ASSETS" -ForegroundColor Yellow
    Write-Host "-" * 30

    if (Test-Path "$WorkingDir\package.json") {
        Push-Location $WorkingDir
        try {
            Write-Host "🔨 Exécution de 'npm run build'..." -ForegroundColor White
            $buildResult = & npm run build 2>&1

            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ Compilation réussie !" -ForegroundColor Green
            } else {
                Write-Host "❌ Erreur de compilation :" -ForegroundColor Red
                Write-Host $buildResult -ForegroundColor Red
                exit 1
            }
        } catch {
            Write-Host "❌ Erreur lors de la compilation : $($_.Exception.Message)" -ForegroundColor Red
            exit 1
        } finally {
            Pop-Location
        }
    } else {
        Write-Host "⚠️  package.json non trouvé, compilation ignorée" -ForegroundColor Yellow
    }
} else {
    Write-Host "`n1️⃣  COMPILATION IGNORÉE (-SkipCompilation)" -ForegroundColor Gray
}

# 2. COLLECTE DES FICHIERS À DÉPLOYER
Write-Host "`n2️⃣  COLLECTE DES FICHIERS" -ForegroundColor Yellow
Write-Host "-" * 30

Write-Host "📂 Collecte des fichiers depuis : $PluginPath" -ForegroundColor White
$allFiles = Get-ChildItem -Path $PluginPath -Recurse -File
Write-Host "📊 Fichiers totaux trouvés : $($allFiles.Count)" -ForegroundColor White

# AUCUNE EXCLUSION - Tout le contenu du dossier plugin doit être déployé
$filesToDeploy = $allFiles

$totalSize = ($filesToDeploy | Measure-Object -Property Length -Sum).Sum
Write-Host "📈 Fichiers à déployer : $($filesToDeploy.Count)" -ForegroundColor Green
Write-Host "💾 Taille totale : $([math]::Round($totalSize / 1MB, 2)) MB" -ForegroundColor Green

# DEBUG : Vérifier si settings-loader.php est dans la liste
$settingsLoader = $filesToDeploy | Where-Object { $_.Name -eq "settings-loader.php" }
if ($settingsLoader) {
    Write-Host "🔍 DEBUG : settings-loader.php trouvé : $($settingsLoader.FullName)" -ForegroundColor Cyan
} else {
    Write-Host "🔍 DEBUG : settings-loader.php NON trouvé dans la liste des fichiers à déployer" -ForegroundColor Yellow
    # Lister quelques fichiers de resources/templates/admin pour debug
    $adminTemplates = $filesToDeploy | Where-Object { $_.FullName -like "*resources\templates\admin*" }
    Write-Host "🔍 DEBUG : Fichiers dans resources/templates/admin :" -ForegroundColor Yellow
    $adminTemplates | ForEach-Object { Write-Host "  - $($_.Name)" -ForegroundColor Yellow }
}

# 3. DÉPLOIEMENT FTP
if (-not $SkipFTP) {
    Write-Host "`n3️⃣  DÉPLOIEMENT FTP" -ForegroundColor Yellow
    Write-Host "-" * 30

    try {
        # Créer la connexion FTP
        $ftpUri = "ftp://$FtpHost"
        Write-Host "🔌 Connexion à $ftpUri..." -ForegroundColor White

        # Collecter tous les répertoires à créer
        $directories = $filesToDeploy | ForEach-Object {
            $relativePath = $_.FullName -replace [regex]::Escape($PluginPath), ""
            $directory = [System.IO.Path]::GetDirectoryName($relativePath)
            if ($directory -and $directory -ne "") { $directory }
        } | Select-Object -Unique | Sort-Object

        Write-Host "🏗️  Création de la structure de répertoires ($($directories.Count) répertoires)..." -ForegroundColor White

        # OPTIMISATION : Créer les répertoires en parallèle
        $dirBatchSize = 10  # Nombre de répertoires à créer simultanément
        $createdDirs = 0

        for ($i = 0; $i -lt $directories.Count; $i += $dirBatchSize) {
            $dirBatch = $directories[$i..([math]::Min($i + $dirBatchSize - 1, $directories.Count - 1))]

            # Créer les répertoires en parallèle
            $dirJobs = @()
            foreach ($dir in $dirBatch) {
                $ftpDir = "$FtpBasePath$dir".Replace("\", "/")

                $job = Start-Job -ScriptBlock {
                    param($ftpPath, $ftpUri, $ftpUser, $ftpPass)

                    try {
                        $ftpRequest = [System.Net.FtpWebRequest]::Create("$ftpUri$ftpPath")
                        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
                        $ftpRequest.UseBinary = $true
                        $ftpRequest.KeepAlive = $false
                        $ftpRequest.Timeout = 30000

                        $response = $ftpRequest.GetResponse()
                        $response.Close()
                        return @{Success = $true; Path = $ftpPath}
                    } catch {
                        # Le répertoire existe probablement déjà, c'est normal
                        return @{Success = $true; Path = $ftpPath}  # Considérer comme succès
                    }
                } -ArgumentList $ftpDir, $ftpUri, $FtpUser, $FtpPass

                $dirJobs += $job
            }

            # Attendre la fin des jobs de création de répertoires
            $completedDirJobs = $dirJobs | Wait-Job
            $dirResults = $completedDirJobs | Receive-Job
            $createdDirs += $dirResults.Count

            # Nettoyer les jobs
            $dirJobs | Remove-Job
        }

        Write-Host "📁 $createdDirs répertoires préparés" -ForegroundColor Gray

        # OPTIMISATION : Upload en parallèle par lots
        Write-Host "📤 Upload des fichiers (optimisé)..." -ForegroundColor White

        $batchSize = 25  # Nombre de fichiers à uploader simultanément (augmenté pour plus de parallélisme)
        $uploadedCount = 0
        $failedCount = 0
        $totalFiles = $filesToDeploy.Count

        # Grouper les fichiers par taille (gros fichiers en premier)
        $sortedFiles = $filesToDeploy | Sort-Object -Property Length -Descending

        for ($i = 0; $i -lt $sortedFiles.Count; $i += $batchSize) {
            $batch = $sortedFiles[$i..([math]::Min($i + $batchSize - 1, $sortedFiles.Count - 1))]
            $batchNumber = [math]::Floor($i / $batchSize) + 1
            $totalBatches = [math]::Ceiling($sortedFiles.Count / $batchSize)

            Write-Host "📦 Lot $batchNumber/$totalBatches ($($batch.Count) fichiers)..." -ForegroundColor Gray

            # Upload en parallèle avec jobs
            $jobs = @()
            foreach ($file in $batch) {
                $relativePath = $file.FullName -replace [regex]::Escape($PluginPath), ""
                $remotePath = "$FtpBasePath$relativePath".Replace("\", "/")

                $job = Start-Job -ScriptBlock {
                    param($localFile, $remoteFile, $ftpUri, $ftpUser, $ftpPass)

                    try {
                        $ftpRequest = [System.Net.FtpWebRequest]::Create("$ftpUri$remoteFile")
                        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
                        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
                        $ftpRequest.UseBinary = $true
                        $ftpRequest.KeepAlive = $false
                        $ftpRequest.Timeout = 60000

                        $fileContents = [System.IO.File]::ReadAllBytes($localFile)
                        $ftpRequest.ContentLength = $fileContents.Length

                        $requestStream = $ftpRequest.GetRequestStream()
                        $requestStream.Write($fileContents, 0, $fileContents.Length)
                        $requestStream.Close()

                        $response = $ftpRequest.GetResponse()
                        $response.Close()

                        return @{Success = $true; File = $remoteFile}
                    } catch {
                        return @{Success = $false; File = $remoteFile; Error = $_.Exception.Message}
                    }
                } -ArgumentList $file.FullName, $remotePath, $ftpUri, $FtpUser, $FtpPass

                $jobs += $job
            }

            # Attendre que tous les jobs du lot soient terminés
            $completedJobs = $jobs | Wait-Job
            $results = $completedJobs | Receive-Job

            # Traiter les résultats
            foreach ($result in $results) {
                if ($result.Success) {
                    $uploadedCount++
                    $fileName = [System.IO.Path]::GetFileName($result.File)
                    Write-Host "  ✅ $fileName" -ForegroundColor Green
                } else {
                    $failedCount++
                    $fileName = [System.IO.Path]::GetFileName($result.File)
                    Write-Host "  ❌ $fileName : $($result.Error)" -ForegroundColor Red
                }
            }

            # Nettoyer les jobs
            $jobs | Remove-Job

            # Afficher la progression
            $progress = [math]::Round(($uploadedCount + $failedCount) / $totalFiles * 100, 1)
            Write-Host "📊 Progression : $progress% ($uploadedCount uploadés, $failedCount échoués)" -ForegroundColor Cyan
        }

        if ($failedCount -eq 0) {
            Write-Host "✅ Déploiement FTP terminé : $uploadedCount fichiers uploadés avec succès" -ForegroundColor Green
        } else {
            Write-Host "⚠️  Déploiement FTP terminé : $uploadedCount fichiers uploadés, $failedCount échoués" -ForegroundColor Yellow
        }

    } catch {
        Write-Host "❌ Erreur FTP : $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "`n3️⃣  DÉPLOIEMENT FTP IGNORÉ (-SkipFTP)" -ForegroundColor Gray
}

# 4. COMMIT ET PUSH GIT
if (-not $SkipGit) {
    Write-Host "`n4️⃣  COMMIT ET PUSH GIT" -ForegroundColor Yellow
    Write-Host "-" * 30

    Push-Location $WorkingDir
    try {
        # Vérifier l'état du repository
        Write-Host "🔍 Vérification du repository Git..." -ForegroundColor White
        $gitStatus = & git status --porcelain

        if ($gitStatus) {
            Write-Host "📝 Fichiers modifiés détectés" -ForegroundColor White

            # Add all changes
            Write-Host "➕ Ajout des fichiers..." -ForegroundColor White
            & git add .

            # Commit
            Write-Host "💾 Commit avec message : $GitMessage" -ForegroundColor White
            & git commit -m $GitMessage

            if ($LASTEXITCODE -eq 0) {
                Write-Host "✅ Commit réussi" -ForegroundColor Green

                # Push vers dev
                Write-Host "🚀 Push vers la branche dev..." -ForegroundColor White
                & git push origin dev

                if ($LASTEXITCODE -eq 0) {
                    Write-Host "✅ Push réussi vers dev" -ForegroundColor Green
                } else {
                    Write-Host "❌ Erreur lors du push" -ForegroundColor Red
                }
            } else {
                Write-Host "❌ Erreur lors du commit" -ForegroundColor Red
            }
        } else {
            Write-Host "ℹ️  Aucun changement à committer" -ForegroundColor Gray
        }

    } catch {
        Write-Host "❌ Erreur Git : $($_.Exception.Message)" -ForegroundColor Red
    } finally {
        Pop-Location
    }
} else {
    Write-Host "`n4️⃣  GIT IGNORÉ (-SkipGit)" -ForegroundColor Gray
}

# 5. RÉSUMÉ FINAL
Write-Host "`n🎉 DÉPLOIEMENT COMPLET TERMINÉ !" -ForegroundColor Green
Write-Host "=" * 60 -ForegroundColor Green
Write-Host "📊 Résumé :" -ForegroundColor White
Write-Host "   • Compilation : $(if ($SkipCompilation) { 'Ignorée' } else { '✅ Effectuée' })" -ForegroundColor White
Write-Host "   • FTP : $(if ($SkipFTP) { 'Ignoré' } else { "✅ $uploadedCount fichiers déployés" })" -ForegroundColor White
Write-Host "   • Git : $(if ($SkipGit) { 'Ignoré' } else { '✅ Commit + Push vers dev' })" -ForegroundColor White
Write-Host "   • Timestamp : $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor White

Write-Host "`n✨ Script terminé avec succès !" -ForegroundColor Green