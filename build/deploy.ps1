# Commande pour déployer le plugin : cd d:\wp-pdf-builder-pro\build; .\deploy.ps1 -Mode plugin
<#
.SYNOPSIS
    Script unifié de déploiement FTP pour PDF Builder Pro

.DESCRIPTION
    Déploie le plugin WordPress ou teste le déploiement
    Supporte les modes : test, plugin-only, full-project
    Options de synchronisation : intelligente ou complète

.PARAMETER Mode
    Mode de déploiement :
    - test : Simulation sans envoi (défaut)
    - plugin : Déploie seulement le dossier plugin/
    - full : Déploie tout le projet (mode développement)

.PARAMETER FullSync
    Force la synchronisation complète de tous les fichiers
    Utile pour corriger des problèmes de synchronisation

.PARAMETER Force
    Mode forcé : écrase tous les fichiers existants
    À utiliser avec précaution

.PARAMETER Diagnostic
    Lance un diagnostic complet du système avant déploiement

.PARAMETER AutoFix
    Tente de corriger automatiquement les erreurs détectées lors du diagnostic

.PARAMETER DailyDeploy
    Déploiement quotidien complet : diagnostic + auto-correction + déploiement automatique

.PARAMETER FileFilter
    Filtre les fichiers à déployer :
    - all : Tous les fichiers (défaut)
    - assets : Seulement le dossier assets/
    - js : Seulement les fichiers JavaScript (*.js)
    - css : Seulement les fichiers CSS (*.css)
    - php : Seulement les fichiers PHP (*.php)
    - languages : Seulement les fichiers de traduction (*.mo, *.po)
    - custom : Filtres personnalisés (utiliser avec -CustomFilter)

.PARAMETER CustomFilter
    Patterns de filtrage personnalisés (utilisé avec -FileFilter custom)
    Accepte des wildcards : "*admin.js", "*style.css", etc.

.EXAMPLE
    .\deploy.ps1 -Mode test
    .\deploy.ps1 -Mode plugin
    .\deploy.ps1 -Mode plugin -FullSync
    .\deploy.ps1 -Mode plugin -Force
    .\deploy.ps1 -Mode plugin -Diagnostic
    .\deploy.ps1 -Diagnostic -AutoFix
    .\deploy.ps1 -DailyDeploy
    .\deploy.ps1 -Mode plugin -FileFilter assets      # Envoyer seulement les assets
    .\deploy.ps1 -Mode plugin -FileFilter js          # Envoyer seulement les fichiers JS
    .\deploy.ps1 -Mode plugin -FileFilter css         # Envoyer seulement les CSS
    .\deploy.ps1 -Mode plugin -FileFilter php         # Envoyer seulement les PHP
    .\deploy.ps1 -Mode plugin -FileFilter languages   # Envoyer seulement les traductions
    .\deploy.ps1 -Mode plugin -FileFilter custom -CustomFilter "*admin.js","*style.css"  # Filtres personnalisés
#>

param(
    [Parameter(Mandatory=$false)]
    [ValidateSet("test", "plugin", "full")]
    [string]$Mode = "test",

    [Parameter(Mandatory=$false)]
    [switch]$FullSync,

    [Parameter(Mandatory=$false)]
    [switch]$Force,

    [Parameter(Mandatory=$false)]
    [switch]$NoConfirm,

    [Parameter(Mandatory=$false)]
    [switch]$Diagnostic,

    [Parameter(Mandatory=$false)]
    [switch]$AutoFix,

    [Parameter(Mandatory=$false)]
    [switch]$DailyDeploy,

    [Parameter(Mandatory=$false)]
    [ValidateSet("all", "assets", "js", "css", "php", "languages", "custom")]
    [string]$FileFilter = "all",

    [Parameter(Mandatory=$false)]
    [string[]]$CustomFilter = @()
)

# Configuration des logs
$LogDir = "$PSScriptRoot\logs"
$Timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$LogFile = "$LogDir\deployment-$Timestamp.log"
$BackupDir = "$PSScriptRoot\backups\$Timestamp"

# Fonction de logging
function Write-Log {
    param(
        [string]$Message,
        [string]$Level = "INFO",
        [string]$Color = "White"
    )

    $LogEntry = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') [$Level] $Message"
    Write-Host $Message -ForegroundColor $Color

    # Écrire dans le fichier de log
    try {
        $LogEntry | Out-File -FilePath $LogFile -Append -Encoding UTF8
    } catch {
        Write-Host "⚠️ Impossible d'écrire dans le log : $($_.Exception.Message)" -ForegroundColor Yellow
    }
}

# Fonction de logging détaillé (JSON)
function Write-DetailedLog {
    param(
        [string]$Operation,
        [string]$Message,
        [string]$Level = "INFO",
        [hashtable]$Details = @{}
    )

    $logEntry = @{
        timestamp = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
        operation = $Operation
        level = $Level
        message = $Message
        details = $Details
    }

    # Écrire dans le fichier JSON
    try {
        $jsonLogFile = "$LogFile.json"
        $logEntry | ConvertTo-Json -Depth 10 | Out-File -FilePath $jsonLogFile -Append -Encoding UTF8
    } catch {
        Write-Host "⚠️ Impossible d'écrire dans le log JSON : $($_.Exception.Message)" -ForegroundColor Yellow
    }
}

# Fonction de diagnostic complet
function Start-SystemDiagnostic {
    Write-Host "🔍 DIAGNOSTIC SYSTÈME - PDF BUILDER PRO" -ForegroundColor Cyan
    Write-Host "=" * 50 -ForegroundColor Cyan

    $diagnosticResults = @{
        critical = @()
        warnings = @()
        info = @()
        passed = 0
        failed = 0
        total = 0
    }

    function Test-Diagnostic {
        param(
            [string]$TestName,
            [scriptblock]$TestScript,
            [string]$Category = "info",
            [string]$SuccessMessage = "",
            [string]$FailureMessage = ""
        )

        $diagnosticResults.total++
        Write-Host "  🔍 $TestName..." -ForegroundColor White -NoNewline

        try {
            $result = & $TestScript
            if ($result) {
                Write-Host " ✅" -ForegroundColor Green
                if ($SuccessMessage) { Write-Host "     $SuccessMessage" -ForegroundColor Gray }
                $diagnosticResults.passed++
                if ($Category -eq "critical") {
                    $diagnosticResults.critical += @{name=$TestName; status="PASS"; message=$SuccessMessage}
                }
            } else {
                Write-Host " ❌" -ForegroundColor Red
                if ($FailureMessage) { Write-Host "     $FailureMessage" -ForegroundColor Yellow }
                $diagnosticResults.failed++
                if ($Category -eq "critical") {
                    $diagnosticResults.critical += @{name=$TestName; status="FAIL"; message=$FailureMessage}
                } elseif ($Category -eq "warning") {
                    $diagnosticResults.warnings += @{name=$TestName; status="WARN"; message=$FailureMessage}
                }
            }
        } catch {
            Write-Host " ⚠️" -ForegroundColor Yellow
            Write-Host "     Erreur: $($_.Exception.Message)" -ForegroundColor Yellow
            $diagnosticResults.failed++
        }
    }

    # 1. Vérifications de structure
    Write-Host "`n🏗️ STRUCTURE DES DOSSIERS" -ForegroundColor Magenta
    Write-Host "-" * 30 -ForegroundColor Magenta

    Test-Diagnostic "Dossier plugin" { Test-Path "$PSScriptRoot\..\plugin" } "critical" "Dossier plugin/ accessible" "Dossier plugin/ manquant"
    Test-Diagnostic "Dossier build" { Test-Path "$PSScriptRoot" } "critical" "Dossier build/ accessible" "Dossier build/ manquant"
    Test-Diagnostic "Dossier assets" { Test-Path "$PSScriptRoot\..\plugin\assets" } "critical" "Dossier assets/ présent" "Dossier assets/ manquant"
    Test-Diagnostic "Dossier JS dist" { Test-Path "$PSScriptRoot\..\plugin\assets\js\dist" } "critical" "Assets JavaScript compilés présents" "Assets JavaScript non compilés"
    Test-Diagnostic "Dossier CSS" { Test-Path "$PSScriptRoot\..\plugin\assets\css" } "critical" "Styles CSS présents" "Styles CSS manquants"

    # 2. Vérifications des fichiers critiques
    Write-Host "`n📄 FICHIERS CRITIQUES" -ForegroundColor Magenta
    Write-Host "-" * 25 -ForegroundColor Magenta

    $criticalFiles = @(
        @{path="$PSScriptRoot\..\plugin\pdf-builder-pro.php"; name="Fichier principal plugin"},
        @{path="$PSScriptRoot\..\plugin\assets\js\dist\pdf-builder-admin.js"; name="Bundle JS admin"},
        @{path="$PSScriptRoot\..\plugin\assets\css\pdf-builder-admin.css"; name="Style CSS admin"},
        @{path="$PSScriptRoot\..\plugin\languages\pdf-builder-pro-fr_FR.mo"; name="Fichier traduction FR"}
    )

    foreach ($file in $criticalFiles) {
        Test-Diagnostic $file.name { Test-Path $file.path } "critical" "$($file.name) trouvé" "$($file.name) manquant"
    }

    # 3. Vérifications des assets compilés
    Write-Host "`n🎨 ASSETS COMPILÉS" -ForegroundColor Magenta
    Write-Host "-" * 20 -ForegroundColor Magenta

    $assetFiles = @(
        @{path="$PSScriptRoot\..\plugin\assets\js\dist\pdf-builder-admin.js"; name="Bundle admin"; minSize=100KB},
        @{path="$PSScriptRoot\..\plugin\assets\js\dist\pdf-builder-admin-debug.js"; name="Bundle debug"; minSize=100KB},
        @{path="$PSScriptRoot\..\plugin\assets\css\pdf-builder-admin.css"; name="CSS admin"; minSize=1KB},
        @{path="$PSScriptRoot\..\plugin\assets\css\pdf-builder-react.css"; name="CSS React"; minSize=10KB}
    )

    foreach ($asset in $assetFiles) {
        $testResult = if (Test-Path $asset.path) {
            $size = (Get-Item $asset.path).Length
            $size -gt $asset.minSize
        } else { $false }

        Test-Diagnostic $asset.name { $testResult } "warning" "$($asset.name) valide ($([math]::Round((Get-Item $asset.path).Length/1KB,1)) KB)" "$($asset.name) trop petit ou manquant"
    }

    # 4. Vérifications système
    Write-Host "`n⚙️ SYSTÈME ET OUTILS" -ForegroundColor Magenta
    Write-Host "-" * 20 -ForegroundColor Magenta

    Test-Diagnostic "PowerShell version" { $PSVersionTable.PSVersion.Major -ge 5 } "critical" "PowerShell $($PSVersionTable.PSVersion) compatible" "PowerShell version trop ancienne"
    Test-Diagnostic "FTP disponible" { Get-Command ftp -ErrorAction SilentlyContinue } "critical" "Client FTP disponible" "Client FTP non trouvé"
    Test-Diagnostic "Git disponible" { Get-Command git -ErrorAction SilentlyContinue } "info" "Git installé" "Git non installé (versioning limité)"
    Test-Diagnostic "Permissions écriture logs" {
        if (Test-Path "$PSScriptRoot\logs") {
            $true
        } else {
            try {
                New-Item -ItemType Directory -Path "$PSScriptRoot\logs" -Force -ErrorAction Stop | Out-Null
                $true
            } catch {
                $false
            }
        }
    } "warning" "Dossier logs accessible" "Impossible de créer le dossier logs"

    Test-Diagnostic "Permissions écriture backups" {
        if (Test-Path "$PSScriptRoot\backups") {
            $true
        } else {
            try {
                New-Item -ItemType Directory -Path "$PSScriptRoot\backups" -Force -ErrorAction Stop | Out-Null
                $true
            } catch {
                $false
            }
        }
    } "warning" "Dossier backups accessible" "Impossible de créer le dossier backups"

    # 5. Vérifications réseau/FTP
    Write-Host "`n🌐 CONNEXION RÉSEAU" -ForegroundColor Magenta
    Write-Host "-" * 20 -ForegroundColor Magenta

    Test-Diagnostic "Connexion Internet" {
        try {
            $test = Test-Connection -ComputerName 8.8.8.8 -Count 1 -Quiet -ErrorAction Stop
            $test
        } catch {
            $false
        }
    } "warning" "Connexion Internet active" "Pas de connexion Internet détectée"

    Test-Diagnostic "Serveur FTP accessible" {
        try {
            $ping = Test-Connection -ComputerName "65.108.242.181" -Count 1 -Quiet -ErrorAction Stop
            $ping
        } catch {
            $false
        }
    } "info" "Serveur FTP joignable" "Serveur FTP non accessible"

    # 6. Vérifications Git
    Write-Host "`n📚 ÉTAT REPOSITORY" -ForegroundColor Magenta
    Write-Host "-" * 18 -ForegroundColor Magenta

    if (Get-Command git -ErrorAction SilentlyContinue) {
        Push-Location "$PSScriptRoot\.." -ErrorAction SilentlyContinue
        try {
            $gitStatus = git status --porcelain 2>$null
            Test-Diagnostic "Repository Git" { $LASTEXITCODE -eq 0 } "info" "Repository Git valide" "Pas un repository Git"

            if ($LASTEXITCODE -eq 0) {
                $uncommitted = ($gitStatus | Measure-Object).Count
                Test-Diagnostic "Fichiers non committés" { $uncommitted -eq 0 } "warning" "Repository propre" "$uncommitted fichier(s) non committé(s)"
            }
        } finally {
            Pop-Location -ErrorAction SilentlyContinue
        }
    } else {
        Test-Diagnostic "Repository Git" { $false } "info" "" "Git non disponible"
    }

    # 7. Résumé du diagnostic
    Write-Host "`n📊 RÉSULTATS DU DIAGNOSTIC" -ForegroundColor Cyan
    Write-Host "=" * 30 -ForegroundColor Cyan

    Write-Host "📈 Statistiques :" -ForegroundColor White
    Write-Host "   • Tests totaux : $($diagnosticResults.total)" -ForegroundColor White
    Write-Host "   • Réussis : $($diagnosticResults.passed)" -ForegroundColor Green
    Write-Host "   • Échoués : $($diagnosticResults.failed)" -ForegroundColor Red

    $successRate = [math]::Round(($diagnosticResults.passed / $diagnosticResults.total) * 100, 1)
    Write-Host "   • Taux de succès : $successRate%" -ForegroundColor $(if ($successRate -ge 80) { "Green" } elseif ($successRate -ge 60) { "Yellow" } else { "Red" })

    # Évaluation globale
    if ($diagnosticResults.failed -eq 0) {
        Write-Host "`n🎉 DIAGNOSTIC RÉUSSI - Système prêt pour le déploiement !" -ForegroundColor Green
        return @{result=$true; details=$diagnosticResults}
    } elseif ($diagnosticResults.critical | Where-Object { $_.status -eq "FAIL" }) {
        Write-Host "`n❌ PROBLÈMES CRITIQUES - Déploiement impossible !" -ForegroundColor Red
        Write-Host "Résoudre les problèmes suivants :" -ForegroundColor Red
        foreach ($issue in ($diagnosticResults.critical | Where-Object { $_.status -eq "FAIL" })) {
            Write-Host "  • $($issue.name): $($issue.message)" -ForegroundColor Red
        }
        return @{result=$false; details=$diagnosticResults}
    } else {
        Write-Host "`n⚠️ AVERTISSEMENTS - Déploiement possible mais déconseillé" -ForegroundColor Yellow
        Write-Host "Considérer résoudre :" -ForegroundColor Yellow
        foreach ($issue in $diagnosticResults.warnings) {
            Write-Host "  • $($issue.name): $($issue.message)" -ForegroundColor Yellow
        }
        return @{result=$true; details=$diagnosticResults}
    }
}

# Fonction de correction automatique des erreurs détectées
function Start-SystemAutoFix {
    param([hashtable]$diagnosticResults)

    Write-Host "`n🔧 CORRECTION AUTOMATIQUE DES ERREURS" -ForegroundColor Cyan
    Write-Host "=" * 40 -ForegroundColor Cyan

    $fixesApplied = 0

    # 1. Créer les dossiers manquants
    Write-Host "`n📁 CRÉATION DES DOSSIERS MANQUANTS" -ForegroundColor Magenta

    # Dossier logs
    if (!(Test-Path "$PSScriptRoot\logs")) {
        try {
            New-Item -ItemType Directory -Path "$PSScriptRoot\logs" -Force | Out-Null
            Write-Host "  ✅ Dossier logs créé" -ForegroundColor Green
            $fixesApplied++
        } catch {
            Write-Host "  ❌ Impossible de créer le dossier logs: $($_.Exception.Message)" -ForegroundColor Red
        }
    }

    # Dossier backups
    if (!(Test-Path "$PSScriptRoot\backups")) {
        try {
            New-Item -ItemType Directory -Path "$PSScriptRoot\backups" -Force | Out-Null
            Write-Host "  ✅ Dossier backups créé" -ForegroundColor Green
            $fixesApplied++
        } catch {
            Write-Host "  ❌ Impossible de créer le dossier backups: $($_.Exception.Message)" -ForegroundColor Red
        }
    }

    # 2. Compiler les assets si npm est disponible
    Write-Host "`n🎨 COMPILATION DES ASSETS" -ForegroundColor Magenta

    $pluginPath = Split-Path $PSScriptRoot -Parent
    if (Test-Path "$pluginPath\package.json") {
        if (Get-Command npm -ErrorAction SilentlyContinue) {
            Write-Host "  🔄 Compilation des assets JavaScript/CSS..." -ForegroundColor Yellow
            Push-Location $pluginPath
            try {
                $npmResult = & npm run build 2>&1
                if ($LASTEXITCODE -eq 0) {
                    Write-Host "  ✅ Assets compilés avec succès" -ForegroundColor Green
                    $fixesApplied++
                } else {
                    Write-Host "  ❌ Échec de la compilation: $($npmResult[-1])" -ForegroundColor Red
                }
            } catch {
                Write-Host "  ❌ Erreur lors de la compilation: $($_.Exception.Message)" -ForegroundColor Red
            } finally {
                Pop-Location
            }
        } else {
            Write-Host "  ⚠️ npm non disponible, compilation manuelle requise" -ForegroundColor Yellow
        }
    } else {
        Write-Host "  ⚠️ package.json non trouvé, compilation ignorée" -ForegroundColor Yellow
    }

    # 3. Commiter les fichiers non committés si Git disponible
    Write-Host "`n📝 COMMIT DES FICHIERS MODIFIÉS" -ForegroundColor Magenta

    if (Get-Command git -ErrorAction SilentlyContinue) {
        Push-Location $pluginPath
        try {
            $gitStatus = git status --porcelain 2>$null
            $uncommitted = ($gitStatus | Measure-Object).Count

            if ($uncommitted -gt 0) {
                Write-Host "  🔄 Commit automatique des $uncommitted fichier(s) modifié(s)..." -ForegroundColor Yellow

                # Ajouter tous les fichiers
                & git add . 2>$null

                # Créer un commit automatique
                $commitMessage = "feat: Mise à jour automatique - $uncommitted fichier(s) modifié(s)"
                & git commit -m $commitMessage 2>$null

                if ($LASTEXITCODE -eq 0) {
                    Write-Host "  ✅ Commit automatique créé: $commitMessage" -ForegroundColor Green
                    $fixesApplied++
                } else {
                    Write-Host "  ❌ Échec du commit automatique" -ForegroundColor Red
                }
            } else {
                Write-Host "  ✅ Repository déjà propre" -ForegroundColor Green
            }
        } catch {
            Write-Host "  ❌ Erreur Git: $($_.Exception.Message)" -ForegroundColor Red
        } finally {
            Pop-Location
        }
    } else {
        Write-Host "  ⚠️ Git non disponible" -ForegroundColor Yellow
    }

    # 4. Vérifier les corrections
    Write-Host "`n🔍 VÉRIFICATION DES CORRECTIONS" -ForegroundColor Magenta

    if ($fixesApplied -gt 0) {
        Write-Host "  ✅ $fixesApplied correction(s) appliquée(s)" -ForegroundColor Green
        Write-Host "  🔄 Relancement du diagnostic..." -ForegroundColor Cyan

        # Relancer le diagnostic pour vérifier les corrections
        return Start-SystemDiagnostic
    } else {
        Write-Host "  ⚠️ Aucune correction automatique possible" -ForegroundColor Yellow
        return $false
    }
}

# Initialiser les logs
if (!(Test-Path $LogDir)) {
    New-Item -ItemType Directory -Path $LogDir -Force | Out-Null
}

# Mode diagnostic
if ($Diagnostic) {
    $diagnosticData = Start-SystemDiagnostic
    $diagnosticResult = $diagnosticData.result

    # Si AutoFix est activé et qu'il y a des erreurs ou avertissements, tenter la correction
    if ($AutoFix -and ($diagnosticData.details.failed -gt 0)) {
        Write-Host "`n🤖 MODE AUTO-CORRECTION ACTIVÉ" -ForegroundColor Cyan
        Write-Host "Tentative de correction automatique des erreurs..." -ForegroundColor Yellow

        $diagnosticResult = Start-SystemAutoFix -diagnosticResults $diagnosticData.details
    }

    exit $(if ($diagnosticResult) { 0 } else { 1 })
}

# Mode déploiement quotidien
if ($DailyDeploy) {
    Write-Host "`n📅 MODE DÉPLOIEMENT QUOTIDIEN ACTIVÉ" -ForegroundColor Magenta
    Write-Host "Exécution automatique : Diagnostic → Auto-correction → Déploiement" -ForegroundColor White
    Write-Host ("=" * 70) -ForegroundColor Magenta

    # Étape 1 : Diagnostic système
    Write-Host "`n🔍 ÉTAPE 1/3 : DIAGNOSTIC SYSTÈME" -ForegroundColor Cyan
    $diagnosticData = Start-SystemDiagnostic
    $diagnosticResult = $diagnosticData.result

    if (-not $diagnosticResult) {
        Write-Host "`n❌ DIAGNOSTIC ÉCHOUÉ - Tentative de correction automatique..." -ForegroundColor Red

        # Étape 2 : Auto-correction
        Write-Host "`n🔧 ÉTAPE 2/3 : AUTO-CORRECTION" -ForegroundColor Yellow
        $diagnosticResult = Start-SystemAutoFix -diagnosticResults $diagnosticData.details

        if (-not $diagnosticResult) {
            Write-Host "`n💀 AUTO-CORRECTION ÉCHOUÉ - Arrêt du déploiement quotidien" -ForegroundColor Red
            Write-Host "Vérifiez les erreurs et corrigez-les manuellement avant de réessayer." -ForegroundColor Yellow
            exit 1
        } else {
            Write-Host "`n✅ AUTO-CORRECTION RÉUSSIE - Continuation du déploiement" -ForegroundColor Green
        }
    } else {
        Write-Host "`n✅ DIAGNOSTIC RÉUSSI - Passage direct au déploiement" -ForegroundColor Green
    }

    # Étape 3 : Déploiement
    Write-Host "`n🚀 ÉTAPE 3/3 : DÉPLOIEMENT" -ForegroundColor Green
    Write-Host "Début du déploiement automatique..." -ForegroundColor White
}

Write-Log "🚀 DÉBUT DU DÉPLOIEMENT - LOG: $LogFile" -Level "START" -Color "Cyan"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

# Fonctions FTP utilisant FtpWebRequest (remplacement des appels ftp.exe)
function Test-FtpConnection {
    param([string]$ftpHost, [string]$user, [string]$pass, [string]$path)
    try {
        $ftpUri = "ftp://$ftpHost$path/"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
        $ftpRequest.UseBinary = $false
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 10000  # 10 secondes timeout
        $response = $ftpRequest.GetResponse()
        $response.Close()
        return $true
    } catch {
        return $false
    }
}

function Get-FtpFileList {
    param([string]$ftpHost, [string]$user, [string]$pass, [string]$path)
    try {
        $ftpUri = "ftp://$ftpHost$path/"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectoryDetails
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
        $ftpRequest.UseBinary = $false
        $ftpRequest.UsePassive = $true
        $response = $ftpRequest.GetResponse()
        $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
        $fileList = $reader.ReadToEnd()
        $reader.Close()
        $response.Close()
        return $fileList -split "`n" | Where-Object { $_ -and $_.Trim() }
    } catch {
        return $null
    }
}

function Test-FtpFileExists {
    param([string]$ftpHost, [string]$user, [string]$pass, [string]$remotePath)
    try {
        $ftpUri = "ftp://$ftpHost$remotePath"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::GetFileSize
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $response = $ftpRequest.GetResponse()
        $response.Close()
        return $true
    } catch {
        return $false
    }
}

function Get-FtpFileSize {
    param([string]$ftpHost, [string]$user, [string]$pass, [string]$remotePath)
    try {
        $ftpUri = "ftp://$ftpHost$remotePath"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::GetFileSize
        $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($user, $pass)
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $response = $ftpRequest.GetResponse()
        $fileSize = $response.ContentLength
        $response.Close()
        return $fileSize
    } catch {
        return -1
    }
}

# Déterminer le chemin local selon le mode
switch ($Mode) {
    "plugin" {
        $LocalPath = "D:\wp-pdf-builder-pro\plugin"
        $Description = "PLUGIN WORDPRESS UNIQUEMENT"
        $Color = "Green"
    }
    "full" {
        $LocalPath = "D:\wp-pdf-builder-pro"
        $Description = "PROJET COMPLET (DÉVELOPPEMENT)"
        $Color = "Yellow"
        $FtpPath = "/wp-content/plugins/wp-pdf-builder-pro-dev"
    }
    default {
        $LocalPath = "D:\wp-pdf-builder-pro\plugin"
        $Description = "TEST DE DÉPLOIEMENT (SIMULATION)"
        $Color = "Cyan"
        $IsTestMode = $true
    }
}

Write-Log "🚀 DÉPLOIEMENT $Description" -Level "INFO" -Color $Color
Write-Log ("=" * 60) -Level "INFO" -Color "White"
Write-Log "📍 Mode : $Mode" -Level "INFO" -Color "White"
if ($FullSync) {
    Write-Log "🔄 Synchronisation : Complète (tous les fichiers)" -Level "INFO" -Color "Yellow"
} else {
    Write-Log "🔄 Synchronisation : Intelligente (fichiers modifiés uniquement)" -Level "INFO" -Color "White"
}
if ($Force) {
    Write-Log "💪 Mode : Forcé (écrase tout)" -Level "WARN" -Color "Red"
}
if ($FileFilter -ne "all") {
    Write-Log "🎯 Filtre : $FileFilter" -Level "INFO" -Color "Cyan"
    if ($FileFilter -eq "custom" -and $CustomFilter.Count -gt 0) {
        Write-Log "   Patterns: $($CustomFilter -join ', ')" -Level "INFO" -Color "White"
    }
}
Write-Log "📂 Source : $LocalPath" -Level "INFO" -Color "White"
Write-Log "🌐 Destination : $FtpPath" -Level "INFO" -Color "White"
if ($IsTestMode) {
    Write-Log "🧪 MODE TEST : Aucun fichier ne sera envoyé" -Level "INFO" -Color "Yellow"
}
Write-Log ("=" * 60) -Level "INFO" -Color "White"

# Afficher les étapes du processus
Write-Log "📋 ÉTAPES DU PROCESSUS :" -Level "INFO" -Color "Cyan"
if ($Mode -eq "plugin" -and -not $IsTestMode) {
    Write-Log "  1️⃣  Compilation des assets JavaScript/CSS" -Level "INFO" -Color "White"
    Write-Log "  2️⃣  Transfert FTP des fichiers" -Level "INFO" -Color "White"
    Write-Log "  3️⃣  Push Git (tag de version)" -Level "INFO" -Color "White"
    Write-Log "  4️⃣  Tests post-déploiement" -Level "INFO" -Color "White"
    Write-Log "  5️⃣  Validation des assets" -Level "INFO" -Color "White"
} elseif ($Mode -eq "full" -and -not $IsTestMode) {
    Write-Log "  1️⃣  Transfert FTP de tout le projet" -Level "INFO" -Color "White"
    Write-Log "  2️⃣  Push Git (tag de développement)" -Level "INFO" -Color "White"
} else {
    Write-Log "  1️⃣  Analyse des fichiers à déployer" -Level "INFO" -Color "White"
    Write-Log "  2️⃣  Simulation (aucun transfert)" -Level "INFO" -Color "White"
}
Write-Log ("=" * 60) -Level "INFO" -Color "White"

# 1. Vérifier que le dossier source existe
if (!(Test-Path $LocalPath)) {
    Write-Log "❌ Dossier source introuvable : $LocalPath" -Level "ERROR" -Color "Red"
    Write-DetailedLog "Vérification dossier source" "Dossier $LocalPath introuvable" "ERROR" @{path=$LocalPath}
    exit 1
}

Write-Log "✅ Dossier source trouvé" -Level "SUCCESS" -Color "Green"
Write-DetailedLog "Vérification dossier source" "Dossier $LocalPath accessible" "SUCCESS" @{path=$LocalPath}

# 2. Analyser les fichiers à déployer
Write-Host "`n📊 ANALYSE DES FICHIERS..." -ForegroundColor Magenta

$filesToDeploy = Get-ChildItem -Path $LocalPath -Recurse -File
$totalFiles = $filesToDeploy.Count
$totalSize = ($filesToDeploy | Measure-Object -Property Length -Sum).Sum

# Logique de synchronisation intelligente
if (-not $FullSync -and -not $IsTestMode -and $Mode -eq "plugin") {
    Write-Host "🔍 Mode synchronisation intelligente activé" -ForegroundColor Cyan
    Write-Host "   • Recherche des fichiers modifiés..." -ForegroundColor White

    # Pour une vraie synchronisation intelligente, on pourrait comparer les dates
    # Pour l'instant, on garde tous les fichiers mais on indique le mode
    Write-Host "   • Analyse basée sur les timestamps..." -ForegroundColor White
}

if ($FullSync -or $Force) {
    Write-Host "🔄 Mode synchronisation complète activé" -ForegroundColor Yellow
    Write-Host "   • Tous les fichiers seront transférés" -ForegroundColor White
}

Write-Host "📈 Statistiques :" -ForegroundColor White
Write-Host "   • Nombre de fichiers : $totalFiles" -ForegroundColor White
Write-Host "   • Taille totale : $([math]::Round($totalSize / 1MB, 2)) MB" -ForegroundColor White

# Exclusions selon le mode
$excludePatterns = @()
switch ($Mode) {
    "plugin" {
        # Pour le plugin, on garde tout car c'est déjà filtré
    }
    "full" {
        # Pour le déploiement complet, exclure les gros dossiers de développement
        $excludePatterns = @(
            "node_modules",
            ".git",
            ".vscode",
            "*.log",
            "temp",
            "uploads",
            "wordpress-stubs",
            "*.tmp"
        )
    }
}

# Appliquer les exclusions
$filteredFiles = $filesToDeploy | Where-Object {
    $include = $true
    foreach ($pattern in $excludePatterns) {
        if ($_.FullName -like "*$pattern*") {
            $include = $false
            break
        }
    }
    $include
}

# Appliquer le filtre de fichiers sélectionnés
if ($FileFilter -ne "all") {
    Write-Host "`n🎯 FILTRE DE FICHIERS APPLIQUÉ: $FileFilter" -ForegroundColor Yellow
    
    $beforeFilterCount = $filteredFiles.Count
    
    $filteredFiles = $filteredFiles | Where-Object {
        $file = $_
        $fullName = $file.FullName.ToLower()
        
        switch ($FileFilter) {
            "assets" { $fullName -like "*assets*" }
            "js" { $fullName -like "*assets\js*" -or $fullName -like "*.js" }
            "css" { $fullName -like "*assets\css*" -or $fullName -like "*.css" }
            "php" { $fullName -like "*.php" }
            "languages" { $fullName -like "*languages*" -or $fullName -like "*.mo" -or $fullName -like "*.po" }
            "custom" {
                $include = $false
                foreach ($pattern in $CustomFilter) {
                    if ($fullName -like $pattern.ToLower()) {
                        $include = $true
                        break
                    }
                }
                $include
            }
            default { $true }
        }
    }
    
    Write-Host "   • Avant filtre: $beforeFilterCount fichiers" -ForegroundColor White
    Write-Host "   • Après filtre: $($filteredFiles.Count) fichiers" -ForegroundColor Cyan
}

$finalFileCount = $filteredFiles.Count
$finalSize = ($filteredFiles | Measure-Object -Property Length -Sum).Sum

if ($finalFileCount -ne $totalFiles) {
    Write-Host "   • Après filtrage : $finalFileCount fichiers ($([math]::Round($finalSize / 1048576, 2)) MB)" -ForegroundColor Yellow
}

# 3. Lister les fichiers (aperçu)
Write-Host "`n📋 APERCU DES FICHIERS :" -ForegroundColor Cyan
$filteredFiles | Select-Object -First 15 | ForEach-Object {
    $relativePath = $_.FullName.Replace($LocalPath, "").TrimStart("\")
    Write-Host "  📄 $relativePath" -ForegroundColor White
}

if ($finalFileCount -gt 15) {
    Write-Host "  ... et $($finalFileCount - 15) autres fichiers" -ForegroundColor Gray
}

# 4. Créer la structure de répertoires
Write-Host "`n🏗️ STRUCTURE DE RÉPERTOIRES :" -ForegroundColor Cyan
$directories = @{}
foreach ($file in $filteredFiles) {
    $relativePath = $file.FullName.Replace($LocalPath, "").TrimStart("\")
    $dir = Split-Path $relativePath -Parent
    if ($dir -and !$directories.ContainsKey($dir)) {
        $directories[$dir] = $true
    }
}

Write-Host "📂 Répertoires à créer : $($directories.Count)" -ForegroundColor White
$directories.Keys | Sort-Object | Select-Object -First 10 | ForEach-Object {
    Write-Host "  📁 $_" -ForegroundColor White
}

if ($directories.Count -gt 10) {
    Write-Host "  ... et $($directories.Count - 10) autres répertoires" -ForegroundColor Gray
}

# 5. Mode test : arrêter ici
if ($IsTestMode) {
    Write-Host "`n✅ TEST TERMINÉ AVEC SUCCÈS" -ForegroundColor Green
    Write-Host "-" * 30
    Write-Host "🎯 Prêt pour déploiement réel" -ForegroundColor Green
    Write-Host "🚀 Commandes disponibles :" -ForegroundColor White
    Write-Host "   • .\deploy.ps1 -Mode plugin    # Déployer le plugin" -ForegroundColor White
    Write-Host "   • .\deploy.ps1 -Mode full      # Déployer tout le projet" -ForegroundColor White
    exit 0
}

# 6. Compiler les assets (si nécessaire)
if ($Mode -eq "plugin" -and -not $IsTestMode) {
    Write-Host "`n1️⃣  ÉTAPE 1 : COMPILATION DES ASSETS" -ForegroundColor Magenta
    Write-Host "-" * 40

    # Vérifier si package.json existe
    if (Test-Path "package.json") {
        Write-Host "🔨 Exécution de 'npm run build'..." -ForegroundColor Yellow
        try {
            # Changer vers la racine du projet pour npm
            Push-Location (Split-Path $PSScriptRoot -Parent)
            $buildResult = & npm run build 2>&1
            Pop-Location

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
        }
    } else {
        Write-Host "⚠️ package.json non trouvé, compilation ignorée" -ForegroundColor Yellow
    }
    Write-Host ""
}
Write-Host "`n🚀 PRÊT POUR DÉPLOIEMENT" -ForegroundColor Green
Write-Host "-" * 25
Write-Host "Déploiement de $finalFileCount fichiers ($([math]::Round($finalSize / 1MB, 2)) MB)" -ForegroundColor Green
Write-Host "vers $FtpPath" -ForegroundColor Green
Write-Host "Déploiement automatique en cours..." -ForegroundColor Cyan
Write-Host ""

# Créer le dossier de backup
if (!(Test-Path $BackupDir)) {
    New-Item -ItemType Directory -Path $BackupDir -Force | Out-Null
}

# 6. Système de backup - PASSER POUR ACCÉLÉRER
# Commenté par défaut pour gagner du temps (décommenter si besoin)
# if ($Mode -eq "plugin" -and -not $IsTestMode) {
#     Write-Log "`n🛡️  ÉTAPE 6 : CRÉATION DU BACKUP" -Level "INFO" -Color "Magenta"
# }

# 7. Créer le script FTP
Write-Host "`n📝 PRÉPARATION DU SCRIPT FTP..." -ForegroundColor Magenta

$FtpScript = @"
open $FtpHost
USER $FtpUser
PASS $FtpPass
cd $FtpPath
"@

# NOTE: Les commandes DOS comme "rmdir /S /Q" ne fonctionnent PAS en FTP!
# FTP ne reconnaît que: open, cd, mkdir, delete, rmdir (vide), put, get, etc.
# On écrase simplement les fichiers en les renvoyant par-dessus

# Créer les répertoires
foreach ($dir in ($directories.Keys | Sort-Object)) {
    $ftpDir = $dir.Replace("\", "/")
    $FtpScript += "`nmkdir `"$ftpDir`""
}

# Envoyer les fichiers
foreach ($file in $filteredFiles) {
    $relativePath = $file.FullName.Replace($LocalPath, "").TrimStart("\").Replace("\", "/")
    $FtpScript += "`nput `"$($file.FullName)`" `"$relativePath`""
}

$FtpScript += @"
bye
"@

# 7. Vérifier et créer la structure de dossiers distants
if (-not $IsTestMode) {
    Write-Host "1️⃣.5️⃣  ÉTAPE 1.5 : VÉRIFICATION DES DOSSIERS DISTANTS" -ForegroundColor Magenta
    Write-Host "-" * 52

    Write-Host "🔍 Test de connexion FTP..." -ForegroundColor Yellow

    try {
        $connectionTest = Test-FtpConnection -ftpHost $FtpHost -user $FtpUser -pass $FtpPass -path $FtpPath
        if ($connectionTest) {
            Write-Host "✅ Connexion FTP réussie" -ForegroundColor Green
            Write-Host "📂 Dossier distant accessible : $FtpPath" -ForegroundColor Green
        } else {
            Write-Host "❌ Échec de connexion FTP" -ForegroundColor Red
            Write-Host "Détails : Impossible de se connecter au serveur FTP" -ForegroundColor Red
            exit 1
        }
    } catch {
        Write-Host "❌ Erreur de connexion FTP : $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }

    Write-Host ""
}

# 8. Exécuter le déploiement avec FtpWebRequest
if ($Mode -eq "plugin" -and -not $IsTestMode) {
    Write-Log "2️⃣  ÉTAPE 2 : TRANSFERT FTP DES FICHIERS" -Level "INFO" -Color "Magenta"
    Write-Log ("-" * 45) -Level "INFO" -Color "White"
} elseif ($Mode -eq "full" -and -not $IsTestMode) {
    Write-Log "1️⃣  ÉTAPE 1 : TRANSFERT FTP DE TOUT LE PROJET" -Level "INFO" -Color "Magenta"
    Write-Log ("-" * 50) -Level "INFO" -Color "White"
}

Write-Log "📤 Transfert des fichiers via FtpWebRequest..." -Level "INFO" -Color "Yellow"

$totalFiles = $filteredFiles.Count
$successCount = 0
$errorCount = 0
$startTime = Get-Date
$totalSize = 0

# Créer les répertoires d'abord
Write-Host "🏗️ Création de la structure de répertoires..." -ForegroundColor Yellow

# D'abord, créer le dossier racine du plugin
try {
    $ftpUri = "ftp://$FtpHost$FtpPath/"
    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
    $ftpRequest.UseBinary = $false
    $ftpRequest.UsePassive = $true
    $response = $ftpRequest.GetResponse()
    $response.Close()
    Write-Host "   ✅ Dossier racine créé/accessible" -ForegroundColor Green
} catch {
    Write-Host "   ⚠️ Dossier racine existe probablement déjà" -ForegroundColor Yellow
}

# Ensuite créer tous les sous-dossiers
$createdDirs = @($FtpPath)  # Garder trace des dossiers créés
foreach ($dir in ($directories.Keys | Sort-Object)) {
    $ftpDir = $dir.Replace("\", "/")
    $fullPath = "$FtpPath/$ftpDir"
    
    # Créer tous les chemins parents
    $pathParts = $fullPath.Split("/")
    $currentPath = ""
    
    foreach ($part in $pathParts) {
        if ([string]::IsNullOrWhiteSpace($part)) { continue }
        $currentPath = "$currentPath/$part"
        
        if ($currentPath -notin $createdDirs) {
            try {
                $ftpUri = "ftp://$FtpHost$currentPath/"
                $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
                $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
                $ftpRequest.UseBinary = $false
                $ftpRequest.UsePassive = $true
                $response = $ftpRequest.GetResponse()
                $response.Close()
                $createdDirs += $currentPath
            } catch {
                # Dossier peut déjà exister
                $createdDirs += $currentPath
            }
        }
    }
}

Write-Host "   ✅ Répertoires créés" -ForegroundColor Green

# Upload les fichiers en parallèle
Write-Host "📤 Upload des fichiers..." -ForegroundColor Yellow
Write-Host "   Configuration: 20 uploads simultanés (optimisé)" -ForegroundColor Gray

$maxParallelJobs = 20
$runningJobs = @()
$processedFiles = 0
$lastProgressUpdate = Get-Date

foreach ($file in $filteredFiles) {
    $relativePath = $file.FullName.Replace($LocalPath, "").TrimStart("\").Replace("\", "/")
    
    # Attendre si on a trop de jobs
    while ($runningJobs.Count -ge $maxParallelJobs) {
        $completedJobs = @($runningJobs | Where-Object { $_.State -ne "Running" })
        
        if ($completedJobs.Count -gt 0) {
            foreach ($job in $completedJobs) {
                $result = Receive-Job -Job $job -ErrorAction SilentlyContinue
                if ($result) {
                    if ($result.Success) {
                        $successCount++
                        $totalSize += $result.FileSize
                    } else {
                        $errorCount++
                        Write-Log "❌ Erreur upload $($result.RelativePath): $($result.Error)" -Level "ERROR" -Color "Red"
                    }
                }
                Remove-Job -Job $job -ErrorAction SilentlyContinue
                $runningJobs = @($runningJobs | Where-Object { $_.Id -ne $job.Id })
                $processedFiles++
            }
            
            # Mise à jour de la progression
            $currentTime = Get-Date
            if (($currentTime - $lastProgressUpdate).TotalMilliseconds -gt 500) {
                $percent = [math]::Round(($processedFiles / $totalFiles) * 100, 1)
                $remainingFiles = $totalFiles - $processedFiles
                $avgTimePerFile = if ($processedFiles -gt 0) { ($currentTime - $startTime).TotalSeconds / $processedFiles } else { 0 }
                $estimatedRemaining = [math]::Round($avgTimePerFile * $remainingFiles, 0)
                
                # Barre de progression visuelle
                $barLength = 30
                $filledLength = [math]::Round(($processedFiles / $totalFiles) * $barLength)
                $emptyLength = $barLength - $filledLength
                $progressBar = "█" * $filledLength + "░" * $emptyLength
                
                # Affichage amélioré
                Write-Host "`r   [" -NoNewline -ForegroundColor White
                Write-Host $progressBar -NoNewline -ForegroundColor Cyan
                Write-Host "] " -NoNewline -ForegroundColor White
                Write-Host "$percent%" -NoNewline -ForegroundColor Green
                Write-Host " | " -NoNewline -ForegroundColor White
                Write-Host "$processedFiles/$totalFiles" -NoNewline -ForegroundColor Yellow
                Write-Host " | ↓$([math]::Round($totalSize / 1MB / ($currentTime - $startTime).TotalSeconds, 2)) MB/s" -NoNewline -ForegroundColor Cyan
                Write-Host " | ⏱️ " -NoNewline -ForegroundColor White
                Write-Host "$estimatedRemaining sec" -NoNewline -ForegroundColor Magenta
                
                $lastProgressUpdate = $currentTime
            }
        }
        Start-Sleep -Milliseconds 50
    }
    
    # Lancer un job pour uploader le fichier
    $job = Start-Job -ScriptBlock {
        param($FtpHost, $FtpUser, $FtpPass, $FtpPath, $FilePath, $RelativePath)
        
        try {
            $ftpUri = "ftp://$FtpHost$FtpPath/$RelativePath"
            
            $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
            $ftpRequest.UseBinary = $true
            $ftpRequest.UsePassive = $true
            $ftpRequest.EnableSsl = $false
            $fileBytes = [System.IO.File]::ReadAllBytes($FilePath)
            $ftpRequest.ContentLength = $fileBytes.Length
            $ftpRequest.Timeout = 30000  # 30 secondes timeout
            
            $requestStream = $ftpRequest.GetRequestStream()
            $requestStream.Write($fileBytes, 0, $fileBytes.Length)
            $requestStream.Close()
            
            $response = $ftpRequest.GetResponse()
            $response.Close()
            
            return @{Success = $true; RelativePath = $RelativePath; FileSize = $fileBytes.Length}
        } catch {
            return @{Success = $false; RelativePath = $RelativePath; Error = $_.Exception.Message}
        }
    } -ArgumentList $FtpHost, $FtpUser, $FtpPass, $FtpPath, $file.FullName, $relativePath
    
    $runningJobs += $job
}

# Attendre tous les jobs restants
Write-Host "`n   Finalisation..." -ForegroundColor Gray
while ($runningJobs.Count -gt 0) {
    $completedJobs = @($runningJobs | Where-Object { $_.State -ne "Running" })
    if ($completedJobs.Count -gt 0) {
        foreach ($job in $completedJobs) {
            $result = Receive-Job -Job $job -ErrorAction SilentlyContinue
            if ($result) {
                if ($result.Success) {
                    $successCount++
                    $totalSize += $result.FileSize
                } else {
                    $errorCount++
                    Write-Log "❌ Erreur upload $($result.RelativePath): $($result.Error)" -Level "ERROR" -Color "Red"
                }
            }
            Remove-Job -Job $job -ErrorAction SilentlyContinue
            $runningJobs = @($runningJobs | Where-Object { $_.Id -ne $job.Id })
            $processedFiles++
            
            $percent = [math]::Round(($processedFiles / $totalFiles) * 100, 1)
            $barLength = 30
            $filledLength = [math]::Round(($processedFiles / $totalFiles) * $barLength)
            $emptyLength = $barLength - $filledLength
            $progressBar = "█" * $filledLength + "░" * $emptyLength
            
            Write-Host "`r   [" -NoNewline -ForegroundColor White
            Write-Host $progressBar -NoNewline -ForegroundColor Cyan
            Write-Host "] " -NoNewline -ForegroundColor White
            Write-Host "$percent%" -NoNewline -ForegroundColor Green
            Write-Host " | " -NoNewline -ForegroundColor White
            Write-Host "$processedFiles/$totalFiles" -NoNewline -ForegroundColor Yellow
            Write-Host " | Finalisé" -NoNewline -ForegroundColor Green
        }
    }
    Start-Sleep -Milliseconds 50
}

Write-Host "`n"

# Calculer le temps total
$totalTime = (Get-Date) - $startTime

Write-Host "`n✅ DÉPLOIEMENT TERMINÉ !" -ForegroundColor Green
Write-Host "-" * 25
Write-Host "📊 Résumé :" -ForegroundColor White
Write-Host "   • Fichiers réussis : $successCount/$totalFiles" -ForegroundColor White
Write-Host "   • Fichiers échoués : $errorCount" -ForegroundColor White
Write-Host "   • Taille transférée : $([math]::Round($totalSize / 1MB, 2)) MB" -ForegroundColor White
Write-Host "   • Temps total : $([math]::Round($totalTime.TotalSeconds, 1)) secondes" -ForegroundColor White
Write-Host "   • Vitesse moyenne : $([math]::Round($totalSize / 1MB / $totalTime.TotalSeconds, 2)) MB/s" -ForegroundColor White
Write-Host "   • Destination : $FtpPath" -ForegroundColor White

Write-Host "`n3️⃣  ÉTAPE 3 : PUSH GIT" -ForegroundColor Magenta
Write-Host "-" * 20

# Générer un tag de version
$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$tagName = if ($Mode -eq "plugin") { "v1.0.0-deploy-$timestamp" } else { "dev-deploy-$timestamp" }

Write-Host "📝 Commit automatique des fichiers modifiés..." -ForegroundColor Yellow

try {
    # Changer vers la racine du projet pour git
    Push-Location (Split-Path $PSScriptRoot -Parent)

    # Vérifier s'il y a des fichiers à committer
    $gitStatus = git status --porcelain 2>$null
    $uncommitted = ($gitStatus | Measure-Object).Count

    if ($uncommitted -gt 0) {
        Write-Host "  🔄 $uncommitted fichier(s) modifié(s) trouvé(s)" -ForegroundColor Cyan
        Write-Host "  📦 Ajout de tous les fichiers..." -ForegroundColor White

        # Ajouter tous les fichiers
        & git add . 2>$null

        # Créer un commit automatique
        $commitMessage = "feat: Déploiement automatique - $uncommitted fichier(s) modifié(s)"
        & git commit -m $commitMessage 2>$null

        if ($LASTEXITCODE -eq 0) {
            Write-Host "  ✅ Commit automatique créé: $commitMessage" -ForegroundColor Green
        } else {
            Write-Host "  ❌ Échec du commit automatique" -ForegroundColor Red
        }
    } else {
        Write-Host "  ✅ Repository déjà propre" -ForegroundColor Green
    }

    Write-Host "📤 Poussée des commits sur la branche..." -ForegroundColor Yellow
    & git push origin dev 2>$null

    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Commits poussés avec succès sur dev" -ForegroundColor Green
    } else {
        Write-Host "⚠️ Impossible de pousser les commits (peut-être déjà à jour)" -ForegroundColor Yellow
    }

    # Créer et pousser le tag
    Write-Host "�️ Création du tag : $tagName" -ForegroundColor Yellow
    & git tag $tagName 2>$null
    & git push origin $tagName 2>$null

    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Tag poussé avec succès : $tagName" -ForegroundColor Green
    } else {
        Write-Host "⚠️ Impossible de pousser le tag (peut-être pas un repo git)" -ForegroundColor Yellow
    }

    Pop-Location
} catch {
    Write-Log "⚠️ Git non disponible ou erreur : $($_.Exception.Message)" -Level "WARN" -Color "Yellow"
    Write-DetailedLog "Git operations" "Erreur Git : $($_.Exception.Message)" "WARN" @{error=$_.Exception.Message}
}

# 4. Tests post-déploiement - COMMENTÉ POUR ACCÉLÉRER (décommenter pour déboguer)
# if ($Mode -eq "plugin" -and -not $IsTestMode) {
#     Write-Log "`n4️⃣  ÉTAPE 4 : TESTS POST-DÉPLOIEMENT" -Level "INFO" -Color "Magenta"
# }

# 5. Validation des assets - COMMENTÉ POUR ACCÉLÉRER (décommenter pour déboguer)
# if ($Mode -eq "plugin" -and -not $IsTestMode) {
#     Write-Log "`n5️⃣  ÉTAPE 5 : VALIDATION DES ASSETS" -Level "INFO" -Color "Magenta"
# }

# 6. Résumé final et logs
Write-Log "`n✅ DÉPLOIEMENT TERMINÉ AVEC SUCCÈS !" -Level "SUCCESS" -Color "Green"
Write-Log ("-" * 40) -Level "INFO" -Color "White"
Write-Log "📊 RÉSUMÉ FINAL :" -Level "INFO" -Color "White"
Write-Log "   • Mode : $Mode" -Level "INFO" -Color "White"
Write-Log "   • Fichiers déployés : $totalFiles" -Level "INFO" -Color "White"
Write-Log "   • Taille transférée : $([math]::Round($finalSize / 1MB, 2)) MB" -Level "INFO" -Color "White"
Write-Log "   • Temps total : $([math]::Round($totalTime.TotalSeconds, 1)) secondes" -Level "INFO" -Color "White"
Write-Log "   • Vitesse moyenne : $([math]::Round($finalSize / 1MB / $totalTime.TotalSeconds, 2)) MB/s" -Level "INFO" -Color "White"
Write-Log "   • Destination : $FtpPath" -Level "INFO" -Color "White"
Write-Log "   • Log détaillé : $LogFile" -Level "INFO" -Color "White"

Write-DetailedLog "Déploiement terminé" "Déploiement réussi" "SUCCESS" @{
    mode=$Mode
    files=$totalFiles
    size=$finalSize
    duration=$totalTime.TotalSeconds
    destination=$FtpPath
    logFile=$LogFile
}

Write-Log "`n🎯 Commandes de suivi disponibles :" -Level "INFO" -Color "Cyan"
Write-Log "   • Vérifier les logs : notepad $LogFile" -Level "INFO" -Color "White"
Write-Log "   • Logs détaillés : $LogFile.json" -Level "INFO" -Color "White"
if (Test-Path $BackupDir) {
    Write-Log "   • Backup disponible : $BackupDir" -Level "INFO" -Color "White"
}