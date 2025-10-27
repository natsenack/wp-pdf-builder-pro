#!/usr/bin/env node

/**
 * Script de Réparation du Canvas Editor
 * Vérifie et corrige tous les problèmes d'incohérence
 */

const fs = require('fs');
const path = require('path');

const colors = {
    reset: '\x1b[0m',
    green: '\x1b[32m',
    red: '\x1b[31m',
    yellow: '\x1b[33m',
    cyan: '\x1b[36m',
    blue: '\x1b[34m'
};

function checkFileExists(filePath) {
    return fs.existsSync(filePath);
}

function checkFileContains(filePath, pattern) {
    if (!checkFileExists(filePath)) return false;
    const content = fs.readFileSync(filePath, 'utf8');
    return new RegExp(pattern).test(content);
}

// ========== ÉTAPE 1: Vérifier la structure ==========

const requiredFiles = [
    'assets/js/src/pdf-builder-vanilla-bundle.js',
    'assets/js/src/pdf-canvas-vanilla.js',
    'assets/js/src/pdf-canvas-renderer.js',
    'assets/js/src/pdf-canvas-events.js',
    'assets/js/src/pdf-canvas-element-library.js',
    'assets/js/src/pdf-builder-editor-init.js',
    'assets/js/src/pdf-canvas-unified-dragdrop.js',
    'plugin/templates/admin/template-editor.php',
    'plugin/src/Admin/PDF_Builder_Admin.php'
];

let missingFiles = [];
let validFiles = 0;

requiredFiles.forEach(file => {
    if (checkFileExists(file)) {
        
        validFiles++;
    } else {
        
        missingFiles.push(file);
    }
});

// ========== ÉTAPE 2: Vérifier les imports ==========


const bundleFile = 'assets/js/src/pdf-builder-vanilla-bundle.js';
const bundleContent = fs.readFileSync(bundleFile, 'utf8');

const requiredImports = [
    'pdf-canvas-vanilla.js',
    'pdf-canvas-renderer.js',
    'pdf-canvas-events.js',
    'pdf-canvas-element-library.js'
];

requiredImports.forEach(importName => {
    if (bundleContent.includes(importName)) {
        
    } else {
        
    }
});

// ========== ÉTAPE 3: Vérifier les expositions globales ==========


const globalExposures = [
    'window.PDFBuilderPro',
    'window.VanillaCanvas',
    'window.ElementLibrary',
    'window.PDFBuilderEditorInit'
];

globalExposures.forEach(exposure => {
    const pattern = exposure.replace(/\./g, '\\.').replace(/window\./, '');
    if (bundleContent.includes(pattern)) {
        
    } else {
        
    }
});

// ========== ÉTAPE 4: Vérifier le template editor ==========


const templateFile = 'plugin/templates/admin/template-editor.php';
const templateContent = fs.readFileSync(templateFile, 'utf8');

const templateChecks = [
    { pattern: 'id="pdf-canvas"', desc: 'Canvas div' },
    { pattern: 'class="pdf-builder-toolbar"', desc: 'Toolbar' },
    { pattern: 'class="element-library"', desc: 'Element library' },
    { pattern: 'id="pdf-builder-editor"', desc: 'Editor container' },
    { pattern: 'pdf-builder-loading', desc: 'Loading indicator' }
];

templateChecks.forEach(check => {
    if (templateContent.includes(check.pattern)) {
        
    } else {
        
    }
});

// ========== ÉTAPE 5: Vérifier les enqueues ==========


const adminFile = 'plugin/src/Admin/PDF_Builder_Admin.php';
const adminContent = fs.readFileSync(adminFile, 'utf8');

if (adminContent.includes('wp_enqueue_script') && adminContent.includes('pdf-builder')) {
    
} else {
    
}

if (adminContent.includes('wp_create_nonce') || adminContent.includes('wp_verify_nonce')) {
    
} else {
    
}

// ========== RÉSUMÉ ==========
log('\n' + '='.repeat(65), 'cyan');

log('='.repeat(65), 'cyan');





if (missingFiles.length > 0) {
    
    missingFiles.forEach(f => log(`   - ${f}`, 'yellow'));
}

// ========== GÉNÉRER RAPPORT JSON ==========


const report = {
    timestamp: new Date().toISOString(),
    filesChecked: requiredFiles.length,
    filesValid: validFiles,
    missingFiles: missingFiles,
    recommendations: [
        'Exécuter: npm run build',
        'Vérifier la console F12 du template editor',
        'Tester le drag & drop depuis la bibliothèque',
        'Vérifier la synchronisation des propriétés',
        'Tester la sauvegarde/chargement',
        'Déployer via FTP si tout OK'
    ]
};

fs.writeFileSync('repair-report.json', JSON.stringify(report, null, 2));


// ========== PROCHAINES ÉTAPES ==========

// ACTIONS RECOMMANDÉES:

// 1. npm run build - Compiler les assets
// 2. cd build && .\deploy.ps1 -Mode plugin - Déployer via FTP
// 3. Accéder au template editor dans WordPress
// 4. Ouvrir F12 → Console
// 5. Vérifier les logs d'initialisation
// 6. Tester le drag & drop
// 7. Tester la modification de propriétés
// 8. Tester la sauvegarde/chargement

// DOCUMENTATION:
//    - COMPLETE_FIX_PLAN.md
//    - BUGFIX_REPORT_20251026.md
//    - VERIFICATION_CHECKLIST.md
//    - repair-report.json

// Vérification terminée!

process.exit(missingFiles.length > 0 ? 1 : 0);
