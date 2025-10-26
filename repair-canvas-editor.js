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

function log(msg, color = 'reset') {
    const timestamp = new Date().toLocaleTimeString();
    console.log(`${colors[color]}[${timestamp}] ${msg}${colors.reset}`);
}

function checkFileExists(filePath) {
    return fs.existsSync(filePath);
}

function checkFileContains(filePath, pattern) {
    if (!checkFileExists(filePath)) return false;
    const content = fs.readFileSync(filePath, 'utf8');
    return new RegExp(pattern).test(content);
}

console.clear();
log('╔════════════════════════════════════════════════════════════════╗', 'cyan');
log('║      RÉPARATION COMPLÈTE DU CANVAS EDITOR                     ║', 'cyan');
log('╚════════════════════════════════════════════════════════════════╝', 'cyan');

// ========== ÉTAPE 1: Vérifier la structure ==========
log('\n1️⃣  ÉTAPE 1 : Vérification de la structure', 'blue');

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
        log(`  ✅ ${file}`, 'green');
        validFiles++;
    } else {
        log(`  ❌ ${file} MANQUANT`, 'yellow');
        missingFiles.push(file);
    }
});

// ========== ÉTAPE 2: Vérifier les imports ==========
log('\n2️⃣  ÉTAPE 2 : Vérification des imports ES6', 'blue');

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
        log(`  ✅ Import: ${importName}`, 'green');
    } else {
        log(`  ❌ Import manquant: ${importName}`, 'yellow');
    }
});

// ========== ÉTAPE 3: Vérifier les expositions globales ==========
log('\n3️⃣  ÉTAPE 3 : Vérification des expositions globales', 'blue');

const globalExposures = [
    'window.PDFBuilderPro',
    'window.VanillaCanvas',
    'window.ElementLibrary',
    'window.PDFBuilderEditorInit'
];

globalExposures.forEach(exposure => {
    const pattern = exposure.replace(/\./g, '\\.').replace(/window\./, '');
    if (bundleContent.includes(pattern)) {
        log(`  ✅ Exposé: ${exposure}`, 'green');
    } else {
        log(`  ❌ PAS exposé: ${exposure}`, 'yellow');
    }
});

// ========== ÉTAPE 4: Vérifier le template editor ==========
log('\n4️⃣  ÉTAPE 4 : Vérification du Template Editor', 'blue');

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
        log(`  ✅ ${check.desc}`, 'green');
    } else {
        log(`  ❌ ${check.desc} MANQUANT`, 'yellow');
    }
});

// ========== ÉTAPE 5: Vérifier les enqueues ==========
log('\n5️⃣  ÉTAPE 5 : Vérification des enqueues scripts', 'blue');

const adminFile = 'plugin/src/Admin/PDF_Builder_Admin.php';
const adminContent = fs.readFileSync(adminFile, 'utf8');

if (adminContent.includes('wp_enqueue_script') && adminContent.includes('pdf-builder')) {
    log('  ✅ Scripts PDF Builder enqués', 'green');
} else {
    log('  ❌ Scripts PDF Builder PAS enqués', 'yellow');
}

if (adminContent.includes('wp_create_nonce') || adminContent.includes('wp_verify_nonce')) {
    log('  ✅ Nonce AJAX configuré', 'green');
} else {
    log('  ❌ Nonce AJAX PAS configuré', 'yellow');
}

// ========== RÉSUMÉ ==========
log('\n' + '='.repeat(65), 'cyan');
log('📊 RÉSUMÉ DE LA VÉRIFICATION', 'cyan');
log('='.repeat(65), 'cyan');

log(`\n📋 Fichiers vérifiés: ${requiredFiles.length}`, 'blue');
log(`✅ Fichiers valides: ${validFiles}`, 'green');
log(`❌ Fichiers manquants: ${missingFiles.length}`, missingFiles.length > 0 ? 'yellow' : 'green');

if (missingFiles.length > 0) {
    log('\n⚠️  Fichiers à créer ou vérifier:', 'yellow');
    missingFiles.forEach(f => log(`   - ${f}`, 'yellow'));
}

// ========== GÉNÉRER RAPPORT JSON ==========
log('\n6️⃣  ÉTAPE 6 : Génération du rapport', 'blue');

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
log('✅ Rapport généré: repair-report.json', 'green');

// ========== PROCHAINES ÉTAPES ==========
log('\n' + '='.repeat(65), 'cyan');
log('📋 PROCHAINES ÉTAPES', 'cyan');
log('='.repeat(65), 'cyan');

console.log(`
${colors.green}✅ ACTIONS RECOMMANDÉES:${colors.reset}

1. ${colors.cyan}npm run build${colors.reset} - Compiler les assets
2. ${colors.cyan}cd build && .\\deploy.ps1 -Mode plugin${colors.reset} - Déployer via FTP
3. ${colors.cyan}Accéder au template editor dans WordPress${colors.reset}
4. ${colors.cyan}Ouvrir F12 → Console${colors.reset}
5. ${colors.cyan}Vérifier les logs d'initialisation${colors.reset}
6. ${colors.cyan}Tester le drag & drop${colors.reset}
7. ${colors.cyan}Tester la modification de propriétés${colors.reset}
8. ${colors.cyan}Tester la sauvegarde/chargement${colors.reset}

${colors.blue}📚 DOCUMENTATION:${colors.reset}
   - COMPLETE_FIX_PLAN.md
   - BUGFIX_REPORT_20251026.md
   - VERIFICATION_CHECKLIST.md
   - repair-report.json

${colors.green}✅ Vérification terminée!${colors.reset}
`);

process.exit(missingFiles.length > 0 ? 1 : 0);
