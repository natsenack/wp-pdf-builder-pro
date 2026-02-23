# Script de construction de ZIP du plugin PDF Builder Pro
# Génère un ZIP versionné du plugin pour upload vers EDD
# Usage: .\build-plugin-zip.ps1

[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
$ErrorActionPreference = "Stop"

# Détecter le répertoire de travail
$WorkingDir = Split-Path $PSScriptRoot -Parent
$PluginDir = Join-Path $WorkingDir "plugin"
$BuildDir = $PSScriptRoot
$DistDir = Join-Path $WorkingDir "dist"

# Vérifier le répertoire plugin
if (!(Test-Path $PluginDir)) {
    Write-Host "❌ Répertoire plugin introuvable: $PluginDir" -ForegroundColor Red
    exit 1
}

# Créer le répertoire dist s'il n'existe pas
if (!(Test-Path $DistDir)) {
    New-Item -ItemType Directory -Path $DistDir -Force | Out-Null
    Write-Host "📁 Répertoire dist créé: $DistDir" -ForegroundColor Cyan
}

# ============================================================================
# Fonction pour extraire la version du plugin
# ============================================================================
function Get-PluginVersion {
    param([string]$PluginFile)
    
    if (!(Test-Path $PluginFile)) {
        Write-Host "❌ Fichier plugin introuvable: $PluginFile" -ForegroundColor Red
        return $null
    }
    
    $content = Get-Content $PluginFile -Raw
    
    # Chercher le header Version:
    if ($content -match "Version:\s*([0-9.]+)") {
        $version = $matches[1]
        Write-Host "✅ Version détectée: $version" -ForegroundColor Green
        return $version
    }
    
    Write-Host "❌ Version non trouvée dans $PluginFile" -ForegroundColor Red
    return $null
}

# ============================================================================
# Fonction pour construire un ZIP du plugin
# ============================================================================
function Build-PluginZip {
    param(
        [string]$PluginDir,
        [string]$OutputDir,
        [string]$Version
    )
    
    if (!$Version) {
        Write-Host "❌ Pas de version fournie" -ForegroundColor Red
        return $false
    }
    
    # Chemins
    $ZipPath = Join-Path $OutputDir "pdf-builder-pro-$Version.zip"
    $TempDir = Join-Path $OutputDir ".temp"
    $PluginTempDir = Join-Path $TempDir "pdf-builder-pro"

    # Créer répertoire temporaire
    if (Test-Path $TempDir) {
        Remove-Item $TempDir -Recurse -Force
    }
    New-Item -ItemType Directory -Path $PluginTempDir -Force | Out-Null
    
    try {
        Write-Host "`n📦 Construction du ZIP: pdf-builder-pro-$Version.zip" -ForegroundColor Cyan
        
        # Copier tous les fichiers du plugin (sauf quelques exceptions)
        $excludeItems = @(".git", "node_modules", ".gitignore", ".env", "test-*.php", "README.md")
        
        Write-Host "   Copie des fichiers..." -ForegroundColor Gray
        Get-ChildItem -Path $PluginDir -Recurse -Force | ForEach-Object {
            $relPath = $_.FullName.Substring($PluginDir.Length + 1)
            $skip = $false
            
            # Vérifier les exclusions
            foreach ($exclude in $excludeItems) {
                if ($relPath -match "^$([regex]::Escape($exclude))" -or $_.Name -eq $exclude) {
                    $skip = $true
                    break
                }
            }
            
            if ($skip) {
                return # continue
            }
            
            $destPath = Join-Path $PluginTempDir $relPath
            
            if ($_.PSIsContainer) {
                # Créer le répertoire destination
                if (!(Test-Path $destPath)) {
                    New-Item -ItemType Directory -Path $destPath -Force | Out-Null
                }
            } else {
                # Copier le fichier
                $destDir = Split-Path $destPath
                if (!(Test-Path $destDir)) {
                    New-Item -ItemType Directory -Path $destDir -Force | Out-Null
                }
                Copy-Item $_.FullName -Destination $destPath -Force
            }
        }
        
        # Compter les fichiers copiés
        $fileCount = (Get-ChildItem -Path $PluginTempDir -Recurse -File).Count
        Write-Host "   Fichiers copiés: $fileCount" -ForegroundColor Green
        
        # Créer le ZIP
        Write-Host "   Création du ZIP..." -ForegroundColor Gray
        
        # Utiliser la compression compression .NET
        Add-Type -AssemblyName System.IO.Compression.FileSystem
        
        if (Test-Path $ZipPath) {
            Remove-Item $ZipPath -Force
        }
        
        [System.IO.Compression.ZipFile]::CreateFromDirectory($PluginTempDir, $ZipPath, [System.IO.Compression.CompressionLevel]::Optimal, $true)
        
        # Vérifier la taille du ZIP
        $zipSize = (Get-Item $ZipPath).Length
        $zipSizeKB = [math]::Round($zipSize / 1KB, 2)
        
        Write-Host "✅ ZIP créé avec succès!" -ForegroundColor Green
        Write-Host "   Chemin: $ZipPath" -ForegroundColor Gray
        Write-Host "   Taille: $zipSizeKB KB" -ForegroundColor Gray
        
        return $true
        
    } catch {
        Write-Host "❌ Erreur lors de la création du ZIP: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    } finally {
        # Nettoyer le répertoire temporaire
        if (Test-Path $TempDir) {
            Remove-Item $TempDir -Recurse -Force -ErrorAction SilentlyContinue
        }
    }
}

# ============================================================================
# EXECUTION PRINCIPALE
# ============================================================================

Write-Host "`n🔨 CONSTRUCTION DU ZIP DES MISES À JOUR PDF BUILDER PRO`n" -ForegroundColor Cyan

# Extraire la version du plugin
$PluginFile = Join-Path $PluginDir "pdf-builder-pro.php"
$Version = Get-PluginVersion $PluginFile

if (!$Version) {
    Write-Host "❌ Impossible de déterminer la version du plugin" -ForegroundColor Red
    exit 1
}

# Construire le ZIP
$success = Build-PluginZip -PluginDir $PluginDir -OutputDir $DistDir -Version $Version

if ($success) {
    Write-Host "`n📤 Pour uploader vers EDD:" -ForegroundColor Cyan
    Write-Host "   1. Aller vers: https://hub.threeaxe.fr/wp-admin/edit.php?post_type=download&page=edd-settings&tab=extensions" -ForegroundColor Gray
    Write-Host "   2. Éditer le produit 'PDF Builder Pro' (ID: 19)" -ForegroundColor Gray
    Write-Host "   3. Uploader le fichier: $(Join-Path $DistDir "pdf-builder-pro-$Version.zip")" -ForegroundColor Gray
    Write-Host "   4. Définir la version: $Version" -ForegroundColor Gray
    Write-Host "   5. Sauvegarder" -ForegroundColor Gray
    
    Write-Host "`n✅ Construction terminée avec succès!" -ForegroundColor Green
    exit 0
} else {
    Write-Host "`n❌ Erreur lors de la construction" -ForegroundColor Red
    exit 1
}
