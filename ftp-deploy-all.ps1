<#
.SYNOPSIS
    Script de déploiement FTP complet - Envoie TOUS les fichiers du plugin

.DESCRIPTION
    Déploie tous les fichiers du plugin vers le serveur FTP sans filtre
    Compilation automatique, gestion des erreurs robuste

.EXAMPLE
    .\ftp-deploy-all.ps1
#>

# Configuration
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = Read-Host "Entrez le mot de passe FTP" -AsSecureString
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"
$LocalPath = "D:\wp-pdf-builder-pro"

Write-Host "🚀 DÉPLOIEMENT FTP COMPLET - TOUS LES FICHIERS" -ForegroundColor Cyan
Write-Host "=" * 60

# 1. Compilation
Write-Host "`n📦 1. Compilation du projet..." -ForegroundColor Yellow
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Erreur de compilation" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Compilation réussie" -ForegroundColor Green

# 2. Créer une liste de tous les fichiers à envoyer
Write-Host "`n📂 2. Récupération de la liste des fichiers..." -ForegroundColor Yellow

$ExcludePatterns = @(
    "node_modules",
    ".git",
    ".gitignore",
    "composer",
    "vendor",
    "temp",
    "uploads",
    "*.ps1",
    "*.md",
    "*.pot",
    "*.json",
    ".env*",
    "ftp_*",
    "test-*",
    "bundle-*",
    "diagnostic-*",
    "deploy-*",
    "validate-*",
    "*.config.js"
)

$FilesToDeploy = @()

# Récupérer tous les fichiers
Get-ChildItem -Path $LocalPath -Recurse -File | ForEach-Object {
    $RelativePath = $_.FullName.Substring($LocalPath.Length + 1)
    $IsExcluded = $false
    
    foreach ($Pattern in $ExcludePatterns) {
        if ($RelativePath -like "*$Pattern*") {
            $IsExcluded = $true
            break
        }
    }
    
    if (-not $IsExcluded) {
        $FilesToDeploy += @{
            LocalPath = $_.FullName
            RemotePath = $RelativePath -replace '\\', '/'
        }
    }
}

Write-Host "✅ $($FilesToDeploy.Count) fichiers détectés" -ForegroundColor Green

# 3. Connexion FTP et déploiement
Write-Host "`n🌐 3. Connexion FTP..." -ForegroundColor Yellow

try {
    $FtpUrl = "ftp://$FtpHost"
    $Credential = New-Object System.Management.Automation.PSCredential($FtpUser, $FtpPass)
    
    $DeployedCount = 0
    $FailedCount = 0
    $Stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    
    foreach ($File in $FilesToDeploy) {
        try {
            $RemoteFile = "$FtpUrl$FtpPath/$($File.RemotePath)"
            $RemoteDir = Split-Path $RemoteFile
            
            # Créer le répertoire si nécessaire
            $FtpRequest = [System.Net.FtpWebRequest]::Create($RemoteDir)
            $FtpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $FtpRequest.Credentials = $Credential
            
            try {
                $FtpRequest.GetResponse() | Out-Null
            } catch {
                # Le répertoire existe probablement déjà
            }
            
            # Envoyer le fichier
            $FtpRequest = [System.Net.FtpWebRequest]::Create($RemoteFile)
            $FtpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $FtpRequest.Credentials = $Credential
            $FtpRequest.UseBinary = $true
            
            $FileStream = [System.IO.File]::OpenRead($File.LocalPath)
            $FtpStream = $FtpRequest.GetRequestStream()
            $FileStream.CopyTo($FtpStream)
            $FtpStream.Close()
            $FileStream.Close()
            
            $Response = $FtpRequest.GetResponse()
            $Response.Close()
            
            $DeployedCount++
            Write-Host "  ✅ $($File.RemotePath)" -ForegroundColor Green
        } catch {
            $FailedCount++
            Write-Host "  ❌ $($File.RemotePath): $_" -ForegroundColor Red
        }
    }
    
    $Stopwatch.Stop()
    $Duration = $Stopwatch.Elapsed.ToString("mm\:ss\.ff")
    
    Write-Host "`n" -ForegroundColor Cyan
    Write-Host "━" * 60
    Write-Host "RÉSUMÉ DU DÉPLOIEMENT" -ForegroundColor Cyan
    Write-Host "━" * 60
    Write-Host "Fichiers déployés: $DeployedCount" -ForegroundColor Green
    Write-Host "Fichiers échoués: $FailedCount" -ForegroundColor $(if ($FailedCount -eq 0) { "Green" } else { "Red" })
    Write-Host "Durée totale: $Duration" -ForegroundColor Cyan
    Write-Host "━" * 60
    
    if ($FailedCount -eq 0) {
        Write-Host "`n✅ DÉPLOIEMENT RÉUSSI!" -ForegroundColor Green
    } else {
        Write-Host "`n⚠️  Déploiement avec $FailedCount erreur(s)" -ForegroundColor Yellow
    }
} catch {
    Write-Host "❌ Erreur FTP: $_" -ForegroundColor Red
    exit 1
}
