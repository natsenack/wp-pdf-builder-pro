#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Script de nettoyage du plugin PDF Builder Pro
.DESCRIPTION
    Supprime les fichiers temporaires, de diagnostic et de développement
#>

param(
    [switch]$Force,
    [switch]$DryRun
)

Write-Host "🧹 Nettoyage du plugin PDF Builder Pro" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan

$rootPath = Split-Path -Parent $PSScriptRoot
$filesToDelete = @(
    # Fichiers de diagnostic
    "resources/js/diagnostics",
    "analyze-properties.js",
    "debug-ui-detection.js",
    "diagnostic-complete.js",
    "diagnostic-corrected.js",
    "fix_tableau.js",
    "fix_tableau.py",
    "tableau_fix.py",

    # Fichiers de démonstration
    "demo-tableaux*.html",

    # Outils temporaires
    "check_template.php",
    "quick-ftp-upload.ps1",
    "tools/pdf-screenshot.js",
    "tools/validate-existing-templates.php",
    "tests/DIAGNOSTIC_RENDERERS.js",

    # Utilitaires temporaires
    "resources/js/force-execute.js",
    "resources/js/force-include.js",
    "resources/js/globalFallback.js",
    "resources/js/react-global.js",

    # Dossiers vides
    ".github/instructions",

    # Rapports de développement
    "docs/reports/phase*.json",
    "docs/reports/phase*.js"
)

$totalFiles = 0
$totalSize = 0

foreach ($file in $filesToDelete) {
    $fullPath = Join-Path $rootPath $file

    # Gestion des wildcards
    if ($file -like "*`**") {
        $matchingFiles = Get-ChildItem -Path $rootPath -Filter $file -Recurse -ErrorAction SilentlyContinue
        foreach ($matchingFile in $matchingFiles) {
            if ($DryRun) {
                Write-Host "❌ Supprimerait: $($matchingFile.FullName)" -ForegroundColor Yellow
            } else {
                if ($Force -or $PSCmdlet.ShouldContinue("Supprimer '$($matchingFile.Name)' ?", "Confirmation")) {
                    try {
                        Remove-Item $matchingFile.FullName -Force -Recurse
                        Write-Host "✅ Supprimé: $($matchingFile.Name)" -ForegroundColor Green
                        $totalFiles++
                        if ($matchingFile.PSIsContainer) {
                            $size = (Get-ChildItem $matchingFile.FullName -Recurse -File -ErrorAction SilentlyContinue | Measure-Object -Property Length -Sum).Sum
                        } else {
                            $size = $matchingFile.Length
                        }
                        $totalSize += $size
                    } catch {
                        Write-Host "❌ Erreur lors de la suppression de $($matchingFile.Name): $($_.Exception.Message)" -ForegroundColor Red
                    }
                }
            }
        }
    } else {
        if (Test-Path $fullPath) {
            if ($DryRun) {
                Write-Host "❌ Supprimerait: $fullPath" -ForegroundColor Yellow
            } else {
                if ($Force -or $PSCmdlet.ShouldContinue("Supprimer '$file' ?", "Confirmation")) {
                    try {
                        Remove-Item $fullPath -Force -Recurse
                        Write-Host "✅ Supprimé: $file" -ForegroundColor Green
                        $totalFiles++
                        if (Test-Path $fullPath -PathType Container) {
                            $size = (Get-ChildItem $fullPath -Recurse -File -ErrorAction SilentlyContinue | Measure-Object -Property Length -Sum).Sum
                        } else {
                            $size = (Get-Item $fullPath).Length
                        }
                        $totalSize += $size
                    } catch {
                        Write-Host "❌ Erreur lors de la suppression de $file : $($_.Exception.Message)" -ForegroundColor Red
                    }
                }
            }
        }
    }
}

if ($DryRun) {
    Write-Host "`n📋 Mode simulation terminé" -ForegroundColor Yellow
} else {
    Write-Host "`n🎉 Nettoyage terminé !" -ForegroundColor Green
    Write-Host "📊 Statistiques:" -ForegroundColor Cyan
    Write-Host "   • Fichiers supprimés: $totalFiles" -ForegroundColor White
    Write-Host ("   • Espace libéré: {0:N2} MB" -f ($totalSize / 1MB)) -ForegroundColor White
}

Write-Host "`n💡 Conseils :" -ForegroundColor Cyan
Write-Host "   • Utilisez -DryRun pour prévisualiser les suppressions" -ForegroundColor White
Write-Host "   • Utilisez -Force pour ignorer les confirmations" -ForegroundColor White
Write-Host "   • Vérifiez le .gitignore pour éviter de commiter des fichiers temporaires" -ForegroundColor White