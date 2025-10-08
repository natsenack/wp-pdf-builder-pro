# 🔍 DIAGNOSTIC FTP COMPLET
# ========================

param(
    [switch]$TestConnection,
    [switch]$TestDirectory,
    [switch]$TestUpload,
    [switch]$Verbose
)

Write-Host "🔍 DIAGNOSTIC FTP COMPLET" -ForegroundColor Cyan
Write-Host "========================" -ForegroundColor Cyan

# Charger la configuration
$configFile = ".\ftp-config.env"
if (-not (Test-Path $configFile)) {
    Write-Host "❌ Fichier de config manquant : $configFile" -ForegroundColor Red
    exit 1
}

Write-Host "📄 Chargement de la configuration..." -ForegroundColor Yellow

# Charger config
Get-Content $configFile | Where-Object { $_ -match '^FTP_' } | ForEach-Object {
    $line = $_.Trim()
    if ($line -and -not $line.StartsWith("#")) {
        $parts = $line -split '=', 2
        if ($parts.Length -eq 2) {
            $key = $parts[0].Trim()
            $value = $parts[1].Trim()
            [Environment]::SetEnvironmentVariable($key, $value)
            if ($Verbose) {
                Write-Host "  $key = $value" -ForegroundColor Gray
            }
        }
    }
}

$FtpHost = $env:FTP_HOST
$FtpUser = $env:FTP_USER
$FtpPassword = $env:FTP_PASSWORD
$FtpPath = $env:FTP_PATH

Write-Host "🔧 Configuration détectée :" -ForegroundColor Yellow
Write-Host "  Host: $FtpHost" -ForegroundColor White
Write-Host "  User: $FtpUser" -ForegroundColor White
Write-Host "  Password: " -NoNewline -ForegroundColor White
Write-Host ("*" * $FtpPassword.Length) -ForegroundColor Gray
Write-Host "  Path: $FtpPath" -ForegroundColor White

# Tests de validation de base
$issues = @()

if (-not $FtpHost) { $issues += "FTP_HOST manquant" }
if (-not $FtpUser) { $issues += "FTP_USER manquant" }
if (-not $FtpPassword) { $issues += "FTP_PASSWORD manquant" }
if (-not $FtpPath) { $issues += "FTP_PATH manquant" }

if ($issues.Count -gt 0) {
    Write-Host "❌ Problèmes de configuration :" -ForegroundColor Red
    $issues | ForEach-Object { Write-Host "  - $_" -ForegroundColor Red }
    exit 1
}

Write-Host "✅ Configuration valide" -ForegroundColor Green

# Test 1: Résolution DNS
if ($TestConnection -or $TestDirectory -or $TestUpload) {
    Write-Host "`n🌐 TEST 1: RÉSOLUTION DNS" -ForegroundColor Magenta
    Write-Host "=========================" -ForegroundColor Magenta

    try {
        $ipAddresses = [System.Net.Dns]::GetHostAddresses($FtpHost)
        Write-Host "✅ Résolution DNS réussie :" -ForegroundColor Green
        $ipAddresses | ForEach-Object { Write-Host "  - $($_.ToString())" -ForegroundColor White }
    } catch {
        Write-Host "❌ Échec de résolution DNS : $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

# Test 2: Connexion FTP de base
if ($TestConnection -or $TestDirectory -or $TestUpload) {
    Write-Host "`n🔌 TEST 2: CONNEXION FTP" -ForegroundColor Magenta
    Write-Host "========================" -ForegroundColor Magenta

    try {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost/")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000

        $response = $ftpRequest.GetResponse()
        $response.Close()

        Write-Host "✅ Connexion FTP réussie" -ForegroundColor Green
    } catch {
        Write-Host "❌ Échec de connexion FTP :" -ForegroundColor Red
        Write-Host "  $($_.Exception.Message)" -ForegroundColor Red

        # Diagnostic détaillé
        if ($_.Exception.Message -contains "530") {
            Write-Host "  💡 Erreur 530 : Identifiants incorrects (login/mot de passe)" -ForegroundColor Yellow
        } elseif ($_.Exception.Message -contains "550") {
            Write-Host "  💡 Erreur 550 : Permission refusée ou répertoire inexistant" -ForegroundColor Yellow
        } elseif ($_.Exception.Message -contains "421") {
            Write-Host "  💡 Erreur 421 : Service indisponible (trop de connexions?)" -ForegroundColor Yellow
        }

        exit 1
    }
}

# Test 3: Vérification du répertoire
if ($TestDirectory -or $TestUpload) {
    Write-Host "`n📁 TEST 3: VÉRIFICATION RÉPERTOIRE" -ForegroundColor Magenta
    Write-Host "=================================" -ForegroundColor Magenta

    try {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$FtpPath/")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000

        $response = $ftpRequest.GetResponse()
        $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
        $files = $reader.ReadToEnd()
        $reader.Close()
        $response.Close()

        $fileCount = ($files -split "`n" | Where-Object { $_.Trim() }).Count
        Write-Host "✅ Répertoire accessible : $fileCount éléments trouvés" -ForegroundColor Green

        if ($Verbose) {
            Write-Host "📋 Contenu du répertoire :" -ForegroundColor Cyan
            ($files -split "`n" | Where-Object { $_.Trim() } | Select-Object -First 10) | ForEach-Object {
                Write-Host "  - $($_.Trim())" -ForegroundColor White
            }
            if ($fileCount -gt 10) {
                Write-Host "  ... et $($fileCount - 10) autres fichiers" -ForegroundColor Gray
            }
        }
    } catch {
        Write-Host "❌ Impossible d'accéder au répertoire $FtpPath :" -ForegroundColor Red
        Write-Host "  $($_.Exception.Message)" -ForegroundColor Red

        # Tentative de création du répertoire
        Write-Host "🔧 Tentative de création du répertoire..." -ForegroundColor Yellow
        try {
            $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$FtpPath/")
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
            $ftpRequest.UsePassive = $true
            $ftpRequest.Timeout = 10000

            $response = $ftpRequest.GetResponse()
            $response.Close()

            Write-Host "✅ Répertoire créé avec succès" -ForegroundColor Green
        } catch {
            Write-Host "❌ Impossible de créer le répertoire :" -ForegroundColor Red
            Write-Host "  $($_.Exception.Message)" -ForegroundColor Red
        }

        exit 1
    }
}

# Test 4: Upload de test
if ($TestUpload) {
    Write-Host "`n📤 TEST 4: UPLOAD DE TEST" -ForegroundColor Magenta
    Write-Host "========================" -ForegroundColor Magenta

    $testFile = "ftp-test-$(Get-Date -Format 'yyyyMMdd-HHmmss').txt"
    $testContent = "Test FTP - $(Get-Date)`nCeci est un fichier de test automatique."

    try {
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$FtpPath/$testFile")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 15000

        $bytes = [System.Text.Encoding]::UTF8.GetBytes($testContent)
        $ftpRequest.ContentLength = $bytes.Length

        $stream = $ftpRequest.GetRequestStream()
        $stream.Write($bytes, 0, $bytes.Length)
        $stream.Close()

        $response = $ftpRequest.GetResponse()
        $response.Close()

        Write-Host "✅ Upload de test réussi : $testFile" -ForegroundColor Green

        # Vérification du fichier uploadé
        Write-Host "🔍 Vérification du fichier uploadé..." -ForegroundColor Yellow
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$FtpPath/$testFile")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000

        $response = $ftpRequest.GetResponse()
        $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
        $downloadedContent = $reader.ReadToEnd()
        $reader.Close()
        $response.Close()

        if ($downloadedContent -eq $testContent) {
            Write-Host "✅ Contenu du fichier vérifié" -ForegroundColor Green
        } else {
            Write-Host "⚠️ Contenu du fichier différent" -ForegroundColor Yellow
        }

        # Nettoyage
        Write-Host "🧹 Nettoyage du fichier de test..." -ForegroundColor Yellow
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$FtpPath/$testFile")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000

        $response = $ftpRequest.GetResponse()
        $response.Close()

        Write-Host "✅ Fichier de test supprimé" -ForegroundColor Green

    } catch {
        Write-Host "❌ Échec de l'upload de test :" -ForegroundColor Red
        Write-Host "  $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

# Analyse du script de déploiement
Write-Host "`n🔧 ANALYSE DU SCRIPT DE DÉPLOIEMENT" -ForegroundColor Magenta
Write-Host "===================================" -ForegroundColor Magenta

$scriptPath = ".\ftp-deploy-simple.ps1"
if (Test-Path $scriptPath) {
    Write-Host "✅ Script trouvé : $scriptPath" -ForegroundColor Green

    # Vérifier les paramètres par défaut
    $scriptContent = Get-Content $scriptPath -Raw

    # Vérifier le RemoteDir par défaut
    if ($scriptContent -match 'RemoteDir = "([^"]*)"') {
        $defaultRemoteDir = $matches[1]
        Write-Host "📁 RemoteDir par défaut : $defaultRemoteDir" -ForegroundColor White

        if ($defaultRemoteDir -ne $FtpPath) {
            Write-Host "⚠️ INCOHÉRENCE DÉTECTÉE :" -ForegroundColor Yellow
            Write-Host "  Script par défaut : $defaultRemoteDir" -ForegroundColor Yellow
            Write-Host "  Config FTP_PATH  : $FtpPath" -ForegroundColor Yellow
            Write-Host "  💡 Le script utilise le paramètre par défaut au lieu de FTP_PATH !" -ForegroundColor Red
        } else {
            Write-Host "✅ RemoteDir cohérent avec FTP_PATH" -ForegroundColor Green
        }
    }

    # Vérifier MaxConcurrent
    if ($scriptContent -match 'MaxConcurrent = (\d+)') {
        $defaultMaxConcurrent = [int]$matches[1]
        Write-Host "🔥 MaxConcurrent par défaut : $defaultMaxConcurrent" -ForegroundColor White
    }

    # Vérifier ChunkSize
    if ($scriptContent -match 'ChunkSize = (\d+)') {
        $defaultChunkSize = [int]$matches[1]
        $chunkSizeMB = [math]::Round($defaultChunkSize / 1MB, 1)
        Write-Host "📦 ChunkSize par défaut : ${chunkSizeMB}MB" -ForegroundColor White
    }

} else {
    Write-Host "❌ Script manquant : $scriptPath" -ForegroundColor Red
}

# Recommandations
Write-Host "`n💡 RECOMMANDATIONS" -ForegroundColor Magenta
Write-Host "=================" -ForegroundColor Magenta

$recommendations = @()

if ($defaultRemoteDir -ne $FtpPath) {
    $recommendations += "🔧 CORRIGER l'incohérence RemoteDir vs FTP_PATH dans le script"
}

if ($defaultMaxConcurrent -gt 20) {
    $recommendations += "⚡ RÉDUIRE MaxConcurrent si des timeouts surviennent (actuellement $defaultMaxConcurrent)"
}

$recommendations += "🧪 TESTER régulièrement la connectivité avec ce diagnostic"
$recommendations += "🔒 CHANGER le mot de passe FTP régulièrement"

if ($recommendations.Count -gt 0) {
    Write-Host "Recommandations :" -ForegroundColor Yellow
    $recommendations | ForEach-Object { Write-Host "  - $_" -ForegroundColor White }
}

Write-Host "`n✅ DIAGNOSTIC TERMINÉ" -ForegroundColor Green
Write-Host "===================" -ForegroundColor Green

if ($TestConnection -or $TestDirectory -or $TestUpload) {
    Write-Host "🎉 Tous les tests sont passés avec succès !" -ForegroundColor Green
} else {
    Write-Host "ℹ️ Utilisez les paramètres -TestConnection, -TestDirectory, -TestUpload pour des tests complets" -ForegroundColor Cyan
}