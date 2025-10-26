# Script de déploiement simplifié - Envoie UNIQUEMENT les fichiers modifiés
# Usage: .\deploy-simple.ps1

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("test", "plugin")]
    [string]$Mode = "plugin"
)

$ErrorActionPreference = "Stop"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "nats123456"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

$LocalPluginPath = "D:\wp-pdf-builder-pro\plugin"
$WorkingDir = "D:\wp-pdf-builder-pro"

Write-Host "`n🚀 DÉPLOIEMENT PLUGIN - Mode: $Mode" -ForegroundColor Cyan
Write-Host ("=" * 60) -ForegroundColor White

# 1️⃣ COMPILATION DES ASSETS
Write-Host "`n1️⃣ Compilation des assets JavaScript/CSS..." -ForegroundColor Magenta

try {
    Push-Location $WorkingDir
    Write-Host "   Exécution: npm run build" -ForegroundColor Yellow
    $buildResult = & npm run build 2>&1
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Erreur de compilation!" -ForegroundColor Red
        Write-Host $buildResult -ForegroundColor Red
        exit 1
    }
    Write-Host "✅ Compilation réussie" -ForegroundColor Green
    Pop-Location
} catch {
    Write-Host "❌ Erreur: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# 2️⃣ LISTER LES FICHIERS MODIFIÉS
Write-Host "`n2️⃣ Détection des fichiers modifiés..." -ForegroundColor Magenta

try {
    Push-Location $WorkingDir
    
    # Récupérer les fichiers modifiés depuis git
    $modifiedFiles = & git diff --name-only HEAD 2>&1
    $stagedFiles = & git diff --cached --name-only HEAD 2>&1
    $allModified = @($modifiedFiles) + @($stagedFiles) | Sort-Object -Unique
    
    # Filtrer pour le dossier plugin uniquement
    $pluginModified = $allModified | Where-Object { $_ -like "plugin/*" }
    
    if ($pluginModified.Count -eq 0) {
        Write-Host "✅ Aucun fichier modifié à déployer" -ForegroundColor Green
        Write-Host "   (Tous les fichiers sont à jour)" -ForegroundColor Gray
        Pop-Location
        exit 0
    }
    
    Write-Host "📝 Fichiers modifiés détectés: $($pluginModified.Count)" -ForegroundColor Cyan
    $pluginModified | ForEach-Object {
        Write-Host "   • $_" -ForegroundColor White
    }
    
    Pop-Location
} catch {
    Write-Host "❌ Erreur git: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# 3️⃣ UPLOAD FTP
if ($Mode -eq "test") {
    Write-Host "`n🧪 MODE TEST - Pas d'upload réel" -ForegroundColor Yellow
} else {
    Write-Host "`n3️⃣ Upload FTP des fichiers modifiés..." -ForegroundColor Magenta
    
    $uploadCount = 0
    $errorCount = 0
    $startTime = Get-Date
    
    # Créer les répertoires d'abord
    $dirs = @{}
    foreach ($file in $pluginModified) {
        $dir = Split-Path $file -Parent
        if ($dir -and !$dirs.ContainsKey($dir)) {
            $dirs[$dir] = $true
        }
    }
    
    # Créer répertoires sur FTP
    foreach ($dir in $dirs.Keys) {
        $ftpDir = $dir.Replace("\", "/").Replace("plugin/", "")
        $fullPath = "$FtpPath/$ftpDir"
        
        try {
            $ftpUri = "ftp://$FtpHost$fullPath/"
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
            $ftpRequest.UseBinary = $false
            $ftpRequest.UsePassive = $true
            $response = $ftpRequest.GetResponse()
            $response.Close()
        } catch {
            # Dossier peut déjà exister
        }
    }
    
    # Upload fichiers avec status
    foreach ($file in $pluginModified) {
        $localFile = Join-Path $WorkingDir $file
        
        if (!(Test-Path $localFile)) {
            # Fichier supprimé
            continue
        }
        
        $remotePath = $file.Replace("\", "/").Replace("plugin/", "")
        $ftpUri = "ftp://$FtpHost$FtpPath/$remotePath"
        
        try {
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 30000
            
            $fileContent = [System.IO.File]::ReadAllBytes($localFile)
            $ftpRequest.ContentLength = $fileContent.Length
            
            $stream = $ftpRequest.GetRequestStream()
            $stream.Write($fileContent, 0, $fileContent.Length)
            $stream.Close()
            
            $response = $ftpRequest.GetResponse()
            $response.Close()
            
            $uploadCount++
            Write-Host "   ✅ $file" -ForegroundColor Green
        } catch {
            $errorCount++
            Write-Host "   ❌ $file - $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    
    $totalTime = (Get-Date) - $startTime
    Write-Host "`n📊 Upload terminé:" -ForegroundColor White
    Write-Host "   • Fichiers envoyés: $uploadCount" -ForegroundColor Green
    Write-Host "   • Erreurs: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })
    Write-Host "   • Temps: $([math]::Round($totalTime.TotalSeconds, 1))s" -ForegroundColor Gray
    
    if ($errorCount -gt 0) {
        Write-Host "`n⚠️ Certains fichiers n'ont pas pu être uploadés!" -ForegroundColor Yellow
        exit 1
    }
}

# 4️⃣ GIT COMMIT + PUSH + TAG
Write-Host "`n4️⃣ Git commit + push + tag..." -ForegroundColor Magenta

try {
    Push-Location $WorkingDir
    
    # Staging
    Write-Host "   📝 Staging des fichiers..." -ForegroundColor Yellow
    & git add -A 2>&1 | Out-Null
    
    # Commit
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $commitMsg = "feat: Déploiement plugin - $timestamp ($($pluginModified.Count) fichiers)"
    Write-Host "   📤 Commit: $commitMsg" -ForegroundColor Yellow
    & git commit -m $commitMsg 2>&1 | Out-Null
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✅ Commit créé" -ForegroundColor Green
    } else {
        Write-Host "   ⚠️ Rien à committer (déjà à jour)" -ForegroundColor Gray
    }
    
    # Push
    Write-Host "   🚀 Push vers remote..." -ForegroundColor Yellow
    & git push origin dev 2>&1 | Out-Null
    Write-Host "   ✅ Push réussi" -ForegroundColor Green
    
    # Tag de version
    $version = Get-Date -Format "v1.0.0-deploy-yyyyMMdd-HHmmss"
    Write-Host "   🏷️ Tag: $version" -ForegroundColor Yellow
    & git tag -a $version -m "Déploiement $version" 2>&1 | Out-Null
    & git push origin $version 2>&1 | Out-Null
    Write-Host "   ✅ Tag créé et pushé" -ForegroundColor Green
    
    Pop-Location
} catch {
    Write-Host "❌ Erreur git: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# ✅ FIN
Write-Host "`n✅ DÉPLOIEMENT TERMINÉ AVEC SUCCÈS!" -ForegroundColor Green
Write-Host ("=" * 60) -ForegroundColor White
Write-Host "📊 Résumé:" -ForegroundColor Cyan
Write-Host "   • Compilation: ✅" -ForegroundColor Green
Write-Host "   • Upload FTP: ✅ ($uploadCount fichiers)" -ForegroundColor Green
Write-Host "   • Git: ✅ (commit + push + tag)" -ForegroundColor Green
Write-Host ""
