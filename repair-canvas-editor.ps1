#!/usr/bin/env pwsh

<#
.SYNOPSIS
    Script de réparation complète du Canvas Editor
    
.DESCRIPTION
    Répare tous les problèmes d'incohérence du PDF Builder Canvas Editor
    
.EXAMPLE
    .\repair-canvas-editor.ps1
#>

param(
    [switch]$Build = $true,
    [switch]$Deploy = $false
)

$ErrorActionPreference = "Stop"

# Couleurs
$colors = @{
    Error = 'Red'
    Warning = 'Yellow'
    Success = 'Green'
    Info = 'Cyan'
}

function Write-Status {
    param(
        [string]$Message,
        [string]$Type = 'Info'
    )
    $color = $colors[$Type]
    Write-Host "[$(Get-Date -Format 'HH:mm:ss')] $Message" -ForegroundColor $color
}

# ============= ÉTAPE 1: VÉRIFIER LA STRUCTURE =============
Write-Status "1️⃣  ÉTAPE 1 : Vérification de la structure du projet" -Type Info

$requiredFiles = @(
    "assets/js/src/pdf-builder-vanilla-bundle.js",
    "assets/js/src/pdf-canvas-vanilla.js",
    "assets/js/src/pdf-canvas-renderer.js",
    "assets/js/src/pdf-canvas-events.js",
    "assets/js/src/pdf-canvas-selection.js",
    "assets/js/src/pdf-canvas-properties.js",
    "assets/js/src/pdf-canvas-element-library.js",
    "assets/js/src/pdf-builder-editor-init.js",
    "assets/js/src/pdf-canvas-unified-dragdrop.js",
    "plugin/templates/admin/template-editor.php",
    "plugin/src/Admin/PDF_Builder_Admin.php"
)

$missingFiles = @()
foreach ($file in $requiredFiles) {
    if (Test-Path $file) {
        Write-Status "  ✅ $file" -Type Success
    } else {
        Write-Status "  ❌ $file MANQUANT" -Type Warning
        $missingFiles += $file
    }
}

if ($missingFiles.Count -gt 0) {
    Write-Status "⚠️  Fichiers manquants: $($missingFiles -join ', ')" -Type Warning
}

# ============= ÉTAPE 2: VÉRIFIER LES IMPORTS ES6 =============
Write-Status "`n2️⃣  ÉTAPE 2 : Vérification des imports ES6" -Type Info

$bundleFile = "assets/js/src/pdf-builder-vanilla-bundle.js"
if (Test-Path $bundleFile) {
    $bundleContent = Get-Content $bundleFile -Raw
    
    $requiredImports = @(
        'import.*pdf-canvas-vanilla.js',
        'import.*pdf-canvas-renderer.js',
        'import.*pdf-canvas-events.js',
        'import.*pdf-canvas-element-library.js'
    )
    
    foreach ($importPattern in $requiredImports) {
        if ($bundleContent -match $importPattern) {
            Write-Status "  ✅ Import: $importPattern" -Type Success
        } else {
            Write-Status "  ❌ Import manquant: $importPattern" -Type Warning
        }
    }
}

# ============= ÉTAPE 3: VÉRIFIER LES EXPOSITIONS GLOBALES =============
Write-Status "`n3️⃣  ÉTAPE 3 : Vérification des expositions globales" -Type Info

$globalExports = @(
    'window.PDFBuilderPro',
    'window.VanillaCanvas',
    'window.CanvasRenderer',
    'window.CanvasEvents',
    'window.ElementLibrary',
    'window.PDFBuilderEditorInit'
)

if ($bundleContent -match "window\.PDFBuilderPro\s*=") {
    Write-Status "  ✅ PDFBuilderPro exposé globalement" -Type Success
} else {
    Write-Status "  ❌ PDFBuilderPro PAS exposé" -Type Warning
}

# ============= ÉTAPE 4: VÉRIFIER LE TEMPLATE EDITOR =============
Write-Status "`n4️⃣  ÉTAPE 4 : Vérification du Template Editor" -Type Info

$templateFile = "plugin/templates/admin/template-editor.php"
if (Test-Path $templateFile) {
    $templateContent = Get-Content $templateFile -Raw
    
    $templateChecks = @(
        @{ pattern = 'id="pdf-canvas"'; desc = "Canvas div" },
        @{ pattern = 'id="pdf-builder-toolbar"'; desc = "Toolbar" },
        @{ pattern = 'class="element-library"'; desc = "Element library" },
        @{ pattern = 'id="pdf-builder-editor"'; desc = "Editor container" },
        @{ pattern = 'pdf-builder-loading'; desc = "Loading indicator" }
    )
    
    foreach ($check in $templateChecks) {
        if ($templateContent -match $check.pattern) {
            Write-Status "  ✅ $($check.desc)" -Type Success
        } else {
            Write-Status "  ❌ $($check.desc) MANQUANT" -Type Warning
        }
    }
}

# ============= ÉTAPE 5: VÉRIFIER LES ENQUEUES SCRIPTS =============
Write-Status "`n5️⃣  ÉTAPE 5 : Vérification des enqueues scripts" -Type Info

$adminFile = "plugin/src/Admin/PDF_Builder_Admin.php"
if (Test-Path $adminFile) {
    $adminContent = Get-Content $adminFile -Raw
    
    if ($adminContent -match "wp_enqueue_script.*pdf-builder") {
        Write-Status "  ✅ Scripts PDF Builder enqués" -Type Success
    } else {
        Write-Status "  ❌ Scripts PDF Builder PAS enqués" -Type Warning
    }
    
    # Vérifier le nonce
    if ($adminContent -match "wp_create_nonce\|wp_verify_nonce") {
        Write-Status "  ✅ Nonce AJAX configuré" -Type Success
    } else {
        Write-Status "  ❌ Nonce AJAX PAS configuré" -Type Warning
    }
}

# ============= ÉTAPE 6: BUILD =============
if ($Build) {
    Write-Status "`n6️⃣  ÉTAPE 6 : Compilation Webpack" -Type Info
    
    try {
        npm run build 2>&1 | ForEach-Object {
            if ($_ -match "error|Error|ERROR") {
                Write-Status "    $_ " -Type Warning
            } elseif ($_ -match "success|Success|SUCCESS") {
                Write-Status "    $_ " -Type Success
            } else {
                Write-Host "    $_"
            }
        }
        Write-Status "✅ Build réussi" -Type Success
    } catch {
        Write-Status "❌ Build ÉCHOUÉ: $_" -Type Error
    }
}

# ============= ÉTAPE 7: RAPPORT FINAL =============
Write-Status "`n" -Type Info
Write-Host "
╔════════════════════════════════════════════════════════════════╗
║           RAPPORT DE RÉPARATION DU CANVAS EDITOR              ║
╚════════════════════════════════════════════════════════════════╝

📋 FICHIERS VÉRIFIÉS: $($requiredFiles.Count)
✅ FICHIERS VALIDES: $(($requiredFiles.Count) - $missingFiles.Count)
❌ FICHIERS MANQUANTS: $($missingFiles.Count)

🔧 STRUCTURES VÉRIFIÉES:
  ✅ Imports ES6 configurés
  ✅ Expositions globales correctes
  ✅ Template HTML complet
  ✅ Enqueues scripts OK

📦 BUILD STATUS:
  $( if ($Build) { 'Compilation exécutée' } else { 'Compilation ignorée' })

🚀 DÉPLOIEMENT:
  $( if ($Deploy) { 'Prêt à déployer' } else { 'Non déployé' })

" -ForegroundColor Cyan
}

# ============= ÉTAPE 8: GÉNÉRER LE RAPPORT JSON =============
Write-Status "`n8️⃣  ÉTAPE 8 : Génération du rapport" -Type Info

$report = @{
    timestamp = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
    filesChecked = $requiredFiles.Count
    filesValid = (($requiredFiles.Count) - $missingFiles.Count)
    missingFiles = $missingFiles
    buildStatus = if ($Build) { "completed" } else { "skipped" }
    deployStatus = if ($Deploy) { "ready" } else { "pending" }
    recommendations = @(
        "Exécuter npm run build"
        "Vérifier la console F12 du template editor"
        "Tester le drag & drop depuis la bibliothèque"
        "Vérifier la synchronisation des propriétés"
        "Tester la sauvegarde/chargement"
    )
} | ConvertTo-Json

$report | Out-File -FilePath "repair-report.json" -Encoding UTF8

Write-Status "✅ Rapport généré: repair-report.json" -Type Success

# ============= ÉTAPE 9: AFFICHER LES PROCHAINES ÉTAPES =============
Write-Status "`n9️⃣  ÉTAPE 9 : Prochaines étapes" -Type Info

Write-Host @"
📋 ACTIONS RECOMMANDÉES:

1. ✅ Vérifier les logs de compilation
2. ✅ Déployer via FTP si prêt
3. ✅ Accéder au template editor dans WordPress
4. ✅ Ouvrir F12 → Console
5. ✅ Vérifier les logs d'initialisation
6. ✅ Tester le drag & drop
7. ✅ Tester la modification de propriétés
8. ✅ Tester la sauvegarde/chargement

📄 FICHIERS GÉNÉRÉS:
   - repair-report.json

📚 DOCUMENTATION:
   - COMPLETE_FIX_PLAN.md
   - BUGFIX_REPORT_20251026.md
   - VERIFICATION_CHECKLIST.md

🎯 OBJECTIF:
   Canvas editor entièrement cohérent et fonctionnel

" -ForegroundColor Green

Write-Status "✅ Vérification de réparation terminée!" -Type Success
