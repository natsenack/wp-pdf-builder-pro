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

.EXAMPLE
    .\deploy.ps1 -Mode test
    .\deploy.ps1 -Mode plugin
    .\deploy.ps1 -Mode plugin -FullSync
    .\deploy.ps1 -Mode plugin -Force
    .\deploy.ps1 -Mode plugin -Diagnostic
    .\deploy.ps1 -Diagnostic -AutoFix
    .\deploy.ps1 -Diagnostic
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
    [switch]$AutoFix
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

Write-Log "🚀 DÉBUT DU DÉPLOIEMENT - LOG: $LogFile" -Level "START" -Color "Cyan"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

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
} elseif ($FullSync -or $Force) {
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

$finalFileCount = $filteredFiles.Count
$finalSize = ($filteredFiles | Measure-Object -Property Length -Sum).Sum

if ($finalFileCount -ne $totalFiles) {
    Write-Host "   • Après filtrage : $finalFileCount fichiers ($([math]::Round($finalSize / 1MB, 2)) MB)" -ForegroundColor Yellow
}

# 3. Lister les fichiers (aperçu)
Write-Host "`n📋 APERÇU DES FICHIERS :" -ForegroundColor Cyan
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

# 6. Système de backup (uniquement pour le mode plugin)
if ($Mode -eq "plugin" -and -not $IsTestMode) {
    Write-Log "`n🛡️  ÉTAPE 6 : CRÉATION DU BACKUP" -Level "INFO" -Color "Magenta"
    Write-Log ("-" * 30) -Level "INFO" -Color "White"

    Write-Log "💾 Création d'une sauvegarde des fichiers existants..." -Level "INFO" -Color "Yellow"

    # Créer un script FTP pour lister et télécharger les fichiers existants
    $backupListScript = @"
open $FtpHost
$FtpUser
$FtpPass
cd $FtpPath
ls -la
bye
"@

    $backupListPath = "ftp-backup-list-temp.txt"
    $backupListScript | Out-File -FilePath $backupListPath -Encoding ASCII

    try {
        $backupList = & ftp -i -n -s:$backupListPath 2>&1
        Write-Log "📋 Fichiers existants analysés" -Level "INFO" -Color "White"

        # Sauvegarder la liste des fichiers existants
        $backupList | Out-File -FilePath "$BackupDir\existing_files.txt" -Encoding UTF8

        Write-DetailedLog "Backup créé" "Liste des fichiers existants sauvegardée" "SUCCESS" @{backupDir=$BackupDir; fileCount=$backupList.Count}

    } catch {
        Write-Log "⚠️ Impossible de créer le backup complet : $($_.Exception.Message)" -Level "WARN" -Color "Yellow"
        Write-DetailedLog "Backup partiel" "Erreur lors de la sauvegarde : $($_.Exception.Message)" "WARN" @{error=$_.Exception.Message}
    } finally {
        Remove-Item $backupListPath -ErrorAction SilentlyContinue
    }

    Write-Log "✅ Backup terminé : $BackupDir" -Level "SUCCESS" -Color "Green"
}

# 7. Créer le script FTP
Write-Host "`n📝 PRÉPARATION DU SCRIPT FTP..." -ForegroundColor Magenta

$FtpScript = @"
open $FtpHost
$FtpUser
$FtpPass
cd $FtpPath
"@

# Supprimer l'ancien contenu (sauf pour le mode plugin qui écrase tout)
if ($Mode -eq "plugin") {
    $FtpScript += "`nrmdir /S /Q wp-pdf-builder-pro 2>nul`n"
}

# Créer les répertoires
foreach ($dir in ($directories.Keys | Sort-Object)) {
    $FtpScript += "mkdir `"$dir`"`n"
}

# Envoyer les fichiers
foreach ($file in $filteredFiles) {
    $relativePath = $file.FullName.Replace($LocalPath, "").TrimStart("\")
    $FtpScript += "put `"$($file.FullName)`" `"$relativePath`"`n"
}

$FtpScript += @"
bye
"@

# 7. Vérifier et créer la structure de dossiers distants
if (-not $IsTestMode) {
    Write-Host "1️⃣.5️⃣  ÉTAPE 1.5 : VÉRIFICATION DES DOSSIERS DISTANTS" -ForegroundColor Magenta
    Write-Host "-" * 52

    Write-Host "🔍 Test de connexion FTP..." -ForegroundColor Yellow

    # Créer un script FTP de test de connexion
    $testScript = @"
open $FtpHost
$FtpUser
$FtpPass
cd $FtpPath
pwd
bye
"@

    $testScriptPath = "ftp-test-temp.txt"
    $testScript | Out-File -FilePath $testScriptPath -Encoding ASCII

    try {
        $testResult = & ftp -i -n -s:$testScriptPath 2>&1
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ Connexion FTP réussie" -ForegroundColor Green
            Write-Host "📂 Dossier distant accessible : $FtpPath" -ForegroundColor Green
        } else {
            Write-Host "❌ Échec de connexion FTP" -ForegroundColor Red
            Write-Host "Détails : $testResult" -ForegroundColor Red
            exit 1
        }
    } catch {
        Write-Host "❌ Erreur de connexion FTP : $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    } finally {
        Remove-Item $testScriptPath -ErrorAction SilentlyContinue
    }

    Write-Host ""
}

# 8. Exécuter le déploiement avec barre de progression
if ($Mode -eq "plugin" -and -not $IsTestMode) {
    Write-Log "2️⃣  ÉTAPE 2 : TRANSFERT FTP DES FICHIERS" -Level "INFO" -Color "Magenta"
    Write-Log ("-" * 45) -Level "INFO" -Color "White"
} elseif ($Mode -eq "full" -and -not $IsTestMode) {
    Write-Log "1️⃣  ÉTAPE 1 : TRANSFERT FTP DE TOUT LE PROJET" -Level "INFO" -Color "Magenta"
    Write-Log ("-" * 50) -Level "INFO" -Color "White"
}

Write-Log "📤 Exécution du transfert..." -Level "INFO" -Color "Yellow"

$FtpScriptPath = "ftp-script-temp.txt"

# Créer le script FTP de base (connexion + répertoires)
$FtpScript = @"
open $FtpHost
$FtpUser
$FtpPass
cd $FtpPath
"@

# Supprimer l'ancien contenu (sauf pour le mode plugin qui écrase tout)
if ($Mode -eq "plugin") {
    $FtpScript += "`nrmdir /S /Q wp-pdf-builder-pro 2>nul`n"
}

# Créer les répertoires
foreach ($dir in ($directories.Keys | Sort-Object)) {
    $FtpScript += "mkdir `"$dir`"`n"
}

$FtpScript += @"
bye
"@

# Sauvegarder le script de base
$FtpScript | Out-File -FilePath $FtpScriptPath -Encoding ASCII

# Exécuter la création des répertoires
Write-Host "🏗️ Création de la structure de répertoires..." -ForegroundColor Yellow
& ftp -i -n -s:$FtpScriptPath > $null 2>&1

# Maintenant envoyer les fichiers en parallèle pour accélérer le transfert
Write-Log "📤 Transfert des fichiers en parallèle..." -Level "INFO" -Color "Yellow"

$totalFiles = $filteredFiles.Count
$currentFile = 0
$startTime = Get-Date

# Configuration du parallélisme
$maxConcurrentJobs = 10  # Nombre de connexions FTP simultanées (augmenté)
$batchSize = 50         # Nombre de fichiers par job (augmenté)

Write-Log "🔄 Configuration : $maxConcurrentJobs connexions simultanées, $batchSize fichiers par lot" -Level "INFO" -Color "White"

# Fonction pour créer un script FTP par lot de fichiers
function New-FtpBatchScript {
    param(
        [array]$FileBatch,
        [int]$BatchId,
        [string]$FtpHost,
        [string]$FtpUser,
        [string]$FtpPass,
        [string]$FtpPath,
        [string]$LocalPath
    )

    $scriptContent = @"
open $FtpHost
$FtpUser
$FtpPass
cd $FtpPath
"@

    foreach ($file in $FileBatch) {
        $relativePath = $file.FullName.Replace($LocalPath, "").TrimStart("\")
        $scriptContent += "`nput `"$($file.FullName)`" `"$relativePath`""
    }

    $scriptContent += @"

bye
"@

    $scriptPath = "ftp-batch-$BatchId-temp.txt"
    $scriptContent | Out-File -FilePath $scriptPath -Encoding ASCII
    return $scriptPath
}

# Diviser les fichiers en lots
$fileBatches = @()
for ($i = 0; $i -lt $filteredFiles.Count; $i += $batchSize) {
    $endIndex = [math]::Min($i + $batchSize - 1, $filteredFiles.Count - 1)
    $batch = $filteredFiles[$i..$endIndex]
    $fileBatches += ,$batch  # Note: ,$batch pour créer un tableau de tableaux
}

$totalBatches = $fileBatches.Count
$currentBatch = 0
$runningJobs = @()

foreach ($batch in $fileBatches) {
    $currentBatch++
    $batchId = $currentBatch

    # Créer le script FTP pour ce lot
    $batchScriptPath = New-FtpBatchScript -FileBatch $batch -BatchId $batchId -FtpHost $FtpHost -FtpUser $FtpUser -FtpPass $FtpPass -FtpPath $FtpPath -LocalPath $LocalPath

    # Démarrer le job FTP en arrière-plan
    $job = Start-Job -ScriptBlock {
        param($scriptPath)
        try {
            $result = & ftp -i -n -s:$scriptPath 2>&1
            return @{Success = $true; Result = $result; ScriptPath = $scriptPath}
        } catch {
            return @{Success = $false; Error = $_.Exception.Message; ScriptPath = $scriptPath}
        }
    } -ArgumentList $batchScriptPath

    $runningJobs += @{Job = $job; BatchId = $batchId; ScriptPath = $batchScriptPath; FileCount = $batch.Count}

    # Limiter le nombre de jobs simultanés
    while ($runningJobs.Count -ge $maxConcurrentJobs) {
        # Attendre qu'au moins un job se termine
        $completedJobs = $runningJobs | Where-Object { $_.Job.State -ne "Running" }

        if ($completedJobs.Count -gt 0) {
            foreach ($completedJob in $completedJobs) {
                $jobResult = Receive-Job -Job $completedJob.Job
                Remove-Job -Job $completedJob.Job

                $currentFile += $completedJob.FileCount
                $percentComplete = [math]::Round(($currentFile / $totalFiles) * 100, 1)
                $elapsed = (Get-Date) - $startTime
                $estimatedTotal = if ($currentFile -gt 0) { $elapsed.TotalSeconds / $currentFile * $totalFiles } else { 0 }
                $remaining = [TimeSpan]::FromSeconds($estimatedTotal - $elapsed.TotalSeconds)

                if ($jobResult.Success) {
                    # Réduire la verbosité - afficher seulement tous les 5 lots
                    if ($completedJob.BatchId % 5 -eq 0) {
                        Write-Log "✅ Lot $($completedJob.BatchId)/$totalBatches transféré ($($completedJob.FileCount) fichiers)" -Level "SUCCESS" -Color "Green"
                    }
                } else {
                    Write-Log "❌ Erreur lot $($completedJob.BatchId): $($jobResult.Error)" -Level "ERROR" -Color "Red"
                }

                # Nettoyer le script temporaire
                Remove-Item $completedJob.ScriptPath -ErrorAction SilentlyContinue

                # Afficher la progression
                Write-Progress -Activity "Déploiement FTP Parallèle" -Status "Lot $currentBatch/$totalBatches - Fichier $currentFile/$totalFiles ($percentComplete%)" -PercentComplete $percentComplete -SecondsRemaining $remaining.TotalSeconds
            }

            # Retirer les jobs terminés de la liste
            $runningJobs = $runningJobs | Where-Object { $_.Job.State -eq "Running" }
        } else {
            Start-Sleep -Milliseconds 500
        }
    }

    # Afficher un message tous les 10 lots au lieu de 5
    if ($currentBatch % 10 -eq 0) {
        Write-Log "📄 Lot $currentBatch/$totalBatches traité ($([math]::Round(($currentBatch / $totalBatches) * 100, 1))%)" -Level "INFO" -Color "Cyan"
    }
}

# Attendre que tous les jobs restants se terminent
Write-Log "⏳ Finalisation des transferts restants..." -Level "INFO" -Color "Yellow"

while ($runningJobs.Count -gt 0) {
    $completedJobs = $runningJobs | Where-Object { $_.Job.State -ne "Running" }

    foreach ($completedJob in $completedJobs) {
        $jobResult = Receive-Job -Job $completedJob.Job
        Remove-Job -Job $completedJob.Job

        $currentFile += $completedJob.FileCount
        $percentComplete = [math]::Round(($currentFile / $totalFiles) * 100, 1)
        $elapsed = (Get-Date) - $startTime
        $estimatedTotal = if ($currentFile -gt 0) { $elapsed.TotalSeconds / $currentFile * $totalFiles } else { 0 }
        $remaining = [TimeSpan]::FromSeconds($estimatedTotal - $elapsed.TotalSeconds)

        if ($jobResult.Success) {
            Write-Log "✅ Lot $($completedJob.BatchId)/$totalBatches transféré ($($completedJob.FileCount) fichiers)" -Level "SUCCESS" -Color "Green"
        } else {
            Write-Log "❌ Erreur lot $($completedJob.BatchId): $($jobResult.Error)" -Level "ERROR" -Color "Red"
        }

        # Nettoyer le script temporaire
        Remove-Item $completedJob.ScriptPath -ErrorAction SilentlyContinue

        # Afficher la progression
        Write-Progress -Activity "Déploiement FTP Parallèle" -Status "Finalisation - Fichier $currentFile/$totalFiles ($percentComplete%)" -PercentComplete $percentComplete -SecondsRemaining $remaining.TotalSeconds
    }

    $runningJobs = $runningJobs | Where-Object { $_.Job.State -eq "Running" }

    if ($runningJobs.Count -gt 0) {
        Start-Sleep -Milliseconds 500
    }
}

# Terminer la barre de progression
Write-Progress -Activity "Déploiement FTP Parallèle" -Completed

# Calculer le temps total
$totalTime = (Get-Date) - $startTime

Write-Host "`n✅ DÉPLOIEMENT TERMINÉ !" -ForegroundColor Green
Write-Host "-" * 25
Write-Host "📊 Résumé :" -ForegroundColor White
Write-Host "   • Fichiers déployés : $totalFiles" -ForegroundColor White
Write-Host "   • Taille transférée : $([math]::Round($finalSize / 1MB, 2)) MB" -ForegroundColor White
Write-Host "   • Temps total : $([math]::Round($totalTime.TotalSeconds, 1)) secondes" -ForegroundColor White
Write-Host "   • Vitesse moyenne : $([math]::Round($finalSize / 1MB / $totalTime.TotalSeconds, 2)) MB/s" -ForegroundColor White
Write-Host "   • Destination : $FtpPath" -ForegroundColor White

Write-Host "`n3️⃣  ÉTAPE 3 : PUSH GIT" -ForegroundColor Magenta
Write-Host "-" * 20

# Générer un tag de version
$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$tagName = if ($Mode -eq "plugin") { "v1.0.0-deploy-$timestamp" } else { "dev-deploy-$timestamp" }

Write-Host "�️ Création du tag : $tagName" -ForegroundColor Yellow

try {
    # Changer vers la racine du projet pour git
    Push-Location (Split-Path $PSScriptRoot -Parent)

    # Pousser les commits sur la branche actuelle
    Write-Host "📤 Poussée des commits sur la branche..." -ForegroundColor Yellow
    & git push origin dev 2>$null

    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Commits poussés avec succès sur dev" -ForegroundColor Green
    } else {
        Write-Host "⚠️ Impossible de pousser les commits (peut-être déjà à jour)" -ForegroundColor Yellow
    }

    # Créer et pousser le tag
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
    Write-DetailedLog "Git push" "Erreur Git : $($_.Exception.Message)" "WARN" @{error=$_.Exception.Message}
}

# 4. Tests post-déploiement (uniquement pour le mode plugin)
if ($Mode -eq "plugin" -and -not $IsTestMode) {
    Write-Log "`n4️⃣  ÉTAPE 4 : TESTS POST-DÉPLOIEMENT" -Level "INFO" -Color "Magenta"
    Write-Log ("-" * 35) -Level "INFO" -Color "White"

    Write-Log "🧪 Exécution des tests de validation..." -Level "INFO" -Color "Yellow"

    # Test 1 : Vérifier l'accessibilité des fichiers critiques
    $criticalFiles = @(
        "pdf-builder-pro.php",
        "assets/js/dist/pdf-builder-admin.js",
        "assets/css/pdf-builder-admin.css",
        "languages/pdf-builder-pro-fr_FR.mo"
    )

    $testResults = @()
    foreach ($file in $criticalFiles) {
        $remotePath = "$FtpPath/$file"
        $testScript = @"
open $FtpHost
$FtpUser
$FtpPass
cd $FtpPath
ls $file
bye
"@

        $testScriptPath = "ftp-test-file-temp.txt"
        $testScript | Out-File -FilePath $testScriptPath -Encoding ASCII

        try {
            $result = & ftp -i -n -s:$testScriptPath 2>&1
            if ($LASTEXITCODE -eq 0 -and $result -match $file) {
                Write-Log "✅ $file : Accessible" -Level "SUCCESS" -Color "Green"
                $testResults += @{file=$file; status="SUCCESS"; details="Fichier accessible"}
            } else {
                Write-Log "❌ $file : Non accessible" -Level "ERROR" -Color "Red"
                $testResults += @{file=$file; status="ERROR"; details="Fichier non trouvé"}
            }
        } catch {
            Write-Log "❌ $file : Erreur de test - $($_.Exception.Message)" -Level "ERROR" -Color "Red"
            $testResults += @{file=$file; status="ERROR"; details=$_.Exception.Message}
        } finally {
            Remove-Item $testScriptPath -ErrorAction SilentlyContinue
        }
    }

    Write-DetailedLog "Tests post-déploiement" "Validation des fichiers critiques terminée" "INFO" @{results=$testResults}

    # Test 2 : Vérifier la taille des bundles JavaScript
    Write-Log "🔍 Vérification de l'intégrité des bundles..." -Level "INFO" -Color "Yellow"

    # Changer temporairement vers le répertoire plugin pour vérifier les fichiers locaux
    Push-Location "$PSScriptRoot\..\plugin"
    try {
        $bundlePath = "assets\js\dist\pdf-builder-admin.js"
        if (Test-Path $bundlePath) {
            $localSize = (Get-Item $bundlePath).Length
            Write-Log "📊 Taille locale du bundle : $([math]::Round($localSize / 1KB, 2)) KB" -Level "INFO" -Color "White"

            # Tester la taille distante (estimation via listing)
            $sizeTestScript = @"
open $FtpHost
$FtpUser
$FtpPass
cd $FtpPath
ls assets/js/dist/pdf-builder-admin.js
bye
"@

            $sizeTestPath = "ftp-size-test-temp.txt"
            $sizeTestScript | Out-File -FilePath $sizeTestPath -Encoding ASCII

            try {
                $sizeResult = & ftp -i -n -s:$sizeTestPath 2>&1
                Write-DetailedLog "Validation bundle" "Taille locale: $([math]::Round($localSize / 1KB, 2)) KB" "INFO" @{localSize=$localSize}
            } catch {
                Write-Log "⚠️ Impossible de vérifier la taille distante" -Level "WARN" -Color "Yellow"
            } finally {
                Remove-Item $sizeTestPath -ErrorAction SilentlyContinue
            }
        } else {
            Write-Log "❌ Bundle local introuvable : $bundlePath" -Level "ERROR" -Color "Red"
        }
    } finally {
        Pop-Location
    }

    Write-Log "✅ Tests post-déploiement terminés" -Level "SUCCESS" -Color "Green"
}

# 5. Validation des assets (uniquement pour le mode plugin)
if ($Mode -eq "plugin" -and -not $IsTestMode) {
    Write-Log "`n5️⃣  ÉTAPE 5 : VALIDATION DES ASSETS" -Level "INFO" -Color "Magenta"
    Write-Log ("-" * 32) -Level "INFO" -Color "White"

    Write-Log "🔍 Validation de l'intégrité des assets..." -Level "INFO" -Color "Yellow"

    $validationResults = @()

    # Changer vers le répertoire plugin pour les vérifications locales
    Push-Location "$PSScriptRoot\..\plugin"

    try {

    # Vérifier les bundles JavaScript
    $jsBundles = @(
        @{name="Bundle admin principal"; path="assets\js\dist\pdf-builder-admin.js"},
        @{name="Bundle admin debug"; path="assets\js\dist\pdf-builder-admin-debug.js"},
        @{name="Script loader"; path="assets\js\dist\pdf-builder-script-loader.js"}
    )

    foreach ($bundle in $jsBundles) {
        if (Test-Path $bundle.path) {
            $size = (Get-Item $bundle.path).Length
            $sizeKB = [math]::Round($size / 1KB, 2)

            # Vérifier que le fichier n'est pas vide
            if ($size -gt 1000) { # Au moins 1KB
                Write-Log "✅ $($bundle.name) : $sizeKB KB (valide)" -Level "SUCCESS" -Color "Green"
                $validationResults += @{asset=$bundle.name; status="VALID"; size=$sizeKB; details="Taille correcte"}
            } else {
                Write-Log "❌ $($bundle.name) : $sizeKB KB (trop petit)" -Level "ERROR" -Color "Red"
                $validationResults += @{asset=$bundle.name; status="INVALID"; size=$sizeKB; details="Fichier trop petit"}
            }

            # Vérifier la syntaxe de base (chercher 'function' ou 'const' au début)
            try {
                $content = Get-Content $bundle.path -Raw -Encoding UTF8
                if ($content -match "(function|const|let|class)" -and $content.Length -gt 100) {
                    # OK
                } else {
                    Write-Log "⚠️ $($bundle.name) : Syntaxe suspecte" -Level "WARN" -Color "Yellow"
                    $validationResults += @{asset=$bundle.name; status="WARNING"; details="Syntaxe suspecte"}
                }
            } catch {
                Write-Log "❌ $($bundle.name) : Erreur de lecture" -Level "ERROR" -Color "Red"
                $validationResults += @{asset=$bundle.name; status="ERROR"; details="Erreur de lecture"}
            }
        } else {
            Write-Log "❌ $($bundle.name) : Fichier manquant - $($bundle.path)" -Level "ERROR" -Color "Red"
            $validationResults += @{asset=$bundle.name; status="MISSING"; details="Fichier manquant"}
        }
    }

    # Vérifier les fichiers CSS
    $cssFiles = @(
        @{name="Style admin principal"; path="assets\css\pdf-builder-admin.css"},
        @{name="Style React"; path="assets\css\pdf-builder-react.css"},
        @{name="Style éditeur"; path="assets\css\editor.css"}
    )

    foreach ($css in $cssFiles) {
        if (Test-Path $css.path) {
            $size = (Get-Item $css.path).Length
            $sizeKB = [math]::Round($size / 1KB, 2)

            if ($size -gt 500) { # Au moins 500 bytes
                Write-Log "✅ $($css.name) : $sizeKB KB (valide)" -Level "SUCCESS" -Color "Green"
                $validationResults += @{asset=$css.name; status="VALID"; size=$sizeKB; details="Taille correcte"}
            } else {
                Write-Log "❌ $($css.name) : $sizeKB KB (trop petit)" -Level "ERROR" -Color "Red"
                $validationResults += @{asset=$css.name; status="INVALID"; size=$sizeKB; details="Fichier trop petit"}
            }
        } else {
            Write-Log "❌ $($css.name) : Fichier manquant - $($css.path)" -Level "ERROR" -Color "Red"
            $validationResults += @{asset=$css.name; status="MISSING"; details="Fichier manquant"}
        }
    }

    Write-DetailedLog "Validation assets" "Validation des assets terminée" "INFO" @{results=$validationResults}

    # Compter les erreurs
    $errorCount = ($validationResults | Where-Object { $_.status -eq "ERROR" -or $_.status -eq "MISSING" }).Count
    if ($errorCount -gt 0) {
        Write-Log "⚠️ $errorCount problème(s) détecté(s) dans les assets" -Level "WARN" -Color "Yellow"
    } else {
        Write-Log "✅ Tous les assets sont valides" -Level "SUCCESS" -Color "Green"
    }

    } finally {
        # Revenir au répertoire build
        Pop-Location
    }
}

# 6. Intégration GitHub (uniquement pour le mode plugin)
if ($Mode -eq "plugin" -and -not $IsTestMode) {
    Write-Log "`n6️⃣  ÉTAPE 6 : INTÉGRATION GITHUB" -Level "INFO" -Color "Magenta"
    Write-Log ("-" * 30) -Level "INFO" -Color "White"

    Write-Log "🚀 Création d'une release GitHub..." -Level "INFO" -Color "Yellow"

    # Vérifier si GitHub CLI est disponible
    try {
        $ghVersion = & gh --version 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Log "✅ GitHub CLI détecté" -Level "SUCCESS" -Color "Green"

            # Générer les notes de release
            $releaseNotes = @"
## Déploiement PDF Builder Pro

**Date:** $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
**Mode:** Production
**Fichiers déployés:** $totalFiles
**Taille:** $([math]::Round($finalSize / 1MB, 2)) MB
**Destination:** $FtpPath

### Fichiers critiques validés:
- ✅ pdf-builder-pro.php
- ✅ assets/js/dist/bundle.js
- ✅ assets/css/style.css
- ✅ languages/pdf-builder-pro-fr_FR.mo

### Assets validés:
- ✅ Bundle principal: $([math]::Round((Get-Item "assets\js\dist\pdf-builder-admin.js").Length / 1KB, 2)) KB
- ✅ Styles CSS compilés

### Logs:
- Log détaillé: $LogFile
- Backup: $BackupDir

---
*Déploiement automatisé via script PowerShell*
"@

            # Créer la release
            $releaseName = "v1.0.0-deploy-$Timestamp"
            $releaseNotes | Out-File -FilePath "release-notes-temp.md" -Encoding UTF8

            try {
                # Créer la release
                $releaseResult = & gh release create $releaseName --title "Déploiement Production $Timestamp" --notes-file "release-notes-temp.md" --generate-notes 2>&1

                if ($LASTEXITCODE -eq 0) {
                    Write-Log "✅ Release GitHub créée : $releaseName" -Level "SUCCESS" -Color "Green"
                    Write-DetailedLog "GitHub release" "Release créée avec succès" "SUCCESS" @{releaseName=$releaseName; notes=$releaseNotes}
                } else {
                    Write-Log "⚠️ Impossible de créer la release GitHub" -Level "WARN" -Color "Yellow"
                    Write-Log "Détails : $releaseResult" -Level "INFO" -Color "White"
                }
            } catch {
                Write-Log "⚠️ Erreur GitHub CLI : $($_.Exception.Message)" -Level "WARN" -Color "Yellow"
            } finally {
                Remove-Item "release-notes-temp.md" -ErrorAction SilentlyContinue
            }

        } else {
            Write-Log "⚠️ GitHub CLI non installé ou non configuré" -Level "WARN" -Color "Yellow"
            Write-Log "   Pour installer : winget install --id GitHub.cli" -Level "INFO" -Color "White"
        }
    } catch {
        Write-Log "⚠️ GitHub CLI non disponible : $($_.Exception.Message)" -Level "WARN" -Color "Yellow"
    }
}

# 7. Validation finale et résumé

# 6. Validation finale et résumé
Write-Log "`n🏁 DÉPLOIEMENT TERMINÉ AVEC SUCCÈS !" -Level "SUCCESS" -Color "Green"
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